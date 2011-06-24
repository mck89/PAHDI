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
 * CSS parser structure builder
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserCSSBuilder extends ParserCSSTokenizer
{
	/**
	 * Base uri
	 *
	 * @var		string
	 * @access 	protected
	 */
	protected $_uri = "";
	
	/**
	 * Owner rule or element
	 *
	 * @var		Element
	 * @access 	protected
	 */
	protected $_owner;
	
	/**
	 * Builds the structure resulted from the parsing of a css code
	 *
	 * @return 	CSSStyleSheet		Resulting structure
	 * @access	protected
	 */
	function _build ()
	{
		$ret = new CSSStyleSheet($this->_prefix);
		$ret->href = $this->_uri;
		if ($this->_owner instanceof Element) {
			$ret->ownerNode = $this->_owner;
		} elseif ($this->_owner instanceof CSSStyleSheet) {
			$ret->parentStyleSheet = $this->_owner;
		} else {
			$ret->ownerRule = $this->_owner;
			$ret->parentStyleSheet = $this->_owner->parentStyleSheet;
		}
		if (count($this->_structure)) {
			foreach ($this->_structure as $rule) {
				$r = null;
				//Simple rule block
				if ($rule["type"] === "block") {
					$r = new CSSStyleRule($rule["selector"]);
					$ret->insertRule($r);
					$this->_applyPropsToStyle($r->style, $rule["rules"]);
					$hasStyle = true;
				}
				//Charset at rule
				elseif ($rule["name"] === "charset") {
					//There must be only a string as parameter
					$reg = "#^([\"'])([^\\1]+)\\1$#";
					if (isset($rule["params"]) &&
						preg_match($reg, $rule["params"], $match)) {
						$r = new CSSCharsetRule;
						$ret->insertRule($r);
						$r->encoding = $match[2];
					}
				}
				//Font-face at rule
				elseif ($rule["name"] === "font-face") {
					//There must be no parameter
					if (!isset($rule["params"]) || !$rule["params"]) {
						$r = new CSSFontFaceRule;
						$ret->insertRule($r);
						$this->_applyPropsToStyle($r->style, $rule["rules"]);
					}
				}
				//Page at rule
				elseif ($rule["name"] === "page") {
					//The parameter is optional so there's no need to
					//validate it
					$r = new CSSPageRule;
					$ret->insertRule($r);
					if (isset($rule["params"])) {
						$r->selectorText = $rule["params"];
					}
					$this->_applyPropsToStyle($r->style, $rule["rules"]);
				}
				//Import at rule
				elseif ($rule["name"] === "import") {
					//The parameter must start with a string
					$reg = "#^([\"'])([^\\1]+)\\1#";
					if (isset($rule["params"]) &&
						preg_match($reg, $rule["params"], $match)) {
						$start = preg_quote($match[0], "#");
						$media = trim(preg_replace("#^$start#", "", $rule["params"]));
						$r = new CSSImportRule($match[2]);
						$ret->insertRule($r);
						if ($media) {
							$r->media->mediaText = $media;
						}
					}
				}
				//Media at-rule
				elseif ($rule["name"] === "media") {
					//There must be at least a parameter
					if (isset($rule["params"]) && $rule["params"]) {
						$r = new CSSMediaRule;
						$ret->insertRule($r);
						if ($rule["params"]) {
							$r->media->mediaText = $rule["params"];
						}
						if (isset($rule["blocks"]) && count($rule["blocks"])) {
							foreach ($rule["blocks"] as $block) {
								$i = new CSSStyleRule($block["selector"]);
								$r->insertRule($i);
								$this->_applyPropsToStyle($i->style, $block["rules"]);
							}
						}
					}
				}
				//Keyframes at rule
				elseif ($rule["name"] === "keyframes" ||
						$rule["name"] === $this->_prefix . "keyframes") {
					//There must be one parameter with alphanumeric characters
					//and "-"
					if (isset($rule["params"]) &&
						preg_match("#^[\w\-]+$#", $rule["params"])) {
						$r = new CSSKeyframesRule;
						$ret->insertRule($r);
						$r->name = $rule["params"];
						if (isset($rule["blocks"]) && count($rule["blocks"])) {
							foreach ($rule["blocks"] as $block) {
								$i = new CSSKeyframeRule($block["selector"]);
								$r->appendRule($i);
								$this->_applyPropsToStyle($i->style, $block["rules"]);
							}
						}
					}
				}
			}
		}
		return $ret;
	}
	
	/**
	 * Builds the structure resulted from the parsing of a style attribute
	 *
	 * @return 	CSSStyleDeclaration		Resulting structure
	 * @access	protected
	 */
	function _buildStyleAttribute ()
	{
		$ret = new CSSStyleDeclaration($this->_owner);
		$this->_applyPropsToStyle($ret, $this->_structure);
		return $ret;
	}
	
	/**
	 * Builds the structure resulted from the parsing of a style attribute
	 *
	 * @param	CSSStyleDeclaration		$style	Style object
	 * @param	array					$prop	Properties array
	 * @return 	void
	 * @access	protected
	 */
	function _applyPropsToStyle (CSSStyleDeclaration $style, $prop)
	{
		if (count($prop)) {
			foreach ($prop as $k => $v) {
				if (!$v) {
					continue;
				}
				$style->setProperty($k, $v["value"], $v["important"]);
			}
		}
	}
}