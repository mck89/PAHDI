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
 * DOM CSS charset rule
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class CSSCharsetRule extends CSSRule
{
	/**
	 * Rule type
	 *
	 * @var			int
	 */
	public $type = 2;
	
	/**
	 * Rule encoding
	 *
	 * @var		string
	 */
	public $encoding = "";
	
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
			case "cssText":
				return "@charset \"{$this->encoding}\";";
			break;
		}
	}
}