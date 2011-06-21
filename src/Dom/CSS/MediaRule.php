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
 * DOM CSS media rule
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class CSSMediaRule extends CSSRule
{
	/**
	 * Sheet css rules
	 *
	 * @var		CSSRulesList
	 */
	public $cssRules;
	
	/**
	 * Rule type
	 *
	 * @var			int
	 */
	public $type = 4;
	
	/**
	 * Rule media list
	 *
	 * @var		MediaList
	 */
	public $media;
	
	/**
	 * Class constructor.
	 */
	function __construct ()
	{
		$this->media = new MediaList;
		$this->cssRules = new CSSRulesList;
	}
	
	/**
	 * Insert a rule at the given index
	 *
	 * @param	string|CSSRule	$rule	Rule code or css rule instance
	 * @param	int				$index	Index at which to insert the rule
	 * @return	void
	 */
	function insertRule ($rule, $index = null)
	{
		if (!$rule instanceof CSSRule) {
			$parser = new ParserCSS($rule, $this);
			$sheet = $parser->parse();
			//Ignore if there are 0 or more than one rules
			$length = $sheet->cssRules->length;
			if ($length !== 1) {
				return;
			}
			$rule = $sheet->cssRules[0];
		}
		if (!$rule instanceof CSSStyleRule) {
			return;
		}
		if ($rule->parentRule) {
			$rule->parentRule->deleteRule($rule);
		} elseif ($rule->parentStyleSheet) {
			$rule->parentStyleSheet->deleteRule($rule);
		}
		$rule->parentStyleSheet = $this->parentStyleSheet;
		$rule->parentRule = $this;
		if ($index !== null) {
			$this->cssRules->_addNodeAt($rule, $index);
		} else {
			$this->cssRules->_appendNode($rule);
		}
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
				$ret = "@media " . $this->media->mediaText . "{";
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