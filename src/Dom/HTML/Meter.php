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
 * DOM HTML Meter element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		int			$max		Maximum value
 * @property		int			$min		Minimum value
 * @property		int			$value		Value
 * @property		int			$low		Low value
 * @property		int			$high		High value
 * @property		int			$optimum	Optimum value
 */
class HTMLMeterElement extends HTMLElement
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
				$min = $this->min;
				if ($val > $max) {
					$val = $max;
				} elseif ($val < $min) {
					$val = $min;
				}
				return $val;
			break;
			case "min":
				$val = (int) $this->_getProperty($name, "int", 0);
				return $val;
			break;
			case "max":
				$val = (int) $this->_getProperty($name, "int", 1);
				$min = $this->min;
				return $val < $min ? $min : $val;
			break;
			case "low":
				$val = (int) $this->_getProperty($name, "int", 0);
				$max = $this->max;
				$min = $this->min;
				$high = (int) $this->_getProperty("high", "int", 1);
				if ($val > $max) {
					$val = $max;
				} elseif ($val < $min) {
					$val = $min;
				} elseif ($val > $high) {
					$val = $high;
				}
				return $val;
			break;
			case "high":
				$val = (int) $this->_getProperty($name, "int", 1);
				$max = $this->max;
				$min = $this->min;
				$low = (int) $this->_getProperty("low", "int", 0);
				if ($val > $max) {
					$val = $max;
				} elseif ($val < $min) {
					$val = $min;
				} elseif ($val < $low) {
					$val = $low;
				}
				return $val;
			break;
			case "optimum":
				$strVal = $this->_getProperty($name, "int");
				$val = (int) $strVal;
				$max = $this->max;
				$min = $this->min;
				if ($strVal === "") {
					$diff = $max - $min;
					$val = $min + ($diff ? round($diff / 2, 2) : 0);
				} elseif ($val > $max) {
					$val = $max;
				} elseif ($val < $min) {
					$val = $min;
				}
				return $val;
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
			case "min":
			case "max":
			case "low":
			case "high":
			case "optimum":
				return $this->_setProperty($name, $value, "int");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}