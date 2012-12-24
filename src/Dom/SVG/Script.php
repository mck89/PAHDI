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
 * DOM SVG Script element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$type		Element's type
 */
class SVGScriptElement extends SVGElement
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
		$ret = null;
		switch ($name) {
			case "type":
				$ret = $this->_getProperty($name);
			break;
			default:
				$ret = parent::__get($name);
			break;
		}
		return $ret;
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
				$this->_setProperty($name, $value);
			break;			
			default:
				parent::__set($name, $value);
			break;
		}
	}
}