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
 * DOM CSS style declaration
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string	$cssText	String representation of the declaration
 * @property-read	int		$length		Number of properties
 */
class CSSStyleDeclaration extends DomObject implements ArrayAccess, IteratorAggregate
{
	/**
	 * Parent CSSRule
	 *
	 * @var		CSSRule
	 */
	public $parentRule;
	
	/**
	 * Parent element
	 *
	 * @var		Element
	 * @ignore
	 */
	protected $_parentElement;
	
	/**
	 * Properties array
	 *
	 * @var		array
	 * @ignore
	 */
	protected $_properties = array();
	
	/**
	 * Class constructor.
	 *
	 * @param	object		$parent		Parent object. It can be an element or a
	 *									css rule
	 */
	function __construct ($parent)
	{
		if ($parent instanceof Element) {
			$this->_parentElement = $parent;
		} else {
			$this->parentRule = $parent;
		}
	}
	
	/**
	 * Builds the structure resulted from the parsing of a style attribute
	 *
	 * @param	string		$name		Property name
	 * @param	string		$value		Property value
	 * @param	string		$important	"important" if the value must have
	 *									priority otherwise ""
	 * @return 	void
	 */
	function setProperty ($name, $value, $important)
	{
		if ($important !== "important" && $important !== "") {
			return;
		}
		//Check that the property has a valid or missing prefix
		if ($this->_checkPrefix($name)) {
			$this->_properties[$name] = array($value, $important);
		}
	}
	
	/**
	 * Return the value of the property with the given name or an empty
	 * string if it does not exists
	 *
	 * @param	string		$name		Property name
	 * @return 	string		Property value
	 */
	function getPropertyValue ($name)
	{
		return 	isset($this->_properties[$name]) ?
				$this->_properties[$name][0] :
				"";
	}
	
	/**
	 * Return the priority of a property
	 *
	 * @param	string		$name		Property name
	 * @return 	string		"important" if the property exists and it has
	 *						the !important flag otherwise ""
	 */
	function getPropertyPriority ($name)
	{
		return 	isset($this->_properties[$name]) ?
				$this->_properties[$name][1] :
				"";
	}
	
	/**
	 * Removes the property with the given name
	 *
	 * @param	string		$name		Property name
	 * @return 	string		Removed property value
	 */
	function removeProperty ($name)
	{
		$ret = $this->getPropertyValue($name);
		unset($this->_properties[$name]);
		return $ret;
	}
	
	/**
	 * Returns the name of the property at the given index
	 *
	 * @param	int			$index		Index
	 * @return 	string		Property name or empty string if
	 *						there's no property at the given
	 *						index
	 */
	function item ($index)
	{
		$values = array_keys($this->_properties);
		return isset($values[$index]) ? $values[$index] : "";
	}
	
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
			case "cssText":
				$ret = "";
				if (count($this->_properties)) {
					foreach ($this->_properties as $k => $v) {
						$ret .= "$k:{$v[0]}" . ($v[1] ? "!important" : "") . ";";
					}
				}
				return $ret;
			break;
			case "length":
				return count($this->_properties);
			break;
			default:
				//Add "-" to the camel cased string
				if ($name === "cssFloat") {
					$name = "float";
				} else {
					$name = strtolower(preg_replace("#([A-Z])#", "-$1", $name));
				}
				return $this->getPropertyValue($name);
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
			case "cssText":
				$parent = 	$this->_parentElement ?
							$this->_parentElement :
							$this->parentRule;
				$parser = new ParserCSS($value, $parent);
				$style = $parser->parseStyleAttribute();
				$this->_properties = $style->_properties;
			break;
			case "length":
				//Ignore
			break;
			default:
				//Update or insert the property
				if (isset($this->_properties[$name])) {
					$this->_properties[$name][0] = $value;
				} else {
					$this->setProperty($name, $value, "");
				}
			break;
		}
	}
	
	/**
	 * Checks if the given offset exists (used to make the class
	 * compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	bool	True if it exists otherwise false
	 * @ignore
	 */
	function offsetExists ($offset)
	{
		if (is_numeric($offset)) {
			return $this->item($offset) !== "";
		} else {
			return isset($this->_properties[$offset]);
		}
	}
	
	/**
	 * Sets the item at the given offset with the given 
	 * value (used to make the class compatible with the
	 * the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @param	int		$value		Value to assign
	 * @return	void
	 * @ignore
	 */
	function offsetSet ($offset, $value)
	{
		if (is_numeric($offset)) {
			$offset = $this->item($offset);
		}
		if ($offset && isset($this->_properties[$offset])) {
			$this->_properties[$offset][0] = $value;
		}
	}
	
	/**
	 * Returns the item at the given offset (used to make the
	 * class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	mixed	Item or false if it's not found
	 * @ignore
	 */
	function offsetGet ($offset)
	{
		if (is_numeric($offset)) {
			$offset = $this->item($offset);
		}
		return $this->getPropertyValue($offset);
	}
	
	/**
	 * Deletes the item at the given offset (used to make the
	 * class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	void
	 * @ignore
	 */
	function offsetUnset ($offset)
	{
		if (is_numeric($offset)) {
			$offset = $this->item($offset);
		}
		$this->removeProperty($offset);
	}
	
	/**
	 * Returns the object iterator (used to make the class
	 * compatible with the IteratorAggregate interface).
	 *
	 * @return	object	Iterator
	 * @ignore
	 */
	function getIterator ()
	{
		$prop = array();
		if (count($this->_properties)) {
			foreach ($this->_properties as $k => $v) {
				$prop[$k] = $v[0];
			}
		}
		return new ArrayIterator($prop);
    }
	
	/**
	 * Tests that the given property name is compatible with
	 * the allowed css prefix
	 *
	 * @param	string	$name	Name to test
	 * @return	bool	True if the name passes the test
	 * @access	protected
	 * @ignore
	 */
	protected function _checkPrefix ($name)
	{
		static $prefix;
		if (!isset($prefix)) {
			if ($this->_parentElement) {
				$doc = $this->_parentElement->ownerDocument;
				$prefix = $doc->_implementation["cssPrefix"];
			} elseif ($this->parentRule) {
				$prefix = $this->parentRule->parentStyleSheet->_prefix;
			}
		}
		if (!$name || ($name[0] === "-" && (!$prefix ||
			strpos($name, $prefix) !== 0))) {
			return false;
		}
		return true;
	}
}