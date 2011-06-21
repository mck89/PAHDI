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
 * DOM node list class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	int		$length		The number of nodes in the list
 */
class DomList extends DomObject implements ArrayAccess, IteratorAggregate
{
	/**
	 * Nodes list
	 *
	 * @var		array
	 * @access	protected
	 * @ignore
	 */
	protected $_nodes = array();
	
	/**
	 * Gets a node by index
	 *
	 * @param	int		$index	Index
	 * @return	mixed	Node or null if not found
	 */
	function item ($index)
	{
		return  isset($this->_nodes[$index]) ? 
				$this->_nodes[$index] :
				null;
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
		if ($name === "length") {
			return count($this->_nodes);
		}
		return null;
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
			case "length":
				//Ignore
			break;
			default:
				$this->$name = $value;
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
		return isset($this->_nodes[$offset]);
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
		$this->_nodes[$offset] = $value;
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
		return $this->_nodes[$offset];
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
		array_splice($this->_nodes, $offset, 1);
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
		return new ArrayIterator($this->_nodes);
    }
}