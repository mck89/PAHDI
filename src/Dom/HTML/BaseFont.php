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
 * @copyright	Copyright (c) 2013, Marco Marchiò
 */
 
/**
 * DOM HTML Base font element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		int			$size		Element's font size
 * @property		string		$face		Element's font
 * @property		string		$color		Element's font color
 */
class HTMLBaseFontElement extends HTMLElement
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
			case "face":
				return $this->_getProperty($name);
			break;
			case "color":
				return $this->_getProperty($name, "color");
			break;
			case "size":
				return (int) $this->_getProperty($name, "int", - 1);
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
			case "face":
				$this->_setProperty($name, $value);
			break;
			case "color":
				$this->_setProperty($name, $value, "color");
			break;
			case "size":
				$this->_setProperty($name, (int) $value, "int", 0);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}