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
 * DOM HTML Script element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$src		Element's source
 * @property		string		$type		Element's type
 * @property		string		$htmlFor	Element's "for"
 * @property		bool		$defer		Element's defer state
 * @property		bool		$async		Element's async state
 * @property		string		$charset	Element's source charset
 * @property		string		$event		Element's event
 * @property		string		$text		Alias of textContent
 */
class HTMLScriptElement extends HTMLElement
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
			case "src":
				return $this->_getProperty($name, "path");
			break;
			case "type":
			case "charset":
			case "event":
				return $this->_getProperty($name);
			break;
			case "htmlFor":
				return $this->_getProperty("for");
			break;
			case "defer":
			case "async":
				return $this->_getProperty($name, "bool");
			break;
			case "text":
				return $this->textContent;
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
			case "src":
			case "type":
			case "charset":
				$this->_setProperty($name, $value);
			break;
			case "htmlFor":
				$this->_setProperty("for");
			break;
			case "defer":
			case "async":
				$this->_setProperty($name, $value, "bool");
			break;
			case "text":
				$this->textContent = $value;
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}