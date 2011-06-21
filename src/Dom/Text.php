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
 * DOM text nodes class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	string	$wholeText					Text composed by the current
 *														node data and its adjacent
 *														text nodes data
 * @property-read	bool	$isElementContentWhitespace	True if the node data contain
 * 														only whitespaces
 */
class Text extends CharacterData
{
	/**
	 * Node type
	 *
	 * @var		int
	 */
	public $nodeType = self::TEXT_NODE;
	
	/**
	 * Node name
	 *
	 * @var		string
	 */
	public $nodeName = "#text";
	
	/**
	 * Splits the current node in two text nodes at the given offset
	 *
	 * @param	int		$offset		Offset at which to split
	 * @return	Text	Created text node
	 */
	function splitText ($offset)
	{
		if ($offset < 0 || $offset > $this->length) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$newdata = $this->_stringFunction("substr", $offset);
		$this->data = $this->_stringFunction("substr", 0, $offset);
		$newnode = $this->ownerDocument->createTextNode($newdata);
		if ($this->parentNode) {
			$next = $this->nextSibling;
			if ($next) {
				$this->parentNode->insertBefore($newnode, $next);
			} else {
				$this->parentNode->appendChild($newnode);
			}
		}
		return $newnode;
	}
	
	/**
	 * Replaces the current text node data and its adjacent text
	 * nodes data with the given string. The result is that only
	 * the current text node is not removed and it's data becomes
	 * the given string
	 *
	 * @param	string	$data	Replacement data
	 * @return	object	Replacemente node (the current one)
	 */
	function replaceWholeText ($data)
	{
		$this->data = $data;
		if ($this->parentNode) {
			$prev = $this->previousSibling;
			while ($prev && $prev->nodeType === 3) {
				$this->parentNode->removeChild($prev);
				$prev = $this->previousSibling;
			}
			$next = $this->nextSibling;
			while ($next && $next->nodeType === 3) {
				$this->parentNode->removeChild($next);
				$next = $this->nextSibling;
			}
		}
		return $this;
	}
	
	/**
	 * Appends the given node to the current one
	 *
	 * @param	Node	$child	Node to append
	 * @return	Node	Appended node
	 */
	function appendChild ($child)
	{
		throw new DomException("Node cannot be inserted at the specified point in the hierarchy");
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
			case "wholeText":
				$ret = "";
				$prev = $this->previousSibling;
				while ($prev && $prev->nodeType === 3) {
					$ret = $prev->data . $ret;
					$prev = $prev->previousSibling;
				}
				$ret .= $this->data;
				$next = $this->nextSibling;
				while ($next && $next->nodeType === 3) {
					$ret .= $next->data;
					$next = $next->nextSibling;
				}
				return $ret;
			break;
			case "isElementContentWhitespace":
				return !$this->length ||
						preg_match("#^\s+$#", $this->data);
			break;
			default:
				return parent::__get($name);
			break;
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
			case "wholeText":
			case "isElementContentWhitespace":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}