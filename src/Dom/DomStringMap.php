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
 * Dataset class.
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class DomStringMap extends DomObject implements ArrayAccess, IteratorAggregate
{
	/**
	 * Owner element
	 *
	 * @var		Element
	 * @ignore
	 * @access	protected
	 */
	protected $_element;
	
	/**
	 * Class constructor.
	 *
	 * @param	Element		$element	Owner element
	 */
	function __construct (Element $element)
	{
		$this->_element = $element;
	}
	
	/**
	 * Fixes the given attribute name
	 *
	 * @var		Element
	 * @access	protected
	 * @ignore
	 */
	protected function _fixName ($name)
	{
		//Add hypens to the string
		$name = preg_replace(
			"#[A-Z]#e",
			"'-'.strtolower('$0')",
			$name
		);
		return "data-$name";
	}
	
	/**
	 * Get the value of a data attribute
	 *
	 * @param	string	$name	Attribute name
	 * @return	mixed	Attribute value
	 */
	function __get ($name)
	{
		$name = $this->_fixName($name);
		return $this->_element->getAttribute($name);
	}
	
	/**
	 * Sets a data attribute
	 *
	 * @param	string	$name	Attribute name
	 * @param	mixed	$value	Attribute value
	 * @return	void
	 */
	function __set ($name, $value)
	{
		$name = $this->_fixName($name);
		$this->_element->setAttribute($name, $value);
	}
	
	/**
	 * Checks if the given data attribute exists
	 *
	 * @param	string	$name	Attribute name
	 * @return	bool	True if it's present otherwise false
	 */
	function __isset ($name)
	{
		return $this->__get($name) !== null;
	}
	
	/**
	 * Removes the given data attribute
	 *
	 * @param	string	$name	Attribute name
	 * @return	void
	 */
	function __unset ($name)
	{
		$name = $this->_fixName($name);
		$this->_element->removeAttribute($name);
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
		return $this->__isset($offset);
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
		return $this->__set($offset, $value);
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
		return $this->__get($offset);
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
		$this->__unset($offset);
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
		$ret = array();
		$l = $this->_element->attributes->length;
		for ($i = 0; $i < $l; $i++) {
			$attr = $this->_element->attributes[$i];
			if (strpos($attr->name, "data-") === 0) {
				$name = preg_replace("#^data-#", "", $attr->name);
				$ret[$name] = $attr->value;
			}
		}
		return new ArrayIterator($ret);
    }
}