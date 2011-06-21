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
 * DOM HTML Field set element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	string			$type		Element's type
 * @property-read	HTMLCollection	$elements	Form elements inside the current
 *												element
 * @property		string			$name		Element's name
 * @property		bool			$disabled	Element's disable state
 */
class HTMLFieldSetElement extends HTMLElement
{
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
			case "name":
				return $this->_getProperty($name);
			break;
			case "disabled":
				return $this->_getProperty($name, "bool");
			break;
			case "type":
				return "fieldset";
			break;
			case "elements":
				$search = new PAHDISearch($this);
				$fn = function ($node) {
					return in_array($node->tagName, ParserHTML::$formAssociated);
				};
				return $search->find($fn)->toHTMLCollection();
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
			case "name":
				$this->_setProperty($name, $value);
			break;
			case "disabled":
				$this->_setProperty($name, $value, "bool");
			break;
			case "type":
			break;
			case "elements":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}