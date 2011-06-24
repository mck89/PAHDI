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
 * DOM HTML Image element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$name		Element's name
 * @property		string		$alt		Element's alt text
 * @property		string		$src		Element's source
 * @property		string		$align		Element's alignment
 * @property		string		$useMap		Element's useMap
 * @property		string		$longDesc	Element's longDesc
 * @property		int			$hspace		Element's horizontal space
 * @property		int			$vspace		Element's vertical space
 * @property		string		$border		Element's border width
 * @property		string		$lowsrc		Element's lowsrc
 * @property		bool		$isMap		Element's ismap state
 */
class HTMLImageElement extends HTMLElement
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
			case "lowsrc":
			case "longDesc":
			case "src":
				return $this->_getProperty($name, "path");
			break;
			case "name":
			case "alt":
			case "align":
			case "useMap":
				return $this->_getProperty($name);
			break;
			case "hspace":
			case "vspace":
				return (int) $this->_getProperty($name, "int", - 1);
			break;
			case "isMap":
				return $this->_getProperty($name, "bool");
			break;
			case "border":
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
			case "alt":
			case "src":
			case "align":
			case "useMap":
			case "longDesc":
			case "lowsrc":
				$this->_setProperty($name, $value);
			break;
			case "hspace":
			case "vspace":
				$this->_setProperty($name, (int) $value, "int", 0);
			break;
			case "isMap":
				$this->_setProperty($name, $value, "bool");
			break;
			case "border":
				$this->_setProperty($name, $value, "intperc");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}