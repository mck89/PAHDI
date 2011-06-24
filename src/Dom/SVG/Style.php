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
 * DOM SVG Style element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$type		Element's type
 * @property		string		$media		Element's media
 * @property		string		$title		Element's title
 */
class SVGStyleElement extends SVGElement
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
			case "type":
			case "media":
			case "title":
				return $this->_getProperty($name);
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
			case "title":
				$this->_setProperty($name, $value);
			break;			
			default:
				parent::__set($name, $value);
			break;
		}
	}
}