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
 * DOM HTML Table column element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$align		Element's alignment
 * @property		string		$width		Element's width
 * @property		string		$chOff		Element's offset of the alignment character.
 * @property		string		$ch			Element's alignment character for cells
 * @property		string		$vAlign		Element's vertical alignment
 * @property		int			$span		Element's span
 */
class HTMLTableColElement extends HTMLElement
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
				return $this->_getProperty($name, "", "left");
			break;
			case "vAlign":
				return $this->_getProperty($name, "", "middle");
			break;
			case "chOff":
				return $this->_getProperty($name);
			break;
			case "ch":
				return $this->_getProperty($name, "", ".");
			break;
			case "width":
				return $this->_getProperty($name, "intperc");
			break;
			case "span":
				return (int) $this->_getProperty($name, "int", 1);
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
			case "chOff":
			case "ch":
			case "vAlign":
				$this->_setProperty($name, $value);
			break;
			case "width":
				$this->_setProperty($name, $value, "intperc");
			break;
			case "span":
				$this->_setProperty($name, (int) $value, "int", - 1);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}