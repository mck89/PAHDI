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
 * PropertyNodeList is a HTMLPropertiesCollection filtered by name
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class PropertyNodeList extends NodeList
{
	/**
	 * Add a node to the list at the given index. 
	 * This method should be used only by the system
	 *
	 * @return	array	Array of values returned by the property itemValue of
	 *					each element in the list
	 */
	function getValues ()
	{
		$ret = array();
		$length = $this->length;
		for ($i = 0; $i < $length ; $i++) {
			$ret[] = $this->_nodes[$i]->itemValue;
		}
		return array_unique($ret);
	}
}