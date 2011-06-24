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
 * DOM CSS page rule
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class CSSPageRule extends CSSRule
{
	/**
	 * Rule type
	 *
	 * @var			int
	 */
	public $type = 6;
	
	/**
	 * Rule selector
	 *
	 * @var		string
	 */
	public $selectorText = "";
	
	/**
	 * Rule style
	 *
	 * @var		CSSStyleDeclaration
	 */
	public $style;
	
	/**
	 * Class constructor.
	 */
	function __construct ()
	{
		$this->style = new CSSStyleDeclaration($this);
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
			case "cssText":
				$ret = "@page";
				if ($this->selectorText) {
					$ret .= " " . $this->selectorText;
				}
				$ret .= "{" . $this->style->cssText . "}";
				return $ret;
			break;
		}
		return null;
	}
}