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
 * DOM Style Sheet
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	string			$title		Owner node title
 * @property-read	string			$type		Owner node type
 * @property-read	MediaList		$media		Media list
 * @property		bool			$disabled	True if the sheet is disabled
 */
class CSSStyleSheet extends DomObject
{
	/**
	 * Sheet css rules
	 *
	 * @var		CSSRuleList
	 */
	public $cssRules;
	
	/**
	 * Style sheet href
	 *
	 * @var		string
	 */
	public $href;
	
	/**
	 * Owner node
	 *
	 * @var		Node
	 */
	public $ownerNode;
	
	/**
	 * Owner CSSRule
	 *
	 * @var		CSSRule
	 */
	public $ownerRule;
	
	/**
	 * Parent style sheet
	 *
	 * @var		CSSStyleSheet
	 */
	public $parentStyleSheet;
	
	/**
	 * Allowed css prefix
	 *
	 * @var		string
	 * @ignore
	 */
	public $_prefix = "";
	
	/**
	 * Media list
	 *
	 * @var		MediaList
	 * @access	protected
	 * @ignore
	 */
	protected $_media = "";
	
	/**
	 * Class constructor.
	 *
	 * @param	string		$prefix		Allowed css prefix
	 */
	function __construct ($prefix)
	{
		$this->cssRules = new CSSRulesList;
		$this->_prefix = $prefix;
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
			//Ignore if there is more than one rule
			$length = $sheet->cssRules->length;
			if ($length !== 1) {
				return;
			}
			$rule = $sheet->cssRules[0];
		}
		if ($rule instanceof CSSKeyframeRule) {
			return;
		}
		if ($rule->parentStyleSheet) {
			$rule->parentStyleSheet->deleteRule($rule);
		}
		$rule->parentStyleSheet = $this;
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
			case "title":
			case "type":
				$ret = "";
				if ($this->ownerNode) {
					$ret = $this->ownerNode->$name;
					if ($name === "type" && !$ret) {
						return "text/css";
					}
				} elseif ($this->parentStyleSheet) {
					return $this->parentStyleSheet->$name;
				}
				return $ret;
			break;
			case "media":
				if (!$this->_media) {
					if ($this->ownerNode) {
						$media = $this->ownerNode->media;
						$this->_media = new MediaList;
						$this->_media->mediaText = $media;
					} else {
						$this->_media = $this->ownerRule->media;
					}
				}
				return $this->_media;
			break;
			case "disabled":
				$ret = false;
				if ($this->ownerNode) {
					$ret = $this->ownerNode->disabled;
				}
				return $ret;
			break;
		}
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
			case "title":
			case "type":
			case "media":
				throw new DomException("Setting a property that has only a getter");
			break;
			case "disabled":
				if ($this->ownerNode) {
					$this->ownerNode->disabled = $value;
				}
			break;
			default:
				$this->$name = $value;
			break;
		}
	}
}