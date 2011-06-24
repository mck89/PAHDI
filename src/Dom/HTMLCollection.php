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
 * An HTMLCollection is a list of nodes in document order
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class HTMLCollection extends NodeList
{
	/**
	 * Returns a node in the current collection that has the given
	 * name or id or null if there is no matching node.
	 *
	 * @param	string		$name		Name or id to look for
	 * @return	mixed		Element with the given name or id or
	 *						null if not found
	 */
	function namedItem ($name)
	{
		$length = $this->length;
		for ($i = 0; $i < $length ; $i++) {
			$node = $this->_nodes[$i];
			if ($node->name === $name || $node->id === $name) {
				return $node;
			}
		}
		return null;
	}
	
	/**
	 * If the offset is numeric it returns true if there's a node
	 * at the given offset. If it's a string it returns true if there's
	 * an element with the name or id equal to the given offset (used to
	 * make the class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	bool	True if it exists otherwise false
	 * @ignore
	 */
	function offsetExists ($offset)
	{
		$found = parent::offsetExists($offset);
		if (!$found && is_string($offset)) {
			$found = $this->namedItem($offset) !== null;
		}
		return $found;
	}
	
	/**
	 * If the offset is a numeric it returns the item at the given
	 * offset otherwise it returns the element with the name or id
	 * equal to the given offset or null if not found (used to make
	 * the class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	mixed	Item or false if it's not found
	 * @ignore
	 */
	function offsetGet ($offset)
	{
		if (is_numeric($offset)) {
			$found = parent::offsetGet($offset);
		} else {
			$found = null;
		}
		if (!$found && is_string($offset)) {
			$found = $this->namedItem($offset);
		}
		return $found;
	}
}