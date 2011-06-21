<?php
/**
 * This file is part of the PAHDI (PHP Advanced HTML Dom Implementation)
 * library, for the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @category    PAHDI
 * @package     PAHDI-DOM
 * @author      Marco Marchiò
 * @license     http://opensource.org/licenses/bsd-license.php The BSD License
 * @copyright	Copyright (c) 2011, Marco Marchiò
 */
 
/**
 * DOM CSS import rule
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	string			$href		Rule href
 * @property-read	CSSStyleSgeet	$styleSheet	Internal style sheet
 */
class CSSImportRule extends CSSRule
{
	/**
	 * Rule type
	 *
	 * @var			int
	 */
	public $type = 3;
	
	/**
	 * Rule href
	 *
	 * @var		string
	 * @access	protected
	 * @ignore
	 */
	protected $_href = "";
	
	/**
	 * Internal style sheet
	 *
	 * @var		CSSStyleSgeet
	 * @access	protected
	 * @ignore
	 */
	protected $_sheet;
	
	/**
	 * Rule media list
	 *
	 * @var		MediaList
	 */
	public $media;
	
	/**
	 * Class constructor.
	 *
	 * @param	string		$href		Rule href
	 */
	function __construct ($href)
	{
		$this->_href = $href;
		$this->media = new MediaList;
	}
	
	/**
	 * Provides a way to access some properties
	 *
	 * @param	string	$name	Property name
	 * @return	mixed	Property value
	 * @ignore
	 */
	function __get ($name)
	{
		switch ($name) {
			case "cssText":
				$ret = "@import \"{$this->_href}\"";
				$media = $this->media->mediaText;
				if ($media) {
					$ret .= " $media";
				}
				$ret .= ";";
				return $ret;
			break;
			case "href":
				$base = $this->parentStyleSheet->href;
				return PAHDIPath::resolve($base, $this->_href);
			break;
			case "styleSheet":
				if (!$this->_sheet) {
					$parser = new ParserCSS("", $this, $this->href);
					$this->_sheet = $parser->parse();
				}
				return $this->_sheet;
			break;
		}
		return null;
	}
	
	/**
	 * Provides a way to set some properties
	 *
	 * @param	string	$name	Property name
	 * @param	mixed	$value	Property value
	 * @return	void
	 * @ignore
	 */
	function __set ($name, $value)
	{
		switch ($name) {
			case "href":
			case "styleSheet":
				throw new DomException("Setting a property that has only a getter");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}