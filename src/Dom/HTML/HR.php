<?php
/**
 * This file is part of the PAHDI (PHP Advanced HTML Dom Implementation)
 * library, for the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @category    PAHDI
 * @package     PAHDI-DOM
 * @author      Marco Marchi�
 * @license     http://opensource.org/licenses/bsd-license.php The BSD License
 * @copyright	Copyright (c) 2011, Marco Marchi�
 */
 
/**
 * DOM HTML HR element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$align		Element's alignment
 * @property		string		$width		Element's width
 * @property		string		$size		Element's size
 * @property		string		$color		Element's color
 * @property		bool		$noShade	Element's noshade state
 */
class HTMLHRElement extends HTMLElement
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
			case "align":
			case "size":
				return $this->_getProperty($name);
			break;
			case "color":
				return $this->_getProperty($name, "color");
			break;
			case "width":
				return $this->_getProperty($name, "intperc");
			break;
			case "noShade":
				return $this->_getProperty($name, "bool");
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
			case "align":
			case "size":
				$this->_setProperty($name, $value);
			break;
			case "color":
				$this->_setProperty($name, $value, "color");
			break;
			case "width":
				$this->_setProperty($name, $value, "intperc");
			break;
			case "noShade":
				$this->_getProperty($name, $value, "bool");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}