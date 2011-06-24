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
 * DOM HTML Textarea element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	int			$textLength		Element's text length
 * @property		string		$name			Element's name
 * @property		string		$type			Element's type
 * @property		string		$accessKey		Element's access key
 * @property		string		$value			Element's value
 * @property		string		$maxLength		Element's max length
 * @property		string		$placeholder	Element's placeholder
 * @property		string		$wrap			Element's wrap mode
 * @property		bool		$disabled		Element's disable state
 * @property		bool		$readOnly		Element's read only state
 * @property		bool		$autofocus		Element's autofocus state
 * @property		bool		$required		Element's required state
 * @property		int			$rows			Element's rows
 * @property		int			$cols			Element's columns
 */
class HTMLTextAreaElement extends HTMLElement
{
	/**
	 * Textarea value
	 *
	 * @var		string
	 * @access	protected
	 * @ignore
	 */
	protected $_value;
	
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
			case "accessKey":
			case "placeholder":
			case "wrap":
				return $this->_getProperty($name);
			break;
			case "value":
				//If there's no "value" attribute take the textContent
				if ($this->_value === null) {
					$this->_value = $this->textContent;;
				}
				return $this->_value;
			break;
			case "type":
				return $this->_getProperty($name, "", "textarea");
			break;
			case "disabled":
			case "readOnly":
			case "autofocus":
			case "required":
				return $this->_getProperty($name, "bool");
			break;
			case "rows":
			case "cols":
			case "maxLength":
				return (int) $this->_getProperty($name, "int", - 1);
			break;
			case "textLength":
				return strlen($this->value);
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
			case "accessKey":
			case "placeholder":
			case "wrap":
				$this->_setProperty($name, $value);
			break;
			case "value":
				$this->_value = $value;
			break;
			case "disabled":
			case "readOnly":
			case "autofocus":
			case "required":
				$this->_setProperty($name, $value, "bool");
			break;
			case "rows":
			case "cols":
			case "maxLength":
				$this->_setProperty($name, (int) $value, "int", 0);
			break;
			case "textLength":
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}