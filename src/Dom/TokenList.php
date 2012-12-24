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
 * List of space-separated tokens
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class TokenList extends StringList implements ArrayAccess, IteratorAggregate
{
	/**
	 * Update function
	 *
	 * @var		Closure
	 * @access	protected
	 * @ignore
	 */
	protected $_updateFn;
	
	/**
	 * Class constructor.
	 *
	 * @param	string		$tokenString	Tokens
	 * @param	Closure		$fn				Update function
	 */
	function __construct ($tokenString, Closure $fn)
	{
		$this->_updateFn = $fn;
		$tokenString = trim($tokenString);
		if ($tokenString) {
			$this->_tokens = preg_split("#\s+#", $tokenString);
		}
	}
	
	/**
	 * Add the given token to the token list
	 *
	 * @param	string	$token	Token to add
	 * @return	void
	 */
	function add ($token)
	{
		if (preg_match("#\s#", $token)) {
			throw new DomException("String contains an invalid character");
		}
		$this->_tokens[] = $token;
		$this->_triggerUpdate();
	}
	
	/**
	 * Remove the given token from the token list
	 *
	 * @param	string	$token	Token to remove
	 * @return	void
	 */
	function remove ($token)
	{
		if (preg_match("#\s#", $token)) {
			throw new DomException("String contains an invalid character");
		}
		while (($key = array_search($token, $this->_tokens)) !== false) {
			array_splice($this->_tokens, $key, 1);
		}
		$this->_triggerUpdate();
	}
	
	/**
	 * Removes the given token if it's present othwrwise
	 * it adds it
	 *
	 * @param	string	$token	Token to remove
	 * @return	bool	True if the token is added otherwise
	 *					false
	 */
	function toggle ($token)
	{
		if (preg_match("#\s#", $token)) {
			throw new DomException("String contains an invalid character");
		}
		if ($this->contains($token)) {
			$this->remove($token);
			$this->_triggerUpdate();
			return false;
		} else {
			$this->add($token);
			$this->_triggerUpdate();
			return true;
		}
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
		if ($this->_isLiteralOffset($offset)) {
			return $this->contains($offset);
		} else {
			return isset($this->_tokens[$offset]);
		}
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
		if (isset($this->_tokens[$offset])) {
			$this->_tokens[$offset] = $value;
			$this->_triggerUpdate();
		} elseif ($offset === "" || $offset === null) {
			$this->add($value);
		}
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
		if ($this->_isLiteralOffset($offset)) {
			$this->remove($offset);
		} else {
			array_splice($this->_tokens, $offset, 1);
			$this->_triggerUpdate();
		}
	}
	
	/**
	 * Apply changes
	 *
	 * @return	void
	 * @access	protected
	 * @ignore
	 */
	protected function _triggerUpdate ()
	{
		$fn = $this->_updateFn;
		$string = "" . $this;
		$fn($string);
	}
	
	/**
	 * Checks that the given value can be used as
	 * literal index of an array
	 *
	 * @param	int		$offset		Offset
	 * @return	bool	Test result
	 * @ignore
	 * @access	protected
	 */
	protected function _isLiteralOffset ($offset)
	{
		return is_string($offset) && preg_match("#\D#", $offset);
	}
}