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
 * DOM HTML Style element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string			$type		Element's type
 * @property		bool			$disabled	Element's disable state
 * @property		string			$media		Element's media
 * @property-read	CSSStyleSheet	$sheet	Element's style sheet
 */
class HTMLStyleElement extends HTMLElement
{
	/**
	 * Stylesheet
	 *
	 * @var		CSSStyleSheet
	 * @ignore
	 */
	protected $_sheet;
	
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
			case "type":
			case "media":
				return $this->_getProperty($name);
			break;
			case "disabled":
				return $this->_getProperty($name, "bool");
			break;
			case "sheet":
				if (!$this->_sheet) {
					$type = $this->type;
					if (!$type || $type === "text/css") {
						$code = $this->textContent;
						$parser = new ParserCSS($code, $this);
						$this->_sheet = $parser->parse();
					}
				}
				return $this->_sheet;
			break;
			default:
				return parent::__get($name);
			break;
		}
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
			case "type":
			case "media":
				$this->_setProperty($name, $value);
				$this->_setProperty($name, $value);
				$this->_sheet = null;
			break;
			case "disabled":
				$this->_setProperty($name, $value, "bool");
			break;
			case "sheet":
				throw new DomException("Setting a property that has only a getter");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}