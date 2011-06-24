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
 * CSS selector parser tokenizer
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserSelectorTokenizer extends ParserStream
{
	/**
	 * Tag name token type
	 *
	 * @const		int
	 */
	const TAG = 1;
	
	/**
	 * Class name token type
	 *
	 * @const		int
	 */
	const CLASSNAME = 2;
	
	/**
	 * Id token type
	 *
	 * @const		int
	 */
	const ID = 3;
	
	/**
	 * Attribute token type
	 *
	 * @const		int
	 */
	const ATTR = 4;
	
	/**
	 * Pseudo token type
	 *
	 * @const		int
	 */
	const PSEUDO = 5;
	
	/**
	 * Combinator token type
	 *
	 * @const		int
	 */
	const COMBINATOR = 6;
	
	/**
	 * Current token
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_token = array(
		"type" => self::TAG,
		"content" => ""
	);
	
	/**
	 * Tokens array
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_tokens = array();
	
	/**
	 * Resulting grups
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_groups = array();
	
	/**
	 * Flag that indicates if the current character must
	 * be escaped or not
	 *
	 * @var		bool
	 * @access	protected
	 */
	protected $_escape = false;
	
	/**
	 * Flag that indicates if the tokenizer is parsing
	 * an attribute token
	 *
	 * @var		bool
	 * @access	protected
	 */
	protected $_inAttrToken = false;
	
	/**
	 * Flag that indicates if the tokenizer is parsing
	 * a pseudo attribute with parenthesis
	 *
	 * @var		bool
	 * @access	protected
	 */
	protected $_inPseudoToken = false;
	
	/**
	 * Current combinator
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_combinator = "";
	
	/**
	 * Starts the tokenization
	 *
	 * @return 	bool	Tokenization result
	 * @access	protected
	 */
	protected function _tokenize ()
	{
		while (!$this->_isEOF) {
			$char = $this->_consume();
			$isEscaped = $this->_escape || $this->_inAttrToken || $this->_inPseudoToken;
			//If the end is reached emit the token and stop
			if ($char === null) {
				$this->_emitToken();
				break;
			}
			//If the character is not escaped and is a "#" then start an id token
			elseif (!$isEscaped && $char === "#") {
				$this->_emitToken();
				$this->_token["type"] = self::ID;
			}
			//If the character is not escaped and is a "." then start a class
			//name token
			elseif (!$isEscaped && $char === ".") {
				$this->_emitToken();
				$this->_token["type"] = self::CLASSNAME;
			}
			//If the character is not escaped and is a ":" then start a pseudo
			//token
			elseif (!$isEscaped && $char === ":") {
				$this->_emitToken();
				$this->_token["type"] = self::PSEUDO;
			}
			//If the character is not escaped and is a "[" then start an attribute
			//token
			elseif (!$isEscaped && $char === "[") {
				$this->_emitToken();
				$this->_token["type"] = self::ATTR;
				$this->_inAttrToken = true;
			}
			//If the character is not escaped and is a "]" then emit the attribute
			//token and start a tag name token
			elseif (!$this->_escape && !$this->_inPseudoToken && $char === "]") {
				//If there's no attribute token return false
				if (!$this->_inAttrToken) {
					return false;
				}
				$this->_emitToken();
				$this->_token["type"] = self::TAG;
				$this->_inAttrToken = false;
			}
			//If the character is not escaped and is a "(" then move then store the
			//current content of the token and get the content between parenthesis
			elseif (!$isEscaped && $char === "(") {
				//The current token must be a pseudo token
				if ($this->_token["type"] !== self::PSEUDO) {
					return false;
				}
				$this->_token["fn"] = $this->_token["content"];
				$this->_token["content"] = "";
				$this->_inPseudoToken = true;
			}
			//If the character is not escaped and is a ")" then emit the pseudo token
			//and start a tag name token
			elseif (!$this->_escape && !$this->_inAttrToken && $char === ")") {
				//If there's no pseudo token return false
				if (!$this->_inPseudoToken) {
					return false;
				}
				$this->_emitToken();
				$this->_token["type"] = self::TAG;
				$this->_inPseudoToken = false;
			}
			//If the character is not escaped and is a single or double quote get every
			//character until a matching quote has been found
			elseif (!$this->_escape && ($char === '"' || $char === "'")) {
				//If the token is not parsing an attribute token or a pseudo token then
				//return false
				if (!$this->_inPseudoToken && !$this->_inAttrToken) {
					return false;
				}
				$chars = $this->_consumeUntilFind($char);
				//If there's no closing quote return false
				if ($chars === false) {
					return false;
				}
				$this->_consume();
				$this->_token["content"] .= $chars;
			}
			//If the character is not escaped and is an escape character escape the
			//next character
			elseif (!$isEscaped && $char === "\\") {
				$this->_escape = true;
			}
			//If the character is not escaped and it's a combinator emit the current
			//token, store the combinator character and start a tag name token
			elseif (!$isEscaped && ($char === " " || $char === ">" || $char === "+" ||
					$char === "~" || $char === ",")) {
				$this->_emitToken();
				$this->_token["type"] = self::TAG;
				$this->_combinator .= $char;
			}
			//Append every other character to the content of the token
			else {
				$this->_token["content"] .= $char;
				$this->_escape = false;
			}
		}
		//If the selector finishes with an escape character or an attribute or pseudo
		//selector has not been closed then there's a parsing error
		if ($this->_escape || $this->_inAttrToken || $this->_inPseudoToken) {
			return false;
		}
		return $this->_fixTokens();
	}
	
	/**
	 * Emits the current token
	 *
	 * @return 	void
	 * @access	protected
	 */
	protected function _emitToken ()
	{
		if (!$this->_token["content"]) {
			return;
		}
		//If there's a pending combinator token emit it
		if ($this->_combinator) {
			$comb = trim($this->_combinator);
			//If the token is not "," but it contains
			//that character emit a combinator token
			//for that character
			if ($comb && $comb !== "," && $comb[0] === ",") {
				$token = array(
					"type" => self::COMBINATOR,
					"content" => ","
				);
				$this->_tokens[] = $token;
				$comb = trim(substr($comb, 1));
			}
			$token = array(
				"type" => self::COMBINATOR,
				"content" => $comb
			);
			$this->_tokens[] = $token;
			$this->_combinator = "";
		}
		$this->_tokens[] = $this->_token;
		$this->_token["content"] = "";
		unset($this->_token["fn"]);
	}
	
	/**
	 * Fixes tokens and build the tokens structure
	 *
	 * @return 	bool	Result
	 * @access	protected
	 */
	protected function _fixTokens ()
	{
		$count = count($this->_tokens);
		//There must be at least one token and the last one can't be
		//a combinator
		if (!$count ||
			$this->_tokens[$count - 1]["type"] === self::COMBINATOR) {
			return false;
		}
		$group = array(
			"combinator" => "",
			"rules" => array()
		);
		$groups = array();
		foreach ($this->_tokens as $k => $t) {
			if ($t["type"] === self::COMBINATOR) {
				//Insert the group in the groups array
				//but only if it's not the first token so
				//that it can accept selectors that start
				//with a combinator
				if ($k !== 0) {
					$groups[] = $group;
				}
				//If it's the first token it can't be a ","
				elseif ($t["content"] === ",") {
					return false;
				}
				//A trimmed combinator must be 0 or 1
				//characters long
				if (strlen($t["content"]) > 1) {
					return false;
				}
				//If the combinator is a "," then store
				//the current group and start a new one
				elseif ($t["content"] === ",") {
					if (!count($groups)) {
						return false;
					}
					$this->_groups[] = $groups;
					$groups = array();
				}
				$group["combinator"] = $t["content"];
				$group["rules"] = array();
				continue;
			}
			//Fix the pseudo selector token
			elseif ($t["type"] === self::PSEUDO) {
				if (!isset($t["fn"])) {
					$t["fn"] = $t["content"];
				} else {
					$t["args"] = $t["content"];
				}
				unset($t["content"]);
			}
			//If it's an attribute token then split the
			//content to find the name, the value and
			//the comparison type
			elseif ($t["type"] === self::ATTR) {
				$parts = preg_split(
					"#([~\^\$\*\|]?=)#",
					$t["content"],
					2,
					PREG_SPLIT_DELIM_CAPTURE
				);
				$t["name"] = $parts[0];
				if (count($parts) === 3) {
					$t["comp"] = $parts[1];
					$t["value"] = $parts[2];
				}
				unset($t["content"]);
			}
			$group["rules"][] = $t;
		}
		if (!count($groups) && !count($group["rules"])) {
			return false;
		}
		$groups[] = $group;
		$this->_groups[] = $groups;
		return true;
	}
}