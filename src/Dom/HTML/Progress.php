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
 * DOM HTML Progress element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		int			$max		Maximum value
 * @property		int			$value		Value
 */
class HTMLProgressElement extends HTMLElement
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
			case "value":
				$val = (int) $this->_getProperty($name, "int", 0);
				$max = $this->max;
				if ($val > $max) {
					$val = $max;
				}
				return $val;
			break;
			case "max":
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
			case "value":
			case "max":
				return $this->_setProperty($name, $value, "int");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}