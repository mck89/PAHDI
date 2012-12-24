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
 * DOM CSS rule
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	string	$cssText		String representation of the rule
 * @abstract
 */
abstract class CSSRule extends DomObject
{
	//Rules type constants
	/**
	 * Unknown rule type
	 *
	 * @const	int
	 */
	const UNKNOWN_RULE = 0;
	
	/**
	 * Style rule type
	 *
	 * @const	int
	 */
	const STYLE_RULE = 1;
	
	/**
	 * Charset rule type
	 *
	 * @const	int
	 */
	const CHARSET_RULE = 2;
	
	/**
	 * Import rule type
	 *
	 * @const	int
	 */
	const IMPORT_RULE = 3;
	
	/**
	 * Media rule type
	 *
	 * @const	int
	 */
	const MEDIA_RULE = 4;
	
	/**
	 * Font-face rule type
	 *
	 * @const	int
	 */
	const FONT_FACE_RULE = 5;
	
	/**
	 * Page rule type
	 *
	 * @const	int
	 */
	const PAGE_RULE = 6;
	
	/**
	 * Keyframes rule type
	 *
	 * @const	int
	 */
	const KEYFRAMES_RULE = 7;
	
	/**
	 * Keyframe rule type
	 *
	 * @const	int
	 */
	const KEYFRAME_RULE = 8;
	
	/**
	 * Rule type
	 *
	 * @var		int
	 */
	public $type;
	
	/**
	 * Parent style sheet
	 *
	 * @var		CSSStyleSheet
	 */
	public $parentStyleSheet;
	
	/**
	 * Parent rule
	 *
	 * @var		CSSRule
	 */
	public $parentRule;
	
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
			case "cssText":
				//ignore
			break;
			default:
				$this->$name = $value;
			break;
		}
	}
}