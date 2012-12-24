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
 * DOM HTML Table section element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	HTMLCollection	$rows		Rows collection
 * @property		string			$align		Element's alignment
 * @property		string			$chOff		Element's offset of the alignment
 *												character.
 * @property		string			$ch			Element's alignment character for cells
 * @property		string			$vAlign		Element's vertical alignment
 */
class HTMLTableSectionElement extends HTMLElement
{
	/**
	 * Inserts a new row at the given index
	 *
	 * @param	int						$index	Index
	 * @return	HTMLTableRowElement		Created row
	 */
	function insertRow ($index)
	{
		$rows = $this->rows;
		$length = $rows->length;
		//If the index is - 1 the cell is appended
		if ($index === - 1) {
			$index = $length;
		}
		if ($index > $length || $index < 0) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$row = $this->ownerDocument->createElement("tr");
		if ($index === $length) {
			$this->appendChild($row);
		} else {
			$this->insertBefore($row, $rows[$index]);
		}
		return $row;
	}
	
	/**
	 * Deletes the row at the given index
	 *
	 * @param	int		$index	Index
	 * @return	void
	 */
	function deleteRow ($index)
	{
		$rows = $this->rows;
		$length = $rows->length;
		if ($index >= $length || $index < 0) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$this->removeChild($rows[$index]);
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
			case "align":
				return $this->_getProperty($name, "", "left");
			break;
			case "vAlign":
				return $this->_getProperty($name, "", "middle");
			break;
			case "chOff":
				return $this->_getProperty($name);
			break;
			case "ch":
				return $this->_getProperty($name, "", ".");
			break;
			case "rows":
				$search = new PAHDISearch($this);
				$fn = function ($node) {
					return $node->tagName === "tr";
				};
				return $search->find($fn, PAHDISearch::CHILDREN)->toHTMLCollection();
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
			case "align":
			case "chOff":
			case "ch":
			case "vAlign":
				$this->_setProperty($name, $value);
			break;
			case "rows":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}