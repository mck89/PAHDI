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
 * List of strings
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class StringList extends DomObject implements ArrayAccess, IteratorAggregate
{
	/**
	 * Tokens
	 *
	 * @var		array
	 * @access	protected
	 * @ignore
	 */
	protected $_tokens = array();
	
	/**
	 * Class constructor.
	 *
	 * @param	string		$tokens		array
	 */
	function __construct ($tokens)
	{
		$this->_tokens = $tokens;
	}
	
	/**
	 * Gets a token by index
	 *
	 * @param	int		$index	Index
	 * @return	mixed	Token or null if not found
	 */
	function item ($index)
	{
		return  isset($this->_tokens[$index]) ? 
				$this->_tokens[$index] :
				null;
	}
	
	/**
	 * Checks if the given token is present in the token
	 * list
	 *
	 * @param	string	$token	Token to check
	 * @return	bool	True if the token is present otherwise
	 *					false
	 */
	function contains ($token)
	{
		if (preg_match("#\s#", $token)) {
			throw new DomException("String contains an invalid character");
		}
		return in_array($token, $this->_tokens);
	}
	
	/**
	 * Returns the value of the length property
	 *
	 * @param	string	$name	Property name
	 * @return	mixed	Property value
	 * @ignore
	 */
	function __get ($name)
	{
		if ($name === "length") {
			return count($this->_tokens);
		}
		return null;
	}
	
	/**
	 * Returns the string representation of the token list
	 *
	 * @return	string	String representation of the token list
	 */
	function __toString ()
	{
		return implode(" ", $this->_tokens);
	}
	
	/**
	 * Checks if the given offset exists (used to make the class
	 * compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	bool	True if it exists otherwise false
	 * @ignore
	 */
	function offsetExists ($offset)
	{
		return isset($this->_tokens[$offset]);
	}
	
	/**
	 * Sets the item at the given offset with the given 
	 * value (used to make the class compatible with the
	 * the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @param	int		$value		Value to assign
	 * @return	void
	 * @ignore
	 */
	function offsetSet ($offset, $value)
	{
		$this->_tokens[$offset] = $value;
	}
	
	/**
	 * Returns the item at the given offset (used to make the
	 * class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	mixed	Item or false if it's not found
	 * @ignore
	 */
	function offsetGet ($offset)
	{
		return $this->_tokens[$offset];
	}
	
	/**
	 * Deletes the item at the given offset (used to make the
	 * class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	void
	 * @ignore
	 */
	function offsetUnset ($offset)
	{
		array_splice($this->_tokens, $offset, 1);
	}
	
	/**
	 * Returns the object iterator (used to make the class
	 * compatible with the IteratorAggregate interface).
	 *
	 * @return	object	Iterator
	 * @ignore
	 */
	function getIterator ()
	{
		return new ArrayIterator($this->_tokens);
    }
}