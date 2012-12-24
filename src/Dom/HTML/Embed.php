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
 * DOM HTML Embed element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$name		Element's name
 * @property		string		$src		Element's source
 * @property		string		$type		Element's type
 * @property		string		$align		Element's alignment
 * @property		string		$width		Element's width
 * @property		string		$height		Element's height
 */
class HTMLEmbedElement extends HTMLElement
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
			case "src":
				return $this->_getProperty($name, "path");
			break;
			case "name":
			case "type":
			case "align":
				return $this->_getProperty($name);
			break;
			case "width":
			case "height":
				return $this->_getProperty($name, "intperc");
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
			case "src":
			case "type":
			case "align":
				$this->_setProperty($name, $value);
			break;
			case "width":
			case "height":
				$this->_setProperty($name, $value, "intperc");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}