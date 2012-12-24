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
 * DOM node list class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class NodeList extends DomList
{
	/**
	 * Add a node to the list at the given index. 
	 * This method should be used only by the system
	 *
	 * @param	Node	$node	Node to add
	 * @param	int		$index	Index at which to insert the new node.
	 * @return	void
	 * @ignore
	 */
	function _addNodeAt ($node, $index)
	{
		array_splice($this->_nodes, $index, 0, array($node));
	}
	
	/**
	 * Append the given node to the list.
	 * This method should be used only by the system
	 *
	 * @param	Node	$node	Node to append
	 * @return	void
	 * @ignore
	 */
	function _appendNode ($node)
	{
		$this->_nodes[] = $node;
	}
	
	/**
	 * Remove the node at the given index from the list.
	 * This method should be used only by the system
	 *
	 * @param	int		$index	Index of the node to remove
	 * @return	void
	 * @ignore
	 */
	function _removeNodeAt ($index)
	{
		array_splice($this->_nodes, $index, 1);
	}
	
	/**
	 * Merge the given node or NodeList with the current
	 * list. This method should be used only by the system
	 *
	 * @param	mixed	$nodes	Node, array of node or NodeList
	 * @return	void
	 * @ignore
	 */
	function _merge ($nodes)
	{
		if ($nodes instanceof NodeList) {
			$this->_nodes = array_merge($this->_nodes, $nodes->_nodes);
		} elseif (is_array($nodes)) {
			$this->_nodes = array_merge($this->_nodes, $nodes);
		} elseif ($nodes instanceof Node) {
			$this->_nodes[] = $nodes;
		}
	}
	
	/**
	 * Prevents manipulation with array like notation
	 * (used to make the class compatible with the
	 * the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @param	int		$value		Value to assign
	 * @return	void
	 * @ignore
	 */
	function offsetSet ($offset, $value)
	{
	}
	
	/**
	 * Prevents manipulation with array like notation (used
	 * to make the class compatible with the ArrayAccess
	 * interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	mixed	Item or false if it's not found
	 * @ignore
	 */
	function offsetUnset ($offset)
	{
	}
}