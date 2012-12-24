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
 * DOM HTML Table cell element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	int			$cellIndex	Index of the cell relative
 *											to the other sibling cells
 * @property		string		$align		Element's alignment
 * @property		string		$width		Element's width
 * @property		string		$height		Element's height
 * @property		string		$chOff		Element's offset of the alignment character.
 * @property		string		$ch			Element's alignment character for cells
 * @property		string		$vAlign		Element's vertical alignment
 * @property		string		$bgColor	Element's background color
 * @property		string		$abbr		Element's abbr
 * @property		string		$scope		Element's scope
 * @property		string		$headers	Element's headers
 * @property		string		$axis		Element's axis
 * @property		bool		$noWrap		Element's nowrap state
 * @property		int			$colSpan	Element's colspan
 * @property		int			$rowSpan	Element's rowspan
 */
class HTMLTableCellElement extends HTMLElement
{
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
			case "chOff":
			case "abbr":
			case "scope":
			case "headers":
			case "axis":
				return $this->_getProperty($name);
			break;
			case "vAlign":
				return $this->_getProperty($name, "", "middle");
			break;
			case "ch":
				return $this->_getProperty($name, "", ".");
			break;
			case "width":
			case "height":
				return $this->_getProperty($name, "intperc");
			break;
			case "noWrap":
				return $this->_getProperty($name, "bool");
			break;
			case "colSpan":
			case "rowSpan":
				return (int) $this->_getProperty($name, "int", 1);
			break;
			case "bgColor":
				return $this->_getProperty($name, "color");
			break;
			case "cellIndex":
				if (!$this->parentNode) {
					return - 1;
				}
				$ret = 0;
				$el = $this;
				while ($el = $el->previousElementSibling) {
					if ($el instanceof HTMLTableCellElement) {
						$ret++;
					}
				}
				return $ret;
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
			case "abbr":
			case "scope":
			case "headers":
			case "axis":
				$this->_setProperty($name, $value);
			break;
			case "width":
			case "height":
				$this->_setProperty($name, $value, "intperc");
			break;
			case "noWrap":
				$this->_setProperty($name, $value, "bool");
			break;
			case "colSpan":
			case "rowSpan":
				$this->_setProperty($name, (int) $value, "int", 1);
			break;
			case "bgColor":
				$this->_setProperty($name, $value, "color");
			break;
			case "cellIndex":
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}