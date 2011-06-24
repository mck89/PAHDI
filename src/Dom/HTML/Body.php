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
 * DOM HTML Body element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$background		Element's background image
 * @property		string		$bgColor		Element's background color
 * @property		string		$link			Unvisited links color
 * @property		string		$vLink			Visited links color
 * @property		string		$aLink			Active links color
 * @property		string		$text			Text color
 */
class HTMLBodyElement extends HTMLElement
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
			case "background":
				return $this->_getProperty($name, "path");
			break;
			case "bgColor":
			case "link":
			case "vLink":
			case "aLink":
			case "text":
				return $this->_getProperty($name, "color");
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
			case "background":
				$this->_setProperty($name, $value);
			break;
			case "bgColor":
			case "link":
			case "vLink":
			case "aLink":
			case "text":
				$this->_setProperty($name, $value, "bool");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}