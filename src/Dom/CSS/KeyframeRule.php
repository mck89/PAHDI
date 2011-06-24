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
 * DOM CSS keyframe rule
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class CSSKeyframeRule extends CSSRule
{
	/**
	 * Rule key text
	 *
	 * @var		string
	 */
	public $keyText;
	
	/**
	 * Rule style
	 *
	 * @var		CSSStyleDeclaration
	 */
	public $style;
	
	/**
	 * Rule type
	 *
	 * @var			int
	 */
	public $type = 8;
	
	/**
	 * Class constructor.
	 *
	 * @param	string		$keytext	Key text
	 */
	function __construct ($keytext)
	{
		$this->style = new CSSStyleDeclaration($this);
		$this->keyText = $keytext;
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
				$ret = $this->keyText . "{";
				$ret .= $this->style->cssText . "}";
				return $ret;
			break;
		}
		return null;
	}
}