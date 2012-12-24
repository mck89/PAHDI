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
 * DOM HTML Table element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	HTMLCollection	$rows			Rows collection
 * @property-read	HTMLCollection	$tBodies		Table bodies collection
 * @property-read	mixed			$tHead			Table head or null if not
 *													present
 * @property-read	mixed			$tFoot			Table head or null if not
 *													present
 * @property-read	mixed			$caption		Table caption or null if not
 *													present
 * @property		string			$align			Element's alignment
 * @property		string			$summary		Element's summary
 * @property		string			$width			Element's width
 * @property		string			$rules			Element's rules
 * @property		string			$bgColor		Element's background color
 * @property		string			$border			Element's border width
 * @property		string			$cellPadding	Element's cell padding
 * @property		string			$cellSpacing	Element's cell spacing
 * @property		string			$frame			Specifies which external table
 *													borders to render.
 */
class HTMLTableElement extends HTMLElement
{
	/**
	 * Creates a caption for the table. If there's already a caption in the
	 * table it returns that element.
	 *
	 * @return	HTMLTableCaptionElement		Caption
	 */
	function createCaption ()
	{
		$caption = $this->caption;
		if (!$caption) {
			$caption = $this->ownerDocument->createElement("caption");
		}
		return $caption;
	}
	
	/**
	 * Removes the table caption
	 *
	 * @return	void
	 */
	function deleteCaption ()
	{
		$caption = $this->caption;
		if ($caption) {
			$caption->parentNode->removeChild($caption);
		}
	}
	
	/**
	 * Creates a table header for the table. If there's already a
	 * header in the table it returns that element.
	 *
	 * @return	HTMLTableSectionElement		Table header
	 */
	function createTHead ()
	{
		$tHead = $this->tHead;
		if (!$tHead) {
			$tHead = $this->ownerDocument->createElement("thead");
		}
		return $tHead;
	}
	
	/**
	 * Removes the table header
	 *
	 * @return	void
	 */
	function deleteTHead ()
	{
		$tHead = $this->tHead;
		if ($tHead) {
			$tHead->parentNode->removeChild($tHead);
		}
	}
	
	/**
	 * Creates a table footer for the table. If there's already a
	 * footer in the table it returns that element.
	 *
	 * @return	HTMLTableSectionElement		Table footer
	 */
	function createTFoot ()
	{
		$tFoot = $this->tFoot;
		if (!$tFoot) {
			$tFoot = $this->ownerDocument->createElement("tfoot");
		}
		return $tFoot;
	}
	
	/**
	 * Removes the table footer
	 *
	 * @return	void
	 */
	function deleteTFoot ()
	{
		$tFoot = $this->tFoot;
		if ($tFoot) {
			$tFoot->parentNode->removeChild($tFoot);
		}
	}
	
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
		$rel = $rows[$index];
		$par = $rel->parentNode;
		$row = $this->ownerDocument->createElement("tr");
		if ($rel->isSameNode($this)) {
			if ($index === $length) {
				$this->appendChild($row);
			} else {
				$this->insertBefore($row, $rel);
			}
		} else {
			$par->insertBefore($row, $rel);
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
		$del = $rows[$index];
		$del->parentNode->removeChild($del);
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
			case "summary":
			case "rules":
			case "frame":
				return $this->_getProperty($name);
			break;
			case "width":
			case "border":
				return $this->_getProperty($name, "intperc");
			break;
			case "cellPadding":
			case "cellSpacing":
				return $this->_getProperty($name, "int");
			break;
			case "bgColor":
				return $this->_getProperty($name, "color");
			break;
			case "rows":
				if (!$this->childNodes->length ||
					$this->childNodes[0]->tagName === "tr") {
					$root = $this;
				} else {
					$root = $this->childNodes;
				}
				$search = new PAHDISearch($root);
				$fn = function ($node) {
					return $node->tagName === "tr";
				};
				return $search->find($fn, PAHDISearch::CHILDREN)->toHTMLCollection();
			break;
			case "tBodies":
				$search = new PAHDISearch($this);
				$fn = function ($node) {
					return $node->tagName === "tbody";
				};
				return $search->find($fn, PAHDISearch::CHILDREN)->toHTMLCollection();
			break;
			case "tHead":
			case "tFoot":
			case "caption":
				$name = strtolower($name);
				$search = new PAHDISearch($this);
				$fn = function ($node) use ($name) {
					return $node->tagName === $name;
				};
				$search->find($fn, PAHDISearch::CHILDREN, 1, 1);
				return $search->length ? $search[0] : null;
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
			case "summary":
			case "rules":
			case "frame":
				$this->_setProperty($name, $value);
			break;
			case "width":
			case "border":
				$this->_setProperty($name, $value, "intperc");
			break;
			case "cellPadding":
			case "cellSpacing":
				$this->_setProperty($name, $value, "int");
			break;
			case "bgColor":
				$this->_setProperty($name, $value, "color");
			break;
			case "tBodies":
			case "tHead":
			case "tFoot":
			case "caption":
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