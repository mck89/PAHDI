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
 * DOM HTML Form element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	HTMLCollection	$elements		Form elements inside the current
 *													element
 * @property-read	int				$length			Number of Form elements inside
 *													the current element
 * @property		string			$name			Element's name
 * @property		string			$target			Element's target
 * @property		string			$action			Element's action
 * @property		string			$method			Element's method
 * @property		string			$enctype		Element's enctype
 * @property		string			$acceptCharset	Element's accepted charset
 * @property		string			$encoding		Alias of enctype
 * @property		string			$autocomplete	Element's autocomplete state
 */
class HTMLFormElement extends HTMLElement
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
			case "target":
			case "action":
			case "enctype":
				return $this->_getProperty($name);
			break;
			case "method":
				$ret = $this->_getProperty($name);
				$ret = strtolower($ret);
				if ($ret !== "post") {
					$ret = "get";
				}
				return $ret;
			break;
			case "encoding":
				return $this->_getProperty("enctype");
			break;
			case "acceptCharset":
				return $this->_getProperty("accept-charset");
			break;
			case "autocomplete":
				$ret = $this->_getProperty($name);
				$ret = strtolower($ret);
				if ($ret !== "on") {
					$ret = "";
				}
				return $ret;
			break;
			case "elements":
				$search = new PAHDISearch($this);
				$fn = function ($node) {
					return in_array($node->tagName, ParserHTML::$formAssociated);
				};
				return $search->find($fn)->toHTMLCollection();
			break;
			case "length":
				return $this->elements->length;
			break;
			default:
				//Search a form control with the given name
				$ret = parent::__get($name);
				if (!$ret) {
					$search = new PAHDISearch($this);
					$fn = function ($node) use ($name) {
						return  $node->name === $name &&
								in_array($node->tagName, ParserHTML::$formAssociated);
					};
					$search->find($fn, PAHDISearch::DESCENDANTS, 1, 1);
					$ret = $search->length ? $search[0] : null;
				}
				return $ret;
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
			case "target":
			case "action":
			case "enctype":
				$this->_setProperty($name, $value);
			break;
			case "method":
				$value = strtolower($value);
				if ($value !== "post") {
					$value = "get";
				}
				$this->_setProperty($name, $value);
			break;
			case "encoding":
				$this->_setProperty("enctype", $value);
			break;
			case "acceptCharset":
				$this->_setProperty("accept-charset", $value);
			break;
			case "autocomplete":
				$value = strtolower($value);
				if ($value  !== "on") {
					$value = "";
				}
				$this->_setProperty($name, $value);
			break;
			case "elements":
			case "length":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}