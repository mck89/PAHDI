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
 * DOM list of css media strings
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$mediaText		Media in form of string
 */
class MediaList extends DomList
{
	/**
	 * Appends a media string
	 *
	 * @param	string	$media	Media string
	 * @return	void
	 */
	function appendMedium ($media)
	{
		$media = strtolower($media);
		foreach ($this->_nodes as $med) {
			if ($med === $media) {
				return;
			}
		}
		$this->_nodes[] = $media;
	}
	
	/**
	 * Deletes a media string
	 *
	 * @param	string	$media	Media string
	 * @return	void
	 */
	function deleteMedium ($media)
	{
		foreach ($this->_nodes as $k => $med) {
			if ($med === $media) {
				array_splice($this->_nodes, $k, 1);
				return;
			}
		}
		throw new DomException("Node was not found");
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
		if ($name === "mediaText") {
			return implode(",", $this->_nodes);
		}
		return parent::__get($name);
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
			case "mediaText":
				$value = trim($value);
				if ($value) {
					$media = preg_split(
						"#\s*,\s*#",
						$value,
						- 1,
						PREG_SPLIT_NO_EMPTY
					);
					$this->_nodes = array_unique($media);
				} else {
					$this->_nodes = array();
				}
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}