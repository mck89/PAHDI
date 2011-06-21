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
 * DOM HTML Table row element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	HTMLCollection	$cells				Row cells
 * @property-read	int				$sectionRowIndex	Index of the row in
 *														the table section
 * @property-read	int				$rowIndex			Index of the row in
 *														the table
 * @property		string			$align				Element's alignment
 * @property		string			$chOff				Element's offset of the
 *														alignment character.
 * @property		string			$ch					Element's alignment
 *														character for cells
 * @property		string			$vAlign				Element's vertical alignment
 * @property		string			$bgColor			Element's background color
 */
class HTMLTableRowElement extends HTMLElement
{
	/**
	 * Inserts a new cell at the given index
	 *
	 * @param	int						$index	Index
	 * @return	HTMLTableCellElement	Created cell
	 */
	function insertCell ($index)
	{
		$cells = $this->cells;
		$length = $cells->length;
		//If the index is - 1 the cell is appended
		if ($index === - 1) {
			$index = $length;
		}
		if ($index > $length || $index < 0) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$cell = $this->ownerDocument->createElement("td");
		if ($index === $length) {
			$this->appendChild($cell);
		} else {
			$this->insertBefore($cell, $cells[$index]);
		}
		return $cell;
	}
	
	/**
	 * Deletes the cell at the given index
	 *
	 * @param	int		$index	Index
	 * @return	void
	 */
	function deleteCell ($index)
	{
		$cells = $this->cells;
		$length = $cells->length;
		if ($index >= $length || $index < 0) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$this->removeChild($cells[$index]);
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
			case "bgColor":
				return $this->_getProperty($name, "color");
			break;
			case "cells":
				$search = new PAHDISearch($this);
				$fn = function ($node) {
					return $node instanceof HTMLTableCellElement;
				};
				return $search->find($fn, PAHDISearch::CHILDREN)->toHTMLCollection();
			break;
			case "sectionRowIndex":
				if (!$this->parentNode) {
					return - 1;
				}
				$ret = 0;
				$el = $this;
				while ($el = $el->previousElementSibling) {
					if ($el instanceof HTMLTableRowElement) {
						$ret++;
					}
				}
				return $ret;
			break;
			case "rowIndex":
				$index = $this->sectionRowIndex;
				//If the row is disconnected or it is a direct child of a
				//table then return the index
				$parent = $this->parentNode;
				if ($index === - 1 || $parent->tagName === "table") {
					return $index;
				}
				//Find the parent table
				$table = $parent->parentNode;
				while ($table && $table->tagName !== "table") {
					$table = $table->parentNode;
				}
				//If there's no parent table return the index
				if (!$table) {
					return $index;
				}
				//Loop through the sections of a table and add the rows count
				foreach ($table->childNodes as $child) {
					if ($child->isSameNode($parent)) {
						break;
					}
					if ($child instanceof HTMLTableSectionElement) {
						$index += $child->rows->length;
					}
				}
				return $index;
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
			case "bgColor":
				$this->_setProperty($name, $value, "color");
			break;
			case "cells":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			case "sectionRowIndex":
			case "rowIndex":
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}