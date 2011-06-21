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
 * DOM character data class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	int		$length		Data length
 */
class CharacterData extends Node
{
	/**
	 * Node data
	 *
	 * @var		string
	 */
	public $data = "";
	
	/**
	 * Class constructor
	 *
	 * @param	string	$data	Node data
	 */
	function __construct ($data)
	{
		parent::__construct();
		$this->data = $data;
	}
	
	/**
	 * Appends data to the current node
	 *
	 * @param	string	$data	Data to append
	 * @return	void
	 */
	function appendData ($data)
	{
		$this->data .= $data;
	}
	
	/**
	 * Inserts data into the current node at the given offset
	 *
	 * @param	int		$offset		Offset at which to insert the data
	 * @param	string	$data		Data to insert
	 * @return	void
	 */
	function insertData ($offset, $data)
	{
		if ($offset < 0 || $offset > $this->length) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$this->data = 	$this->_stringFunction("substr", 0, $offset) . $data .
						$this->_stringFunction("substr", $offset);
	}
	
	/**
	 * Extracts the given number of characters starting from the given
	 * offset from the current node's data
	 *
	 * @param	int		$offset		Offset at which to start taking data
	 * @param	int		$count		Number of character to extract
	 * @return	string	Extracted data
	 */
	function substringData ($offset, $count)
	{
		if ($offset < 0 || ($offset + $count) > $this->length) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		return $this->_stringFunction("substr", $offset, $count);
	}
	
	/**
	 * Deletes the given number of characters starting from the given
	 * offset from the current node's data
	 *
	 * @param	int		$offset		Offset at which to start deleting data
	 * @param	int		$count		Number of character to delete
	 * @return	void
	 */
	function deleteData ($offset, $count)
	{
		if ($offset < 0 || ($offset + $count) > $this->length) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$this->data = 	$this->_stringFunction("substr", 0, $offset) .
						$this->_stringFunction("substr", $offset + $count);
	}
	
	/**
	 * Replaces the given number of characters starting from the given
	 * offset from the current node's data with the data given in the
	 * last parameter
	 *
	 * @param	int		$offset	Offset at which to start deleting data
	 * @param	int		$count	Number of character to delete
	 * @param	string	$data	Data to append
	 * @return	void
	 */
	function replaceData ($offset, $count, $data)
	{
		if ($offset < 0 || ($offset + $count) > $this->length) {
			$msg = "Index or size is negative or greater than the allowed amount";
			throw new DomException($msg);
		}
		$this->data = 	$this->_stringFunction("substr", 0, $offset) . $data .
						$this->_stringFunction("substr", $offset + $count);
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
		if ($name === "length") {
			return $this->_stringFunction("strlen");
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
		if ($name === "length") {
			throw new DomException("Setting a property that has only a getter");
		}
		parent::__set($name, $value);
	}
	
	/**
	 * Call a function on the data of the current node
	 *
	 * @param	string	$fn		Function to call
	 * @param	mixed	...		Function arguments
	 * @return	mixed	Function result
	 * @ignore
	 */
	protected function _stringFunction ($fn)
	{
		static $supportsMB;
		if (!isset($supportsMB)) {
			$supportsMB = function_exists("mb_strlen");
		}
		if ($supportsMB) {
			$encoding = $this->ownerDocument->characterSet;
		}
		if ($fn === "strlen") {
			if ($supportsMB) {
				return mb_strlen($this->data, $encoding);
			} else {
				return strlen($this->data);
			}
		} elseif ($fn === "substr") {
			$args = func_get_args();
			if (!$supportsMB) {
				if (count($args) === 3) {
					return substr($this->data, $args[1], $args[2]);
				}
				return substr($this->data, $args[1]);
			}
			if (!isset($args[2])) {
				$args[2] = mb_strlen($this->data, $encoding) - $args[1];
			}
			return mb_substr($this->data, $args[1], $args[2], $encoding);
		}
	}
}