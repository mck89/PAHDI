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
 * DOM HTML Input element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	int			$textLength		Element's text length
 * @property		string		$name			Element's name
 * @property		string		$alt			Element's alt text
 * @property		string		$src			Element's source
 * @property		string		$type			Element's type
 * @property		string		$align			Element's alignment
 * @property		string		$value			Element's value
 * @property		string		$useMap			Element's useMap
 * @property		string		$accept			Element's accepted formats
 * @property		string		$placeholder	Element's placeholder
 * @property		string		$pattern		Element's validation pattern
 * @property		string		$formAction		Element's form action
 * @property		string		$formEnctype	Element's form enctype
 * @property		string		$formMethod		Element's form method
 * @property		string		$formTarget		Element's form target
 * @property		string		$autocomplete	Element's autocomplete state
 * @property		bool		$disabled		Element's disable state
 * @property		bool		$readOnly		Element's read only state
 * @property		bool		$multiple		Element's multiple selection state
 * @property		bool		$checked		Element's checked state
 * @property		bool		$autofocus		Element's autofocus state
 * @property		bool		$required		Element's required state
 * @property		bool		$indeterminate	Element's indeterminate state
 * @property		int			$maxLength		Element's max length
 * @property		int			$size			Element's size
 */
class HTMLInputElement extends HTMLElement
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
			case "alt":
			case "src":
			case "align":
			case "value":
			case "useMap":
			case "accept":
			case "placeholder":
			case "pattern":
				return $this->_getProperty($name);
			break;
			case "type":
				return $this->_getProperty($name, "", "text");
			break;
			case "disabled":
			case "readOnly":
			case "multiple":
			case "checked":
			case "autofocus":
			case "required":
			case "indeterminate":
				return $this->_getProperty($name, "bool");
			break;
			case "maxLength":
			case "size":
				return (int) $this->_getProperty($name, "int", - 1);
			break;
			case "textLength":
				return strlen($this->value);
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
			case "autocomplete":
				$ret = $this->_getProperty($name);
				$ret = strtolower($ret);
				if ($ret !== "on") {
					$ret = "";
				}
				return $ret;
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
			case "type":
			case "align":
			case "value":
			case "useMap":
			case "placeholder":
			case "pattern":
				$this->_setProperty($name, $value);
			break;
			case "disabled":
			case "readOnly":
			case "multiple":
			case "autofocus":
			case "required":
			case "indeterminate":
				$this->_setProperty($name, $value, "bool");
			break;
			case "checked":
				$this->_setProperty($name, $value, "bool");
				//Emulate radio behaviour
				if ($this->checked &&
					strtolower($this->type) === "radio" &&
					$name = $this->name) {
					$search = new PAHDISearch($this->ownerDocument);
					$fn = function ($node) use ($name) {
						return  $node->tagName === "input" &&
								$node->name === $name &&
								$node->checked &&
								strtolower($node->type) === "radio";
					};
					$search->find($fn);
					$length = $search->length;
					for ($i = 0; $i < $length; $i++) {
						if (!$search[$i]->isSameNode($this)) {
							$search[$i]->checked = false;
						}
					}
				}
			break;
			case "maxLength":
			case "size":
				$this->_setProperty($name, (int) $value, "int", 0);
			break;
			case "textLength":
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
			case "autocomplete":
				$value = strtolower($value);
				if ($value  !== "on") {
					$value = "";
				}
				$this->_setProperty($name, $value);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}