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
 * DOM CSS keyframes rule
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class CSSKeyframesRule extends CSSRule
{
	/**
	 * Sheet css rules
	 *
	 * @var		CSSRulesList
	 */
	public $cssRules;
	
	/**
	 * Rule name
	 *
	 * @var		string
	 */
	public $name;
	
	/**
	 * Rule type
	 *
	 * @var			int
	 */
	public $type = 7;
	
	/**
	 * Class constructor.
	 */
	function __construct ()
	{
		$this->cssRules = new CSSRulesList;
	}
	
	/**
	 * Append a rule to the current one
	 *
	 * @param	string|CSSRule	$rule	Rule code or css rule instance
	 * @return	void
	 */
	function appendRule ($rule)
	{
		if (!$rule instanceof CSSRule) {
			$rule = "@keyframes test{" . $rule . "}";
			$parser = new ParserCSS($rule, $this);
			$sheet = $parser->parse();
			if (!$sheet->cssRules->length) {
				return;
			}
			$parentRule = $sheet->cssRules[0];
			//Ignore if there are 0 or more than one rules
			$length = $parentRule->cssRules->length;
			if ($length !== 1) {
				return;
			}
			$rule = $parentRule->cssRules[0];
		}
		if (!$rule instanceof CSSKeyframeRule) {
			return;
		}
		if ($rule->parentRule) {
			$rule->parentRule->deleteRule($rule);
		} elseif ($rule->parentStyleSheet) {
			$rule->parentStyleSheet->deleteRule($rule);
		}
		$rule->parentStyleSheet = $this->parentStyleSheet;
		$rule->parentRule = $this;
		$this->cssRules->_appendNode($rule);
	}
	
	/**
	 * Remove the rule at the given index
	 *
	 * @param	int|CSSRule		$index	Index of the rule to remove or rule
	 * 									object to remove
	 * @return	void
	 */
	function deleteRule ($index)
	{
		if ($index instanceof CSSRule) {
			$rule = $index;
			$index = null;
			$length = $this->cssRules->length;
			for ($i = 0; $i < $length; $i++) {
				if ($this->cssRules[$i] === $rule) {
					$index = $i;
					break;
				}
			}
			if ($index === null) {
				throw new DomException("Rule not found");
			}
		} elseif (!isset($this->cssRules[$index])) {
			throw new DomException("Index or size is negative or greater than the allowed amount");
		}
		$rule = $this->cssRules[$index];
		$rule->parentStyleSheet = null;
		$rule->parentRule = null;
		$this->cssRules->_removeNodeAt($index);
	}
	
	/**
	 * Returns the rule with the given keytext or null if not found
	 *
	 * @param	string	$keytext	Keytext to search
	 * @return	mixed	Css rule with the given key text or null if not
	 *					found
	 */
	function findRule ($keytext)
	{
		$l = $this->cssRules->length;
		for ($i = 0; $i < $l; $i++) {
			if ($this->cssRules[$i]->keyText === $keytext) {
				return $this->cssRules[$i];
			}
		}
		return null;
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
				$ret = "@keyframes " . $this->name . "{";
				$l = $this->cssRules->length;
				for ($i = 0; $i < $l; $i++) {
					$ret .= $this->cssRules[$i]->cssText;
				}
				$ret .= "}";
				return $ret;
			break;
		}
		return null;
	}
}