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
 * DOM HTML Applet element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$name		Element's name
 * @property		string		$alt		Element's alt text
 * @property		string		$align		Element's alignment
 * @property		string		$width		Element's width
 * @property		string		$height		Element's height
 * @property		string		$codeBase	Element's code base
 * @property		int			$hspace		Element's horizontal space
 * @property		int			$vspace		Element's vertical space
 * @property		string		$archive	Element's list of archives for resources
 * @property		string		$code		Name of an applet class file
 * @property		string		$object		URL of a serialized representation of an applet
 */
class HTMLAppletElement extends HTMLElement
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
			case "codeBase":
				return $this->_getProperty($name, "path");
			break;
			case "name":
			case "alt":
			case "align":
			case "archive":
			case "code":
			case "object":
				return $this->_getProperty($name);
			break;
			case "width":
			case "height":
				return $this->_getProperty($name, "intperc");
			break;
			case "hspace":
			case "vspace":
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
			case "name":
			case "alt":
			case "align":
			case "codeBase":
			case "archive":
			case "code":
			case "object":
				$this->_setProperty($name, $value);
			break;
			case "width":
			case "height":
				$this->_setProperty($name, $value, "intperc");
			break;
			case "hspace":
			case "vspace":
				$this->_getProperty($name, (int) $value, "int", 0);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}