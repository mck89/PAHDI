<?php
/**
 * This file is part of the PAHDI (PHP Advanced HTML Dom Implementation)
 * library, for the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @category    PAHDI
 * @package     PAHDI
 * @author      Marco Marchiò
 * @license     http://opensource.org/licenses/bsd-license.php The BSD License
 * @copyright	Copyright (c) 2011, Marco Marchiò
 */
 
/**
 * PAHDI main class
 *
 * @category    	PAHDI
 * @package     	PAHDI
 */
class PAHDI
{
	/**
	 * Parse the source from a remote page and returns
	 * the document object
	 *
	 * @param	string			$url		Remote page url
	 * @param	array			$options	Options array. See
	 *										parseString for the
	 *										list of available
	 *										options
	 * @return	HTMLDocument	Resulting document object or null
	 *							on error
	 * @static
	 */
	static function parseRemoteSource ($url, $options = array())
	{
		$session = curl_init($url);
		$curlopt = 	isset($options["curlOptions"]) ?
					$options["curlOptions"] :
					array();
		//The request must return the body of the
		//response without headers
		$curlopt[CURLOPT_HEADER] = false;
		$curlopt[CURLOPT_RETURNTRANSFER] = true;
		foreach ($curlopt as $opt => $val) {
			curl_setopt($session, $opt, $val);
		}
		$html = curl_exec($session);
		if ($html === false) {
			return null;
		}
		curl_close($session);
		if (!isset($options["baseURL"]) || !$options["baseURL"]) {
			$options["baseURL"] = $url;
		}
		return self::parseString($html, $options);
	}
	
	/**
	 * Parse the source from a local file and returns
	 * the document object
	 *
	 * @param	string			$path		File path
	 * @param	array			$options	Options array. See
	 *										parseString for the
	 *										list of available
	 *										options
	 * @return	HTMLDocument	Resulting document object or null
	 *							on error
	 * @static
	 */
	static function parseLocalSource ($path, $options = array())
	{
		if (!is_readable($path)) {
			return null;
		}
		$html = file_get_contents($path);
		if ($html === false) {
			return null;
		}
		if (!isset($options["baseURL"]) || !$options["baseURL"]) {
			$options["baseURL"] = realpath($path);
		}
		return self::parseString($html, $options);
	}
	
	/**
	 * Parse the given code and returns the document object.
	 * The second argument is an optional array of parsing options
	 * in the key => value format. Available options are:
	 *
	 * - javascriptEnabled: Boolean that indicates if the code must be
	 *	 processed using rules for the javascript enabled or not. For
	 *	 example noscript tags content is ignored if this flag is true.
	 *	 Default: true
	 *
	 * - encoding: Output encoding. The parser will convert the code to
	 *	 this encoding. If null the original encoding of the document
	 *	 will be preserved. Default: null
	 *
	 * - cssPrefix: allowed css prefix (like "-moz-" for Firefox, or
	 *	 "-webkit-" for webkit browsers). Default: ""
	 *
	 * - baseURL: Document base url. For local and remote files this
	 *	 option is not required since it will use the file location
	 *	 as base url.
	 *
	 * - curlOptions: additional options to use for curl requests in
	 *	 key => value format.  This is useful only when parsing remote
	 *	 files.
	 *
	 * @param	string			$code		HTML Code
	 * @param	array			$options	Options array
	 * @return	HTMLDocument	Resulting document object or null
	 *							on error
	 * @static
	 */
	static function parseString ($code, $options = array())
	{
		//If the base path is not set use the current file path
		//otherwise if it's not an url get its absolute
		//version
		$base = isset($options["baseURL"]) ?
				$options["baseURL"] :
				"";
		if (!$base) {
			$base = __FILE__;
		} elseif (!PAHDIPath::isURL($base)) {
			$base = realpath($base);
		}
		//Get the output encoding if set
		$encoding = isset($options["encoding"]) ?
					$options["encoding"] :
					null;
		$parser = new ParserHTML($code, $encoding);
		//Set the js enabled flag
		$jsEnabled = isset($options["javascriptEnabled"]) ?
					 $options["javascriptEnabled"] :
					 true;
		$parser->scriptingFlag = $jsEnabled;
		//Parse the code
		$document = $parser->parse();
		//Set the document uri
		$document->documentURI = $base;
		//Use the href of the base tag in the source if it's
		//present otherwise use the given base path
		$document->baseURI = $parser->base ? $parser->base : $base;
		//Make sure that the css prefix starts and finishes with "-"
		if (isset($options["cssPrefix"]) && $options["cssPrefix"]) {
			$prefix = $options["cssPrefix"];
			if ($prefix[strlen($prefix) - 1] !== "-") {
				$prefix .= "-";
			}
			if ($prefix[0] !== "-") {
				$prefix = "-$prefix";
			}
			$options["cssPrefix"] = $prefix;
		}
		//Store the current instance on the created document
		$document->_implementation = $options;
		//Set the encodings
		$document->inputEncoding = $parser->codeEncoding;
		$document->characterSet = $parser->encoding;
		return $document;
	}
	
	/**
	 * Saves the given document into a file at the given path
	 *
	 * @param	HTMLDocument	$doc	Document to save
	 * @param	string			$path	Path
	 * @return	void
	 * @static
	 */
	static function saveDocument (HTMLDocument $doc, $path)
	{
		$html = ParserHTML::serialize($doc);
		if (!file_put_contents($path, $html)) {
			throw new DomException("Can't save the file");
		}
	}
	
	/**
	 * Classes autoloader
	 *
	 * @param	string	$class	Class name to load
	 * @return	void
	 * @static
	 */
	static function Autoload ($class)
	{	
		$ds = DIRECTORY_SEPARATOR;
		$currentPath = PAHDI_BASE . $ds;
		if (strpos($class, "PAHDI") === 0) {
			$filename = str_replace("PAHDI", "", $class);
			require_once $currentPath . "Utilities" . $ds . "$filename.php";
		}
		//HTMLFormElement => Dom/HTML/Form.php
		elseif (preg_match("#^(HTML|SVG)(\w+)Element$#", $class, $match)) {
			$file = $currentPath . "Dom" . $ds . $match[1] .
					$ds . $match[2] . ".php";
			if (file_exists($file)) {
				require_once $file;
			}
		} else {
			$parts = preg_match_all(
				"#[A-Z][a-z]+|[A-Z]{2,}(?![a-z])#",
				$class,
				$matches
			);
			$path = implode($ds, $matches[0]) . '.php';
			$check = array(
				//ParserHTML=>Parser/HTML.php
				$currentPath . $path,
				//HTMLDocument=>Dom/HTML/Document.php
				$currentPath . 'Dom' . $ds . $path,
				//Text=>Dom/Text.php
				$currentPath . 'Dom' . $ds . $class . '.php'
			);
			if (strpos($class, "CSS") === 0) {
				//CSSRule => Dom/CSS/Rule.php
				$file = substr($class, 3) . ".php";
				$check[] = $currentPath . "Dom" . $ds . "CSS" . $ds . $file;
			}
			foreach ($check as $c) {
				if (file_exists($c)) {
					require_once $c;
					break;
				}
			}
		}
	}
}

define("PAHDI_BASE", dirname(__FILE__));
spl_autoload_register(array("PAHDI", "Autoload"));