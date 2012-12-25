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
 * DOM HTML Label element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string			$htmlFor	Element's "for"
 * @property		HTMLElement		$control	Labeled control
 */
class HTMLLabelElement extends HTMLElement
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
			case "htmlFor":
				return $this->_getProperty("for");
			break;
			case "control":
				$for = $this->htmlFor;
				if ($for !== "") {
					return $this->ownerDocument->getElementById($for);
				} else {
					$controls = ParserHTML::$formAssociated;
					$labelKey = array_search("label", $controls);
					array_splice($controls, $labelKey, 1);
					return $this->querySelector(implode(",", $controls));
				}
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
			case "htmlFor":
				$this->_setProperty("for", $value);
			break;
			case "control":
				//Ignore
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}