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
 * An HTMLPropertiesCollection is a list of elements that add name-value pairs
 * to a particular item in the microdata model.
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		StringList		$names		Names list as StringList
 */
class HTMLPropertiesCollection extends HTMLCollection
{
	/**
	 * Returns a PropertyNodeList of the elements in the current
	 * list filtered by the given name
	 *
	 * @param	string				$name	Name to look for in the
	 *										itemprop of every element
	 * @return	PropertyNodeList	PropertyNodeList containing filtered
	 *								elements
	 */
	function namedItem ($name)
	{
		$length = $this->length;
		$list = new PropertyNodeList;
		for ($i = 0; $i < $length ; $i++) {
			$node = $this->_nodes[$i];
			if ($node->itemProp->contains($name)) {
				$list->_appendNode($node);
			}
		}
		return $list;
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
			$list = $this->namedItem($offset);
			$found = $list->length > 0;
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
			if ($found->length === 0) {
				$found = null;
			}
		}
		return $found;
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
			case "names":
				$names = array();
				$length = $this->length;
				for ($i = 0; $i < $length ; $i++) {
					$node = $this->_nodes[$i];
					if ($node->itemProp->length) {
						foreach ($node->itemProp as $prop) {
							$names[] = $prop;
						}
					}
				}
				return new StringList(array_unique($names));
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
			case "names":
				//Ignore
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}