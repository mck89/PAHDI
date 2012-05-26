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
 * DOM HTML Button element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$name			Element's name
 * @property		string		$type			Element's type
 * @property		string		$value			Element's value
 * @property		string		$formAction		Element's form action
 * @property		string		$formEnctype	Element's form enctype
 * @property		string		$formMethod		Element's form method
 * @property		string		$formTarget		Element's form target
 * @property		bool		$disabled		Element's disable state
 * @property		bool		$autofocus		Element's autofocus state
 */
class HTMLButtonElement extends HTMLElement
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
			case "name":
			case "value":
				return $this->_getProperty($name);
			break;
			case "type":
				return $this->_getProperty($name, "", "submit");
			break;
			case "disabled":
			case "autofocus":
				return $this->_getProperty($name, "bool");
			break;
			case "formAction":
			case "formEnctype":
			case "formMethod":
			case "formTarget":
				$form = $this->form;
				if ($form) {
					$prop = strtolower(str_replace("form", "", $name));
					return $form->$prop;
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
			case "name":
			case "type":
			case "value":
				$this->_setProperty($name, $value);
			break;
			case "disabled":
			case "autofocus":
				$this->_setProperty($name, $value, "bool");
			break;
			case "formAction":
			case "formEnctype":
			case "formMethod":
			case "formTarget":
				$form = $this->form;
				if ($form) {
					$prop = strtolower(str_replace("form", "", $name));
					$form->$prop = $value;
				}
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}