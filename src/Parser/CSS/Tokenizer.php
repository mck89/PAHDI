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
 * CSS parser tokenizer
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserCSSTokenizer extends ParserStream
{
	//Tokenizer states
	const EMPTY_STATE = 1;
	const SELECTOR_STATE = 2;
	const RULE_BLOCK_STATE = 3;
	const PROPERTY_NAME_STATE = 4;
	const WHITESPACE_BEFORE_ASSIGNMENT_STATE = 5;
	const INVALID_STATE = 6;
	const PROPERTY_VALUE_STATE = 7;
	const AT_RULE_NAME_STATE = 8;
	const AT_RULE_PARAM_STATE = 9;
	
	//Tokens type
	const SELECTOR = "selector";
	const PROP_NAME = "prop_name";
	const PROP_VALUE = "prop_val";
	const AT_RULE_NAME = "at_name";
	const AT_RULE_PARAM = "at_param";
	
	//atRules flag values
	const AT_RULE_SINGLE_BLOCK = 1;
	const AT_RULE_MULTI_BLOCK = 2;
	const AT_RULE_INVALID_VALUE = 3;
	
	/**
	 * Tokenizer's current state
	 *
	 * @var		int
	 */
	public $state;
	
	/**
	 * Tokenizer's current state
	 *
	 * @var		int
	 * @access 	protected
	 */
	protected $_atRule;
	
	/**
	 * Flag that indicates if the tokenizer has been activated to parse
	 * a style attribute
	 *
	 * @var		bool
	 * @access 	protected
	 */
	protected $_styleAttribute = false;
	
	/**
	 * Allowed css prefix
	 *
	 * @var		string
	 * @access 	protected
	 */
	protected $_prefix = "";
	
	/**
	 * Tokenizer's tokens
	 *
	 * @var		array
	 * @access 	protected
	 */
	protected $_tokens = array(
		"selector" => "",
		"prop_name" => "",
		"prop_val" => "",
		"at_name" => "",
		"at_param" => ""
	);
	
	/**
	 * Resulting structure
	 *
	 * @var		array
	 * @access 	protected
	 */
	protected $_structure = array();
	
	/**
	 * Current structure key
	 *
	 * @var		int
	 * @access 	protected
	 */
	protected $_currentKey = - 1;
	
	/**
	 * Current at rule block key in structure
	 *
	 * @var		int
	 * @access 	protected
	 */
	protected $_atRuleBlockKey;
	
	/**
	 * Last property name in structure
	 *
	 * @var		string
	 * @access 	protected
	 */
	protected $_lastPropertyName;
	
	/**
	 * Starts the tokenization
	 *
	 * @return 	void
	 * @access	protected
	 */
	protected function _tokenize ()
	{
		while (!$this->_isEOF) {
			$char = $this->_consume();
			switch ($this->state) {
				//EMPTY_STATE: the tokenizer is in this state when it's
				//outside rule blocks and at-rules blocks
				case self::EMPTY_STATE:
					//If the character is "@" and the arRule flag is not
					//set than switch the state to AT_RULE_NAME_STATE
					if ($char === "@" && !$this->_atRule) {
						$this->state = self::AT_RULE_NAME_STATE;
					}
					//If the character is "}" unset the atRule flag
					elseif ($char === "}") {
						$this->_atRule = null;
					}
					//If the character is one of [0-9a-zA-Z]#.[:* unconsume the
					//character and switch the state to SELECTOR_STATE
					elseif (($char >= "a" && $char <= "z") ||
							($char >= "A" && $char <= "Z") ||
							($char >= "0" && $char <= "9") ||
							$char === "#" || $char === "." ||
							$char === "[" || $char === ":" ||
							$char === "*") {
						$this->_unconsume();
						$this->state = self::SELECTOR_STATE;
					}
					//Otherwise continue
				break;
				
				//SELECTOR_STATE: the tokenizer is in this state when it is
				//processing a rule block selector
				case self::SELECTOR_STATE:
					//If the character is "}" unconsume the character and
					//switch the state to INVALID_STATE
					if ($char === "}") {
						$this->_unconsume();
						$this->state = self::INVALID_STATE;
					}
					//If the character is "{", emit the selector token and
					//switch the state to RULE_BLOCK_STATE
					elseif ($char === "{") {
						$this->state = self::RULE_BLOCK_STATE;
						$this->_emitToken(self::SELECTOR);
					}
					//Otherwise append the character to the selector token
					elseif ($char !== null) {
						$this->_tokens[self::SELECTOR] .= $char;
					}
				break;
				
				//RULE_BLOCK_STATE: the tokenizer is in this state when it is
				//inside a rule block
				case self::RULE_BLOCK_STATE:
					//If the character is a letter or "-" unconsume the
					//character and switch the state to PROPERTY_NAME_STATE
					if (($char >= "a" && $char <= "z") ||
						($char >= "A" && $char <= "Z") ||
						$char === "-") {
						$this->_unconsume();
						$this->state = self::PROPERTY_NAME_STATE;
					}
					//If it's ";" or a whitespace continue
					elseif ($char === "\x09" || $char === "\x0A" ||
							$char === "\x0C" || $char === "\x0D" ||
							$char === "\x20" || $char === ";") {
						continue;
					}
					//If it's "}" and the parser is not parsing a style
					//attribute switch the state to EMPTY_STATE, if the
					//atRule flag is AT_RULE_SINGLE_BLOCK unconsume the
					//character
					elseif ($char === "}" && !$this->_styleAttribute) {
						if ($this->_atRule === self::AT_RULE_SINGLE_BLOCK) {
							$this->_unconsume();
						}
						$this->state = self::EMPTY_STATE;
					}
					//Otherwise unconsume the character and switch the state
					//to INVALID_STATE
					elseif ($char !== null) {
						$this->_unconsume();
						$this->state = self::INVALID_STATE;
					}
				break;
				
				//PROPERTY_NAME_STATE: the tokenizer is in this state when
				//it is processing a property name
				case self::PROPERTY_NAME_STATE:
					//If the character is ";" empty the property name token
					//and switch the state to RULE_BLOCK_STATE
					if ($char === ";") {
						$this->_tokens[self::PROP_NAME] = "";
						$this->state = self::RULE_BLOCK_STATE;
					}
					//If it's a letter or "-" append the character to the
					//property name token
					elseif (($char >= "a" && $char <= "z") ||
							($char >= "A" && $char <= "Z") ||
							$char === "-") {
						$this->_tokens[self::PROP_NAME] .= $char;
					}
					//If it's ":" emit the property name token and switch
					//the state to PROPERTY_VALUE_STATE
					elseif ($char === ":") {
						$this->state = self::PROPERTY_VALUE_STATE;
						$this->_emitToken(self::PROP_NAME);
					}
					//If it's a whitespace switch the state to
					//WHITESPACE_BEFORE_ASSIGNMENT_STATE
					elseif ($char === "\x09" || $char === "\x0A" ||
							$char === "\x0C" || $char === "\x0D" ||
							$char === "\x20") {
						$this->state = self::WHITESPACE_BEFORE_ASSIGNMENT_STATE;
					}
					//Otherwise unconsume the character and switch the state
					//to INVALID_STATE
					elseif ($char !== null) {
						$this->_unconsume();
						$this->state = self::INVALID_STATE;
					}
				break;
				
				//WHITESPACE_BEFORE_ASSIGNMENT_STATE: the tokenizer is in this
				//state when it has parsed a property name and it is waiting
				//for ":" to switch to the PROPERTY_VALUE_STATE
				case self::WHITESPACE_BEFORE_ASSIGNMENT_STATE:
					//If the character is ";" or ":" unconsume the character
					//and switch the state to PROPERTY_NAME_STATE
					if ($char === ";" || $char === ":") {
						$this->_unconsume();
						$this->state = self::PROPERTY_NAME_STATE;
					}
					//If it's a whitespace continue
					elseif ($char === "\x09" || $char === "\x0A" ||
							$char === "\x0C" || $char === "\x0D" ||
							$char === "\x20") {
						continue;
					}
					//Otherwise unconsume the character and switch the state
					//to INVALID_STATE
					elseif ($char !== null) {
						$this->_unconsume();
						$this->state = self::INVALID_STATE;
					}
				break;
				
				//INVALID_STATE: the tokenizer is in this state when it is
				//processing an invalid group of characters
				case self::INVALID_STATE:
					//If the character is "}" and the parser is not parsing
					//a style attribute unconsume the character and switch
					//the state to EMPTY_STATE
					if ($char === "}" && !$this->_styleAttribute) {
						$this->_unconsume();
						$this->state = self::EMPTY_STATE;
					}
					//If it's ";", then if the atRule flag is different from
					//AT_RULE_INVALID_VALUE then switch to the RULE_BLOCK_STATE
					//otherwise unset the atRule flag an switch to EMPTY_STATE
					elseif ($char === ";") {
						if ($this->_atRule !== self::AT_RULE_INVALID_VALUE) {
							$this->state = self::RULE_BLOCK_STATE;
						} else {
							$this->_atRule = null;
							$this->state = self::EMPTY_STATE;
						}
					}
				break;
				
				//INVALID_STATE: the tokenizer is in this state when it is
				//processing an invalid group of characters
				case self::PROPERTY_VALUE_STATE:
					//If the character is ";" switch the state to RULE_BLOCK_STATE
					//and emit the property value token
					if ($char === ";") {
						$this->state = self::RULE_BLOCK_STATE;
						$this->_emitToken(self::PROP_VALUE);
					}
					//If it's "}" unconsume the character and emit the property value
					//token then if the atRule flag is AT_RULE_MULTI_BLOCK switch the
					//state to RULE_BLOCK_STATE otherwise switch the state to
					//INVALID_STATE 
					elseif ($char === "}") {
						$this->_unconsume();
						if ($this->_atRule === self::AT_RULE_MULTI_BLOCK) {
							$this->state = self::RULE_BLOCK_STATE;
						} else {
							$this->state = self::INVALID_STATE;
						}
						$this->_emitToken(self::PROP_VALUE);
					}
					//If it's a single or double quote consume every character
					//until the same quote character has been found and append
					//them to the property value token. If the closing quote is
					//missing match everything until the end of the file has been
					//reached and emit the property value token
					elseif ($char === '"' || $char === "'") {
						$chars = $char;
						$chars .= $this->_consumeUntilFind($char);
						if ($chars === false) {
							$chars = $this->_consumeRemaining();
							$this->_tokens[self::PROP_VALUE] .= $chars;
							$this->_emitToken(self::PROP_VALUE);
						} else {
							$chars .= $this->_consume();
							$this->_tokens[self::PROP_VALUE] .= $chars;
						}
					}
					//Anything else: apppend the character to the property value
					//token
					elseif ($char !== null) {
						$this->_tokens[self::PROP_VALUE] .= $char;
					}
					//EOF: emit the property value token
					else {
						$this->_emitToken(self::PROP_VALUE);
					}
				break;
				
				//AT_RULE_NAME_STATE: the tokenizer is in this state when it is
				//processing the name of an at rule
				case self::AT_RULE_NAME_STATE:
					//If the character is a letter or "-" than append the character
					//to the at rule name token
					if (($char >= "a" && $char <= "z") ||
						($char >= "A" && $char <= "Z") ||
						$char === "-") {
						$this->_tokens[self::AT_RULE_NAME] .= $char;
					}
					//If it's a whitespace emit the at rule name token and switch
					//the state to AT_RULE_PARAM_STATE
					elseif ($char === "\x09" || $char === "\x0A" ||
							$char === "\x0C" || $char === "\x0D" ||
							$char === "\x20") {
						$this->_emitToken(self::AT_RULE_NAME);
						$this->state = self::AT_RULE_PARAM_STATE;
					}
					//If it's "{" emit the at rule name token, unconsume the
					//character and switch the state to AT_RULE_PARAM_STATE
					elseif ($char === "{") {
						$this->_emitToken(self::AT_RULE_NAME);
						$this->_unconsume();
						$this->state = self::AT_RULE_PARAM_STATE;
					}
					//If it's ";" emit the at name token and switch the state to
					//EMPTY_STATE
					elseif ($char === ";") {
						$this->_emitToken(self::AT_RULE_NAME);
						$this->state = self::EMPTY_STATE;
					}
					//Otherwise unconsume the character, switch the state
					//to INVALID_STATE, and set the atRule flag to
					//AT_RULE_INVALID_VALUE
					elseif ($char !== null) {
						$this->_unconsume();
						$this->_atRule = self::AT_RULE_INVALID_VALUE;
						$this->state = self::INVALID_STATE;
					}
				break;
				
				//AT_RULE_PARAM_STATE: the tokenizer is in this state when it is
				//processing the parameters of an at rule
				case self::AT_RULE_PARAM_STATE:
					//If the character is "}" unconsume the character and
					//switch the state to INVALID_STATE
					if ($char === "}") {
						$this->_unconsume();
						$this->state = self::INVALID_STATE;
					}
					//If it's ";" emit the at rule param token and switch the state
					//to EMPTY_STATE
					elseif ($char === ";") {
						$this->_emitToken(self::AT_RULE_PARAM);
						$this->state = self::EMPTY_STATE;
					}
					//If it's "{" emit the at rule param token then if the at rule is
					//"media" or "keyframes" (with or without css prefix) then set
					//the atRule flag to AT_RULE_MULTI_BLOCK and switch to the
					//EMPTY_STATE othwrwise set the atRule flag to AT_RULE_SINGLE_BLOCK
					//and switch to the RULE_BLOCK_STATE
					elseif ($char === "{") {
						$atName = $this->_structure[$this->_currentKey]["name"];
						$this->_emitToken(self::AT_RULE_PARAM);
						if ($atName === "media" ||
							$atName === "keyframes" ||
							$atName === $this->_prefix . "keyframes") {
							$this->_atRule = self::AT_RULE_MULTI_BLOCK;
							$this->state = self::EMPTY_STATE;
						} else {
							$this->_atRule = self::AT_RULE_SINGLE_BLOCK;
							$this->state = self::RULE_BLOCK_STATE;
						}
					}
					//If it's a single or double quote consume every character
					//until the same quote character has been found and append
					//them to the at param token. If the closing quote is
					//missing match everything until the end of the file has been
					//reached and emit the at param token
					elseif ($char === '"' || $char === "'") {
						$chars = $char;
						$chars .= $this->_consumeUntilFind($char);
						if ($chars === false) {
							$chars = $this->_consumeRemaining();
							$this->_tokens[self::AT_RULE_PARAM] .= $chars;
							$this->_emitToken(self::AT_RULE_PARAM);
						} else {
							$chars .= $this->_consume();
							$this->_tokens[self::AT_RULE_PARAM] .= $chars;
						}
					}
					//Anything else: append the character to the at param token
					elseif ($char !== null) {
						$this->_tokens[self::AT_RULE_PARAM] .= $char;
					}
				break;
			}
		}
	}
	
	/**
	 * Emits a token and create the resulting structure
	 *
	 * @param	string		$token		Token name
	 * @return 	void
	 * @access	protected
	 */
	protected function _emitToken ($token)
	{
		$value = trim($this->_tokens[$token]);
		$this->_tokens[$token] = "";
		//If it's parsing a style attribute then the only tokens
		//that this method will receive are property name and values
		if ($this->_styleAttribute) {
			if ($token === self::PROP_NAME) {
				$this->_structure[$value] = null;
				$this->_lastPropertyName = $value;
			} elseif ($token === self::PROP_VALUE) {
				list($value, $important) = $this->_fixPropertyValue($value);
				$this->_structure[$this->_lastPropertyName] = array(
					"value" => $value,
					"important" => $important
				);
			}
			return;
		}
		switch ($token) {
			case self::AT_RULE_NAME:
				$this->_structure[] = array(
					"type" => "at_rule",
					"name" => $value
				);
				$this->_currentKey++;
			break;
			
			case self::AT_RULE_PARAM:
				if ($value) {
					$this->_structure[$this->_currentKey]["params"] = $value;
				}
			break;
			
			case self::SELECTOR:
				$key = $this->_currentKey;
				//Multi block at rule
				if ($this->_atRule === self::AT_RULE_MULTI_BLOCK) {
					if (!isset($this->_structure[$key]["blocks"])) {
						$this->_structure[$key]["blocks"] = array();
						$this->_atRuleBlockKey = - 1;
					}
					$this->_structure[$key]["blocks"][] = array(
						"selector" => $value,
						"rules" => array()
					);
					$this->_atRuleBlockKey++;
				}
				//Simple block
				else {
					$this->_structure[] = array(
						"type" => "block",
						"selector" => $value,
						"rules" => array()
					);
					$this->_currentKey++;
				}
			break;
			
			case self::PROP_NAME:
				$key = $this->_currentKey;
				//Multi block at rule
				if ($this->_atRule === self::AT_RULE_MULTI_BLOCK) {
					$atKey = $this->_atRuleBlockKey;
					$this->_structure[$key]["blocks"][$atKey]["rules"][$value] = null;
				}
				//Single block at rule and simple block
				else {
					if (!isset($this->_structure[$key]["rules"])) {
						$this->_structure[$key]["rules"] = array();
					}
					$this->_structure[$key]["rules"][$value] = null;
				}
				$this->_lastPropertyName = $value;
			break;
			
			case self::PROP_VALUE:
				$key = $this->_currentKey;
				$name = $this->_lastPropertyName;
				//Multi block at rule
				list($value, $important) = $this->_fixPropertyValue($value);
				if ($this->_atRule === self::AT_RULE_MULTI_BLOCK) {
					$atKey = $this->_atRuleBlockKey;
					$this->_structure[$key]["blocks"][$atKey]["rules"][$name] = array(
						"value" => $value,
						"important" => $important
					);
				}
				//Single block at rule and simple block
				else {
					$this->_structure[$key]["rules"][$name] = array(
						"value" => $value,
						"important" => $important
					);
				}
			break;
		}
	}
	
	/**
	 * Fixes a property value
	 *
	 * @param	string		$value		Value
	 * @return 	array		An array where the first offset is the fixed value
	 *						and the second is "important" if the value has the
	 *						!important flag otherwise an empty string
	 * @access	protected
	 */
	function _fixPropertyValue ($value)
	{
		$pos = strpos($value, "!important");
		if ($pos !== false) {
			$value = substr($value, 0, $pos);
			$important = "important";
		} else {
			$important = "";
		}
		return array($value, $important);
	}
}