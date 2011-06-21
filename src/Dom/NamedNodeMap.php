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
 * DOM named node map class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class NamedNodeMap extends DomList
{
	/**
	 * Node that owns the current object
	 *
	 * @var		object
	 * @ignore
	 * @access	protected
	 */
	protected $_owner;
	
	/**
	 * Class constructor
	 *
	 * @param	object	$node	Node that owns the current object
	 */
	function __construct ($node)
	{
		$this->_owner = $node;
	}
	
	/**
	 * Gets an attribute node by namespace and name
	 *
	 * @param	string	$name	Attribute's name
	 * @return	mixed	Attribute node or null if not found
	 */
	function getNamedItem ($name)
	{
		$index = $this->_findItemIndex($name);
		if ($index !== null) {
			$ret = $this->item($index);
		} else {
			$ret = null;
		}
		return $ret;
	}
	
	/**
	 * Adds an attribute node to the item list
	 *
	 * @param	Attr	$node	Attribute node to add
	 * @return	mixed	Replaced attribute node (if any) or null
	 */
	function setNamedItem (Attr $node)
	{
		if ($node->ownerElement) {
			throw new DomException("Attribute already in use");
		}
		$index = $this->_findItemIndex($node->name);
		if ($index !== null) {
			$ret = $this->item($index);
			unset($this[$index]);
			$ret->ownerElement = null;
		} else {
			$ret = null;
		}
		$node->ownerElement = null;
		$node->ownerElement = $this->_owner;
		$this->_nodes[] = $node;
		return $ret;
	}
	
	/**
	 * Removes an attribute node from the item list
	 *
	 * @param	string	$name	Attribute's name
	 * @return	Attr	Removed attribute node
	 */
	function removeNamedItem ($name)
	{
		$index = $this->_findItemIndex($name);
		if ($index === null) {
			throw new DomException("Node was not found");
		}
		$ret = $this->item($index);
		unset($this[$index]);
		$ret->ownerElement = null;
		return $ret;
	}
	
	/**
	 * Gets a namespaced attribute node by namespace and name
	 *
	 * @param	string	$ns		Attribute's namespace
	 * @param	string	$name	Attribute's name
	 * @return	mixed	Attribute node or null if not found
	 */
	function getNamedItemNS ($ns, $name)
	{
		$index = $this->_findItemIndex($name, $ns, true);
		if ($index !== null) {
			$ret = $this->item($index);
		} else {
			$ret = null;
		}
		return $ret;
	}
	
	/**
	 * Adds a namespaced attribute node to the item list
	 *
	 * @param	Attr	$node	Attribute node to add
	 * @return	mixed	Replaced attribute node (if any) or null
	 */
	function setNamedItemNS (Attr $node)
	{
		if ($node->ownerElement) {
			throw new DomException("Attribute already in use");
		}
		$index = $this->_findItemIndex(
			$node->localName,
			$node->namespaceURI,
			true
		);
		if ($index !== null) {
			$ret = $this->item($index);
			unset($this[$index]);
			$ret->ownerElement = null;
		} else {
			$ret = null;
		}
		$node->ownerElement = null;
		$node->ownerElement = $this->_owner;
		$this->_nodes[] = $node;
		return $ret;
	}
	
	/**
	 * Removes a namespaced attribute node from the item list
	 *
	 * @param	string	$ns		Attribute's namespace
	 * @param	string	$name	Attribute's name
	 * @return	Attr	Removed attribute node
	 */
	function removeNamedItemNS ($ns, $name)
	{
		$index = $this->_findItemIndex($name, $ns, true);
		if ($index === null) {
			throw new DomException("Node was not found");
		}
		$ret = $this->item($index);
		unset($this[$index]);
		$ret->ownerElement = null;
		return $ret;
	}
	
	/**
	 * Returns the attribute node with the given name if it is present
	 *
	 * @param	string	$name	Attribute name
	 * @return	mixed	Attribute node or null if not found
	 */
	function __get ($name)
	{
		if ($name !== "length") {
			$ret = $this->getNamedItem($name);
			if ($ret === null) {
				throw new DomException("Node was not found");
			}
			return $ret;
		}
		return parent::__get($name);
	}
	
	/**
	 * Checks if an attribute with the given name is present
	 *
	 * @param	string	$name	Attribute name
	 * @return	bool	True if it's present otherwise false
	 */
	function __isset ($name)
	{
		return $this->getNamedItem($name) !== null;
	}
	
	/**
	 * Removes an attribute by its name
	 *
	 * @param	string	$name	Attribute name
	 * @return 	void
	 */
	function __unset ($name)
	{
		$this->removeNamedItem($name);
	}
	
	/**
	 * Finds the index of the attribute with the given name
	 *
	 * @param	string	$name	Attribute's name
	 * @param	string	$ns		Attribute's namespace
	 * @param	string	$local	If true the method will check
	 *							the localName instead of the name
	 * @return	int		Index of the element or null if not found
	 * @access	protected
	 * @ignore
	 */
	protected function _findItemIndex ($name, $ns = null, $local = false)
	{
		$check = $local ? "localName" : "name";
		$name = strtolower($name);
		foreach ($this->_nodes as $k => $v) {
			if ($v->$check === $name &&
				(!$local || $v->namespaceURI === $ns)) {
				return $k;
			}
		}
		return null;
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
		if ($this->_isLiteralOffset($offset)) {
			return $this->__isset($offset);
		} else {
			return parent::offsetExists($offset);
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
		if (isset($this->_nodes[$offset])) {
			parent::offsetSet($offset, $value);
		} else {
			$msg = "You cannot add elements in the array-style way";
			throw new DomException($msg);
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
		if ($this->_isLiteralOffset($offset)) {
			return $this->__get($offset);
		} else {
			return parent::offsetGet($offset);
		}
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
		if ($this->_isLiteralOffset($offset)) {
			return $this->__unset($offset);
		} else {
			return parent::offsetUnset($offset);
		}
	}
	
	/**
	 * Checks that the given value can be used as
	 * literal index of an array
	 *
	 * @param	int		$offset		Offset
	 * @return	bool	Test result
	 * @access	protected
	 * @ignore
	 */
	protected function _isLiteralOffset ($offset)
	{
		return is_string($offset) && preg_match("#\D#", $offset);
	}
}