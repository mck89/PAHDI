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
 * DOM HTML Object element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$name		Element's name
 * @property		string		$type		Element's type
 * @property		string		$align		Element's alignment
 * @property		string		$useMap		Element's useMap
 * @property		string		$width		Element's width
 * @property		string		$height		Element's height
 * @property		string		$codeType	Element's code type
 * @property		string		$standby	Element's stand by message
 * @property		string		$codeBase	Element's code base
 * @property		int			$hspace		Element's horizontal space
 * @property		int			$vspace		Element's vertical space
 * @property		string		$border		Element's border width
 * @property		string		$archive	Element's list of archives for resources
 * @property		string		$code		Name of an applet class file
 * @property		string		$data		Element's resource address
 * @property		bool		$declare	Element's declare attribut
 */
class HTMLObjectElement extends HTMLElement
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
			case "data":
				return $this->_getProperty($name, "path");
			break;
			case "name":
			case "type":
			case "align":
			case "useMap":
			case "codeType":
			case "standby":
			case "archive":
			case "code":
				return $this->_getProperty($name);
			break;
			case "width":
			case "height":
			case "border":
				return $this->_getProperty($name, "intperc");
			break;
			case "declare":
				return $this->_getProperty($name, "bool");
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
	 * @return void
	 * @ignore
	 */
	function __set ($name, $value)
	{
		switch ($name) {
			case "name":
			case "type":
			case "align":
			case "useMap":
			case "codeType":
			case "standby":
			case "codeBase":
			case "code":
			case "archive":
			case "data":
				$this->_setProperty($name, $value);
			break;
			case "width":
			case "height":
			case "border":
				$this->_setProperty($name, $value, "intperc");
			break;
			case "declare":
				$this->_setProperty($name, $value, "bool");
			break;
			case "hspace":
			case "vspace":
				$this->_setProperty($name, (int) $value, "int", 0);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}