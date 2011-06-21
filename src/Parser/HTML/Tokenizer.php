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
 * HTML parser tokenizer
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserHTMLTokenizer extends ParserStream
{
	//Tokenizer states
	const DATA_STATE = 1;
	const CHARACTER_REFERENCE_IN_DATA_STATE = 2;
	const TAG_OPEN_STATE = 3;
	const RCDATA_STATE = 4;
	const CHARACTER_REFERENCE_IN_RCDATA_STATE = 5;
	const RCDATA_LESS_THAN_SIGN_STATE = 6;
	const RAWTEXT_STATE = 7;
	const RAWTEXT_LESS_THAN_SIGN_STATE = 8;
	const SCRIPT_DATA_STATE = 9;
	const SCRIPT_DATA_LESS_THAN_SIGN_STATE = 10;
	const PLAINTEXT_STATE = 11;
	const MARKUP_DECLARATION_OPEN_STATE = 12;
	const END_TAG_OPEN_STATE = 13;
	const BOGUS_COMMENT_STATE = 14;
	const TAG_NAME_STATE = 15;
	const BEFORE_ATTRIBUTE_NAME_STATE = 16;
	const SELF_CLOSING_START_TAG_STATE = 17;
	const RCDATA_END_TAG_OPEN_STATE = 18;
	const RCDATA_END_TAG_NAME_STATE = 19;
	const RAWTEXT_END_TAG_OPEN_STATE = 20;
	const RAWTEXT_END_TAG_NAME_STATE = 21;
	const SCRIPT_DATA_END_TAG_OPEN_STATE = 22;
	const SCRIPT_DATA_ESCAPE_START_STATE = 23;
	const SCRIPT_DATA_END_TAG_NAME_STATE = 24;
	const SCRIPT_DATA_ESCAPE_START_DASH_STATE = 25;
	const SCRIPT_DATA_ESCAPED_DASH_DASH_STATE = 26;
	const SCRIPT_DATA_ESCAPED_STATE = 27;
	const SCRIPT_DATA_ESCAPED_DASH_STATE = 28;
	const SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN_STATE = 29;
	const SCRIPT_DATA_ESCAPED_END_TAG_OPEN_STATE = 30;
	const SCRIPT_DATA_DOUBLE_ESCAPE_START_STATE = 31;
	const SCRIPT_DATA_ESCAPED_END_TAG_NAME_STATE = 32;
	const SCRIPT_DATA_DOUBLE_ESCAPED_STATE = 33;
	const SCRIPT_DATA_DOUBLE_ESCAPED_DASH_STATE = 34;
	const SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN_STATE = 35;
	const SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH_STATE = 36;
	const SCRIPT_DATA_DOUBLE_ESCAPE_END_STATE = 37;
	const ATTRIBUTE_NAME_STATE = 38;
	const AFTER_ATTRIBUTE_NAME_STATE = 39;
	const BEFORE_ATTRIBUTE_VALUE_STATE = 40;
	const ATTRIBUTE_VALUE_DOUBLE_QUOTED_STATE = 41;
	const ATTRIBUTE_VALUE_UNQUOTED_STATE = 42;
	const ATTRIBUTE_VALUE_SINGLE_QUOTED_STATE = 43;
	const AFTER_ATTRIBUTE_VALUE_QUOTED_STATE = 44;
	const CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE_STATE = 45;
	const COMMENT_START_STATE = 46;
	const DOCTYPE_STATE = 47;
	const COMMENT_START_DASH_STATE = 48;
	const COMMENT_STATE = 49;
	const COMMENT_END_STATE = 50;
	const COMMENT_END_DASH_STATE = 51;
	const COMMENT_END_BANG_STATE = 52;
	const BEFORE_DOCTYPE_NAME_STATE = 53;
	const DOCTYPE_NAME_STATE = 54;
	const AFTER_DOCTYPE_NAME_STATE = 55;
	const AFTER_DOCTYPE_PUBLIC_KEYWORD_STATE = 56;
	const AFTER_DOCTYPE_SYSTEM_KEYWORD_STATE = 57;
	const BOGUS_DOCTYPE_STATE = 58;
	const BEFORE_DOCTYPE_PUBLIC_IDENTIFIER_STATE = 59;
	const DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED_STATE = 60;
	const DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED_STATE = 61;
	const AFTER_DOCTYPE_PUBLIC_IDENTIFIER_STATE = 62;
	const BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS_STATE = 63;
	const DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED_STATE = 64;
	const AFTER_DOCTYPE_SYSTEM_IDENTIFIER_STATE = 65;
	const BEFORE_DOCTYPE_SYSTEM_IDENTIFIER_STATE = 66;
	const CDATA_SECTION_STATE = 67;
	const DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED_STATE = 68;
	
	//Tokens
	const DOCTYPE = 1;
	const START_TAG = 2;
	const END_TAG = 3;
	const COMMENT = 4;
	const CHAR = 5;
	const EOF = 6;
	
	/**
	 * Tokenizer's current state
	 *
	 * @var		int
	 */
	public $state;
	
	/**
	 * Temporary token data that must be stored
	 *
	 * @var		mixed
	 * @access	protected
	 */
	protected $_tempToken;
	
	/**
	 * Last processed attribute name
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_attribute;

	/**
	 * Temporary buffer
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_tempBuffer;

	/**
	 * Temporary allowed character in the character reference.
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_tempAllowedCharacter = null;

	/**
	 * Previous tokenizer state.
	 *
	 * @var		int
	 * @access	protected
	 */
	protected $_previousState = null;

	/**
	 * The character that originates the switch to the bogus comment state
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_bogusCommentStateSwitcher;

	/**
	 * Illegal entities to Unicode conversion
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_illegalEntitiesToUnicode = array(
		0x00 => 0xFFFD, 0x0D => 0x000A, 0x80 => 0x20AC, 0x81 => 0x0081, 0x82 => 0x201A,
		0x83 => 0x0192, 0x84 => 0x201E, 0x85 => 0x2026, 0x86 => 0x2020, 0x87 => 0x2021,
		0x88 => 0x02C6, 0x89 => 0x2030, 0x8A => 0x0160, 0x8B => 0x2039, 0x8C => 0x0152,
		0x8D => 0x008D, 0x8E => 0x017D, 0x8F => 0x008F, 0x90 => 0x0090, 0x91 => 0x2018,
		0x92 => 0x2019, 0x93 => 0x201C, 0x94 => 0x201D, 0x95 => 0x2022, 0x96 => 0x2013,
		0x97 => 0x2014, 0x98 => 0x02DC, 0x99 => 0x2122, 0x9A => 0x0161, 0x9B => 0x203A,
		0x9C => 0x0153, 0x9D => 0x009D, 0x9E => 0x017E, 0x9F => 0x0178
    );
	
	/**
	 * Starts the tokenization
	 *
	 * @return 	void
	 * @access	protected
	 */
	protected function _tokenize ()
	{
		while (!$this->_isEOF) {
			switch ($this->state) {
				//Data state
				case self::DATA_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0026 AMPERSAND (&): Switch to the character reference in
					//data state.
					if ($char === "&") {
						$this->state = self::CHARACTER_REFERENCE_IN_DATA_STATE;
					}
					//U+003C LESS-THAN SIGN (<): Switch to the tag open state.
					elseif ($char === "<") {
						$this->state = self::TAG_OPEN_STATE;
					}
					//EOF: Emit an end-of-file token
					elseif ($char === null) {
						$this->_emitToken(self::EOF);
					}
					//Anything else: Emit the current input character as a
					//character token.
					else {
						/*Match everything that is not processed by other
						conditions and is not a whitespace because whitespaces
						are treated differently in the tree building phase*/
						$chars = $char;
						if ($char !== "\x09" && $char !== "\x0A" &&
							$char !== "\x0C" && $char !== "\x0D" &&
							$char !== "\x20") {
							$allowed = "\x09\x0A\x0C\x0D\x20&<";
							$chars .= $this->_consumeUntilFind($allowed);
						}
						$this->_emitToken(self::CHAR, $chars);
					}
				break;

				//Character reference in data state
				case self::CHARACTER_REFERENCE_IN_DATA_STATE:
					//Attempt to consume a character reference, with no
					//additional allowed character
					$token = $this->_tokenizeCharacterReference();
					//If nothing is returned, emit a U+0026 AMPERSAND character
					//(&) token. Otherwise, emit the character token that was
					//returned.
					if ($token === false) {
						$token = "&";
					}
					$this->_emitToken(self::CHAR, $token);
					//Finally, switch to the data state.
					$this->state = self::DATA_STATE;
				break;

				//RCDATA state
				case self::RCDATA_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0026 AMPERSAND (&): Switch to the character reference
					//in RCDATA state.
					if ($char === "&") {
						$this->state = self::CHARACTER_REFERENCE_IN_RCDATA_STATE;
					}
					//U+003C LESS-THAN SIGN (<): Switch to the RCDATA less-than
					//sign state.
					elseif ($char === "<") {
						$this->state = self::RCDATA_LESS_THAN_SIGN_STATE;
					}
					//EOF: Emit an end-of-file token
					elseif ($char === null) {
						$this->_emitToken(self::EOF);
					}
					//Anything else: Emit the current input character as a
					//character token.
					else {
						$char .= $this->_consumeUntilFind('<&');
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Character reference in RCDATA state
				case self::CHARACTER_REFERENCE_IN_RCDATA_STATE:
					//Attempt to consume a character reference, with no
					//additional allowed character
					$token = $this->_tokenizeCharacterReference();
					//If nothing is returned, emit a U+0026 AMPERSAND character
					//(&) token. Otherwise, emit the character token that was
					//returned.
					if ($token === false) {
						$token = "&";
					}
					$this->_emitToken(self::CHAR, $token);
					//Finally, switch to the RCDATA state.
					$this->state = self::RCDATA_STATE;
				break;

				//RAWTEXT state
				case self::RAWTEXT_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+003C LESS-THAN SIGN (<): Switch to the RAWTEXT less-than sign state.
					if ($char === "<") {
						$this->state = self::RAWTEXT_LESS_THAN_SIGN_STATE;
					}
					//EOF: Emit an end-of-file token
					elseif ($char === null) {
						$this->_emitToken(self::EOF);
					}
					//Anything else: Emit the current input character as a
					//character token.
					else {
						$char .= $this->_consumeUntilFind('<');
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Script data state
				case self::SCRIPT_DATA_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+003C LESS-THAN SIGN (<): Switch to the script data
					//less-than sign state.
					if ($char === "<") {
						$this->state = self::SCRIPT_DATA_LESS_THAN_SIGN_STATE;
					}
					//EOF: Emit an end-of-file token
					elseif ($char === null) {
						$this->_emitToken(self::EOF);
					}
					//Anything else: Emit the current input character as a
					//character token.
					else {
						$char .= $this->_consumeUntilFind('<');
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//PLAINTEXT state
				case self::PLAINTEXT_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//EOF: Emit an end-of-file token
					if ($char === null) {
						$this->_emitToken(self::EOF);
					}
					//Anything else: Emit the current input character as a
					//character token.
					else {
						$char .= $this->_consumeRemaining();
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Tag open state
				case self::TAG_OPEN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0021 EXCLAMATION MARK (!): Switch to the markup
					//declaration open state.
					if ($char === "!") {
						$this->state = self::MARKUP_DECLARATION_OPEN_STATE;
					}
					//U+002F SOLIDUS (/): Switch to the end tag open state.
					elseif ($char === "/") {
						$this->state = self::END_TAG_OPEN_STATE;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Create a new start tag token, set its
					//tag name to the lowercase version of the current input
					//character (add 0x0020 to the character's code point),
					//then switch to the tag name state. (Don't emit the token
					//yet; further details will be filled in before it is
					//emitted.)
					elseif ($char >= "A" && $char <= "Z") {
						$this->state = self::TAG_NAME_STATE;
						$this->_tempToken = array(
							"token" => self::START_TAG, 
							"args" => array("tagname" => strtolower($char))
						);
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Create a new start tag token, set its tag name
					//to the current input character, then switch to the tag
					//name state. (Don't emit the token yet; further details
					//will be filled in before it is emitted.)
					elseif ($char >= "a" && $char <= "z") {
						$this->state = self::TAG_NAME_STATE;
						$this->_tempToken = array(
							"token" => self::START_TAG,
							"args" => array("tagname" => $char)
						);
					}
					//U+003F QUESTION MARK (?): Parse error. Switch to the
					//bogus comment state.
					elseif ($char === "?") {
						$this->state = self::BOGUS_COMMENT_STATE;
						$this->_bogusCommentStateSwitcher = $char;
					}
					//Anything else: Parse error. Emit a U+003C LESS-THAN SIGN
					//character token and reconsume the current input character
					//in the data state.
					else {
						$this->_emitToken(self::CHAR, "<");
						$this->state = self::DATA_STATE;
						$this->_unconsume();
					}
				break;

				//End tag open state
				case self::END_TAG_OPEN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Create a new end tag token, set its tag
					//name to the lowercase version of the current input
					//character (add 0x0020 to the character's code point), then
					//switch to the tag name state. (Don't emit the token yet;
					//further details will be filled in before it is emitted.)
					if ($char >= "A" && $char <= "Z") {
						$this->state = self::TAG_NAME_STATE;
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => strtolower($char))
						);
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Create a new end tag token, set its tag name to
					//the current input character, then switch to the tag name
					//state. (Don't emit the token yet; further details will be
					//filled in before it is emitted.)
					elseif ($char >= "a" && $char <= "z") {
						$this->state = self::TAG_NAME_STATE;
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => $char)
						);
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Switch to the
					//data state.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
					}
					//EOF: Parse error. Emit a U+003C LESS-THAN SIGN character
					//token and a U+002F SOLIDUS character token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(self::CHAR, "</");
						$this->state = self::DATA_STATE;
						/*Change the EOF flag so that another loop is executed
						and the EOF char can be handled in the data state*/
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Switch to the bogus comment
					//state.
					else {
						$this->state = self::BOGUS_COMMENT_STATE;
						$this->_bogusCommentStateSwitcher = $char;
					}
				break;

				//Tag name state
				case self::TAG_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the before
					//attribute name state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
					}
					//U+002F SOLIDUS (/): Switch to the self-closing start tag
					//state.
					elseif ($char === "/") {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current tag token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the current tag token's tag name.
					//Anything else: Append the current input character to the
					//current tag token's tag name. 
					else {
						$char .= $this->_consumeUntilFind("\x09\x0A\x0C\x20/>");
						$char = strtolower($char);
						$this->_tempToken["args"]["tagname"] .= $char;
					}
				break;

				//RCDATA less-than sign state
				case self::RCDATA_LESS_THAN_SIGN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002F SOLIDUS (/): Set the temporary buffer to the empty
					//string. Switch to the RCDATA end tag open state.
					if ($char === "/") {
						$this->_tempBuffer = "";
						$this->state = self::RCDATA_END_TAG_OPEN_STATE;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token and reconsume the current input character in the
					//RCDATA state.
					else {
						$this->_emitToken(self::CHAR, "<");
						$this->state = self::RCDATA_STATE;
						$this->_unconsume();
					}
				break;

				//RCDATA end tag open state
				case self::RCDATA_END_TAG_OPEN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Create a new end tag token, and set its
					//tag name to the lowercase version of the current input
					//character (add 0x0020 to the character's code point).
					//Append the current input character to the temporary buffer.
					//Finally, switch to the RCDATA end tag name state. (Don't
					//emit the token yet; further details will be filled in
					//before it is emitted.)
					if ($char >= "A" && $char <= "Z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => strtolower($char))
						);
						$this->_tempBuffer .= $char;
						$this->state = self::RCDATA_END_TAG_NAME_STATE;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Create a new end tag token, and set its tag name
					//to the current input character. Append the current input
					//character to the temporary buffer. Finally, switch to the
					//RCDATA end tag name state. (Don't emit the token yet;
					//further details will be filled in before it is emitted.)
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => $char)
						);
						$this->_tempBuffer .= $char;
						$this->state = self::RCDATA_END_TAG_NAME_STATE;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, and reconsume
					//the current input character in the RCDATA state.
					else {
						$this->_emitToken(self::CHAR, "</");
						$this->state = self::RCDATA_STATE;
						$this->_unconsume();
					}
				break;

				//RCDATA end tag name state
				case self::RCDATA_END_TAG_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					$tempTokenTagName = $this->_tempToken["args"]["tagname"];
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: If the current end
					//tag token is an appropriate end tag token, then switch to
					//the before attribute name state. Otherwise, treat it as
					//per the "anything else" entry below.
					if (($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") &&
						$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
					}
					//U+002F SOLIDUS (/): If the current end tag token is an
					//appropriate end tag token, then switch to the self-closing
					//start tag state. Otherwise, treat it as per the "anything
					//else" entry below.
					elseif ($char === "/" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): If the current end tag token
					//is an appropriate end tag token, then emit the current tag
					//token and switch to the data state. Otherwise, treat it as
					//per the "anything else" entry below.
					elseif ($char === ">" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the current tag token's tag name. Append
					//the current input character to the temporary buffer.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempToken["args"]["tagname"] .= strtolower($char);
						$this->_tempBuffer .= $char;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Append the current input character to the
					//current tag token's tag name. Append the current input
					//character to the temporary buffer.
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken["args"]["tagname"] .= $char;
						$this->_tempBuffer .= $char;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, a character token
					//for each of the characters in the temporary buffer (in the
					//order they were added to the buffer), and reconsume the
					//current input character in the RCDATA state.
					else {
						$this->_emitToken(self::CHAR, "</" . $this->_tempBuffer);
						$this->state = self::RCDATA_STATE;
						$this->_unconsume();
					}
				break;

				//RAWTEXT less-than sign state
				case self::RAWTEXT_LESS_THAN_SIGN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002F SOLIDUS (/): Set the temporary buffer to the empty
					//string. Switch to the RAWTEXT end tag open state.
					if ($char === "/") {
						$this->_tempBuffer = "";
						$this->state = self::RAWTEXT_END_TAG_OPEN_STATE;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token and reconsume the current input character in the
					//RAWTEXT state.
					else {
						$this->_emitToken(self::CHAR, "<");
						$this->state = self::RAWTEXT_STATE;
						$this->_unconsume();
					}
				break;

				//RAWTEXT end tag open state
				case self::RAWTEXT_END_TAG_OPEN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Create a new end tag token, and set its
					//tag name to the lowercase version of the current input
					//character (add 0x0020 to the character's code point).
					//Append the current input character to the temporary
					//buffer. Finally, switch to the RAWTEXT end tag name
					//state. (Don't emit the token yet; further details will be
					//filled in before it is emitted.)
					if ($char >= "A" && $char <= "Z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => strtolower($char))
						);
						$this->_tempBuffer .= $char;
						$this->state = self::RAWTEXT_END_TAG_NAME_STATE;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Create a new end tag token, and set its tag name
					//to the current input character. Append the current input
					//character to the temporary buffer. Finally, switch to the
					//RAWTEXT end tag name state. (Don't emit the token yet;
					//further details will be filled in before it is emitted.)
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => $char)
						);
						$this->_tempBuffer .= $char;
						$this->state = self::RAWTEXT_END_TAG_NAME_STATE;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, and reconsume the
					//current input character in the RAWTEXT state.
					else {
						$this->_emitToken(self::CHAR, "</");
						$this->state = self::RAWTEXT_STATE;
						$this->_unconsume();
					}
				break;

				//RAWTEXT end tag name state
				case self::RAWTEXT_END_TAG_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					$tempTokenTagName = $this->_tempToken["args"]["tagname"];
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: If the current end
					//tag token is an appropriate end tag token, then switch to
					//the before attribute name state. Otherwise, treat it as per
					//the "anything else" entry below.
					if (($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") &&
						$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
					}
					//U+002F SOLIDUS (/): If the current end tag token is an
					//appropriate end tag token, then switch to the self-closing
					//start tag state. Otherwise, treat it as per the "anything
					//else" entry below.
					elseif ($char === "/" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): If the current end tag token
					//is an appropriate end tag token, then emit the current tag
					//token and switch to the data state. Otherwise, treat it as
					//per the "anything else" entry below.
					elseif ($char === ">" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the current tag token's tag name. Append
					//the current input character to the temporary buffer.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempToken["args"]["tagname"] .= strtolower($char);
						$this->_tempBuffer .= $char;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Append the current input character to the
					//current tag token's tag name. Append the current input
					//character to the temporary buffer.
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken["args"]["tagname"] .= $char;
						$this->_tempBuffer .= $char;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, a character token
					//for each of the characters in the temporary buffer (in the
					//order they were added to the buffer), and reconsume the
					//current input character in the RCDATA state.
					else {
						$this->_emitToken(self::CHAR, "</" . $this->_tempBuffer);
						$this->state = self::RAWTEXT_STATE;
						$this->_unconsume();
					}
				break;

				//Script data less-than sign state
				case self::SCRIPT_DATA_LESS_THAN_SIGN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002F SOLIDUS (/): Set the temporary buffer to the empty
					//string. Switch to the script data end tag open state.
					if ($char === "/") {
						$this->_tempBuffer = "";
						$this->state = self::SCRIPT_DATA_END_TAG_OPEN_STATE;
					}
					//U+0021 EXCLAMATION MARK (!): Switch to the script data
					//escape start state. Emit a U+003C LESS-THAN SIGN character
					//token and a U+0021 EXCLAMATION MARK character token.
					elseif ($char === "!") {
						$this->state = self::SCRIPT_DATA_ESCAPE_START_STATE;
						$this->_emitToken(self::CHAR, "<!");
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token and reconsume the current input character in the
					//script data state.
					else {
						$this->_emitToken(self::CHAR, "<");
						$this->state = self::SCRIPT_DATA_STATE;
						$this->_unconsume();
					}
				break;

				//Script data end tag open state
				case self::SCRIPT_DATA_END_TAG_OPEN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Create a new end tag token, and set its
					//tag name to the lowercase version of the current input
					//character (add 0x0020 to the character's code point).
					//Append the current input character to
					//the temporary buffer. Finally, switch to the script data
					//end tag name state. (Don't emit the token yet; further
					//details will be filled in before it is emitted.)
					if ($char >= "A" && $char <= "Z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => strtolower($char))
						);
						$this->_tempBuffer .= $char;
						$this->state = self::SCRIPT_DATA_END_TAG_NAME_STATE;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Create a new end tag token, and set its tag name
					//to the current input character. Append the current input
					//character to the temporary buffer. Finally, switch to the
					//script data end tag name state. (Don't emit the token yet;
					//further details will be filled in before it is emitted.)
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => $char)
						);
						$this->_tempBuffer .= $char;
						$this->state = self::SCRIPT_DATA_END_TAG_NAME_STATE;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, and reconsume the
					//current input character in the script data state.
					else {
						$this->_emitToken(self::CHAR, "</");
						$this->state = self::SCRIPT_DATA_STATE;
						$this->_unconsume();
					}
				break;

				//Script data end tag name state
				case self::SCRIPT_DATA_END_TAG_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					$tempTokenTagName = $this->_tempToken["args"]["tagname"];
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF), U+000C
					//FORM FEED (FF), U+0020 SPACE: If the current end tag token
					//is an appropriate end tag token, then switch to the before
					//attribute name state. Otherwise, treat it as per the
					//"anything else" entry below.
					if (($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") &&
						$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
					}
					//U+002F SOLIDUS (/): If the current end tag token is an 
					//appropriate end tag token, then switch to the self-closing
					//start tag state. Otherwise, treat it as per the "anything
					//else" entry below.
					elseif ($char === "/" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): If the current end tag token
					//is an appropriate end tag token, then emit the current tag
					//token and switch to the data state. Otherwise, treat it as
					//per the "anything else" entry below.
					elseif ($char === ">" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the current tag token's tag name. Append
					//the current input character to the temporary buffer.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempToken["args"]["tagname"] .= strtolower($char);
						$this->_tempBuffer .= $char;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Append the current input character to the
					//current tag token's tag name. Append the current input
					//character to the temporary buffer.
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken["args"]["tagname"] .= $char;
						$this->_tempBuffer .= $char;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, a character token
					//for each of the characters in the temporary buffer (in the
					//order they were added to the buffer), and reconsume the
					//current input character in the RCDATA state.
					else {
						$this->_emitToken(self::CHAR, "</" . $this->_tempBuffer);
						$this->state = self::SCRIPT_DATA_STATE;
						$this->_unconsume();
					}
				break;

				//Script data escape start state
				case self::SCRIPT_DATA_ESCAPE_START_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the script data escape
					//start dash state. Emit a U+002D HYPHEN-MINUS character
					//token.
					if ($char === "-") {
						$this->state = self::SCRIPT_DATA_ESCAPE_START_DASH_STATE;
						$this->_emitToken(self::CHAR, "-");
					}
					//Anything else: Reconsume the current input character in
					//the script data state.
					else {
						$this->state = self::SCRIPT_DATA_STATE;
						$this->_unconsume();
					}
				break;
				
				//Script data escape start dash state
				case self::SCRIPT_DATA_ESCAPE_START_DASH_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the script data escaped
					//dash dash state. Emit a U+002D HYPHEN-MINUS character
					//token.
					if ($char === "-") {
						$this->state = self::SCRIPT_DATA_ESCAPED_DASH_DASH_STATE;
						$this->_emitToken(self::CHAR, "-");
					}
					//Anything else: Reconsume the current input character in
					//the script data state.
					else {
						$this->state = self::SCRIPT_DATA_STATE;
						$this->_unconsume();
					}
				break;

				//Script data escaped state
				case self::SCRIPT_DATA_ESCAPED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the script data escaped
					//dash state. Emit a U+002D HYPHEN-MINUS character token.
					if ($char === "-") {
						$this->state = self::SCRIPT_DATA_ESCAPED_DASH_STATE;
						$this->_emitToken(self::CHAR, "-");
					}
					//U+003C LESS-THAN SIGN (<): Switch to the script data
					//escaped less-than sign state.
					elseif ($char === "<") {
						$this->state = self::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN_STATE;
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Emit the current input character as a
					//character token.
					else {
						$char .= $this->_consumeUntilFind("-<");
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Script data escaped dash state
				case self::SCRIPT_DATA_ESCAPED_DASH_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the script data
					//escaped dash dash state. Emit a U+002D HYPHEN-MINUS
					//character token.
					if ($char === "-") {
						$this->state = self::SCRIPT_DATA_ESCAPED_DASH_DASH_STATE;
						$this->_emitToken(self::CHAR, "-");
					}
					//U+003C LESS-THAN SIGN (<): Switch to the script data
					//escaped less-than sign state.
					elseif ($char === "<") {
						$this->state = self::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN_STATE;
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Switch to the script data escaped state.
					//Emit the current input character as a character token.
					else {
						$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Script data escaped dash dash state
				case self::SCRIPT_DATA_ESCAPED_DASH_DASH_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Emit a U+002D HYPHEN-MINUS
					//character token.
					if ($char === "-") {
						$this->_emitToken(self::CHAR, "-");
					}
					//U+003C LESS-THAN SIGN (<): Switch to the script data
					//escaped less-than sign state.
					elseif ($char === "<") {
						$this->state = self::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the script data
					//state. Emit a U+003E GREATER-THAN SIGN character token.
					elseif ($char === ">") {
						$this->state = self::SCRIPT_DATA_STATE;
						$this->_emitToken(self::CHAR, ">");
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Switch to the script data escaped state.
					//Emit the current input character as a character token.
					else {
						$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Script data escaped less-than sign state
				case self::SCRIPT_DATA_ESCAPED_LESS_THAN_SIGN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002F SOLIDUS (/): Set the temporary buffer to the empty
					//string. Switch to the script data escaped end tag open
					//state.
					if ($char === "/") {
						$this->_tempBuffer = "";
						$this->state = self::SCRIPT_DATA_ESCAPED_END_TAG_OPEN_STATE;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Set the temporary buffer to the empty
					//string. Append the lowercase version of the current input
					//character (add 0x0020 to the character's code point) to
					//the temporary buffer. Switch to the script data double
					//escape start state. Emit a U+003C LESS-THAN SIGN
					//character token and the current input character as a
					//character token.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempBuffer = "";
						$this->_tempBuffer .= strtolower($char);
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPE_START_STATE;
						$this->_emitToken(self::CHAR, "<$char");
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Set the temporary buffer to the empty string.
					//Append the current input character to the temporary
					//buffer. Switch to the script data double escape start
					//state. Emit a U+003C LESS-THAN SIGN character token and
					//the current input character as a character token.
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempBuffer = "";
						$this->_tempBuffer .= $char;
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPE_START_STATE;
						$this->_emitToken(self::CHAR, "<$char");
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token and reconsume the current input character in the
					//script data escaped state.
					else {
						$this->_emitToken(self::CHAR, "<");
						$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						$this->_unconsume();
					}
				break;

				//Script data escaped end tag open state
				case self::SCRIPT_DATA_ESCAPED_END_TAG_OPEN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Create a new end tag token, and set its
					//tag name to the lowercase version of the current input
					//character (add 0x0020 to the character's code point).
					//Append the current input character to the temporary
					//buffer. Finally, switch to the script data escaped end
					//tag name state. (Don't emit the token yet; further
					//details will be filled in before it is emitted.)
					if ($char >= "A" && $char <= "Z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => strtolower($char))
						);
						$this->_tempBuffer .= $char;
						$this->state = self::SCRIPT_DATA_ESCAPED_END_TAG_NAME_STATE;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Create a new end tag token, and set its tag
					//name to the current input character. Append the current
					//input character to the temporary buffer. Finally, switch
					//to the script data escaped end tag name state. (Don't
					//emit the token yet; further details will be filled in
					//before it is emitted.)
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken = array(
							"token" => self::END_TAG,
							"args" => array("tagname" => $char)
						);
						$this->_tempBuffer .= $char;
						$this->state = self::SCRIPT_DATA_ESCAPED_END_TAG_NAME_STATE;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, and reconsume
					//the current input character in the script data escaped
					//state.
					else {
						$this->_emitToken(self::CHAR, "</");
						$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						$this->_unconsume();
					}
				break;

				//Script data escaped end tag name state
				case self::SCRIPT_DATA_ESCAPED_END_TAG_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					$tempTokenTagName = $this->_tempToken["args"]["tagname"];
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: If the current end
					//tag token is an appropriate end tag token, then switch to
					//the before attribute name state. Otherwise, treat it as
					//per the "anything else" entry below.
					if (($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") &&
						$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
					}
					//U+002F SOLIDUS (/): If the current end tag token is an
					//appropriate end tag token, then switch to the self-closing
					//start tag state. Otherwise, treat it as per the "anything
					//else" entry below.
					elseif ($char === "/" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): If the current end tag
					//token is an appropriate end tag token, then emit the
					//current tag token and switch to the data state.
					//Otherwise, treat it as per the "anything else" entry below.
					elseif ($char === ">" &&
							$this->_isAppropriateEndTagToken($tempTokenTagName)) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the current tag token's tag name. Append
					//the current input character to the temporary buffer.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempToken["args"]["tagname"] .= strtolower($char);
						$this->_tempBuffer .= $char;
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Append the current input character to the
					//current tag token's tag name. Append the current input
					//character to the temporary buffer.
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempToken["args"]["tagname"] .= $char;
						$this->_tempBuffer .= $char;
					}
					//Anything else: Emit a U+003C LESS-THAN SIGN character
					//token, a U+002F SOLIDUS character token, a character
					//token for each of the characters in the temporary buffer
					//(in the order they were added to the buffer), and
					//reconsume the current input character in the script data
					//escaped state.
					else {
						$this->_emitToken(self::CHAR, "</" . $this->_tempBuffer);
						$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						$this->_unconsume();
					}
				break;

				//Script data double escape start state
				case self::SCRIPT_DATA_DOUBLE_ESCAPE_START_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE, U+002F SOLIDUS (/),
					//U+003E GREATER-THAN SIGN (>): If the temporary buffer is
					//the string "script", then switch to the script data
					//double escaped state. Otherwise, switch to the script
					//data escaped state. Emit the current input character as
					//a character token.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20" ||
						$char === "/" || $char === ">") {
						if ($this->_tempBuffer === "script") {
							$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_STATE;
						} else {
							$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						}
						$this->_emitToken(self::CHAR, $char);
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the temporary buffer. Emit the current
					//input character as a character token.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempBuffer .= strtolower($char);
						$this->_emitToken(self::CHAR, $char);
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Append the current input character to the
					//temporary buffer. Emit the current input character as a
					//character token.
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempBuffer .= $char;
						$this->_emitToken(self::CHAR, $char);
					}
					//Anything else: Reconsume the current input character in
					//the script data escaped state.
					else {
						$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						$this->_unconsume();
					}
				break;

				//Script data double escaped state
				case self::SCRIPT_DATA_DOUBLE_ESCAPED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the script data double
					//escaped dash state. Emit a U+002D HYPHEN-MINUS character
					//token.
					if ($char === "-") {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_STATE;
						$this->_emitToken(self::CHAR, "-");
					}
					//U+003C LESS-THAN SIGN (<): Switch to the script data
					//double escaped less-than sign state. Emit a U+003C
					//LESS-THAN SIGN character token.
					elseif ($char === "<") {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN_STATE;
						$this->_emitToken(self::CHAR, "<");
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Emit the current input character as a
					//character token.
					else {
						$char .= $this->_consumeUntilFind("-<");
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Script data double escaped dash state
				case self::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the script data double
					//escaped dash dash state. Emit a U+002D HYPHEN-MINUS
					//character token.
					if ($char === "-") {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH_STATE;
						$this->_emitToken(self::CHAR, "-");
					}
					//U+003C LESS-THAN SIGN (<): Switch to the script data
					//double escaped less-than sign state. Emit a U+003C
					//LESS-THAN SIGN character token.
					elseif ($char === "<") {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN_STATE;
						$this->_emitToken(self::CHAR, "<");
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Switch to the script data double escaped
					//state. Emit the current input character as a character
					//token.
					else {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_STATE;
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Script data double escaped dash dash state
				case self::SCRIPT_DATA_DOUBLE_ESCAPED_DASH_DASH_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Emit a U+002D HYPHEN-MINUS
					//character token.
					if ($char === "-") {
						$this->_emitToken(self::CHAR, "-");
					}
					//U+003C LESS-THAN SIGN (<): Switch to the script data
					//double escaped less-than sign state. Emit a U+003C
					//LESS-THAN SIGN character token. 
					elseif ($char === "<") {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN_STATE;
						$this->_emitToken(self::CHAR, "<");
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the script data
					//state. Emit a U+003E GREATER-THAN SIGN character token.
					elseif ($char === ">") {
						$this->state = self::SCRIPT_DATA_STATE;
						$this->_emitToken(self::CHAR, ">");
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Switch to the script data double escaped
					//state. Emit the current input character as a character
					//token.
					else {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_STATE;
						$this->_emitToken(self::CHAR, $char);
					}
				break;

				//Script data double escaped less-than sign state
				case self::SCRIPT_DATA_DOUBLE_ESCAPED_LESS_THAN_SIGN_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002F SOLIDUS (/): Set the temporary buffer to the empty
					//string. Switch to the script data double escape end state.
					//Emit a U+002F SOLIDUS character token.
					if ($char === "/") {
						$this->_tempBuffer = "";
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPE_END_STATE;
						$this->_emitToken(self::CHAR, "/");
					}
					//Anything else: Reconsume the current input character in
					//the script data double escaped state.
					else {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_STATE;
						$this->_unconsume();
					}
				break;

				//Script data double escape end state
				case self::SCRIPT_DATA_DOUBLE_ESCAPE_END_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE, U+002F SOLIDUS (/),
					//U+003E GREATER-THAN SIGN (>): If the temporary buffer is
					//the string "script", then switch to the script data
					//escaped state. Otherwise, switch to the script data
					//double escaped state. Emit the current input character as
					//a character token.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20" ||
						$char === "/" || $char === ">") {
						if ($this->_tempBuffer === "script") {
							$this->state = self::SCRIPT_DATA_ESCAPED_STATE;
						} else {
							$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_STATE;
						}
						$this->_emitToken(self::CHAR, $char);
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the temporary buffer. Emit the current
					//input character as a character token.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempBuffer .= strtolower($char);
						$this->_emitToken(self::CHAR, $char);
					}
					//U+0061 LATIN SMALL LETTER A through to U+007A LATIN SMALL
					//LETTER Z: Append the current input character to the
					//temporary buffer. Emit the current input character as a
					//character token.
					elseif ($char >= "a" && $char <= "z") {
						$this->_tempBuffer .= $char;
						$this->_emitToken(self::CHAR, $char);
					}
					//Anything else: Reconsume the current input character in
					//the script data double escaped state.
					else {
						$this->state = self::SCRIPT_DATA_DOUBLE_ESCAPED_STATE;
						$this->_unconsume();
					}
				break;

				//Before attribute name state
				case self::BEFORE_ATTRIBUTE_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+002F SOLIDUS (/): Switch to the self-closing start tag
					//state.
					elseif ($char === "/") {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current tag token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Start a new attribute in the current
					//tag token. Set that attribute's name to the lowercase
					//version of the current input character (add 0x0020 to the
					//character's code point), and its value to the empty
					//string. Switch to the attribute name state.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_attribute = strtolower($char);
						$this->state = self::ATTRIBUTE_NAME_STATE;
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					} else {
						//U+0022 QUOTATION MARK ("), U+0027 APOSTROPHE ('),
						//U+003C LESS-THAN SIGN (<), U+003D EQUALS SIGN (=):
						//Parse error. Treat it as per the "anything else"
						//entry below.
						//Anything else: Start a new attribute in the current
						//tag token. Set that attribute's name to the current
						//input character, and its value to the empty string.
						//Switch to the attribute name state.
						$this->_attribute = $char;
						$this->state = self::ATTRIBUTE_NAME_STATE;
					}
				break;

				//Attribute name state
				case self::ATTRIBUTE_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the after
					//attribute name state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::AFTER_ATTRIBUTE_NAME_STATE;
						$this->_generateAttributeOnToken();
					}
					//U+002F SOLIDUS (/): Switch to the self-closing start tag
					//state.
					elseif ($char === "/") {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
						$this->_generateAttributeOnToken();
					}
					//U+003D EQUALS SIGN (=): Switch to the before attribute
					//value state
					elseif ($char === "=") {
						$this->state = self::BEFORE_ATTRIBUTE_VALUE_STATE;
						$this->_generateAttributeOnToken();
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current tag token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_generateAttributeOnToken();
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
						$this->_generateAttributeOnToken();
					} else {
						//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
						//CAPITAL LETTER Z: Append the lowercase version of the
						//current input character (add 0x0020 to the character's
						//code point) to the current attribute's name.
						//U+0022 QUOTATION MARK ("), U+0027 APOSTROPHE ('),
						//U+003C LESS-THAN SIGN (<): Parse error. Treat it as
						//per the "anything else" entry below.
						//Anything else: Append the current input character to
						//the current attribute's name.
						$char .= $this->_consumeUntilFind("\x09\x0A\x0C\x20/=>");
						$this->_attribute .= strtolower($char);
					}
				break;

				//After attribute name state
				case self::AFTER_ATTRIBUTE_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+002F SOLIDUS (/): Switch to the self-closing start tag
					//state.
					elseif ($char === "/") {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003D EQUALS SIGN (=): Switch to the before attribute
					//value state.
					elseif ($char === "=") {
						$this->state = self::BEFORE_ATTRIBUTE_VALUE_STATE;
						$this->_generateAttributeOnToken();
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current tag token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Start a new attribute in the current tag
					//token. Set that attribute's name to the lowercase version
					//of the current input character (add 0x0020 to the
					//character's code point), and its value to the empty string.
					//Switch to the attribute name state.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_attribute = strtolower($char);
						$this->state = self::ATTRIBUTE_NAME_STATE;
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					} else {
						//U+0022 QUOTATION MARK ("), U+0027 APOSTROPHE ('),
						//U+003C LESS-THAN SIGN (<): Parse error. Treat it as
						//per the "anything else" entry below.
						//Anything else: Start a new attribute in the current
						//tag token. Set that attribute's name to the current
						//input character, and its value to the empty string.
						//Switch to the attribute name state.
						$this->_attribute = $char;
						$this->state = self::ATTRIBUTE_NAME_STATE;
					}
				break;

				//Before attribute value state
				case self::BEFORE_ATTRIBUTE_VALUE_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+0022 QUOTATION MARK ("): Switch to the attribute value
					//(double-quoted) state.
					elseif ($char === '"') {
						$this->state = self::ATTRIBUTE_VALUE_DOUBLE_QUOTED_STATE;
					}
					//U+0026 AMPERSAND (&): Switch to the attribute value
					//(unquoted) state and reconsume this current input
					//character.
					elseif ($char === "&") {
						$this->state = self::ATTRIBUTE_VALUE_UNQUOTED_STATE;
						$this->_unconsume();
					}
					//U+0027 APOSTROPHE ('): Switch to the attribute value
					//(single-quoted) state.
					elseif ($char === "'") {
						$this->state = self::ATTRIBUTE_VALUE_SINGLE_QUOTED_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Switch to the
					//data state. Emit the current tag token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					} else {
						//U+003C LESS-THAN SIGN (<), U+003D EQUALS SIGN (=),
						//U+0060 GRAVE ACCENT (`) : Parse error. Treat it as
						//per the "anything else" entry below.
						//Anything else: Append the current input character to
						//the current attribute's value. Switch to the
						//attribute value (unquoted) state.
						if ($this->_attribute) {
							$this->_tempToken["args"]["attributes"][$this->_attribute]["value"] .= $char;
						}
						$this->state = self::ATTRIBUTE_VALUE_UNQUOTED_STATE;
					}
				break;

				//Attribute value (double-quoted) state
				case self::ATTRIBUTE_VALUE_DOUBLE_QUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0022 QUOTATION MARK ("): Switch to the after attribute
					//value (quoted) state.
					if ($char === '"') {
						$this->state = self::AFTER_ATTRIBUTE_VALUE_QUOTED_STATE;
					}
					//U+0026 AMPERSAND (&): Switch to the character reference
					//in attribute value state, with the additional allowed
					//character being U+0022 QUOTATION MARK (").
					elseif ($char === "&") {
						$this->_previousState = $this->state;
						$this->state = self::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE_STATE;
						$this->_tempAllowedCharacter = '"';
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the
					//current attribute's value.
					else {
						$char .= $this->_consumeUntilFind('"&');
						if ($this->_attribute) {
							$this->_tempToken["args"]["attributes"][$this->_attribute]["value"] .= $char;
						}
					}
				break;

				//Attribute value (single-quoted) state
				case self::ATTRIBUTE_VALUE_SINGLE_QUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0027 APOSTROPHE ('): Switch to the after attribute
					//value (quoted) state.
					if ($char === "'") {
						$this->state = self::AFTER_ATTRIBUTE_VALUE_QUOTED_STATE;
					}
					//U+0026 AMPERSAND (&): Switch to the character reference
					//in attribute value state, with the additional allowed
					//character being U+0027 APOSTROPHE (').
					elseif ($char === "&") {
						$this->_previousState = $this->state;
						$this->state = self::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE_STATE;
						$this->_tempAllowedCharacter = "'";
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the current attribute's value.
					else {
						$char .= $this->_consumeUntilFind("'&");
						if ($this->_attribute) {
							$this->_tempToken["args"]["attributes"][$this->_attribute]["value"] .= $char;
						}
					}
				break;

				//Attribute value (unquoted) state
				case self::ATTRIBUTE_VALUE_UNQUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the before
					//attribute name state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
					}
					//U+0026 AMPERSAND (&): Switch to the character reference
					//in attribute value state, with the additional allowed
					//character being U+003E GREATER-THAN SIGN (>).
					elseif ($char === "&") {
						$this->_previousState = $this->state;
						$this->state = self::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE_STATE;
						$this->_tempAllowedCharacter = ">";
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current tag token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					} else {
						//U+0022 QUOTATION MARK ("), U+0027 APOSTROPHE ('),
						//U+003C LESS-THAN SIGN (<), U+003D EQUALS SIGN (=),
						//U+0060 GRAVE ACCENT (`): Parse error. Treat it as per
						//the "anything else" entry below.
						//Anything else: Append the current input character to
						//the current attribute's value.
						$char .= $this->_consumeUntilFind(">&\x09\x0A\x0C\x20");
						if ($this->_attribute) {
							$this->_tempToken["args"]["attributes"][$this->_attribute]["value"] .= $char;
						}
					}
				break;

				//Character reference in attribute value state
				case self::CHARACTER_REFERENCE_IN_ATTRIBUTE_VALUE_STATE:
					//Attempt to consume a character reference.
					$char = $this->_tokenizeCharacterReference(
						$this->_tempAllowedCharacter,
						true
					);
					//If nothing is returned, append a U+0026 AMPERSAND
					//character (&) to the current attribute's value.
					if ($char === false) {
						$char = "&";
					}
					//Otherwise, append the returned character token to the
					//current attribute's value.
					if ($this->_attribute) {
						$this->_tempToken["args"]["attributes"][$this->_attribute]["value"] .= $char;
					}
					//Finally, switch back to the attribute value state that
					//you were in when were switched into this state.
					$this->state = $this->_previousState;
					$this->_tempAllowedCharacter = $this->_previousState = null;
				break;

				//After attribute value (quoted) state
				case self::AFTER_ATTRIBUTE_VALUE_QUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the before
					//attribute name state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
					}
					//U+002F SOLIDUS (/): Switch to the self-closing start tag
					//state.
					elseif ($char === "/") {
						$this->state = self::SELF_CLOSING_START_TAG_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current tag token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Reconsume the character in
					//the before attribute name state.
					else {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
						$this->_unconsume();
					}
				break;

				//Self-closing start tag state
				case self::SELF_CLOSING_START_TAG_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+003E GREATER-THAN SIGN (>): Set the self-closing flag
					//of the current tag token. Switch to the data state.
					//Emit the current tag token.
					if ($char === ">") {
						$this->_tempToken["args"]["self-closing"] = true;
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Reconsume the EOF character in the data
					//state.
					elseif ($char === null) {
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Reconsume the character in
					//the before attribute name state.
					else {
						$this->state = self::BEFORE_ATTRIBUTE_NAME_STATE;
						$this->_unconsume();
					}
				break;

				//Bogus comment state
				case self::BOGUS_COMMENT_STATE:
					//Consume every character up to and including the first
					//U+003E GREATER-THAN SIGN character (>) or the end of the
					//file (EOF), whichever comes first.
					$chars = $this->_consumeUntil(">");
					$this->_consume();
					//Emit a comment token whose data is the concatenation of
					//all the characters starting from and including the
					//character that caused the state machine to switch into
					//the bogus comment state, up to and including the
					//character immediately before the last consumed character
					//(i.e. up to the character just before the U+003E or EOF
					//character).
					if ($chars === false) {
						$chars = $this->_consumeRemaining();
					}
					//(If the comment was started by the end of the file (EOF),
					//the token is empty.)
					$chars = $chars !== false ?
							 $this->_bogusCommentStateSwitcher . $chars :
							 "";
					$this->_bogusCommentStateSwitcher = null;
					$this->_emitToken(self::COMMENT, $chars);
					//Switch to the data state.
					$this->state = self::DATA_STATE;
					//If the end of the file was reached, reconsume the EOF
					//character.
					if ($this->_isEOF) {
						$this->_isEOF = false;
					}
				break;

				//Markup declaration open state
				case self::MARKUP_DECLARATION_OPEN_STATE:
					//If the next two characters are both U+002D HYPHEN-MINUS
					//characters (-), consume those two characters, create a
					//comment token whose data is the empty string, and switch
					//to the comment start state.
					$chars = $this->_consume();
					if ($chars === false) {
						$chars = "";
					}
					$char = $this->_consume();
					if ($char === false) {
						$char = "";
					}
					$chars .= $char;
					if ($chars === "--") {
						$this->_tempToken = array(
							"token" => self::COMMENT,
							"args" => ""
						);
						$this->state = self::COMMENT_START_STATE;
					} else {
						for ($i = 0; $i < 5; $i++) {
							$char = $this->_consume();
							if ($char === false) {
								$char = "";
							}
							$chars .= $char;
						}
						//Otherwise, if the next seven characters are an ASCII
						//case-insensitive match for the word "DOCTYPE", then
						//consume those characters and switch to the DOCTYPE
						//state.
						if (strtolower($chars) === "doctype") {
							$this->state = self::DOCTYPE_STATE;
						}
						//Otherwise, if the current node is not an element in
						//the HTML namespace and the next seven characters are an
						//case-sensitive match for the string "[CDATA[" (the five
						//uppercase letters "CDATA" with a U+005B LEFT SQUARE BRACKET
						elseif ($this->current->namespaceURI !== self::HTML_NAMESPACE &&
								$chars === "[CDATA[") {
							$this->state = self::CDATA_SECTION_STATE;
						}
						//Otherwise, this is a parse error. Switch to the bogus
						//comment state. The next character that is consumed,
						//if any, is the first character that will be in the comment.
						else {
							$len = strlen($chars);
							/*If more than 1 character is found unconsume
							//everything except the first one*/
							if ($len > 1) {
								$this->_unconsume($len - 1);
							}
							$this->state = self::BOGUS_COMMENT_STATE;
							$this->_bogusCommentStateSwitcher =  $len ?
																$chars[0] :
																"";
							if ($this->_isEOF) {
								$this->_isEOF = false;
							}
						}
					}
				break;

				//Comment start state
				case self::COMMENT_START_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the comment start dash
					//state.
					if ($char === "-") {
						$this->state = self::COMMENT_START_DASH_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Switch to the
					//data state. Emit the comment token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Emit the comment token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the
					//comment token's data. Switch to the comment state.
					else {
						$this->_tempToken["args"] .= $char;
						$this->state = self::COMMENT_STATE;
					}
				break;

				//Comment start state
				case self::COMMENT_START_DASH_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the comment end state
					if ($char === "-") {
						$this->state = self::COMMENT_END_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Switch to the
					//data state. Emit the comment token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Emit the comment token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append a U+002D HYPHEN-MINUS character (-)
					//and the current input character to the comment token's data.
					//Switch to the comment state.
					else {
						$this->_tempToken["args"] .= "-" . $char;
						$this->state = self::COMMENT_STATE;
					}
				break;

				//Comment state
				case self::COMMENT_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the comment end dash state
					if ($char === "-") {
						$this->state = self::COMMENT_END_DASH_STATE;
					}
					//EOF: Parse error. Emit the comment token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the
					//comment token's data.
					else {
						$char .= $this->_consumeUntilFind("-");
						$this->_tempToken["args"] .= $char;
					}
				break;

				//Comment end dash state
				case self::COMMENT_END_DASH_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Switch to the comment end state
					if ($char === "-") {
						$this->state = self::COMMENT_END_STATE;
					}
					//EOF: Parse error. Emit the comment token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append a U+002D HYPHEN-MINUS character (-)
					//and the current input character to the comment token's
					//data. Switch to the comment state.
					else {
						$this->_tempToken["args"] .= "-" . $char;
						$this->state = self::COMMENT_STATE;
					}
				break;

				//Comment end state
				case self::COMMENT_END_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the comment token.
					if ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//U+0021 EXCLAMATION MARK (!): Parse error. Switch to the
					//comment end bang state.
					elseif ($char === "!") {
						$this->state = self::COMMENT_END_BANG_STATE;
					}
					//U+002D HYPHEN-MINUS (-): Parse error. Append a U+002D
					//HYPHEN-MINUS character (-) to the comment token's data.
					elseif ($char === "-") {
						$this->_tempToken["args"] .= $char;
					}
					//EOF: Parse error. Emit the comment token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Append two U+002D
					//HYPHEN-MINUS characters (-) and the current input
					//character to the comment token's data. Switch to the
					//comment state.
					else {
						$this->_tempToken["args"] .= "--" . $char;
						$this->state = self::COMMENT_STATE;
					}
				break;

				//Comment end bang state
				case self::COMMENT_END_BANG_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+002D HYPHEN-MINUS (-): Append two U+002D HYPHEN-MINUS
					//characters (-) and a U+0021 EXCLAMATION MARK character
					//(!) to the comment token's data. Switch to the comment
					//end dash state.
					if ($char === "-") {
						$this->_tempToken["args"] .= "--!";
						$this->state = self::COMMENT_END_DASH_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the comment token.
					elseif ($char === ">") {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
					}
					//EOF: Parse error. Emit the comment token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append two U+002D HYPHEN-MINUS characters
					//(-), a U+0021 EXCLAMATION MARK character (!), and the
					//current input character to the comment token's data.
					//Switch to the comment state.
					else {
						$this->_tempToken["args"] .= "--!" . $char;
						$this->state = self::COMMENT_STATE;
					}
				break;

				//DOCTYPE state
				case self::DOCTYPE_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the before
					//DOCTYPE name state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::BEFORE_DOCTYPE_NAME_STATE;
					}
					//EOF: Parse error. Create a new DOCTYPE token. Set its
					//force-quirks flag to on. Emit the token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							self::DOCTYPE,
							array("force-quirks" => "on")
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Reconsume the character in
					//the before DOCTYPE name state.
					else {
						$this->state = self::BEFORE_DOCTYPE_NAME_STATE;
						$this->_unconsume();
					}
				break;

				//Before DOCTYPE name state
				case self::BEFORE_DOCTYPE_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Create a new DOCTYPE token. Set the
					//token's name to the lowercase version of the current
					//input character (add 0x0020 to the character's code point).
					//Switch to the DOCTYPE name state.
					elseif ($char >= "A" && $char <= "Z") {
						$this->_tempToken = array(
							"token" => self::DOCTYPE,
							"args" => array(
								"name" => strtolower($char)
							)
						);
						$this->state = self::DOCTYPE_NAME_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Create a new
					//DOCTYPE token. Set its force-quirks flag to on. Switch to
					//the data state. Emit the token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							self::DOCTYPE,
							array("force-quirks" => "on")
						);
					}
					//EOF: Parse error. Create a new DOCTYPE token. Set its
					//force-quirks flag to on. Emit the token. Reconsume the
					//EOF character in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							self::DOCTYPE,
							array("force-quirks" => "on")
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Create a new DOCTYPE token. Set the
					//token's name to the current input character. Switch to
					//the DOCTYPE name state.
					else {
						$this->_tempToken = array(
							"token" => self::DOCTYPE,
							"args" => array("name" => $char)
						);
						$this->state = self::DOCTYPE_NAME_STATE;
					}
				break;

				//DOCTYPE name state
				case self::DOCTYPE_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the after
					//DOCTYPE name state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::AFTER_DOCTYPE_NAME_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current DOCTYPE token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//U+0041 LATIN CAPITAL LETTER A through to U+005A LATIN
					//CAPITAL LETTER Z: Append the lowercase version of the
					//current input character (add 0x0020 to the character's
					//code point) to the current DOCTYPE token's name.
					//Anything else: Append the current input character to the
					//current DOCTYPE token's name.
					else {
						$char .= $this->_consumeUntilFind("\x09\x0A\x0C\x20>");
						$char = strtolower($char);
						$this->_tempToken["args"]["name"] .= $char;
					}
				break;

				//After DOCTYPE name state
				case self::AFTER_DOCTYPE_NAME_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current DOCTYPE token.
					elseif ($char === ">") {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else:
					else {
						$chars = $char;
						for ($i = 0; $i < 5; $i++) {
							$char = $this->_consume();
							if ($char === false) {
								$char = "";
							}
							$chars .= $char;
						}
						//If the six characters starting from the current input
						//character are an ASCII case-insensitive match for the
						//word "PUBLIC", then consume those characters and 
						//switch to the after DOCTYPE public keyword state.
						if (strtolower($chars) === "public") {
							$this->state = self::AFTER_DOCTYPE_PUBLIC_KEYWORD_STATE;
						}
						//Otherwise, if the six characters starting from the
						//current input character are an ASCII case-insensitive
						//match for the word "SYSTEM", then consume those
						//characters and switch to the after DOCTYPE system
						//keyword state.
						elseif (strtolower($chars) === "system") {
							$this->state = self::AFTER_DOCTYPE_SYSTEM_KEYWORD_STATE;
						}
						//Otherwise, this is the parse error. Set the DOCTYPE
						//token's force-quirks flag to on. Switch to the bogus
						//DOCTYPE state.
						else {
							/*Unconsume every consumed character except the
							first*/
							$len = strlen($chars);
							if ($len > 1) {
								$this->_unconsume($len - 1);
							}
							$this->_tempToken["args"]["force-quirks"] = "on";
							$this->state = self::BOGUS_DOCTYPE_STATE;
						}
					}
				break;

				//After DOCTYPE public keyword state
				case self::AFTER_DOCTYPE_PUBLIC_KEYWORD_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the before
					//DOCTYPE public identifier state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::BEFORE_DOCTYPE_PUBLIC_IDENTIFIER_STATE;
					}
					//U+0022 QUOTATION MARK ("): Parse error. Set the DOCTYPE
					//token's public identifier to the empty string (not missing), then
					//switch to the DOCTYPE public identifier (double-quoted)
					//state.
					elseif ($char === '"') {
						$this->state = self::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED_STATE;
					}
					//U+0027 APOSTROPHE ('): Parse error. Set the DOCTYPE
					//token's public identifier to the empty string (not
					//missing), then switch to the DOCTYPE public identifier
					//(single-quoted) state.
					elseif ($char === "'") {
						$this->state = self::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Set the
					//DOCTYPE token's force-quirks flag to on. Switch to the
					//data state. Emit that DOCTYPE token.
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);						
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Set the DOCTYPE token's
					//force-quirks flag to on. Switch to the bogus DOCTYPE
					//state.
					else {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::BOGUS_DOCTYPE_STATE;
					}
				break;

				//Before DOCTYPE public identifier state
				case self::BEFORE_DOCTYPE_PUBLIC_IDENTIFIER_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+0022 QUOTATION MARK ("): Set the DOCTYPE token's public
					//identifier to the empty string (not missing), then switch
					//to the DOCTYPE public identifier (double-quoted) state.
					elseif ($char === '"') {
						$this->_tempToken["args"]["public"] = "";
						$this->state = self::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED_STATE;
					}
					//U+0027 APOSTROPHE ('): Set the DOCTYPE token's public
					//identifier to the empty string (not missing), then switch
					//to the DOCTYPE public identifier (single-quoted) state.
					elseif ($char === "'") {
						$this->_tempToken["args"]["public"] = "";
						$this->state = self::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Set the
					//DOCTYPE token's force-quirks flag to on. Switch to the
					//data state. Emit that DOCTYPE token.
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Set the DOCTYPE token's
					//force-quirks flag to on. Switch to the bogus DOCTYPE
					//state.
					else {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::BOGUS_DOCTYPE_STATE;
					}
				break;

				//DOCTYPE public identifier (double-quoted) state
				case self::DOCTYPE_PUBLIC_IDENTIFIER_DOUBLE_QUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0022 QUOTATION MARK ("): Switch to the after DOCTYPE
					//public identifier state.
					if ($char === '"') {
						$this->state = self::AFTER_DOCTYPE_PUBLIC_IDENTIFIER_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Set the
					//DOCTYPE token's force-quirks flag to on. Switch to the
					//data state. Emit that DOCTYPE token.
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the
					//current DOCTYPE token's public identifier.
					else {
						$char .= $this->_consumeUntilFind('">');
						$this->_tempToken["args"]["public"] .= $char;
					}
				break;

				//DOCTYPE public identifier (single-quoted) state
				case self::DOCTYPE_PUBLIC_IDENTIFIER_SINGLE_QUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0027 APOSTROPHE ('): Switch to the after DOCTYPE public
					//identifier state.
					if ($char === "'") {
						$this->state = self::AFTER_DOCTYPE_PUBLIC_IDENTIFIER_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Set the
					//DOCTYPE token's force-quirks flag to on. Switch to the
					//data state. Emit that DOCTYPE token. 
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the
					//current DOCTYPE token's public identifier.
					else {
						$char .= $this->_consumeUntilFind("'>");
						$this->_tempToken["args"]["public"] .= $char;
					}
				break;

				//After DOCTYPE public identifier state
				case self::AFTER_DOCTYPE_PUBLIC_IDENTIFIER_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the
					//between DOCTYPE public and system identifiers state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current DOCTYPE token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);						
					}
					//U+0022 QUOTATION MARK ("): Parse error. Set the DOCTYPE
					//token's system identifier to the empty string (not
					//missing), then switch to the DOCTYPE system identifier
					//(double-quoted) state.
					elseif ($char === '"') {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED_STATE;
					}
					//U+0027 APOSTROPHE ('): Parse error. Set the DOCTYPE
					//token's system identifier to the empty string (not
					//missing), then switch to the DOCTYPE system identifier
					//(single-quoted) state.
					elseif ($char === "'") {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED_STATE;
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Set the DOCTYPE token's
					//force-quirks flag to on. Switch to the bogus DOCTYPE
					//state.
					else {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::BOGUS_DOCTYPE_STATE;
					}
				break;

				//Between DOCTYPE public and system identifiers state
				case self::BETWEEN_DOCTYPE_PUBLIC_AND_SYSTEM_IDENTIFIERS_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current DOCTYPE token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//U+0022 QUOTATION MARK ("): Set the DOCTYPE token's system
					//identifier to the empty string (not missing), then switch
					//to the DOCTYPE system identifier (double-quoted) state.
					elseif ($char === '"') {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED_STATE;
					}
					//U+0027 APOSTROPHE ('): Set the DOCTYPE token's system
					//identifier to the empty string (not missing), then switch
					//to the DOCTYPE system identifier (single-quoted) state.
					elseif ($char === "'") {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED_STATE;
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Set the DOCTYPE token's
					//force-quirks flag to on. Switch to the bogus DOCTYPE
					//state.
					else {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::BOGUS_DOCTYPE_STATE;
					}
				break;

				//After DOCTYPE system keyword state
				case self::AFTER_DOCTYPE_SYSTEM_KEYWORD_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Switch to the
					//before DOCTYPE system identifier state.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						$this->state = self::BEFORE_DOCTYPE_SYSTEM_IDENTIFIER_STATE;
					}
					//U+0022 QUOTATION MARK ("): Parse error. Set the DOCTYPE
					//token's system identifier to the empty string (not
					//missing), then switch to the DOCTYPE system identifier
					//(double-quoted) state.
					elseif ($char === '"') {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED_STATE;
					}
					//U+0027 APOSTROPHE ('): Parse error. Set the DOCTYPE
					//token's system identifier to the empty string (not
					//missing), then switch to the DOCTYPE system identifier
					//(single-quoted) state.
					elseif ($char === "'") {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current DOCTYPE token.
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Set the DOCTYPE token's
					//force-quirks flag to on. Switch to the bogus DOCTYPE
					//state.
					else {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::BOGUS_DOCTYPE_STATE;
					}
				break;

				//Before DOCTYPE system identifier state
				case self::BEFORE_DOCTYPE_SYSTEM_IDENTIFIER_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+0022 QUOTATION MARK ("): Set the DOCTYPE token's system
					//identifier to the empty string (not missing), then switch
					//to the DOCTYPE system identifier (double-quoted) state.
					elseif ($char === '"') {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED_STATE;
					}
					//U+0027 APOSTROPHE ('): Set the DOCTYPE token's system
					//identifier to the empty string (not missing), then switch
					//to the DOCTYPE system identifier (single-quoted) state.
					elseif ($char === "'") {
						$this->_tempToken["args"]["system"] = "";
						$this->state = self::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Set the
					//DOCTYPE token's force-quirks flag to on. Switch to the
					//data state. Emit that DOCTYPE token.
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Set the DOCTYPE token's
					//force-quirks flag to on. Switch to the bogus DOCTYPE
					//state.
					else {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::BOGUS_DOCTYPE_STATE;
					}
				break;

				//DOCTYPE system identifier (double-quoted) state
				case self::DOCTYPE_SYSTEM_IDENTIFIER_DOUBLE_QUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0022 QUOTATION MARK ("): Switch to the after DOCTYPE
					//system identifier state.
					if ($char === '"') {
						$this->state = self::AFTER_DOCTYPE_SYSTEM_IDENTIFIER_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Set the
					//DOCTYPE token's force-quirks flag to on. Switch to the
					//data state. Emit that DOCTYPE token.
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the
					//current DOCTYPE token's system identifier.
					else {
						$char .= $this->_consumeUntilFind('">');
						$this->_tempToken["args"]["system"] .= $char;
					}
				break;

				//DOCTYPE system identifier (single-quoted) state
				case self::DOCTYPE_SYSTEM_IDENTIFIER_SINGLE_QUOTED_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0027 APOSTROPHE ('): Switch to the after DOCTYPE system
					//identifier state.
					if ($char === "'") {
						$this->state = self::AFTER_DOCTYPE_SYSTEM_IDENTIFIER_STATE;
					}
					//U+003E GREATER-THAN SIGN (>): Parse error. Set the
					//DOCTYPE token's force-quirks flag to on. Switch to the
					//data state. Emit that DOCTYPE token.
					elseif ($char === ">") {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);						
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Append the current input character to the
					//current DOCTYPE token's system identifier.
					else {
						$char .= $this->_consumeUntilFind("'>");
						$this->_tempToken["args"]["system"] .= $char;
					}
				break;

				//After DOCTYPE system identifier state
				case self::AFTER_DOCTYPE_SYSTEM_IDENTIFIER_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
					//U+000C FORM FEED (FF), U+0020 SPACE: Ignore the character.
					if ($char === "\x09" || $char === "\x0A" ||
						$char === "\x0C" || $char === "\x20") {
						continue;
					}
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current DOCTYPE token.
					elseif ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Parse error. Set the DOCTYPE token's force-quirks
					//flag to on. Emit that DOCTYPE token. Reconsume the EOF
					//character in the data state.
					elseif ($char === null) {
						$this->_tempToken["args"]["force-quirks"] = "on";
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Switch to the bogus DOCTYPE
					//state. (This does not set the DOCTYPE token's
					//force-quirks flag to on.)
					else {
						$this->state = self::BOGUS_DOCTYPE_STATE;
					}
				break;

				//Bogus DOCTYPE state
				case self::BOGUS_DOCTYPE_STATE:
					//Consume the next input character
					$char = $this->_consume();
					//U+003E GREATER-THAN SIGN (>): Switch to the data state.
					//Emit the current DOCTYPE token.
					if ($char === ">") {
						$this->state = self::DATA_STATE;
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
					}
					//EOF: Emit the DOCTYPE token. Reconsume the EOF character
					//in the data state.
					elseif ($char === null) {
						$this->_emitToken(
							$this->_tempToken["token"],
							$this->_tempToken["args"]
						);
						$this->state = self::DATA_STATE;
						$this->_isEOF = false;
					}
					//Anything else: Parse error. Ignore the character.
					else {
						continue;
					}
				break;

				//CDATA section state
				case self::CDATA_SECTION_STATE:
					//Consume every character up to the next occurrence of the
					//three character sequence U+005D RIGHT SQUARE BRACKET
					//U+005D RIGHT SQUARE BRACKET U+003E GREATER-THAN SIGN
					//(]]>), or the end of the file (EOF), whichever comes
					//first.
					$chars = $this->_consumeUntil("]]>");
					if ($chars === false) {
						$chars = $this->_consumeRemaining();
					}
					//Emit a series of character tokens consisting of all the
					//characters consumed except the matching three character
					//sequence at the end (if one was found before the end of
					//the file).
					if ($chars) {
						$len = strlen($chars);
						for ($i = 0; $i < $len; $i++) {
							$this->_emitToken(self::CHAR, $chars[$i]);
						}
					}
					//Switch to the data state.
					$this->state = self::DATA_STATE;
					//If the end of the file was reached, reconsume the EOF
					//character.
					if ($chars === false) {
						$this->_isEOF = false;
					}
				break;
			}
		}
		//Once the user agent stops parsing the document, the user agent must
		//run the following steps:
		//Pop all the nodes off the stack of open elements.
		while (count($this->_stack)) {
			$this->_popStack();
		}
		/*Set the compatMode property on the document according to the quirks
		mode flag*/
		if (!$this->fragmentCase) {
			if ($this->quirksMode === ParserHTMLBuilder::QUIRKS_MODE) {
				$this->document->compatMode = "BackCompat";
			} else {
				$this->document->compatMode = "CSS1Compat";
			}
		}
	}
	
	/**
	 * Tokenizes a character reference
	 *
	 * @param	string			$allowed	Additional allowed character
	 * @param	bool			$attribute	True if the character reference is
	 *										inside an attribute value
	 * @return	string|bool		Token string or false if it's not a character
	 *							reference
	 * @access	protected
	 */
	protected function _tokenizeCharacterReference ($allowed = null, $attribute = false)
	{
		//The behavior depends on the identity of the next character (the one
		//immediately after the U+0026 AMPERSAND character):
		$char = $this->_consume();
		//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF), U+000C FORM FEED
		//(FF), U+0020 SPACE, U+003C LESS-THAN SIGN, U+0026 AMPERSAND, EOF,
		//The additional allowed character, if there is one: Not a character
		//reference. No characters are consumed, and nothing is returned.
		//(This is not an error, either.)
		if ($char === "\x09" || $char === "\x0A" || $char === "\x0C" ||
			$char === "\x20" || $char === "<" || $char === "&" ||
			$char === null || $char === $allowed) {
			$this->_unconsume();
			return false;
		}
		//U+0023 NUMBER SIGN (#)
		elseif ($char === "#") {
			//The behavior further depends on the character after the U+0023
			//NUMBER SIGN:
			$char2 = $this->_consume();
			$int = false;
			//U+0078 LATIN SMALL LETTER X, U+0058 LATIN CAPITAL LETTER X:
			//Follow the steps below, but using the range of characters U+0030
			//DIGIT ZERO (0) to U+0039 DIGIT NINE (9), U+0061 LATIN SMALL
			//LETTER A to U+0066 LATIN SMALL LETTER F, and U+0041 LATIN CAPITAL
			//LETTER A to U+0046 LATIN CAPITAL LETTER F (in other words, 0-9,
			//A-F, a-f).
			if ($char2 === "x" || $char2 === "X") {
				$range = "[0-9a-fA-F]+";
			}
			//Anything else: Follow the steps below, but using the range of
			//characters U+0030 DIGIT ZERO (0) to U+0039 DIGIT NINE (9).
			else {
				$range = "\d+";
				$int = true;
			}
			//Consume as many characters as match the range of characters given
			//above.
			$num = $this->_consumeRegexp($range);
			if ($int) {
				$num = $char2 . ($num === false ? "" : $num);
			}
			//If no characters match the range, then don't consume any
			//characters (and unconsume the U+0023 NUMBER SIGN character and,
			//if appropriate, the X character). This is a parse error; nothing
			//is returned.
			if (!$num) {
				$this->_unconsume(2);
				return false;
			}
			//Otherwise, if the next character is a U+003B SEMICOLON, consume
			//that too. If it isn't, there is a parse error.
			$next = $this->_consume();
			if ($next !== ";") {
				$this->_unconsume();
			}
			//If one or more characters match the range, then take them all and
			//interpret the string of characters as a number (either hexadecimal
			//or decimal as appropriate).
			if (!$int) {
				$num = hexdec($num);
			} else {
				$num = (int) $num;
			}
			//If that number is one of the numbers in the first column of the
			//following table, then this is a parse error. Find the row with
			//that number in the first column, and return a character token
			//for the Unicode character given in the second column of that row.
			if (isset($this->_illegalEntitiesToUnicode[$num])) {
				return $this->_getEntity($this->_illegalEntitiesToUnicode[$num]);
			}
			//Otherwise, if the number is in the range 0xD800 to 0xDFFF or is
			//greater than 0x10FFFF, then this is a parse error. Return a
			//U+FFFD REPLACEMENT CHARACTER.
			elseif (($num >= 0xD800 && $num <= 0xDFFF) || $num > 0x10FFFF) {
				return $this->_unicodeReplacementCharacter;
			}
			//Otherwise, return a character token for the Unicode character whose code point is that number.
			else {
				//If the number is in the range 0x0001 to 0x0008, 0x000E to
				//0x001F,  0x007F  to 0x009F, 0xFDD0 to 0xFDEF, or is one of
				//0x000B, 0xFFFE, 0xFFFF, 0x1FFFE, 0x1FFFF, 0x2FFFE, 0x2FFFF,
				//0x3FFFE, 0x3FFFF, 0x4FFFE, 0x4FFFF, 0x5FFFE, 0x5FFFF,
				//0x6FFFE, 0x6FFFF, 0x7FFFE, 0x7FFFF, 0x8FFFE, 0x8FFFF, 0x9FFFE,
				//0x9FFFF, 0xAFFFE, 0xAFFFF, 0xBFFFE, 0xBFFFF, 0xCFFFE, 0xCFFFF,
				//0xDFFFE, 0xDFFFF, 0xEFFFE, 0xEFFFF, 0xFFFFE, 0xFFFFF, 0x10FFFE,
				//or 0x10FFFF, then this is a parse error.
				return $this->_getEntity($num);
			}
		}
		//Anything else
		else {
			static $regexp, $ncrTable;
			//Consume the maximum number of characters possible, with the
			//consumed characters matching one of the identifiers in the first
			//column of the named character references table (in a
			//case-sensitive manner).
			$this->_unconsume();
			if (!isset($regexp)) {
				$ncrTable = self::_getNamedCharacterReferences();
				$regexp = implode("|", array_keys($ncrTable));
			}
			$ent = $this->_consumeRegexp($regexp);
			$ncr = $ent && isset($ncrTable[$ent]) ? $ncrTable[$ent] : null;
			//If no match can be made, then no characters are consumed, and
			//nothing is returned. In this case, if the characters after the
			//U+0026 AMPERSAND character (&) consist of a sequence of one or
			//more characters in the range U+0030 DIGIT ZERO (0) to U+0039
			//DIGIT NINE (9), U+0061 LATIN SMALL LETTER A to U+007A LATIN SMALL
			//LETTER Z, and U+0041 LATIN CAPITAL LETTER A to U+005A LATIN
			//CAPITAL LETTER Z, followed by a U+003B SEMICOLON character (;),
			//then this is a parse error.
			if ($ncr === null) {
				if ($ent) {
					$this->_unconsume(strlen($ent));
				}
				return false;
			} else {
				$length = strlen($ent);
				$hasSemicolon = $ent[$length - 1] === ";";
				//If the character reference is being consumed as part of an
				//attribute, and the last character matched is not a U+003B
				//SEMICOLON character (;), and the next character is either a
				//U+003D EQUALS SIGN character (=) or in the range U+0030
				//DIGIT ZERO (0) to U+0039 DIGIT NINE (9), U+0041 LATIN CAPITAL
				//LETTER A to U+005A LATIN CAPITAL LETTER Z, or U+0061 LATIN
				//SMALL LETTER A to U+007A LATIN SMALL LETTER Z, then, for
				//historical reasons, all the characters that were matched
				//after the U+0026 AMPERSAND character (&) must be unconsumed,
				//and nothing is returned.
				if ($attribute && !$hasSemicolon) {
					$nx = $this->_consume();
					$this->_unconsume();
					if (preg_match("#[=0-9a-zA-Z]#", $nx)) {
						$this->_unconsume($length);
						return false;
					}
				}
				//Otherwise, a character reference is parsed. If the last
				//character matched is not a U+003B SEMICOLON character (;),
				//there is a parse error.
				//Return one or two character tokens for the character(s)
				//corresponding to the character reference name (as given by
				//the second column of the named character references table).
				return $this->_getEntity($ncr);
			}
		}
	}
	
	/**
	 * Gets the value of a numeric html entity
	 *
	 * @param	int		$code		Decimal ascii value of the entity
	 * @return	string	The decoded entity
	 * @access	protected
	 */
	protected function _getEntity ($code)
	{
		static $entities = array();
		if (!isset($entities[$code])) {
			$ent = html_entity_decode("&#$code;", ENT_QUOTES, 'UTF-8');
			$entities[$code] = $this->decode($ent, null, 'UTF-8');
		}
		return $entities[$code];
	}

	/**
	 * Returns the Named Character References conversion table
	 *
	 * @static
	 * @return	array	Named Character References conversion table
	 * @access	protected
	 */
	protected static function _getNamedCharacterReferences ()
	{
		$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		$refFile = $dir . "named-character-references";
		return unserialize(file_get_contents($refFile));
	}
	
	/**
	 * Returns true if the given tag name is an appropriate end tag token
	 *
	 * @param	string		$tag	Tag name
	 * @return	bool		True if it's an appropriate end tag token otherwise
	 *						false
	 * @access	protected
	 */
	protected function _isAppropriateEndTagToken ($tag)
	{
		//An appropriate end tag token is an end tag token whose tag name
		//matches the tag name of the last start tag to have been emitted from
		//this tokenizer, if any. If no start tag has been emitted from this 
		//tokenizer, then no end tag token is appropriate.
		$count = count($this->_startTags);
		return $count && $this->_startTags[$count - 1] === $tag;
	}
	
	/**
	 * Generate an attribute on the current temporary token
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function _generateAttributeOnToken()
	{
		if (!isset($this->_tempToken["args"]["attributes"])) {
			$this->_tempToken["args"]["attributes"] = array();
		}
		if (isset($this->_tempToken["args"]["attributes"][$this->_attribute])) {
			$this->_attribute = null;
		} else {
			$this->_tempToken["args"]["attributes"][$this->_attribute] = array(
				"value" => ""
			);
		}
	}
}