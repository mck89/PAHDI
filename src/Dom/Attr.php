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
 * DOM attribute nodes class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	bool		$isId		True if the attribute is an id
 */
class Attr extends Node
{	
	/**
	 * Node type
	 *
	 * @var		int
	 */
	public $nodeType = self::ATTRIBUTE_NODE;
	
	/**
	 * Attribute name
	 *
	 * @var		string
	 */
	public $name = "";
	
	/**
	 * Attribute value
	 *
	 * @var		string
	 */
	public $value = "";
	
	/**
	 * This property is used only when the value of the attribute
	 * must be used as a magic property
	 *
	 * @var		string
	 * @access	protected
	 * @ignore
	 */
	protected $_value = "";
	
	/**
	 * Owner element
	 *
	 * @var		Element
	 */
	public $ownerElement;
	
	/**
	 * Class constructor
	 *
	 * @param	string	$name	Attribute name
	 */
	function __construct ($name)
	{
		parent::__construct();
		if (!$name || preg_match("#[^\p{L}\p{N}:\.\-_\d]#u", utf8_encode($name))) {
			throw new DomException("String contains an invalid character");
		}
		static $mb;
		if (!isset($mb)) {
			$mb = function_exists("mb_strtolower");
		}
		$name = $mb ? mb_strtolower($name) : strtolower($name);
		$this->name = $this->nodeName = $this->localName = $name;
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
			case "isId":
			case "ownerElement":
				if ($value === null) {
					$this->$name = $value;
				} else {
					$msg = "Setting a property that has only a getter";
					throw new DomException($msg);
				}
			break;
			case "value":
				//If the attribute is "style" then set the css text of
				//the owner element
				if ($this->name === "style" && $this->ownerElement) {
					$this->ownerElement->style->cssText = $value;
				}
				$this->_value = $value;
			break;
			default:
				parent::__set($name, $value);
			break;
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
			case "isId":
				return $this->name === "id";
			break;
			case "value":
				//If the attribute is "style" then take the css text of
				//the owner element
				if ($this->name === "style" && $this->ownerElement) {
					$this->_value = $this->ownerElement->style->cssText;
				}
				return $this->_value;
			break;
			default:
				return parent::__get($name);
			break;
		}
	}
}