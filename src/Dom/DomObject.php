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
 * Base class for all dom classes
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class DomObject
{
	/**
	 * Returns the string representation of the current object
	 *
	 * @return	string	String representation of the current object
	 */
	function __toString ()
	{
		$class = get_class($this);
		return "[object $class]";
	}
	
	/**
	 * Checks if the given property exists
	 *
	 * @param	string	$name	Property
	 * @return	bool	True if it exists otherwise false
	 * @ignore
	 */
	function __isset ($name)
	{
		try {
			$ret = $this->$name;
			return $ret !== null;
		} catch (DomException $e) {
			return false;
		}
	}
}