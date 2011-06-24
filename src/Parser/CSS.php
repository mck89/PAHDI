<?php
/**
 * This file is part of the PAHDI (PHP Advanced HTML Dom Implementation)
 * library, for the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @category    PAHDI
 * @package     PAHDI-Parser
 * @author      Marco Marchiò
 * @license     http://opensource.org/licenses/bsd-license.php The BSD License
 * @copyright	Copyright (c) 2011, Marco Marchiò
 */
 
/**
 * CSS parser
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserCSS extends ParserCSSBuilder
{
	/**
	 * Class constructor. Sets the HTML code to parse.
	 *
	 * @param	string	$code		HTML code
	 * @param	object	$parent		Element that has initialized the parser,
	 *								parent css rule or style sheet
	 * @param	string	$uri		Base uri. If given the code will be taken
	 *								from it.
	 */
	function __construct ($code, $parent, $uri = false)
	{
		if (!$code && $uri) {
			$code = $this->_getCode($uri);
		}
		if ($parent instanceof Element) {
			if (!$uri) {
				$uri = $parent->baseURI;
			}
			$impl = $parent->ownerDocument->_implementation;
			$prefix = isset($impl["cssPrefix"]) ? $impl["cssPrefix"] : null;
		} elseif ($parent instanceof CSSStyleSheet) {
			$prefix =  $parent->_prefix;
		} else {
			$prefix = $parent->parentStyleSheet->_prefix;
		}
		$this->_owner = $parent;
		$this->_prefix = $prefix;
		$this->_uri = $uri;
		parent::__construct($code);
		//Remove comments
		$this->code = preg_replace("#/\*.*?\*/#s", "", $this->code);
		//Remove unclosed comment
		$pos = strpos($this->code, "/*");
		if ($pos !== false) {
			$this->code = substr($this->code, 0, $pos);
		}
		$this->state = self::EMPTY_STATE;
	}

	/**
	 * Starts the parsing process
	 *
	 * @return	CSSStyleSheet		Result of the parsing
	 */
	function parse ()
	{
		$this->_tokenize();
		return $this->_build();
	}
	
	/**
	 * Starts the parsing process of a style attribute
	 *
	 * @return	CSSStyleDeclaration	Result of the parsing
	 */
	function parseStyleAttribute ()
	{
		$this->_styleAttribute = true;
		$this->state = self::RULE_BLOCK_STATE;
		$this->_tokenize();
		return $this->_buildStyleAttribute();
	}
	
	/**
	 * Starts the parsing process of a style attribute
	 *
	 * @param	string		$uri	Path of the file that contains
	 *								the code
	 * @return	string		Code
	 */
	function _getCode ($uri)
	{
		return (string) @file_get_contents($uri);
	}
}