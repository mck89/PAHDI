<?php
/**
 * This file is part of the PAHDI (PHP Advanced HTML Dom Implementation)
 * library, for the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @category    Utilities
 * @package     PAHDI-Utilities
 * @author      Marco Marchiò
 * @license     http://opensource.org/licenses/bsd-license.php The BSD License
 * @copyright	Copyright (c) 2011, Marco Marchiò
 */
 
/**
 * Path analizer
 *
 * @category    	Utilities
 * @package     	PAHDI-Utilities
 */
class PAHDIPath
{
	/**
	 * Checks if the given string is an url
	 *
	 * @param	string	$path	String to check
	 * @return	bool	True if the given string is a url,
	 *					otherwise false
	 * @static
	 */
	static function isURL ($path)
	{
		return (bool) preg_match("#^\w{2,}://(?:[^/]|$)#", $path);
	}
	
	/**
	 * Checks if the given string is a data uri
	 *
	 * @param	string	$path	String to check
	 * @return	bool	True if the given string is a data
	 *					uri, otherwise false
	 * @static
	 */
	static function isDataURI ($path)
	{
		return strpos($path, "data:") === 0;
	}
	
	/**
	 * Checks if the given path or url is absolute
	 *
	 * @param	string	$path	URL or path
	 * @return	bool	True if the given path or url is
	 *					absolute
	 * @static
	 */
	static function isAbsolute ($path)
	{
		if (!$path) {
			return false;
		}
		
		if (self::isDataURI($path)) {
			return false;
		} elseif (self::isURL($path)) {
			$url = self::getURLInstance($path);
			return $url->isAbsolute();
		}
		
		static $win;
		
		$path = self::sanitizePath($path);
		
		if (!isset($win)) {
			$win = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
		}
		
		if ($win) {
			return preg_match("#^\w:\\\\#", $path);
		} else {
			return $path[0] === "/" || $path[0] === "\\";
		}
	}
	
	/**
	 * Resolves a path relative to and absolute url or path
	 *
	 * @param	string	$abs	Absolute path or url
	 * @param	string	$rel	Relative path to resolve
	 * @return	string	Resolved path
	 * @static
	 */
	static function resolve ($abs, $rel)
	{		
		if (self::isAbsolute($rel)) {
			return $rel;
		} elseif (!self::isAbsolute($abs)) {
			return false;
		} elseif (self::isDataURI($abs) ||
				self::isDataURI($rel)) {
			return false;
		}
		
		if (self::isURL($abs)) {
			$url = self::getURLInstance($abs);
			$path = $url->path;
			if (!$path) {
				$url->path = "/";
			} else {
				//Handle the common case where the url does not
				//finish with a slash but the last part is a dir
				//and not a file, so if the last part of the URL
				//path does not contain a dot add a slahs and
				//consider it as a folder
				$parts = explode("/", $path);
				$last = $parts[count($parts) - 1];
				if (strpos($last, ".") === false) {
					$url->path = implode("/", $parts) . "/";
				}
			}
			$res = $url->resolve($rel);
			return $res ? $res->getURL() : false;
		}
		
		$abs = self::sanitizePath($abs);
		
		$cwd = getcwd();
		if (!chdir(dirname($abs))) {
			return false;
		}
		$resolved = realpath($rel);
		chdir($cwd);
		return $resolved;
	}
	
	/**
	 * Returns the url class for the given url
	 *
	 * @param	string		$url	URL
	 * @return	PAHDIUrl		URL class instance
	 * @static
	 */
	static function getURLInstance ($url)
	{
		return new PAHDIUrl($url);
	}
	
	/**
	 * Sanitizes the given path
	 *
	 * @param	string	$path	Path
	 * @return	string	Sanitized path
	 * @static
	 */
	static function sanitizePath ($path)
	{
		return preg_replace("#^file:/+#", "", $path);
	}
	
	/**
	 * Get data uri parts
	 *
	 * @param	string	$path	Path
	 * @return	array	An array with the parts of the
	 *					data uri. The indexes are:
	 *					- "mime": data mime type
	 *					- "encoding": data encoding
	 *					- "base64": true if the data is
	 *								base 64 encoded
	 *								otherwise false
	 *					- "data": encoded data
	 * @static
	 */
	static function getDataURIParts ($path)
	{
		$reg = "^data:([^/]+/[^;\,]+)(?:;charset=([^,;]+))?(;base64)?,(.*)";
		if (!preg_match("#$reg#", $path, $match)) {
			return false;
		}
		$ret = array(
			"mime" => $match[1],
			"charset" => $match[2] ? $match[2] : null,
			"base64" => (bool) $match[3],
			"data" => $match[4]
		);
		if ($ret["base64"]) {
			$ret["data"] = base64_decode($ret["data"]);
		}
		return $ret;
	}
}