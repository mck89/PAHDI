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
 * DOM HTML Option element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$value		Element's value
 * @property		string		$label		Element's label
 * @property		string		$text		Alias of textContent
 * @property		bool		$disabled	Element's disable state
 * @property		bool		$selected	Element's selected state
 * @property		int			$index		Index of the element in the
 *											parent select
 */
class HTMLOptionElement extends HTMLElement
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
			case "value":
				$ret = $this->_getProperty($name, "", false);
				if ($ret === false) {
					$ret = $this->textContent;
				}
				return $ret;
			break;
			case "label":
				return $this->_getProperty($name);
			break;
			case "disabled":
			case "selected":
				return $this->_getProperty($name, "bool");
			break;
			case "text":
				return $this->textContent;
			break;
			case "index":
				$par = $this->_getParentSelect();
				if ($par) {
					$opts = $par->options;
					$length = $opts->length;
					for ($i = 0; $i < $length; $i++) {
						if ($opts[$i]->isSameNode($this)) {
							return $i;
						}
					}
				}
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
			case "value":
			case "label":
				$this->_setProperty($name, $value);
			break;
			case "selected":
				$this->_setProperty($name, $value, "bool");
				//Find the parent select and set the value to the
				//option value
				if ($this->selected) {
					$par = $this->_getParentSelect();
					if ($par && !$par->multiple) {
						$opts = $par->options;
						$length = $opts->length;
						for ($i = 0; $i < $length; $i++) {
							if ($opts[$i]->isSameNode($this)) {
								$par->selectedIndex = $i;
							}
						}
					}
				}
			break;
			case "disabled":
				$this->_setProperty($name, $value, "bool");
			break;
			case "text":
				$this->textContent = $value;
			break;
			case "index":
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
	
	/**
	 * Returns the parent select of the current option
	 *
	 * @return	mixed	Parent select or null if the
	 *					option is disconnected
	 * @access	protected
	 * @ignore
	 */
	protected function _getParentSelect ()
	{
		$par = $this->parentNode;
		while ($par && $par->tagName !== "select") {
			$par = $par->parentNode;
		}
		return $par;
	}
}