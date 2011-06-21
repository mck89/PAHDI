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
 * DOM HTML Select element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string			$name			Element's name
 * @property		string			$accessKey		Element's access key
 * @property		bool			$disabled		Element's disable state
 * @property		bool			$multiple		Element's multiple selection state
 * @property-read	bool			$type			Element's selection type
 * @property		bool			$autofocus		Element's autofocus state
 * @property		bool			$required		Element's required state
 * @property		int				$size			Element's size
 * @property		string			$value			Element's value
 * @property		int				$selectedIndex	Selected option index
 * @property		int				$length			Number of options in the select
 * @property		HTMLCollection	$options	Options in the select
 */
class HTMLSelectElement extends HTMLElement
{
	/**
	 * Returns the option at the given index
	 *
	 * @param	int		$index		Option index
	 * @return	mixed	Option or null if there's no option at the
	 *					given index
	 */
	function item ($index)
	{
		return $this->options->item($index);
	}
	
	/**
	 * Returns an option that has the given name or id or null if
	 * there is no matching option.
	 *
	 * @param	string	$name		Name or id to look for
	 * @return	mixed	Option or null if there's no option with
	 *					the given name or id
	 */
	function namedItem ($name)
	{
		return $this->options->namedItem($name);
	}
	
	/**
	 * Removes the option at the given index
	 *
	 * @param	int		$index		Index of the option to remove
	 * @return	void
	 */
	function remove ($index)
	{
		$opt = $this->item($index);
		if ($opt) {
			$opt->parentNode->removeChild($opt);
		}
	}
	
	/**
	 * Adds the given option to the current select element
	 *
	 * @param	HTMLOptionElement	$opt	Option to add
	 * @param	HTMLOptionElement	$ref	If given the option
	 *										will be inserted before
	 *										this option
	 * @return	void
	 */
	function add (HTMLOptionElement $opt, HTMLOptionElement $ref = null)
	{
		if (!$ref) {
			$this->appendChild($opt);
		} else {
			$ref->parentNode->insertBefore($opt, $ref);
		}
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
			case "name":
			case "accessKey":
				return $this->_getProperty($name);
			break;
			case "disabled":
			case "multiple":
			case "autofocus":
			case "required":
				return $this->_getProperty($name, "bool");
			break;
			case "type":
				return $this->multiple ? "select-multiple" : "select-one";
			break;
			case "size":
				return (int) $this->_getProperty($name, "int", - 1);
			break;
			case "options":
				return $this->getElementsByTagName("option");
			break;
			case "length":
				return $this->options->length;
			break;
			case "value":
				return $this->options[$this->selectedIndex]->value;
			break;
			case "selectedIndex":
				//Get the index of the first selected option or
				//zero if there are no selected options
				$options = $this->options;
				$length = $options->length;
				for ($i = 0; $i < $length; $i++) {
					if ($options[$i]->selected) {
						return $i;
					}
				}
				return 0;
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
			case "name":
			case "accessKey":
				$this->_setProperty($name, $value);
			break;
			case "disabled":
			case "multiple":
			case "autofocus":
			case "required":
				$this->_setProperty($name, $value, "bool");
			break;
			case "type":
				throw new DomException("Setting a property that has only a getter");
			break;
			case "size":
				$this->_setProperty($name, (int) $value, "int", 0);
			break;
			case "options":
			case "length":
				//Don't set anything
				return;
			break;
			case "value":
				$options = $this->options;
				$length = $options->length;
				$isMultiple = $this->multiple;
				for ($i = 0; $i < $length; $i++) {
					$opt = $options[$i];
					if ($opt->value === $value) {
						if (!$opt->selected) {
							$opt->selected = true;
						}
					} elseif (!$isMultiple) {
						$opt->selected = false;
					}
				}
			break;
			case "selectedIndex":
				$options = $this->options;
				$length = $options->length;
				$isMultiple = $this->multiple;
				for ($i = 0; $i < $length; $i++) {
					$opt = $options[$i];
					if ($i === $value) {
						if (!$opt->selected) {
							$opt->selected = true;
						}
					} elseif (!$isMultiple) {
						$opt->selected = false;
					}
				}
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}