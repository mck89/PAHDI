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
 * HTML parser tree builder
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserHTMLBuilder extends ParserHTMLTokenizer
{
	//Tree builder current insertion mode
	const INITIAL_MODE = 1;
	const BEFORE_HTML_MODE = 2;
	const BEFORE_HEAD_MODE = 3;
	const IN_HEAD_MODE = 4;
	const IN_HEAD_NOSCRIPT_MODE = 5;
	const AFTER_HEAD_MODE = 6;
	const IN_BODY_MODE = 7;
	const TEXT_MODE = 8;
	const IN_TABLE_MODE = 9;
	const IN_TABLE_TEXT_MODE = 10;
	const IN_CAPTION_MODE = 11;
	const IN_COLUMN_GROUP_MODE = 12;
	const IN_TABLE_BODY_MODE = 13;
	const IN_ROW_MODE = 14;
	const IN_CELL_MODE = 15;
	const IN_SELECT_MODE = 16;
	const IN_SELECT_IN_TABLE_MODE = 17;
	const AFTER_BODY_MODE = 18;
	const IN_FRAMESET_MODE = 19;
	const AFTER_FRAMESET_MODE = 20;
	const AFTER_AFTER_BODY_MODE = 21;
	const AFTER_AFTER_FRAMESET_MODE = 22;

	//Quirks mode values
	const NO_QUIRKS_MODE = 1;
	const QUIRKS_MODE = 2;
	const LIMITED_QUIRKS_MODE = 3;

	//Namespaces constants
	const HTML_NAMESPACE = "http://www.w3.org/1999/xhtml";
	const MATHML_NAMESPACE = "http://www.w3.org/1998/Math/MathML";
	const SVG_NAMESPACE = "http://www.w3.org/2000/svg";
	const XLINK_NAMESPACE = "http://www.w3.org/1999/xlink";
	const XML_NAMESPACE = "http://www.w3.org/XML/1998/namespace";
	const XMLNS_NAMESPACE = "http://www.w3.org/2000/xmlns/";

	//Tree builder marker
	const MARKER = 100;
	
	/**
	 * Emitted start tag tokens
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_startTags = array();
	
	/**
	 * Quirks mode
	 *
	 * @var		int
	 */
	public $quirksMode;

	/**
	 * Open elements stack
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_stack;
	
	/**
	 * If this flag is true elements will be foster parented
	 * instead of appended
	 *
	 * @var		HTMLElement
	 * @access	protected
	 */
	protected $_forceFosterParent = false;

	/**
	 * Head element pointer
	 *
	 * @var		HTMLElement
	 * @access	protected
	 */
	protected $_headPointer;

	/**
	 * Form element pointer
	 *
	 * @var		HTMLElement
	 * @access	protected
	 */
	protected $_formPointer;

	/**
	 * Support property to ignore next line feed
	 * tokens when required.
	 *
	 * @var		bool
	 * @access	protected
	 */
	protected $_ignoreLFToken = false;

	/**
	 * Frameset-ok flag
	 *
	 * @var		string
	 */
	public $framesetOkFlag = "ok";

	/**
	 * Active formatting elements array
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_AFElements = array();
	
	/**
	 * Tree constructor current insertion mode
	 *
	 * @var		int
	 * @access	protected
	 */
	protected $_mode;

	/**
	 * When the insertion mode is switched to "text" or "in table text",
	 * the original insertion mode  is also set. This is the insertion mode
	 * to which the tree construction stage will return.
	 *
	 * @var		int
	 * @access	protected
	 */
	protected $_originalInsertionMode;

	/**
	 * Pending table character tokens array
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_pendingTableCharacterTokens = array();

	/**
	 * Current element
	 *
	 * @var		HTMLElement
	 */
	public $current;
	
	/**
	 * Base path
	 *
	 * @var		string
	 */
	public $base;
	
	/**
	 * Emits a token
	 *
	 * @param	int		$token		Token to emit
	 * @param	array	$data		Token's data array
	 * @param	int		$mode		Insertion mode to use. By default is the
	 *								current one.
	 * @return	mixed	Nothing or false if the token was ignored
	 * @access	protected
	 */
	protected function _emitToken ($token, $data = null, $mode = null)
	{
		if ($token === self::START_TAG) {
			$tagname = $data["tagname"];
			$this->_startTags[] = $tagname;
		} elseif ($token === self::END_TAG) {
			$tagname = $data["tagname"];
		} else {
			$tagname = null;
		}	
		//If there is no current node, If the current node is an
		//element in the HTML namespace, If the current node is a
		//MathML text integration point and the token is a start
		//tag whose tag name is neither "mglyph" nor "malignmark",
		//If the current node is a MathML text integration point
		//and the token is a character token If the current node
		//is an annotation-xml element in the MathML namespace and
		//the token is a start tag whose tag name is "svg", If the
		//current node is an HTML integration point and the token
		//is a start tag, If the current node is an HTML integration
		//point and the token is a character token If the token is an
		//end-of-file token
		//Process the token according to the rules given in the section
		//corresponding to the current insertion mode in HTML content.
	    //Otherwise: Process the token according to the rules given in
		//the section for parsing tokens in foreign content.
		if ($this->current && $token !== self::EOF &&
			$this->current->namespaceURI !== self::HTML_NAMESPACE &&
			($this->current->tagName !== "annotation-xml" ||
			$this->current->namespaceURI !== self::MATHML_NAMESPACE ||
			$token !== self::START_TAG || $tagname !== "svg") &&
			(!$this->_isMathTextIntegrationPoint() ||
			($token !== self::START_TAG && $token !== self::CHAR) ||
			$tagname === "mglyph" || $tagname === "malignmark") &&
			(!$this->_isHTMLIntegrationPoint() ||
			($token !== self::START_TAG && $token !== self::CHAR))) {
			return $this->_parseTokenInForeignContent($token, $data, $tagname);
		}
		if (!$mode) {
			$mode = $this->_mode;
		}
		switch ($mode) {
			//The "initial" insertion mode
			case self::INITIAL_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Ignore the token.
				if ($token === self::CHAR && ($data === "\x09" || 				
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					return false;
				}
				//A comment token: Append a Comment node to the Document object
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data, $this->document);
				}
				//A DOCTYPE token
				elseif ($token === self::DOCTYPE) {
					$systemMissing = !isset($data["system"]);
					$publicMissing = !isset($data["public"]);
					$name = isset($data["name"]) ? $data["name"] : "";
					$public = !$publicMissing ? $data["public"] : "";
					$system = !$systemMissing ? $data["system"] : "";
					//Append a DocumentType node to the Document node, with the
					//name attribute set to the name given in the DOCTYPE token,
					//or the empty string if the name was missing; the publicId
					//attribute set to the public identifier given in the
					//DOCTYPE token, or the empty string if the public
					//identifier was missing; the systemId attribute set to the
					//system identifier given in the DOCTYPE token, or the empty
					//string if the system identifier was missing
					$this->_insertDoctype($name, $public, $system);
					$CPublic = strtolower($public);
					$CSystem = strtolower($system);
					//Then, if the DOCTYPE token matches one of the conditions
					//in the following list, then set the Document to quirks
					//mode
					if ((isset($data["force-quirks"]) && 
						$data["force-quirks"] === "on") || $name !== "html" ||
						$CPublic === "-//w3o//dtd w3 html strict 3.0//en//" || 
						$CPublic === "-/w3c/dtd html 4.0 transitional/en" ||
						$CPublic === "html" ||
						$CSystem === "http://www.ibm.com/data/dtd/v11/ibmxhtml1-transitional.dtd" ||
						($systemMissing &&
						preg_match("#^-//w3c//dtd html 4\.01 (?:frameset|transitional)//#", $CPublic))) {
						$this->quirksMode = self::QUIRKS_MODE;
					} else {
						$publicQuirks = array(
							"+//silmaril//dtd html pro v0r11 19970101//",
							"-//advasoft ltd//dtd html 3.0 aswedit + extensions//",
							"-//as//dtd html 3.0 aswedit + extensions//",
							"-//ietf//dtd html 2.0 level 1//",
							"-//ietf//dtd html 2.0 level 2//",
							"-//ietf//dtd html 2.0 strict level 1//",
							"-//ietf//dtd html 2.0 strict level 2//",
							"-//ietf//dtd html 2.0 strict//",
							"-//ietf//dtd html 2.0//",
							"-//ietf//dtd html 2.1e//",
							"-//ietf//dtd html 3.0//",
							"-//ietf//dtd html 3.2 final//",
							"-//ietf//dtd html 3.2//",
							"-//ietf//dtd html 3//",
							"-//ietf//dtd html level 0//",
							"-//ietf//dtd html level 1//",
							"-//ietf//dtd html level 2//",
							"-//ietf//dtd html level 3//",
							"-//ietf//dtd html strict level 0//",
							"-//ietf//dtd html strict level 1//",
							"-//ietf//dtd html strict level 2//",
							"-//ietf//dtd html strict level 3//",
							"-//ietf//dtd html strict//",
							"-//ietf//dtd html//",
							"-//metrius//dtd metrius presentational//",
							"-//microsoft//dtd internet explorer 2.0 html strict//",
							"-//microsoft//dtd internet explorer 2.0 html//",
							"-//microsoft//dtd internet explorer 2.0 tables//",
							"-//microsoft//dtd internet explorer 3.0 html strict//",
							"-//microsoft//dtd internet explorer 3.0 html//",
							"-//microsoft//dtd internet explorer 3.0 tables//",
							"-//netscape comm. corp.//dtd html//",
							"-//netscape comm. corp.//dtd strict html//",
							"-//o'reilly and associates//dtd html 2.0//",
							"-//o'reilly and associates//dtd html extended 1.0//",
							"-//o'reilly and associates//dtd html extended relaxed 1.0//",
							"-//softquad software//dtd hotmetal pro 6.0::19990601::extensions to html 4.0//",
							"-//softquad//dtd hotmetal pro 4.0::19971010::extensions to html 4.0//",
							"-//spyglass//dtd html 2.0 extended//",
							"-//sq//dtd html 2.0 hotmetal + extensions//",
							"-//sun microsystems corp.//dtd hotjava html//",
							"-//sun microsystems corp.//dtd hotjava strict html//",
							"-//w3c//dtd html 3 1995-03-24//",
							"-//w3c//dtd html 3.2 draft//",
							"-//w3c//dtd html 3.2 final//",
							"-//w3c//dtd html 3.2//",
							"-//w3c//dtd html 3.2s draft//",
							"-//w3c//dtd html 4.0 frameset//",
							"-//w3c//dtd html 4.0 transitional//",
							"-//w3c//dtd html experimental 19960712//",
							"-//w3c//dtd html experimental 970421//",
							"-//w3c//dtd w3 html//",
							"-//w3o//dtd w3 html 3.0//",
							"-//webtechs//dtd mozilla html 2.0//",
							"-//webtechs//dtd mozilla html//"
						);
						$found = false;
						foreach ($publicQuirks as $pq) {
							if (strpos($CPublic, $pq) === 0) {
								$found = true;
								$this->quirksMode = self::QUIRKS_MODE;
								break;
							}
						}
						//Otherwise, if the DOCTYPE token matches one of the
						//conditions in the following list, then set the
						//Document to limited-quirks mode
						if (!$found &&
							(preg_match("#^-//w3c//dtd xhtml 1\.0 (?:frameset|transitional)//#", $CPublic) ||
							(!$systemMissing &&
							preg_match("#^-//w3c//dtd html 4\.01 (?:frameset|transitional)//#", $CPublic))
							)) {
							$this->quirksMode = self::LIMITED_QUIRKS_MODE;			
						}
						//Then, switch the insertion mode to "before html".
						$this->_mode = self::BEFORE_HTML_MODE;
					}
				}
				//Anything else
				else {
					//If the document is not an iframe srcdoc document, then
					//this is a parse error; set the Document to quirks mode.
					if (!$this->srcdoc) {
						$this->quirksMode = self::QUIRKS_MODE;
					}
					//In any case, switch the insertion mode to "before html",
					//then reprocess the current token.
					$this->_mode = self::BEFORE_HTML_MODE;
					$this->_emitToken($token, $data);
				}
			break;

			//The "before html" insertion mode
			case self::BEFORE_HTML_MODE:
				//A DOCTYPE token Parse error. Ignore the token.
				if ($token === self::DOCTYPE) {
					return false;
				}
				//A comment token: Append a Comment node to the Document object
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data, $this->document);
				}
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Ignore the token.
				elseif ($token === self::CHAR && ($data === "\x09" || 
						$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
						$data === "\x20")) {
					return false;
				}
				//A start tag whose tag name is "html"
				elseif ($token === self::START_TAG && $tagname === "html") {
					//Create an element for the token in the HTML namespace.
					//Append it to the Document object. Put this element in the
					//stack of open elements.
					$this->_insertElement($data, self::HTML_NAMESPACE, true, true);
					//Switch the insertion mode to "before head".
					$this->_mode = self::BEFORE_HEAD_MODE;
				}
				//An end tag whose tag name is one of: "head", "body", "html",
				//"br": Act as described in the "anything else" entry below.
				//Any other end tag: Parse error. Ignore the token.
				elseif ($token === self::END_TAG && $tagname !== "head" &&
						$tagname !== "body" && $tagname !== "html" &&
						$tagname !== "br") {
					return false;
				}
				//Anything else
				else {
					//Create an html element. Append it to the Document object.
					//Put this element in the stack of open elements.
					$this->_insertElement(
						array("tagname" => "html"), 
						self::HTML_NAMESPACE,
						true,
						true
					);
					//Switch the insertion mode to "before head", then
					//reprocess the current token.
					$this->_mode = self::BEFORE_HEAD_MODE;
					$this->_emitToken($token, $data);
				}
			break;

			//The "before head" insertion mode
			case self::BEFORE_HEAD_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Ignore the token.
				if ($token === self::CHAR && ($data === "\x09" ||
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					return false;
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token using
				//the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor(
						$token, 
						$data,
						self::IN_BODY_MODE
					);
				}
				//A start tag whose tag name is "head"
				elseif ($token === self::START_TAG && $tagname === "head") {
					//Insert an HTML element for the token.
					//Set the head element pointer to the newly created head
					//element.
					$this->_headPointer = $this->_insertElement(
						$data,
						self::HTML_NAMESPACE
					);
					//Switch the insertion mode to "in head".
					$this->_mode = self::IN_HEAD_MODE;
				}
				//An end tag whose tag name is one of: "head", "body", "html",
				//"br": Act as if a start tag token with the tag name "head" and
				//no attributes had been seen, then reprocess the current token.
				elseif ($token === self::END_TAG && ($tagname === "head" ||
						$tagname === "body" || $tagname === "html" ||
						$tagname === "br")) {
					$this->_emitToken(self::START_TAG, array("tagname" => "head"));
					$this->_emitToken($token, $data);
				}
				//Any other end tag: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//Anything else : Act as if a start tag token with the tag name
				//"head" and no attributes had been seen, then reprocess the
				//current token.
				else {
					$this->_emitToken(self::START_TAG, array("tagname" => "head"));
					$this->_emitToken($token, $data);
				}
			break;

			//The "in head" insertion mode
			case self::IN_HEAD_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION, 
				// U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Insert the character into the
				//current node.
				if ($token === self::CHAR && ($data === "\x09" ||
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					$this->_insertText($data);
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token using
				//the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor(
						$token, 
						$data, 
						self::IN_BODY_MODE
					);
				}
				//A start tag whose tag name is one of: "base", "basefont",
				//"bgsound", "command", "link"
				elseif ($token === self::START_TAG && ($tagname === "base" ||
						$tagname === "basefont" || $tagname === "bgsound" ||
						$tagname === "command"  || $tagname === "link")) {
					//Insert an HTML element for the token. Immediately pop the
					//current node off the stack of open elements.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_popStack();
				}
				//A start tag whose tag name is "meta"
				elseif ($token === self::START_TAG && $tagname === "meta") {
					//Insert an HTML element for the token. Immediately pop the
					//current node off the stack of open elements.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_popStack();
				}
				//A start tag whose tag name is "title": Follow the generic
				//RCDATA element parsing algorithm.
				elseif ($token === self::START_TAG && $tagname === "title") {
					$this->_parseGenericRCDATAOrText($data, true);
				}
				//A start tag whose tag name is "noscript", if the scripting
				//flag is enabled, A start tag whose tag name is one of:
				//"noframes", "style": Follow the generic raw text element
				//parsing algorithm.
				elseif ($token === self::START_TAG && ($tagname === "noframe" ||
						$tagname === "style" || ($tagname === "noscript" &&
						$this->scriptingFlag))) {
					$this->_parseGenericRCDATAOrText($data);
				}
				//A start tag whose tag name is "noscript", if the scripting
				//flag is disabled
				elseif ($token === self::START_TAG && $tagname === "noscript" &&
						!$this->scriptingFlag) {
					//Insert an HTML element for the token.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					//Switch the insertion mode to "in head noscript".
					$this->_mode = self::IN_HEAD_NOSCRIPT_MODE;
				}
				//A start tag whose tag name is "script"
				elseif ($token === self::START_TAG && $tagname === "script") {
					//Create an element for the token in the HTML namespace.
					//Append the new element to the current node and push it
					//onto the stack of open elements.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					//Switch the tokenizer to the script data state.
					$this->state = self::SCRIPT_DATA_STATE;
					//Let the original insertion mode be the current insertion
					//mode.
					$this->_originalInsertionMode = $this->_mode;
					//Switch the insertion mode to "text".
					$this->_mode = self::TEXT_MODE;
				}
				//An end tag whose tag name is "head"
				elseif ($token === self::END_TAG && $tagname === "head") {
					//Pop the current node (which will be the head element) off
					//the stack of open elements.
					$this->_popStack();
					//Switch the insertion mode to "after head".
					$this->_mode = self::AFTER_HEAD_MODE;
				}
				//A start tag whose tag name is "head", Any other end tag:
				//Parse error. Ignore the token.
				elseif (($token === self::START_TAG && $tagname === "head") ||
						($token === self::END_TAG && ($tagname !== "html" ||
						$tagname !== "body" || $tagname !== "br"))) {
					return false;
				}
				//Anything else: Act as if an end tag token with the tag name
				//"head" had been seen, and reprocess the current token.
				else {
					$this->_emitToken(self::END_TAG, array("tagname" => "head"));
					$this->_emitToken($token, $data);
				}
			break;

			//The "in head noscript" insertion mode
			case self::IN_HEAD_NOSCRIPT_MODE:
				//A DOCTYPE token: Parse error. Ignore the token.
				if ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token using
				//the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor(
						$token,
						$data,
						self::IN_BODY_MODE
					);
				}
				//An end tag whose tag name is "noscript"
				elseif ($token === self::END_TAG && $tagname === "noscript") {
					//Pop the current node (which will be a noscript element)
					//from the stack of open elements; the new current node
					//will be a head element.
					$this->_popStack();
					//Switch the insertion mode to "in head".
					$this->_mode = self::IN_HEAD_MODE;
				}
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), U+0020 SPACE, A comment token, A start tag whose
				//tag name is one of: "basefont", "bgsound", "link", "meta",
				//"noframes", "style": Process the token using the rules for
				//the "in head" insertion mode.
				elseif (($token === self::CHAR && ($data === "\x09" ||
						$data === "\x0A" || $data === "\x0C" ||
						$data === "\x0D" || $data === "\x20")) ||
						$token === self::COMMENT ||
						($token === self::START_TAG &&
						($tagname === "basefont" || $tagname === "bgsound" ||
						$tagname === "link" || $tagname === "meta" ||
						$tagname === "noframes" || $tagname === "style"))) {
					$this->_processTokenWithRulesFor(
						$token,
						$data,
						self::IN_HEAD_MODE
					);
				}
				//A start tag whose tag name is one of: "head", "noscript",
				//Any other end tag: Parse error. Ignore the token.
				elseif (($token === self::END_TAG && $tagname !== "br") ||
						($token === self::START_TAG && ($tagname !== "head" ||
						$tagname !== "noscript"))) {
					return false;
				}
				//Anything else: Parse error. Act as if an end tag with the tag
				//name "noscript" had been seen and reprocess the current token.
				else {
					$this->_emitToken(self::END_TAG, array("tagname" => "noscript"));
					$this->_emitToken($token, $data);
				}
			break;

			//The "after head" insertion mode
			case self::AFTER_HEAD_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Insert the character into the
				//current node.
				if ($token === self::CHAR && ($data === "\x09" ||
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					$this->_insertText($data);
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token using
				//the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor(
						$token,
						$data,
						self::IN_BODY_MODE
					);
				}
				//A start tag whose tag name is "body"
				elseif ($token === self::START_TAG && $tagname === "body") {
					//Insert an HTML element for the token.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					//Set the frameset-ok flag to "not ok".
					$this->framesetOkFlag = "not ok";
					//Switch the insertion mode to "in body".
					$this->_mode = self::IN_BODY_MODE;
				}
				//A start tag whose tag name is "frameset"
				elseif ($token === self::START_TAG && $tagname === "frameset") {
					//Insert an HTML element for the token.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					//Switch the insertion mode to "in frameset".
					$this->_mode = self::IN_FRAMESET_MODE;
				}
				//A start tag token whose tag name is one of: "base",
				//"basefont", "bgsound", "link", "meta", "noframes", "script",
				//"style", "title"
				elseif ($token === self::START_TAG && ($tagname === "base" ||
						$tagname === "basefont" || $tagname === "bgsound" ||
						$tagname === "link" || $tagname === "meta" ||
						$tagname === "noframes" || $tagname === "script" ||
						$tagname === "style" || $tagname === "title")) {
					//Parse error.
					//Push the node pointed to by the head element pointer onto
					//the stack of open elements.
					$this->_stack[] = $this->_headPointer;
					$this->_setCurrentElement($this->_headPointer);
					//Process the token using the rules for the "in head"
					//insertion mode.
					$this->_processTokenWithRulesFor(
						$token,
						$data,
						self::IN_HEAD_MODE
					);
					//Remove the node pointed to by the head element pointer
					//from the stack of open elements.
					$this->_popStack($this->_headPointer);
				}
				//A start tag whose tag name is "head", Any other end tag:
				//Parse error. Ignore the token.
				elseif (($token === self::START_TAG && $tagname === "head") ||
						($token === self::END_TAG && ($tagname === "body" ||
						$tagname === "html" || $tagname === "br"))) {
					return false;
				}
				//Anything else: Act as if a start tag token with the tag name
				//"body" and no attributes had been seen, then set the
				//frameset-ok flag back to "ok", and then reprocess the current
				//token.
				else {
					$this->_emitToken(self::START_TAG, array("tagname" => "body"));
					$this->framesetOkFlag = "ok";
					$this->_emitToken($token, $data);
				}
			break;

			//The "in body" insertion mode
			case self::IN_BODY_MODE:
				//A character token
				if ($token === self::CHAR) {
					//Reconstruct the active formatting elements, if any.
					$this->_reconstructActiveFormattingElements();
					//Insert the token's character into the current node.
					$this->_insertText($data);
					//If the token is not one of U+0009 CHARACTER TABULATION,
					//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D
					//CARRIAGE RETURN (CR), U+0020 SPACE, or U+FFFD REPLACEMENT
					//CHARACTER, then set the frameset-ok flag to "not ok".
					if ($data !== "\x09" && $data !== "\x0A" &&
						$data !== "\x0C" && $data !== "\x0D" &&
						$data !== "\x20" &&
						$data !== $this->_unicodeReplacementCharacter) {
						$this->framesetOkFlag = "not ok";
					}
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//An end-of-file token
				elseif ($token === self::EOF) {
					//If there is a node in the stack of open elements that is
					//not either a dd element, a dt element, an li element, a p
					//element, a tbody element, a td element, a tfoot element,
					//a th element, a thead element, a tr element, the body
					//element, or the html element, then this is a parse error.
					//Stop parsing.
					return;
				} elseif ($token === self::START_TAG) {
					//A start tag whose tag name is "html": Parse error. For
					//each attribute on the token, check to see if the attribute
					//is already present on the top element of the stack of open
					//elements. If it is not, add the attribute and its
					//corresponding value to that element.
					if ($tagname === "html") {
						$this->_applyAttributesToElement($this->_stack[0], $data);
					}
					//A start tag token whose tag name is one of: "base",
					//"basefont", "bgsound", "command", "link", "meta",
					//"noframes", "script", "style", "title": Process the token
					//using the rules for the "in head" insertion mode.
					elseif ($tagname === "base" || $tagname === "basefont" ||
							$tagname === "bgsound" || $tagname === "command" ||
							$tagname === "link" || $tagname === "meta" ||
							$tagname === "noframes" || $tagname === "script" || 
							$tagname === "style" || $tagname === "title") {
						$this->_processTokenWithRulesFor(
							$token,
							$data,
							self::IN_HEAD_MODE
						);
					}
					//A start tag whose tag name is "body"
					elseif ($tagname === "body") {
						//Parse error.
						//If the second element on the stack of open elements
						//is not a body element, or, if the stack of open
						//elements has only one node on it,then ignore the
						//token. (fragment case)
						if (count($this->_stack) === 1 ||
							$this->_stack[1]->tagName !== "body") {
							return false;
						}
						//Otherwise, set the frameset-ok flag to "not ok";
						//then, for each attribute on the token, check to see
						//if the attribute is already present on the body
						//element (the second element) on the stack of open
						//elements, and if it is not, add the attribute
						//and its corresponding value to that element.
						$this->framesetOkFlag = "not ok";
						$this->_applyAttributesToElement($this->_stack[1], $data);
					}
					//A start tag whose tag name is "frameset"
					elseif ($tagname === "frameset") {
						//Parse error.
						//If the second element on the stack of open elements is
						//not a body element, or, if the stack of open elements
						//has only one node on it, then ignore the token.
						//(fragment case)
						if (count($this->_stack) === 1 ||
							$this->_stack[1]->tagName !== "body") {
							return false;
						}
						//If the frameset-ok flag is set to "not ok", ignore the
						//token.
						if ($this->framesetOkFlag === "not ok") {
							return false;
						}
						//Otherwise, run the following steps:
						//Remove the second element on the stack of open
						//elements from its parent node, if it has one.
						if ($this->_stack[1]->parentNode) {
							$this->_stack[1]->parentNode->removeChild($this->_stack[1]);
						}
						//Pop all the nodes from the bottom of the stack of open
						//elements, from the current node up to, but not
						//including, the root html element.
						while (count($this->_stack) > 1) {
							$this->_popStack();
						}
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						//Switch the insertion mode to "in frameset".
						$this->_mode = self::IN_FRAMESET_MODE;
					}
					//A start tag whose tag name is one of: "address", "article",
					//"aside", "blockquote", "center", "details", "dir",
					//"div", "dl", "fieldset", "figcaption", "figure",
					//"footer", "header", "hgroup", "menu", "nav", "ol", "p",
					//"section", "summary", "ul"
					elseif ($tagname === "address" || $tagname === "article" ||
							$tagname === "aside" || $tagname === "blockquote" ||
							$tagname === "center" || $tagname === "details" ||
							$tagname === "dir" || $tagname === "div" ||
							$tagname === "dl" || $tagname === "fieldset" || 
							$tagname === "figcaption" || $tagname === "figure" ||
							$tagname === "footer" || $tagname === "header" ||
							$tagname === "hgroup" || $tagname === "menu" ||
							$tagname === "nav" || $tagname === "ol" ||
							$tagname === "p" || $tagname === "section" ||
							$tagname === "summary" || $tagname === "ul") {
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p","button")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "p"));
						}
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
					}
					//A start tag whose tag name is one of: "h1", "h2", "h3",
					//"h4", "h5", "h6"
					elseif ($tagname === "h1" || $tagname === "h2" ||
							$tagname === "h3" || $tagname === "h4" ||
							$tagname === "h5" || $tagname === "h6") {
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p","button")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "p"));
						}
						//If the current node is an element whose tag name is
						//one of "h1", "h2", "h3", "h4", "h5", or "h6",
						//then this is a parse error; pop the current node off
						//the stack of open elements.
						$tag = $this->current->tagName;
						if ($tag === "h1" || $tag === "h2" || $tag === "h3" ||
							$tag === "h4" || $tag === "h5" || $tag === "h6") {
							$this->_popStack();
						}
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
					}
					//A start tag whose tag name is one of: "pre", "listing"
					elseif ($tagname === "pre" || $tagname === "listing") {
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p","button")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "p"));
						}
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						//If the next token is a U+000A LINE FEED (LF) character
						//token, then ignore that token and move on to the next
						//one. (Newlines at the start of pre blocks are ignored
						//as an authoring convenience.)
						$this->_ignoreLFToken = true;
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
					}
					//A start tag whose tag name is "form"
					elseif ($tagname === "form") {
						//If the form element pointer is not null, then this is 
						//a parse error; ignore the token.
						if ($this->_formPointer) {
							return false;
						}
						//Otherwise:
						else {
							//If the stack of open elements has a p element in
							//button scope, then act as if an end tag with
							//the tag name "p" had been seen.
							if ($this->_hasElementInScope("p","button")) {
								$this->_emitToken(
									self::END_TAG, 
									array("tagname" => "p")
								);
							}
							//Insert an HTML element for the token, and set the
							//form element pointer to point to the element
							//created.
							$this->_formPointer = $this->_insertElement(
								$data, 
								self::HTML_NAMESPACE
							);
						}
					}
					//A start tag whose tag name is "li": Run these steps:
					elseif ($tagname === "li") {
						//1. Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
						$count = count($this->_stack);
						for ($i = $count - 1; $i >= 0; $i--) {
							//2. Initialize node to be the current node
							//(the bottommost node of the stack).
							$node = $this->_stack[$i];
							//3. Loop: If node is an li element, then act as if
							//an end tag with the tag name "li" had been seen,
							//then jump to the last step.
							if ($node->tagName === "li") {
								$this->_emitToken(
									self::END_TAG,
									array("tagname" => $node->tagName)
								);
								break;
							}
							//4. If node is in the special category, but is not
							//an address, div, or p element, then jump to the
							//last step.
							if ($this->_isSpecialElement($node)) {
								break;
							}
							//5. Otherwise, set node to the previous entry in
							//the stack of open elements and return to the step
							//labeled loop.
						}
						//6. This is the last step.
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p","button")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "p"));
						}
						//Finally, insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
					}
					//A start tag whose tag name is one of: "dd", "dt": Run
					//these steps:
					elseif ($tagname === "dd" || $tagname === "dt") {
						//1. Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
						$count = count($this->_stack);
						for ($i = $count - 1; $i >= 0; $i--) {
							//2. Initialize node to be the current node (the
							//bottommost node of the stack).
							$node = $this->_stack[$i];
							//3. 3.	Loop: If node is a dd or dt element, then
							//act as if an end tag with the same tag name as node
							//had been seen, then jump to the last step.
							if ($node->tagName == "dt" || $node->tagName == "dd") {
								$this->_emitToken(
									self::END_TAG,
									array("tagname" => $node->tagName)
								);
								break;
							}
							//4.If node is in the special category, but is not
							//an address, div, or p element, then jump to the
							//last step.
							if ($node->tagName !== "address" &&
								$node->tagName !== "div" &&
								$node->tagName !== "p" && 
								$this->_isSpecialElement($node)) {
								break;
							}
							//5. Otherwise, set node to the previous entry in
							//the stack of open elements and return to the step
							//labeled loop.
						}
						//6. This is the last step.
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p","button")) {
							$this->_emitToken(
								self::END_TAG,
								array("tagname" => "p")
							);
						}
						//Finally, insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
					}
					//A start tag whose tag name is "plaintext"
					elseif ($tagname === "plaintext") {
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p","button")) {
							$this->_emitToken(
								self::END_TAG,
								array("tagname" => "p")
							);
						}
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						//Switch the tokenizer to the PLAINTEXT state.
						$this->state = self::PLAINTEXT_STATE;
					}
					//A start tag whose tag name is "button"
					elseif ($tagname === "button") {
						//If the stack of open elements has a button element in
						//scope, then this is a parse error; act as if an
						//end tag with the tag name "button" had been seen,
						//then reprocess the token.
						if ($this->_hasElementInScope("button")) {
							//buttons");
							$this->_emitToken(
								self::END_TAG,
								array("tagname" => "p")
							);
							$this->_emitToken($token, $data);
						}
						//Otherwise:
						else {
							//Reconstruct the active formatting elements, if
							//any.
							$this->_reconstructActiveFormattingElements();
							//Insert an HTML element for the token.
							$this->_insertElement($data, self::HTML_NAMESPACE);
							//Set the frameset-ok flag to "not ok".
							$this->framesetOkFlag = "not ok";
						}
					}
					//A start tag whose tag name is "a":
					elseif ($tagname === "a") {
						//If the list of active formatting elements contains an
						//element whose tag name is "a" between the end of the
						//list and the last marker on the list (or the start of
						//the list if there is no marker on the list), then
						//this is a parse error; act as if an end tag with the
						//tag name "a" had been seen, then remove that element
						//from the list of active formatting elements and the
						//stack of open elements if the end tag didn't already
						//remove it (it might not have if the element is not in
						//table scope).
						for ($i = count($this->_AFElements) - 1; $i >= 0; $i--) {
							if ($this->_AFElements[$i] === self::MARKER) {
								break;
							}
							if ($this->_AFElements[$i]["element"]->tagName === "a") {
								$this->_emitToken(
									self::END_TAG,
									array("tagname" => "a")
								);
								array_splice($this->_AFElements, $i, 1);
								$this->_popStack($this->_AFElements[$i]["element"]);
								break;
							}
						}
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token. Push
						//onto the list of active formatting elements that
						//element.
						$element = $this->_insertElement($data, self::HTML_NAMESPACE);
						$this->_pushOntoActiveFormattingElementsList($data, $element);
					}
					//A start tag whose tag name is one of: "b", "big", "code",
					//"em", "font", "i", "s", "small", "strike", "strong",
					//"tt", "u"
					elseif ($tagname === "b" || $tagname === "big" ||
							$tagname === "code" || $tagname === "em" ||
							$tagname === "font" || $tagname === "i" ||
							$tagname === "s" || $tagname === "small" ||
							$tagname === "strike" || $tagname === "strong" ||
							$tagname === "tt" || $tagname === "u") {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token. Push
						//onto the list of active formatting elements that
						//element.
						$element = $this->_insertElement($data, self::HTML_NAMESPACE);
						$this->_pushOntoActiveFormattingElementsList($data, $element);
					}
					//A start tag whose tag name is "nobr"
					elseif ($tagname === "nobr") {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//If the stack of open elements has a nobr element in
						//scope, then this is a parse error; act as if an end
						//tag with the tag name "nobr" had been seen, then once
						//again reconstruct the active formatting elements, if
						//any.
						if ($this->_hasElementInScope("nobr")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "nobr"));
							$this->_reconstructActiveFormattingElements();
						}
						//Insert an HTML element for the token. Push
						//onto the list of active formatting elements that
						//element.
						$element = $this->_insertElement($data, self::HTML_NAMESPACE);
						$this->_pushOntoActiveFormattingElementsList($data, $element);
					}
					//A start tag token whose tag name is one of: "applet",
					//"marquee", "object"
					elseif ($tagname === "applet" || $tagname === "marquee" ||
							$tagname === "object") {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						//Insert a marker at the end of the list of active
						//formatting elements.
						$this->_AFElements[] = self::MARKER;
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
					}
					//A start tag whose tag name is "table"
					elseif ($tagname === "table") {
						//If the Document is not set to quirks mode, and the
						//stack of open elements has a p element in button
						//scope, then act as if an end tag with the tag name
						//"p" had been seen.
						if ($this->quirksMode === self::QUIRKS_MODE &&
							$this->_hasElementInScope("p", "button")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "p"));
						}
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
						//Switch the insertion mode to "in table".
						$this->_mode = self::IN_TABLE_MODE;
					}
					//A start tag whose tag name is one of: "area", "br",
					//"embed", "img", "keygen", "wbr"
					elseif ($tagname === "area" || $tagname === "br" ||
							$tagname === "embed" || $tagname === "img" ||
							$tagname === "keygen" || $tagname === "wbr" ) {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token. Immediately
						//pop the current node off the stack of open elements.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						$this->_popStack();
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
					}
					//A start tag whose tag name is "input"
					elseif ($tagname === "input" ) {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token. Immediately
						//pop the current node off the stack of open elements.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						$this->_popStack();
						//If the token does not have an attribute with the name
						//"type", or if it does, but that attribute's value is
						//not an ASCII  case-insensitive match for the string
						//"hidden", then: set the frameset-ok  flag to "not ok".
						if (!isset($data["attributes"]["type"]) ||
							strtolower($data["attributes"]["type"]["value"]) !== "hidden") {
							$this->framesetOkFlag = "not ok";
						}
					}
					//A start tag whose tag name is one of: "param", "source",
					//"track"
					elseif ($tagname === "param" || $tagname === "source" ||
							$tagname === "track") {
						//Insert an HTML element for the token. Immediately
						//pop the current node off the stack of open elements.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						$this->_popStack();
					}
					//A start tag whose tag name is "hr"
					elseif ($tagname === "hr") {
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p", "button")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "p"));
						}
						//Insert an HTML element for the token. Immediately pop
						//the current node off the stack of open elements.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						$this->_popStack();
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
					}
					//A start tag whose tag name is "image": Parse error.
					//Change the token's tag name to "img" and reprocess it.
					elseif ($tagname === "image") {
						$tagname = "img";
						$this->_emitToken($token, $data);
					}
					//A start tag whose tag name is "isindex"
					elseif ($tagname === "isindex") {
						//If the form element pointer is not null, then ignore
						//the token.
						if ($this->_formPointer) {
							return false;
						}
						//Otherwise:
						else {
							//Act as if a start tag token with the tag name
							//"form" had been seen. If the token has an
							//attribute called "action", set the action
							//attribute on the resulting form element to the
							//value of the "action" attribute of the token.
							$datatoken = array("tagname" => "form");
							$prompt = null;
							$inputattr = array();
							if (isset($data["attributes"])) {
								if (isset($data["attributes"]["action"])) {
									$datatoken["attributes"] = array(
										"action" => array(
											"value" => $data["attributes"]["action"]
										)
									);
								}
								if (isset($data["attributes"]["prompt"])) {
									$prompt = $data["attributes"]["prompt"];
								}
								$inputattr = $data["attributes"];
								unset($inputattr["prompt"]);
								unset($inputattr["action"]);
							}
							$this->_emitToken(self::START_TAG, $datatoken);
							//Act as if a start tag token with the tag name
							//"hr" had been seen.
							$this->_emitToken(self::START_TAG, array("tagname" => "hr"));
							//Act as if a start tag token with the tag name
							//"label" had been seen.
							$this->_emitToken(self::START_TAG, array("tagname" => "label"));
							//Act as if a stream of character tokens had been
							//seen (see below for what they should say).
							$this->_emitToken(
								self::CHAR, $prompt !== null ?
								$prompt : 
								"This is a searchable index. " .
								"Enter search keywords: "
							);
							//Act as if a start tag token with the tag name
							//"input" had been seen, with all the attributes
							//from the "isindex" token except "name", "action",
							//and "prompt". Set the name attribute of the
							//resulting input element to the value "isindex".
							$inputattr[] = array("name" => array("value" => "isindex"));
							$this->_emitToken(
								self::START_TAG,
								array(
									"tagname" => "input", 
									"attributes" => $inputattr
								)
							);
							//Act as if a stream of character tokens had been
							//seen (see below for what they should say).
							$this->_emitToken(self::CHAR, "");
							//Act as if an end tag token with the tag name
							//"label" had been seen.
							$this->_emitToken(self::END_TAG, array("tagname" => "label"));
							//Act as if a start tag token with the tag name
							//"hr" had been seen.
							$this->_emitToken(self::END_TAG, array("tagname" => "hr"));
							//Act as if an end tag token with the tag name
							//"form" had been seen.
							$this->_emitToken(self::END_TAG, array("tagname" => "form"));
							//If the token has an attribute with the name
							//"prompt", then the first stream of characters
							//must be the same string as given in that
							//attribute, and the second stream of characters
							//must be empty. Otherwise, the two streams of
							//character tokens together should, together with
							//the input element, express the equivalent of
							//"This is a searchable index. Enter search
							//keywords: (input field)" in the user's preferred
							//language.
						}
					}
					//A start tag whose tag name is "textarea"
					elseif ($tagname === "textarea") {
						//Run these steps:
						//1. Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						//2. If the next token is a U+000A LINE FEED (LF)
						//character token, then ignore that token and move on
						//to the next one. (Newlines at the start of textarea
						//elements are ignored as an authoring convenience.)
						$this->_ignoreLFToken = true;
						//3. Switch the tokenizer to the RCDATA state.
						$this->state = self::RCDATA_STATE;
						//4. Let the original insertion mode be the current
						//insertion mode.
						$this->_originalInsertionMode = $this->_mode;
						//5. Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
						//6. Switch the insertion mode to "text".
						$this->_mode = self::TEXT_MODE;
					}
					//A start tag whose tag name is "xmp"
					elseif ($tagname === "xmp") {
						//If the stack of open elements has a p element in
						//button scope, then act as if an end tag with the tag
						//name "p" had been seen.
						if ($this->_hasElementInScope("p", "button")) {
							$this->_emitToken(self::END_TAG, array("tagname" => "p"));
						}
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
						//Follow the generic raw text element parsing algorithm.
						$this->_parseGenericRCDATAOrText($data);
					}
					//A start tag whose tag name is "iframe"
					elseif ($tagname === "iframe") {
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
						//Follow the generic raw text element parsing algorithm.
						$this->_parseGenericRCDATAOrText($data);
					}
					//A start tag whose tag name is "noembed", A start tag
					//whose tag name is "noscript", if the scripting flag is
					//enabled
					elseif ($tagname === "noembed" || ($tagname === "noscript" &&
							$this->scriptingFlag)) {
						//Follow the generic raw text element parsing algorithm.
						$this->_parseGenericRCDATAOrText($data);
					}
					//A start tag whose tag name is "select"
					elseif ($tagname === "select") {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
						//Set the frameset-ok flag to "not ok".
						$this->framesetOkFlag = "not ok";
						//If the insertion mode is one of in table", "in
						//caption", "in column group", "in table body",
						//"in row", or "in cell", then switch the insertion
						//mode to "in select in table". Otherwise, switch the
						//insertion mode to "in select".
						if ($this->_mode === self::IN_TABLE_MODE ||
							$this->_mode === self::IN_CAPTION_MODE ||
							$this->_mode === self::IN_COLUMN_GROUP_MODE ||
							$this->_mode === self::IN_TABLE_BODY_MODE ||
							$this->_mode === self::IN_ROW_MODE ||
							$this->_mode === self::IN_CELL_MODE) {
							$this->_mode = self::IN_SELECT_IN_TABLE_MODE;
						} else {
							$this->_mode = self::IN_SELECT_MODE;
						}
					}
					//A start tag whose tag name is one of: "optgroup", "option"
					elseif ($tagname === "optgroup" || $tagname === "option") {
						//If the current node is an option element, then act
						//as if an end tag with the tag name "option" had been
						//seen.
						if ($this->current->tagName === "option") {
							$this->_emitToken(
								self::END_TAG,
								array("tagname" => "option")
							);
						}
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
					}
					//A start tag whose tag name is one of: "rp", "rt"
					elseif ($tagname === "rp" || $tagname === "rt") {
						//If the stack of open elements has a ruby element in
						//scope, then generate implied end tags. If the
						//current node is not then a ruby element, this is a
						//parse error.
						if ($this->_hasElementInScope("ruby")) {
							$this->_generateImpliedEndTags();
						}
						//Insert an HTML element for the token.
						$this->_insertElement($data, self::HTML_NAMESPACE);
					}
					//A start tag whose tag name is "math"
					elseif ($tagname === "math") {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Adjust MathML attributes for the token. (This fixes
						//the case of MathML attributes that are not all
						//lowercase.)
						$data = $this->_adjustMathMLAttributes($data);
						//Adjust foreign attributes for the token. (This fixes
						//the use of namespaced attributes, in particular XLink.)
						$data = $this->_adjustForeignAttributes($data);
						//Insert a foreign element for the token, in the MathML
						//namespace.
						$this->_insertElement($data, self::MATHML_NAMESPACE);
						//If the token has its self-closing flag set, pop the
						//current node off the stack of open elements
						if (isset($data["self-closing"]) &&
							$data["self-closing"]) {
							$this->_popStack();
						}
					}
					//A start tag whose tag name is "svg"
					elseif ($tagname === "svg") {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Adjust SVG attributes for the token. (This fixes the
						//case of SVG attributes that are not all lowercase.)
						$data = $this->_adjustSVGAttributes($data);
						//Adjust foreign attributes for the token. (This fixes
						//the use of namespaced attributes, in particular XLink
						//in SVG.)
						$data = $this->_adjustForeignAttributes($data);
						//Insert a foreign element for the token, in the SVG
						//namespace.
						$this->_insertElement($data, self::SVG_NAMESPACE);
						//If the token has its self-closing flag set, pop the
						//current node off the stack of open elements
						if (isset($data["self-closing"]) &&
							$data["self-closing"]) {
							$this->_popStack();
						}
					}
					//A start tag whose tag name is one of: "caption", "col",
					//"colgroup", "frame", "head", "tbody", "td", "tfoot",
					//"th", "thead", "tr": Parse error. Ignore the token.
					elseif ($tagname === "caption" || $tagname === "col" ||
							$tagname === "colgroup" || $tagname === "frame" ||
							$tagname === "head" ||  $tagname === "tbody" ||
							$tagname === "td" || $tagname === "tfoot" ||
							$tagname === "th" || $tagname === "thead" ||
							$tagname === "tr") {
						//Parse error. Ignore the token.
						return false;
					}
					//Any other start tag
					else {
						//Reconstruct the active formatting elements, if any.
						$this->_reconstructActiveFormattingElements();
						//Insert an HTML element for the token.
						//This element will be a ordinary element.
						$this->_insertElement($data, self::HTML_NAMESPACE);
					}
				} elseif ($token === self::END_TAG) {
					//An end tag whose tag name is "body"
					if ($tagname === "body") {
						//If the stack of open elements does not have a body
						//element in scope, this is a parse error; ignore the
						//token.
						if (!$this->_hasElementInScope("body")) {
							return false;
						}
						//Otherwise, if there is a node in the stack of open
						//elements that is not either a dd element, a dt
						//element, an li element, an optgroup element, an
						//option element, a p element, an rp element, an rt
						//element, a tbody element, a td element, a tfoot
						//element, a th element, a thead element, a tr element,
						//the body element, or the html element, then this is a
						//parse error.
						//Switch the insertion mode to "after body".
						$this->_mode = self::AFTER_BODY_MODE;
					}
					//An end tag whose tag name is "html": Act as if an end tag
					//with tag name "body" had been seen, then, if that token
					//wasn't ignored, reprocess the current token.
					elseif ($tagname === "html") {
						$emit = $this->_emitToken(
							self::END_TAG, 
							array("tagname" => "body")
						);
						if ($emit !== false) {
							$this->_emitToken($token, $data);
						}
					}
					//An end tag whose tag name is one of: "address", "article",
					//"aside", "blockquote", "button", "center", "details",
					//"dir", "div", "dl", "fieldset", "figcaption", "figure",
					//"footer", "header", "hgroup", "listing", "menu", "nav",
					//"ol", "pre", "section", "summary", "ul"
					elseif ($tagname === "address" || $tagname === "article" ||
							$tagname === "aside" || $tagname === "blockquote" ||
							$tagname === "button" || $tagname === "center" ||
							$tagname === "details" || $tagname === "dir" ||
							$tagname === "div" || $tagname === "dl" ||
							$tagname === "fieldset" || $tagname === "figcaption" ||
							$tagname === "figure" || $tagname === "footer" ||
							$tagname === "header" || $tagname === "hgroup" ||
							$tagname === "listing" || $tagname === "menu" ||
							$tagname === "nav" || $tagname === "ol" ||
							$tagname === "pre" || $tagname === "section" ||
							$tagname === "summary" || $tagname === "ul") {
						//If the stack of open elements does not have an
						//element in scope with the same tag name as that of the
						//token, then this is a parse error; ignore the token.
						if (!$this->_hasElementInScope($tagname)) {
							return false;
						}
						//Otherwise, run these steps:
						else {
							//1. Generate implied end tags.
							$this->_generateImpliedEndTags();
							//2. If the current node is not an element with the
							//same tag name as that of the token, then this is a
							//parse error.
							//3. Pop elements from the stack of open elements
							//until an element with the same tag name as the
							//token has been popped from the stack.
							$this->_popStackUntil($tagname);
						}
					}
					//An end tag whose tag name is "form"
					elseif ($tagname === "form") {
						//If node is null or the stack of open elements does
						//not have node in scope, then this is a parse error;
						//ignore the token.
						if (!$this->_formPointer ||
							!$this->_elementInStack($this->_formPointer)) {
							return false;
						}
						//Otherwise, run these steps:
						else {
							//Let node be the element that the form element
							//pointer is set to.
							$node = $this->_formPointer;
							//Set the form element pointer to null.
							$this->_formPointer = null;
							//1. Generate implied end tags.
							$this->_generateImpliedEndTags();
							//2. If the current node is not node, then this is
							//a parse error.
							//3. Remove node from the stack of open elements.
							$this->_popStack($node);
						}
					}
					//An end tag whose tag name is "p"
					elseif ($tagname === "p") {
						//If the stack of open elements does not have an
						//element in button scope with the same tag name as
						//that of the token, then this is a parse error; act
						//as if a start tag with the tag name "p" had been seen,
						//then reprocess the current token.
						if (!$this->_hasElementInScope($tagname, "button")) {
							$this->_emitToken(self::START_TAG, array("tagname" => "p"));
							$this->_emitToken($token, $data);
						}
						//Otherwise, run these steps:
						else {
							//1. Generate implied end tags, except for elements
							//with the same tag name as the token.
							$this->_generateImpliedEndTags(array($tagname));
							//2. If the current node is not an element with the
							//same tag name as that of the token, then this is
							// aparse error.
							//3. Pop elements from the stack of open elements
							//until an element with the same tag name as the
							//token has been popped from the stack.
							$this->_popStackUntil($tagname);
						}
					}
					//An end tag whose tag name is "li"
					elseif ($tagname === "li") {
						//If the stack of open elements does not have an
						//element in list item scope with the same tag name as
						//that of the token, then this is a parse error; ignore
						//the token.
						if (!$this->_hasElementInScope($tagname, "list")) {
							return false;
						}
						//Otherwise, run these steps:
						else {
							//1. Generate implied end tags, except for elements
							//with the same tag name as the token.
							$this->_generateImpliedEndTags(array($tagname));
							//2. If the current node is not an element with the
							//same tag name as that of the token, then this is a
							//parse error.
							//3. Pop elements from the stack of open elements
							//until an element with the same tag name as the token
							//has been popped from the stack.
							$this->_popStackUntil($tagname);
						}
					}
					//An end tag whose tag name is one of: "dd", "dt"
					elseif ($tagname === "dd" || $tagname === "dt") {
						//If the stack of open elements does not have an
						//element in scope with the same tag name as that of the
						//token, then this is a parse error; ignore the token.
						if (!$this->_hasElementInScope($tagname)) {
							return false;
						}
						//Otherwise, run these steps:
						else {
							//1. Generate implied end tags, except for elements
							//with the same tag name as the token.
							$this->_generateImpliedEndTags(array($tagname));
							//2. If the current node is not an element with the
							//same tag name as that of the token, then this is a
							//parse error.
							//3. Pop elements from the stack of open elements
							//until an element with the same tag name as the token
							//has been popped from the stack.
							$this->_popStackUntil($tagname);
						}
					}
					//An end tag whose tag name is one of: "h1", "h2", "h3", "h4", "h5", "h6"
					elseif ($tagname === "h1" || $tagname === "h2" ||
							$tagname === "h3" || $tagname === "h4" ||
							$tagname === "h5" || $tagname === "h6") {
						$poptags = array("h1", "h2", "h3", "h4", "h5", "h6");
						//If the stack of open elements does not have an
						//element in scope whose tag name is one of "h1", "h2",
						//"h3", "h4", "h5", or "h6", then this is a parse error;
						//ignore the token.
						if (!$this->_hasElementInScope($poptags)) {
							return false;
						}
						//Otherwise, run these steps:
						else {
							//1. Generate implied end tags, except for elements
							//with the same tag name as the token.
							$this->_generateImpliedEndTags(array($tagname));
							//2. If the current node is not an element with the
							//same tag name as that of the token, then this is a
							//parse error.
							//3. Pop elements from the stack of open elements
							//until an element whose tag name is one of "h1", "h2",
							//"h3", "h4", "h5", or "h6" has been popped from
							//the stack.
							$this->_popStackUntil($poptags);
						}
					}
					//An end tag token whose tag name is one of: "applet",
					//"marquee", "object"
					elseif ($tagname === "applet" || $tagname === "marquee" ||
							$tagname === "object") {
						//If the stack of open elements does not have an
						//element in scope with the same tag name as that of
						//the token, then this is a parse error; ignore the token.
						if (!$this->_hasElementInScope($tagname)) {
							return false;
						}
						//Otherwise, run these steps:
						else {
							//1. Generate implied end tags.
							$this->_generateImpliedEndTags();
							//2. If the current node is not an element with the
							//same tag name as that of the token, then this is
							//a parse error.
							//4.Pop elements from the stack of open elements
							//until an element with the same tag name as the
							//token has been popped from the stack.
							$this->_popStackUntil($tagname);
							//4.Clear the list of active formatting elements up
							//to the last marker.
							$this->_clearActiveFormattingElementsList();
						}
					}
					//An end tag whose tag name is "br"
					elseif ($tagname === "br") {
						//Parse error. Act as if a start tag token with the tag
						//name "br" had been seen. Ignore the end tag token.
						$this->_emitToken(self::START_TAG, array("tagname" => "br"));
						return false;
					}
					//Any other end tag
					else {
						//An end tag whose tag name is one of: "a", "b", "big",
						//"code", "em", "font", "i", "nobr", "s", "small",
						//"strike", "strong", "tt", "u"
						$anythingElse = true;
						if ($tagname === "a" || $tagname === "b" ||
							$tagname === "big" || $tagname === "code" ||
							$tagname === "em" || $tagname === "font" || 
							$tagname === "i" || $tagname === "s" ||
							$tagname === "small" || $tagname === "strike" ||
							$tagname === "strong" || $tagname === "tt" ||
							$tagname === "u") {
							$anythingElse = false;
							//Run these steps:
							//Let outer loop counter be zero.
							//Outer loop: If outer loop counter is greater than or
							//equal to eight, then abort these steps.							
							for ($outer = 0; $outer < 8; $outer++) {
								//Increment outer loop counter by one.
								//Let the formatting element be the last element
								//in the list of active formatting elements that
								//is between the end of the list and the last
								//scope marker in the list, if any, or the start
								//of the list otherwise, and has the same tag name
								//as the token.
								$formattingElement = $foundPos = null;
								for ($i = count($this->_AFElements) - 1; $i >= 0; $i--) {
									if ($this->_AFElements[$i] === self::MARKER) {
										break;
									}
									if ($this->_AFElements[$i]["data"]["tagname"] === $tagname) {
										$formattingElement = $this->_AFElements[$i]["element"];
										//Let a bookmark note the position of
										//the formatting element in the list of
										//active formatting elements relative
										//to the elements on either side of it in
										//the list.
										$foundPos = $bookmark = $i;
										break;
									}
								}
								//If there is no such node, then abort these steps and instead
								//act as described in the "any other end tag" entry below.								
								$inStack =  $formattingElement ?
											$this->_elementInStack($formattingElement, $key) :
											false;
								if (!$formattingElement) {
									$anythingElse = false;
									break;
								}
								//Otherwise, if there is such a node, but that node
								//is not in the stack of open elements, then this
								//is a parse error; remove the element from the
								//list, and abort these steps.
								elseif ($formattingElement && !$inStack) {
									array_splice($this->_AFElements, $foundPos, 1);
									return false;
								}
								//Otherwise, if there is such a node, and that node is
								//also in the stack of open elements, but the element
								//is not in scope, then this is a parse error; ignore
								//the token, and abort these steps.
								elseif ($formattingElement && $inStack &&
										!$this->_hasElementInScope($formattingElement->tagName)) {
									return false;
								}
								//Otherwise, there is a formatting element and that
								//element is in the stack and is in scope. If the
								//element is not the current node,this is a parse
								//error. In any case, proceed with the algorithm as
								//written in the following steps.
								//Let the furthest block be the topmost node in
								//the stack of open elements that is lower in the
								//stack than the formatting element, and is an
								//element in the special category. There might not
								//be one.
								$furthestBlock = null;
								$Scount = count($this->_stack);
								for ($i = $key + 1; $i < $Scount; $i++) {
									if ($this->_isSpecialElement($this->_stack[$i])) {
										$fbkey = $i;
										$furthestBlock = $this->_stack[$i];
									}
								}
								//If there is no furthest block, then the UA
								//must skip the subsequent steps and instead just
								//pop all the nodes from the bottom of the stack of
								//open elements, from the current node up to and
								//including the formatting element, and remove the
								//formatting element from the list of active
								//formatting elements.
								if (!$furthestBlock) {
									array_splice($this->_AFElements, $foundPos, 1);
									for ($i = $Scount - 1; $i >= 0; $i--) {
										$this->_popStack($this->_stack[$i]);
										if ($i === $key) {
											break;
										}
									}
									break;
								}
								//Let the common ancestor be the element
								//immediately above the formatting element in the
								//stack of open elements.
								$commonAncestor = $this->_stack[$key - 1];
								//Let node and last node be the furthest block.
								//Follow these steps:
								//Let inner loop counter be zero.
								//Inner loop: If inner loop counter is greater than
								//or equal to three, then abort these steps.								
								$node = $lastNode = $furthestBlock;
								$nkey = $fbkey;
								for ($inner = 0; $inner < 3 && $nkey >= 0; $inner++) {
									$nkey--;
									//Increment inner loop counter by one.
									//Let node be the element immediately above
									//node in the stack of open elements, or if
									//node is no longer in the stack of open
									//elements (e.g. because it got removed by the
									//next step), the element that was immediately
									//above node in the stack of open elements
									//before node was removed.
									$node = $this->_stack[$nkey];
									//If node is not in the list of active
									//formatting elements, then remove node from 
									//the stack of open elements and then go back
									//to the step labeled inner loop.
									if (!$this->_isInActiveFormattingElements($node, $nafekey)) {
										$this->_popStack($node);
										continue;
									}
									//Otherwise, if node is the formatting element,
									//then go to the next step in the overall
									//algorithm.
									if ($node->isSameNode($formattingElement)) {
										break;
									}
									//Create an element for the token for which the
									//element node was created, replace the entry
									//for node in the list of active
									//formatting elements with an entry for the new
									//element, replace the entry for node in the
									//stack of open elements with an entry for the
									//new element, and let node be the new element.
									$node = $this->_insertElement(
										$this->_AFElements[$nafekey]["data"],
										$this->_AFElements[$nafekey]["element"]->namespaceURI,
										false
									);
									$this->_AFElements[$nafekey]["element"] = $node;
									$this->_stack[$nkey] = $node;
									//If last node is the furthest block, then move
									//the aforementioned bookmark to be immediately
									//after the new node in the list of active
									//formatting elements.
									if ($furthestBlock->isSameNode($lastNode)) {
										$bookmark = $nkey + 1;
									}
									//Insert last node into node, first removing it
									//from its previous parent node if any.
									$node->appendChild($lastNode);
									//Let last node be node.
									$lastNode = $node;
									//Return to the step labeled inner loop.
								}
								//If the common ancestor node is a table, tbody,
								//tfoot, thead, or tr element, then, foster parent
								//whatever last node ended up being in the previous
								//step, first removing it from its previous parent
								//node if any.
								$catn = $commonAncestor->tagName;
								if ($catn === "table" || $catn === "tbody" ||
									$catn === "tfoot" || $catn === "thead" ||
									$catn === "tr") {
									$this->_fosterParent($lastNode);
								}
								//Otherwise, append whatever last node ended up
								//being in the previous step to the common ancestor
								//node, first removing it from its previous parent
								//node if any.
								else {
									$commonAncestor->appendChild($lastNode);
								}
								//Create an element for the token for which the
								//formatting element was created.
								$usedToken = $this->_AFElements[$foundPos];
								$element = $this->_insertElement(
									$usedToken["data"], 
									$this->_AFElements[$foundPos]["element"]->namespaceURI,
									false
								);
								//Take all of the child nodes of the furthest
								//block and append them to the element created in
								//the last step.
								while ($furthestBlock->childNodes->length) {
									$element->appendChild($furthestBlock->childNodes[0]);
								}
								//Append that new element to the furthest block.
								$furthestBlock->appendChild($element);
								//11. Remove the formatting element from the list
								//of active formatting elements, and insert the new
								//element into the list of active formatting elements
								//at the position of the aforementioned bookmark.
								array_splice($this->_AFElements, $foundPos, 1);
								array_splice(
									$this->_AFElements,
									$bookmark, 0,
									array(
										array(
											"data" => $usedToken["data"],
											"element" => $element
										)
									)
								);
								//Remove the formatting element from the stack
								//of open elements, and insert the new element into
								//the stack of open elements
								//immediately below the position of the furthest
								//block in that stack.
								$this->_popStack($formattingElement);
								array_splice($this->_stack, $fbkey + 1, 0, array($element));
								//JJump back to the step labeled outer loop.
							}
						}
						if (!$anythingElse) {
							return;
						}
						//Run these steps:
						//1. Initialize node to be the current node (the
						//bottommost node of the stack).
						$node = $this->current;
						$nodeIndex = count($this->_stack) - 1;
						for (;;) {
							//2. Loop: If node has the same tag name as the
							//token, then:
							if ($node->tagName === $tagname) {
								//1. Generate implied end tags, except for
								//elements with the same tag name as the token.
								$this->_generateImpliedEndTags(array($tagname));
								//2. If the tag name of the end tag token does
								//not match the tag name of the current node,
								//this is a parse error.
								//3. Pop all the nodes from the current node up
								//to node, including node, then stop these
								//steps.
								for ($i = count($this->_stack) - 1; $i >= 0; $i--) {
									$break = $this->_stack[$i]->isSameNode($node);
									$this->_popStack();
									if ($break) {
										break;
									}
								}
								break;
							}
							//4. Otherwise, if node is in the special category,
							//then this is a parse error; ignore the token,
							//and abort these steps.
							if ($this->_isSpecialElement($node)) {
								return false;
							}
							//5. Set node to the previous entry in the stack of
							//open elements.
							$nodeIndex--;
							$node = $this->_stack[$nodeIndex];
							//6. Return to the step labeld loop.
						}
					}
				}
			break;

			//The "text" insertion mode
			case self::TEXT_MODE:
				//A character token: Insert the token's character into the
				//current node.
				if ($token === self::CHAR) {
					$this->_insertText($data);
				}
				//An end-of-file token
				elseif ($token === self::EOF) {
					//Parse error.
					//Pop the current node off the stack of open elements.
					$this->_popStack();
					//Switch the insertion mode to the original insertion mode
					//and reprocess the current token.
					$this->_mode = $this->_originalInsertionMode;
					$this->_emitToken($token, $data);
				}
				//An end tag whose tag name is "script"
				elseif ($token === self::END_TAG && $tagname === "script") {
					//Pop the current node off the stack of open elements.
					$this->_popStack();
					//Switch the insertion mode to the original insertion mode.
					$this->_mode = $this->_originalInsertionMode;
				}
				//Any other end tag
				elseif ($token === self::END_TAG) {
					//Pop the current node off the stack of open elements.
					$this->_popStack();
					//Switch the insertion mode to the original insertion mode.
					$this->_mode = $this->_originalInsertionMode;
				}
			break;

			//The "in table" insertion mode
			case self::IN_TABLE_MODE:
				//A character token
				if ($token === self::CHAR) {
					//Let the pending table character tokens be an empty list
					//of tokens.
					$this->_pendingTableCharacterTokens = array();
					//Let the original insertion mode be the current insertion
					//mode.
					$this->_originalInsertionMode = $this->_mode;
					//Switch the insertion mode to "in table text" and
					//reprocess the token.
					$this->_mode = self::IN_TABLE_TEXT_MODE;
					$this->_emitToken($token, $data);
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "caption"
				elseif ($token === self::START_TAG && $tagname === "caption") {
					//Clear the stack back to a table context. (See below.)
					$this->_clearStackBackToTable();
					//Insert a marker at the end of the list of active
					//formatting elements.
					$this->_AFElements[] = self::MARKER;
					//Insert an HTML element for the token, then switch the
					//insertion mode to "in caption".
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_mode = self::IN_CAPTION_MODE;
				}
				//A start tag whose tag name is "colgroup"
				elseif ($token === self::START_TAG && $tagname === "colgroup") {
					//Clear the stack back to a table context. (See below.)
					$this->_clearStackBackToTable();
					//Insert an HTML element for the token, then switch the
					//insertion mode to "in column group".
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_mode = self::IN_COLUMN_GROUP_MODE;
				}
				//A start tag whose tag name is "col": Act as if a start tag
				//token with the tag name "colgroup" had been seen, then
				//reprocess the current token.
				elseif ($token === self::START_TAG && $tagname === "col") {
					$this->_emitToken(self::START_TAG, array("tagname" => "colgroup"));
					$this->_emitToken($token, $data);
				}
				//A start tag whose tag name is one of: "tbody", "tfoot","thead"
				elseif ($token === self::START_TAG && ($tagname === "tbody" ||
						$tagname === "tfoot" || $tagname === "thead")) {
					//Clear the stack back to a table context. (See below.)
					$this->_clearStackBackToTable();
					//Insert an HTML element for the token, then switch the
					//insertion mode to "in table body".
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_mode = self::IN_TABLE_BODY_MODE;
				}
				//A start tag whose tag name is one of: "td", "th", "tr": Act
				//as if a start tag token with the tag name "tbody" had been
				//seen, then reprocess the current token.
				elseif ($token === self::START_TAG && ($tagname === "td" ||
						$tagname === "th" || $tagname === "tr")) {
					$this->_emitToken(self::START_TAG, array("tagname" => "tbody"));
					$this->_emitToken($token, $data);
				}
				//A start tag whose tag name is "table"
				elseif ($token === self::START_TAG && $tagname === "table") {
					//Parse error. Act as if an end tag token with the tag
					//name "table" had been seen, then, if that token wasn't
					//ignored, reprocess the current token.
					$emit = $this->_emitToken(self::END_TAG, array("tagname" => "table"));
					if ($emit !== false) {
						$this->_emitToken($token, $data);
					}
				}
				//An end tag whose tag name is "table"
				elseif ($token === self::END_TAG && $tagname === "table") {
					//If the stack of open elements does not have an element
					//in table scope with the same tag name as the token, this
					//is a parse error. Ignore the token. (fragment case)
					if (!$this->_hasElementInScope("table", "table")) {
						return false;
					}
					//Otherwise:
					else {
						//Pop elements from this stack until a table element
						//has been popped from the stack.
						$this->_popStackUntil("table");
						//Reset the insertion mode appropriately.
						$this->_resetInsertionMode();
					}
				}
				//An end tag whose tag name is one of: "body", "caption",
				//"col", "colgroup", "html", "tbody", "td", "tfoot", "th",
				//"thead", "tr": Parse error. Ignore the token.
				elseif ($token === self::END_TAG && ($tagname === "body" ||
						$tagname === "caption" || $tagname === "col" ||
						$tagname === "colgroup" || $tagname === "html" ||
						$tagname === "tbody" || $tagname === "td" ||
						$tagname === "tfoot" || $tagname === "th" ||
						$tagname === "thead" || $tagname === "tr")) {
					return false;
				}
				//A start tag whose tag name is one of: "style", "script":
				//Process the token using the rules for the "in head" insertion
				//mode.
				elseif ($token === self::START_TAG && ($tagname === "style" ||
						$tagname === "script")) {
					$this->_processTokenWithRulesFor($token, $data, self::IN_HEAD_MODE);
				}
				//A start tag whose tag name is "form"
				elseif ($token === self::START_TAG && $tagname === "form") {
					//Parse error.
					//If the form element pointer is not null, ignore the token.
					if ($this->_formPointer) {
						return false;
					}
					//Otherwise:
					else {
						//Insert an HTML element for the token, and set the
						//form element pointer to point to the element created.
						$this->_formPointer = $this->_insertElement($data, self::HTML_NAMESPACE);
						//Pop that form element off the stack of open elements.
						$this->_popStack();
					}
				}
				//An end-of-file token
				elseif ($token === self::EOF) {
					//If the current node is not the root html element, then
					//this is a parse error. It can only be the current node
					//in the fragment case.
					//Stop parsing.
					return;
				}
				//A start tag whose tag name is "input"
				//If the token does not have an attribute with the name
				//"type", or if it does, but that attribute's value is
				//not an ASCII case-insensitive match for the string
				//"hidden", then: act as described in the "anything
				//else" entry below.
				elseif ($token === self::START_TAG && $tagname === "input" &&
						isset($data["attributes"]["type"]) &&
						strtolower($data["attributes"]["type"]["value"]) === "hidden"
						) {
					//Otherwise: Parse error.
					//Insert an HTML element for the token.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					//Pop that input element off the stack of
					//open elements.
					$this->_popStack();
					break;
				}
				//Anything else
				else {
					//Parse error. Process the token using the rules for
					//the "in body" insertion mode, except that if the
					//current node is a table, tbody, tfoot, thead, or tr
					//element, then, whenever a node would be inserted into
					//the current node, it must instead be foster parented.
					if ($this->current->tagName === "table" ||
						$this->current->tagName === "tbody" ||
						$this->current->tagName === "tfoot" ||
						$this->current->tagName === "thead" ||
						$this->current->tagName === "tr") {
						$this->_forceFosterParent = true;
					}
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
			break;

			//The "in table text" insertion mode
			case self::IN_TABLE_TEXT_MODE:
				//A character token: Append the character token to the pending
				//table character tokens list.
				if ($token === self::CHAR) {
					$this->_pendingTableCharacterTokens[] = $data;
				}
				//Anything else
				else {
					if (count($this->_pendingTableCharacterTokens)) {
						//If any of the tokens in the pending table character
						//tokens list are character tokens that are not one of
						//U+0009 CHARACTER TABULATION, U+000A LINE FEED (LF),
						//U+000C FORM FEED (FF), U+000D CARRIAGE RETURN (CR),
						//or U+0020 SPACE, then reprocess those character
						//tokens using the rules given in the "anything else"
						//entry in the in table" insertion mode.
						$noWhitespace = false;
						foreach ($this->_pendingTableCharacterTokens as $char) {
							if ($char !== "\x09" && $char !== "\x0A" &&
								$char !== "\x0C" && $char !== "\x0D" &&
								$char !== "\x20") {
								$noWhitespace = true;
								break;
							}
						}
						foreach ($this->_pendingTableCharacterTokens as $char) {
							if ($noWhitespace) {
								if ($this->current->tagName === "table" ||
									$this->current->tagName === "tbody" ||
									$this->current->tagName === "tfoot" ||
									$this->current->tagName === "thead" ||
									$this->current->tagName === "tr") {
									$this->_forceFosterParent = true;
								}
								$this->_processTokenWithRulesFor(self::CHAR, $char, self::IN_BODY_MODE);
							}
							//Otherwise, insert the characters given by the
							//pending table character tokens list into the
							//current node.
							else {
								$this->_insertText($char);
							}
						}
					}
					//Switch the insertion mode to the original insertion mode
					//and reprocess the token.
					$this->_mode = $this->_originalInsertionMode;
					$this->_emitToken($token, $data);
				}
			break;

			//The "in caption" insertion mode
			case self::IN_CAPTION_MODE:
				//An end tag whose tag name is "caption"
				if ($token === self::END_TAG && $tagname === "caption") {
					//If the stack of open elements does not have an element
					//in table scope with the same tag name as the token, this
					//is a parse error.
					//Ignore the token. (fragment case)
					if (!$this->_hasElementInScope($tagname, "table")) {
						return false;
					}
					//Otherwise:
					else {
						//Generate implied end tags.
						$this->_generateImpliedEndTags();
						//Now, if the current node is not a caption element,
						//then this is a parse error.
						//Pop elements from this stack until a caption element
						//has been popped from the stack.
						$this->_popStackUntil("caption");
						//Clear the list of active formatting elements up to
						//the last marker.
						$this->_clearActiveFormattingElementsList();
						//Switch the insertion mode to "in table".
						$this->_mode = self::IN_TABLE_MODE;
					}
				}
				//A start tag whose tag name is one of: "caption", "col",
				//"colgroup", "tbody", "td", "tfoot", "th", "thead", "tr"
				//An end tag whose tag name is "table"
				elseif (($token === self::START_TAG && ($tagname === "caption" ||
						$tagname === "col" || $tagname === "colgroup" ||
						$tagname === "tbody" || $tagname === "td" ||
						$tagname === "tfoot" || $tagname === "th" ||
						$tagname === "thead" || $tagname === "tr")) ||
						($token === self::END_TAG && $tagname === "table")) {
					//Parse error. Act as if an end tag token with the tag name
					//"caption" had been seen, then, if that token wasn't
					//ignored, reprocess the current token.
					$emit = $this->_emitToken(self::END_TAG, array("tagname" => "caption"));
					if ($emit !== false) {
						$this->_emitToken($token, $data);
					}
				}
				//An end tag whose tag name is one of: "body", "col", "colgroup",
				//"html", "tbody", "td", "tfoot", "th", "thead", "tr": Parse
				//error. Ignore the token.
				elseif ($token === self::END_TAG && ($tagname === "body" ||
						$tagname === "col" || $tagname === "colgroup" ||
						$tagname === "html" || $tagname === "tbody" ||
						$tagname === "td" || $tagname === "tfoot" ||
						$tagname === "th" || $tagname === "thead" ||
						$tagname === "tr")) {
					return false;
				}
				//Anything else: Process the token using the rules for the "in
				//body" insertion mode.
				else {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
			break;

			//The "in column group" insertion mode
			case self::IN_COLUMN_GROUP_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Insert the character into the
				//current node.
				if ($token === self::CHAR && ($data === "\x09" ||
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					$this->inserText($data);
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token
				//using the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//A start tag whose tag name is "col"
				elseif ($token === self::START_TAG && $tagname === "col") {
					//Insert an HTML element for the token. Immediately pop the current
					//node off the stack of open elements.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_popStack();
				}
				//An end tag whose tag name is "colgroup"
				elseif ($token === self::END_TAG && $tagname === "colgroup") {
					//If the current node is the root html element, then this
					//is a parse error; ignore the token. (fragment case)
					if ($this->current->tagName === "html") {
						return false;
					}
					//Otherwise, pop the current node (which will be a colgroup
					//element) from the stack of open elements. Switch the
					//insertion mode to "in table".
					else {
						$this->_popStack();
						$this->_mode = self::IN_TABLE_MODE;
					}
				}
				//An end tag whose tag name is "col": Parse error. Ignore the
				//token.
				elseif ($token === self::END_TAG && $tagname === "col") {
					return false;
				}
				//Anything else
				else {
					//An end-of-file token
					//If the current node is the root html element, then stop
					//parsing. (fragment case)
					//Otherwise, act as described in the "anything else" entry
					//below.
					if ($token === self::EOF && $this->current->tagName === "html") {
						return;
					}
					//Parse error. Act as if an end tag token with the tag name
					//"table" had been seen, then, if that token wasn't ignored,
					//reprocess the current token.
					$emit = $this->_emitToken(self::END_TAG, array("tagname" => "colgroup"));
					if ($emit !== false) {
						$this->_emitToken($token, $data);
					}
				}
			break;

			//The "in table body" insertion mode
			case self::IN_TABLE_BODY_MODE:
				//A start tag whose tag name is "tr"
				if ($token === self::START_TAG && $tagname === "tr") {
					//Clear the stack back to a table body context. (See below.)
					$this->__clearStackBackToTableBody();
					//Insert an HTML element for the token, then switch the
					//insertion mode to "in row".
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_mode = self::IN_ROW_MODE;
				}
				//A start tag whose tag name is one of: "th", "td": Parse error.
				//Act as if a start tag with the tag name "tr" had been seen,
				//then reprocess the current token.
				elseif ($token === self::START_TAG && ($tagname === "th" ||
						$tagname === "td")) {
					$this->_emitToken(self::START_TAG, array("tagname" => "tr"));
					$this->_emitToken($token, $data);
				}
				//An end tag whose tag name is one of: "tbody", "tfoot", "thead"
				elseif ($token === self::END_TAG && ($tagname === "tbody" ||
						$tagname === "tfoot" || $tagname === "thead")) {
					//If the stack of open elements does not have an element
					//in table scope with the same tag name as the token, this
					//is a parse error. Ignore the token.
					if (!$this->_hasElementInScope(array($tagname, "table"))) {
						return false;
					}
					//Otherwise:
					else {
						//Clear the stack back to a table body context.
						$this->__clearStackBackToTableBody();
						//Pop the current node from the stack of open elements.
						//Switch the insertion mode to "in table".
						$this->_popStack();
						$this->_mode = self::IN_TABLE_MODE;
					}
				}
				//A start tag whose tag name is one of: "caption", "col",
				//"colgroup", "tbody", "tfoot", "thead", An end tag whose tag
				//name is "table"
				elseif (($token === self::START_TAG && ($tagname === "caption" ||
						$tagname === "col" || $tagname === "colgroup" ||
						$tagname === "tbody" || $tagname === "tfoot" ||
						$tagname === "thead")) || ($token === self::END_TAG &&
						$tagname === "table")) {
					//If the stack of open elements does not have a tbody,
					//thead, or tfoot element in table scope, this is a parse
					//error. Ignore the token. (fragment case)
					$accepted = array("tbody", "thead", "tfoot", "table");
					if (!$this->_hasElementInScope($accepted)) {
						return false;
					}
					//Otherwise:
					else {
						//Clear the stack back to a table body context.
						$this->__clearStackBackToTableBody();
						//Act as if an end tag with the same tag name as the
						//current node ("tbody", "tfoot", or "thead") had been
						//seen, then reprocess the current token.
						$this->_emitToken(
							self::END_TAG,
							array("tagname" => $this->current->tagName)
						);
						$this->_emitToken($token, $data);
					}
				}
				//An end tag whose tag name is one of: "body", "caption",
				//"col", "colgroup", "html", "td", "th", "tr": Parse error.
				//Ignore the token.
				elseif ($token === self::END_TAG && ($tagname === "body" ||
						$tagname === "caption" || $tagname === "col" ||
						$tagname === "colgroup" || $tagname === "html" ||
						$tagname === "td" || $tagname === "th" ||
						$tagname === "tr")) {
					return false;
				}
				//Anything else: Process the token using the rules for the "in table"
				//insertion mode.
				else {
					$this->_processTokenWithRulesFor($token, $data, self::IN_TABLE_MODE);
				}
			break;

			//The "in row" insertion mode
			case self::IN_ROW_MODE:
				//A start tag whose tag name is one of: "th", "td"
				if ($token === self::START_TAG && ($tagname === "td" ||
					$tagname === "th")) {
					//Clear the stack back to a table row context. (See below.)
					$this->__clearStackBackToTableRow();
					//Insert an HTML element for the token, then switch the
					//insertion mode to "in cell".
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_mode = self::IN_CELL_MODE;
					//Insert a marker at the end of the list of active
					//formatting elements.
					$this->_AFElements[] = self::MARKER;
				}
				//An end tag whose tag name is "tr"
				elseif ($token === self::END_TAG && $tagname === "tr") {
					//If the stack of open elements does not have an element
					//in table scope with the same tag name as the token, this
					//is a parse error. Ignore the token. (fragment case)
					if (!$this->_hasElementInScope($tagname, "table")) {
						return false;
					}
					//Otherwise:
					else {
						//Clear the stack back to a table row context.
						//(See below.)
						$this->__clearStackBackToTableRow();
						//Pop the current node (which will be a tr element)
						//from the stack of open elements. Switch the
						//insertion mode to "in table body".
						$this->_popStack();
						$this->_mode = self::IN_TABLE_BODY_MODE;
					}
				}
				//A start tag whose tag name is one of: "caption", "col",
				//"colgroup", "tbody", "tfoot", "thead", "tr"
				//An end tag whose tag name is "table"
				elseif (($token === self::START_TAG && ($tagname === "caption" ||
						$tagname === "col" || $tagname === "colgroup" ||
						$tagname === "tbody" || $tagname === "tfoot" ||
						$tagname === "thead" || $tagname === "tr")) ||
						($token === self::END_TAG && $tagname === "table")) {
					//Parse error. Act as if an end tag token with the tag name
					//"tr" had been seen, then, if that token wasn't ignored,
					//reprocess the current token.
					$emit = $this->_emitToken(self::END_TAG, array("tagname" => "tr"));
					if ($emit !== false) {
						$this->_emitToken($token, $data);
					}
				}
				//An end tag whose tag name is one of: "tbody", "tfoot", "thead"
				elseif ($token === self::END_TAG && ($tagname === "tbody" ||
						$tagname === "tfoot" || $tagname === "thead")) {
					//If the stack of open elements does not have an element
					//in table scope with the same tag name as the token, this
					//is a parse error. Ignore the token.
					if (!$this->_hasElementInScope($tagname, "table")) {
						return false;
					}
					//Otherwise, act as if an end tag with the tag name "tr"
					//had been seen, then reprocess the current token.
					else {
						$this->_emitToken(self::END_TAG, array("tagname" => "tr"));
						$this->_emitToken($token, $data);
					}
				}
				//An end tag whose tag name is one of: "body", "caption",
				//"col", "colgroup", "html", "td", "th": Parse error.
				//Ignore the token.
				elseif ($token === self::END_TAG && ($tagname === "body" ||
						$tagname === "caption" || $tagname === "col" ||
						$tagname === "colgroup" || $tagname === "html" ||
						$tagname === "td" || $tagname === "th")) {
					return false;
				}
				//Anything else: Process the token using the rules for the "in
				//table" insertion mode.
				else {
					$this->_processTokenWithRulesFor($token, $data, self::IN_TABLE_MODE);
				}
			break;

			//The "in cell" insertion mode
			case self::IN_CELL_MODE:
				//An end tag whose tag name is one of: "td", "th"
				if ($token === self::END_TAG && ($tagname === "td" ||
					$tagname === "th")) {
					//If the stack of open elements does not have an element
					//in table scope with the same tag name as that of the
					//token, then this is a parse error and the token must be
					//ignored.
					if (!$this->_hasElementInScope($tagname, "table")) {
						return false;
					}
					//Otherwise:
					else {
						//Generate implied end tags.
						$this->_generateImpliedEndTags();
						//Now, if the current node is not an element with the
						//same tag name as the token, then this is a parse
						//error.
						//Pop elements from the stack of open elements stack
						//until an element with the same tag name as the token
						//has been popped from the stack.
						$this->_popStackUntil($tagname);
						//Clear the list of active formatting elements up to
						//the last marker.
						$this->_clearActiveFormattingElementsList();
						//Switch the insertion mode to "in row".
						$this->_mode = self::IN_ROW_MODE;
					}
				}
				//A start tag whose tag name is one of: "caption", "col",
				//"colgroup", "tbody", "td", "tfoot", "th", "thead", "tr"
				elseif ($token === self::START_TAG && ($tagname === "caption" ||
						$tagname === "col" || $tagname === "colgroup" ||
						$tagname === "tbody" || $tagname === "td" ||
						$tagname === "tfoot" || $tagname === "th" ||
						$tagname === "thead" || $tagname === "tr")) {
					//If the stack of open elements does not have a td or th
					//element in table scope, then this is a parse error;
					//ignore the token. (fragment case)
					if (!$this->_hasElementInScope(array("td", "th"), "table")) {
						return false;
					}
					//Otherwise, close the cell (see below) and reprocess the
					//current token.
					else {
						$this->_closeCell();
						$this->_emitToken($token, $data);
					}
				}
				//An end tag whose tag name is one of: "body", "caption", "col",
				//"colgroup", "html": Parse error. Ignore the token.
				elseif ($token === self::END_TAG && ($tagname === "body" ||
						$tagname === "caption" || $tagname === "col" ||
						$tagname === "colgroup" || $tagname === "html")) {
					return false;
				}
				//An end tag whose tag name is one of: "table", "tbody",
				//"tfoot", "thead", "tr"
				elseif ($token === self::END_TAG && ($tagname === "table" ||
						$tagname === "tbody" || $tagname === "tfoot" ||
						$tagname === "thead" || $tagname === "tr")) {
					//If the stack of open elements does not have an element
					//in table scope with the same tag name as that of the
					//token (which can only happen for "tbody", "tfoot" and
					//"thead", or in the fragment case), then this is a parse
					//error and the token must be ignored.
					if (!$this->_hasElementInScope($tagname, "table")) {
						return false;
					}
					//Otherwise, close the cell (see below) and reprocess the
					//current token.
					else {
						$this->_closeCell();
						$this->_emitToken($token, $data);
					}
				}
				//Anything else: Process the token using the rules for the
				//"in body" insertion mode.
				else {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
			break;

			//The "in select" insertion mode
			case self::IN_SELECT_MODE:
				//A character token: Insert the token's character into the
				//current node.
				if ($token === self::CHAR) {
					$this->_insertText($data);
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token
				//using the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//A start tag whose tag name is "option"
				elseif ($token === self::START_TAG && $tagname === "option") {
					//If the current node is an option element, act as if an
					//end tag with the tag name "option" had been seen.
					if ($this->current->tagName === "option") {
						$this->_emitToken(self::END_TAG, array("tagname" => "option"));
					}
					//Insert an HTML element for the token.
					$this->_insertElement($data, self::HTML_NAMESPACE);
				}
				//A start tag whose tag name is "optgroup"
				elseif ($token === self::START_TAG && $tagname === "optgroup") {
					//If the current node is an option element, act as if an
					//end tag with the tag name "option" had been seen.
					if ($this->current->tagName === "option") {
						$this->_emitToken(self::END_TAG, array("tagname" => "option"));
					}
					//If the current node is an optgroup element, act as if an
					//end tag with the tag name "optgroup" had been seen.
					if ($this->current->tagName === "optgroup") {
						$this->_emitToken(self::END_TAG, array("tagname" => "optgroup"));
					}
					//Insert an HTML element for the token.
					$this->_insertElement($data, self::HTML_NAMESPACE);
				}
				//An end tag whose tag name is "optgroup"
				elseif ($token === self::END_TAG && $tagname === "optgroup") {
					//First, if the current node is an option element, and the
					//node immediately before it in the stack of open elements
					//is an optgroup element, then act as if an end tag with
					//the tag name "option" had been seen.
					$beforeLast = count($this->_stack) - 2;
					if ($this->current->tagName === "option" &&
						isset($this->_stack[$beforeLast]) &&
						$this->_stack[$beforeLast]->tagName === "optgroup") {
						$this->_emitToken(self::END_TAG, array("tagname" => "option"));
					}
					//If the current node is an optgroup element, then pop
					//that node from the stack of open elements. Otherwise,
					//this is a parse error; ignore the token.
					if ($this->current->tagName === "optgroup") {
						$this->_popStack();
					} else {
						return false;
					}
				}
				//An end tag whose tag name is "option"
				elseif ($token === self::END_TAG && $tagname === "option") {
					//If the current node is an option element, then pop that
					//node from the stack of open elements. Otherwise, this is
					//a parse error; ignore the token.
					if ($this->current->tagName === "option") {
						$this->_popStack();
					} else {
						return false;
					}
				}
				//An end tag whose tag name is "select"
				elseif ($token === self::END_TAG && $tagname === "select") {
					//If the stack of open elements does not have an element
					//in select scope with the same tag name as the token,
					//this is a parse error. Ignore the token. (fragment case)
					if (!$this->_hasElementInScope($tagname, "select")) {
						return false;
					}
					//Otherwise:
					else {
						//Pop elements from the stack of open elements until a
						//select element has been popped from the stack.
						$this->_popStackUntil("select");
						//Reset the insertion mode appropriately.
						$this->_resetInsertionMode();
					}
				}
				//A start tag whose tag name is "select": Parse error. Act as
				//if the token had been an end tag with the tag name "select"
				//instead.
				elseif ($token === self::START_TAG && $tagname === "select") {
					$this->_emitToken(self::END_TAG, array("tagname" => "select"));
				}
				//A start tag whose tag name is one of: "input", "keygen", "textarea"
				elseif ($token === self::START_TAG && ($tagname === "input" ||
						$tagname === "keygen" || $tagname === "textarea")) {
					//If the stack of open elements does not have a select
					//element in select scope, ignore the token. (fragment case)
					if (!$this->_hasElementInScope("select", "select")) {
						return false;
					}
					//Otherwise, act as if an end tag with the tag name
					//"select" had been seen, and reprocess the token.
					$this->_emitToken(self::END_TAG, array("tagname" => "select"));
					$this->_emitToken($token, $data);
				}
				//A start tag token whose tag name is "script": Process the
				//token using the rules for the "in head" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "script") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_HEAD_MODE);
				}
				//An end-of-file token
				elseif ($token === self::EOF) {
					//If the current node is not the root html element, then
					//this is a parse error. It can only be the current node
					//in the fragment case.
					//Stop parsing.
					return;
				}
				//Anything else: Parse error. Ignore the token.
				else {
					return false;
				}
			break;

			//The "in select in table" insertion mode
			case self::IN_SELECT_IN_TABLE_MODE:
				//A start tag whose tag name is one of: "caption", "table",
				//"tbody", "tfoot", "thead", "tr", "td", "th": Parse error.
				//Act as if an end tag with the tag name "select" had been
				//seen, and reprocess the token.
				if ($token === self::START_TAG && ($tagname === "caption" ||
					$tagname === "table" || $tagname === "tbody" ||
					$tagname === "tfoot" || $tagname === "thead" ||
					$tagname === "tr" || $tagname === "td" ||
					$tagname === "th")) {
					$this->_emitToken(self::END_TAG, array("tagname" => "select"));
					$this->_emitToken($token, $data);
				}
				//An end tag whose tag name is one of: "caption", "table",
				//"tbody", "tfoot", "thead", "tr", "td", "th"
				elseif ($token === self::END_TAG && ($tagname === "caption" ||
						$tagname === "table" || $tagname === "tbody" ||
						$tagname === "tfoot" || $tagname === "thead" ||
						$tagname === "tr" || $tagname === "td" ||
						$tagname === "th")) {
					//Parse error.
					//If the stack of open elements has an element in table
					//scope with the same tag name as that of the token, then
					//act as if an end tag with the tag name "select" had been
					//seen, and reprocess the token. Otherwise, ignore the
					//token.
					if ($this->_hasElementInScope($tagname, "table")) {
						$this->_emitToken(self::END_TAG, array("tagname" => "select"));
						$this->_emitToken($token, $data);
					} else {
						return false;
					}
				}
				//Anything else: Process the token using the rules for the "in
				//select" insertion mode.
				else {
					$this->_processTokenWithRulesFor($token, $data, self::IN_SELECT_MODE);
				}
			break;

			//The "after body" insertion mode
			case self::AFTER_BODY_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Process the token using the
				//rules for the "in body" insertion mode.
				if ($token === self::CHAR && ($data === "\x09" ||
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//A comment token: Append a Comment node to the first element
				//in the stack of open elements (the html element), with the
				//data attribute set to the data given in the comment token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data, $this->_stack[0]);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token
				//using the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//An end tag whose tag name is "html"
				elseif ($token === self::END_TAG && $tagname === "html") {
					//If the parser was originally created as part of the HTML
					//fragment parsing algorithm, this is a parse error; ignore
					//the token. (fragment case)
					if ($this->fragmentCase) {
						return false;
					}
					//Otherwise, switch the insertion mode to "after after body".
					else {
						$this->_mode = self::AFTER_AFTER_BODY_MODE;
					}
				}
				//An end-of-file token: Stop parsing.
				elseif ($token === self::EOF) {
					return;
				}
				//Anything else: Parse error. Switch the insertion mode to
				//"in body" and reprocess the token.
				else {
					$this->_mode = self::IN_BODY_MODE;
					$this->_emitToken($token, $data);
				}
			break;

			//The "in frameset" insertion mode
			case self::IN_FRAMESET_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Insert the character into the
				//current node.
				if ($token === self::CHAR && ($data === "\x09" ||
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					$this->_insertText($data);
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token
				//using the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//A start tag whose tag name is "frameset": Insert an HTML
				//element for the token.
				elseif ($token === self::START_TAG && $tagname === "frameset") {
					$this->_insertElement($data, self::HTML_NAMESPACE);
				}
				//An end tag whose tag name is "frameset"
				elseif ($token === self::END_TAG && $tagname === "frameset") {
					//If the current node is the root html element, then this
					//is a parse error; ignore the token. (fragment case)
					if ($this->current->tagName === "html") {
						return false;
					}
					//Otherwise, pop the current node from the stack of open
					//elements.
					$this->_popStack();
					//If the parser was not originally created as part of the
					//HTML fragment parsing algorithm (fragment case), and the
					//current node is no longer a frameset element, then switch
					//the insertion mode to "after frameset".
					if (!$this->fragmentCase && $this->current->tagName !== "frameset") {
						$this->_mode = self::AFTER_FRAMESET_MODE;
					}
				}
				//A start tag whose tag name is "frame"
				elseif ($token === self::START_TAG && $tagname === "frame") {
					//Insert an HTML element for the token. Immediately pop
					//the current node off the stack of open elements.
					$this->_insertElement($data, self::HTML_NAMESPACE);
					$this->_popStack();
				}
				//A start tag whose tag name is "noframes". Process the token
				//using the rules for the "in head" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "noframes") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_HEAD_MODE);
				}
				//An end-of-file token
				elseif ($token === self::EOF) {
					//If the current node is not the root html element, then
					//this is a parse error. It can only be the current node
					//in the fragment case.
					//Stop parsing.
					return;
				}
				//Anything else: Parse error. Ignore the token.
				else {
					return false;
				}
			break;

			//The "after frameset" insertion mode
			case self::AFTER_FRAMESET_MODE:
				//A character token that is one of U+0009 CHARACTER TABULATION,
				//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D CARRIAGE
				//RETURN (CR), or U+0020 SPACE: Insert the character into the
				//current node.
				if ($token === self::CHAR && ($data === "\x09" ||
					$data === "\x0A" || $data === "\x0C" || $data === "\x0D" ||
					$data === "\x20")) {
					$this->_insertText($data);
				}
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				elseif ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token: Parse error. Ignore the token.
				elseif ($token === self::DOCTYPE) {
					return false;
				}
				//A start tag whose tag name is "html": Process the token
				//using the rules for the "in body" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "html") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//An end tag whose tag name is "html": Switch the insertion
				//mode to "after after frameset".
				elseif ($token === self::END_TAG && $tagname === "html") {
					$this->_mode = self::AFTER_AFTER_FRAMESET_MODE;
				}
				//A start tag whose tag name is "noframes": Process the token
				//using the rules for the "in head" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "noframes") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_HEAD_MODE);
				}
				//An end-of-file token: Stop parsing.
				elseif ($token === self::EOF) {
					return;
				}
				//Anything else: Parse error. Ignore the token.
				else {
					return false;
				}
			break;

			//The "after after body" insertion mode
			case self::AFTER_AFTER_BODY_MODE:
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				if ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token, A character token that is one of U+0009
				//CHARACTER TABULATION, U+000A LINE FEED (LF), U+000C FORM
				//FEED (FF), U+000D CARRIAGE RETURN (CR), or U+0020 SPACE,
				//A start tag whose tag name is "html": Process the token
				//using the rules for the "in body" insertion mode.
				elseif ($token === self::DOCTYPE || ($token === self::CHAR &&
						($data === "\x09" || $data === "\x0A" ||
						$data === "\x0C" || $data === "\x0D" || $data === "\x20")) ||
						($token === self::START_TAG && $tagname === "html")) {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//An end-of-file token: Stop parsing.
				elseif ($token === self::EOF) {
					return;
				}
				//Anything else: Parse error. Switch the insertion mode to
				//"in body" and reprocess the token.
				else {
					$this->_mode = self::IN_BODY_MODE;
					$this->_emitToken($token, $data);
					return;
				}
			break;

			//The "after after frameset" insertion mode
			case self::AFTER_AFTER_FRAMESET_MODE:
				//A comment token: Append a Comment node to the current node
				//with the data attribute set to the data given in the comment
				//token.
				if ($token === self::COMMENT) {
					$this->_insertComment($data);
				}
				//A DOCTYPE token, A character token that is one of U+0009
				//CHARACTER TABULATION, U+000A LINE FEED (LF), U+000C FORM
				//FEED (FF), U+000D CARRIAGE RETURN (CR), or U+0020 SPACE,
				//A start tag whose tag name is "html": Process the token using
				//the rules for the "in body" insertion mode.
				elseif ($token === self::DOCTYPE || ($token === self::CHAR &&
						($data === "\x09" || $data === "\x0A" ||
						$data === "\x0C" || $data === "\x0D" ||
						$data === "\x20")) || ($token === self::START_TAG &&
						$tagname === "html")) {
					$this->_processTokenWithRulesFor($token, $data, self::IN_BODY_MODE);
				}
				//An end-of-file token: Stop parsing.
				elseif ($token === self::EOF) {
					return;
				}
				//A start tag whose tag name is "noframes": Process the token
				//using the rules for the "in head" insertion mode.
				elseif ($token === self::START_TAG && $tagname === "noframes") {
					$this->_processTokenWithRulesFor($token, $data, self::IN_HEAD_MODE);
				}
				//Anything else: Parse error. Ignore the token.
				else {
					return false;
				}
			break;
		}
	}

	/**
	 * Inserts a comment node into the current node
	 *
	 * @param	string		$text		Comment's text
	 * @param	HTMLElement	$parent		By default the comment is appended
	 *									to the current node but if an element
	 *									is passed in this parameter the
	 *									comment will be appended to it
	 * @return	void
	 * @access	protected
	 */
	protected function _insertComment ($text, $parent = null)
	{
		if (!$parent) {
			$parent = $this->current;
		}
		$comment = $this->document->createComment($text);
		$parent->appendChild($comment);
	}

	/**
	 * Creates a doctype object and associate it with the document
	 *
	 * @param	string	$name		Doctype name
	 * @param	string	$public		Doctype public attribute
	 * @param	system	$system		Doctype system attribute
	 * @return	void
	 * @access	protected
	 */
	protected function _insertDoctype ($name, $public, $system)
	{
		$doctype = new DocumentType($name, $public, $system);
		if (!$this->document->childNodes->length) {
			$this->document->appendChild($doctype);
		} else {
			$this->document->insertBefore($doctype, $this->document->childNodes[0]);
		}
	}

	/**
	 * Inserts an element into the current node
	 *
	 * @param	array		$data		Element's data
	 * @param	string		$namespace	Element's namespace
	 * @param	bool		$insert		If this parameter is false the
	 *									element will be created but not
	 *									appended
	 * @param	bool		$doc		If this parameter is true the
	 *									element will be added to the document
	 * @return	HTMLElement	The created element
	 * @access	protected
	 */
	protected function _insertElement ($data, $namespace, $insert = true, $doc = false)
	{
		$element = $this->document->createElementNS($namespace, $data["tagname"]);
		//If the tag name is "base" and it has the "href" attribute and the "href"
		//attribute is absolute, then store the attribute value as the document
		//base path
		if ($data["tagname"] === "base" && isset($data["attributes"]["href"])) {
			$base = $data["attributes"]["href"]["value"];
			if (PAHDIPath::isAbsolute($base)) {
				$this->base = $base;
			}
		}
		//If an element created by the insert an HTML element algorithm is
		//a form-associated element, and the form element pointer is not null,
		//and the newly created element doesn't have a form attribute, the user
		//agent must associate the newly created element with the form element
		//pointed to by the form element pointer before inserting it wherever
		//it is to be inserted.
		$this->_applyAttributesToElement($element, $data);
		if ($insert) {
			/*Foster parent elements inside tables, tbody, thead, tfoot and tr*/
			if ($this->_forceFosterParent) {
				 $this->_forceFosterParent = false;
				 $this->_fosterParent($element);
			} elseif ($doc) {
				$this->document->appendChild($element);
			} else {
				$this->current->appendChild($element);
			}
			$this->_setCurrentElement($element);
			$this->_stack[] = $element;
		}
		return $element;
	}

	/**
	 * Inserts a text node into the current node
	 *
	 * @param	string		$text	Text node's text
	 * @return	void
	 * @access	protected
	 */
	protected function _insertText ($text)
	{
		if ($this->_ignoreLFToken) {
			$this->_ignoreLFToken = false;
			/*If the line feed character must be ignored and the text is a line
			feed character ignore it*/
			if ($text === "\n") {
				return;
			}
			/*Make sure that the text does not contain a line feed at the begin
			(if it is composed by more than one character)*/
			else {
				$text = preg_replace("#^\n#","",$text);
			}
		}
		//When the steps below require the UA to insert a character into a node,
		//if that node has a child immediately before where the character is to
		//be inserted, and that child is a Text node, then the character must
		//be appended to that Text node; otherwise, a new Text node whose data
		//is just that character must be inserted in the appropriate place.
		$count = $this->current->childNodes->length;
		if ($count && $this->current->childNodes[$count - 1]->nodeType === 3) {
			$this->current->childNodes[$count - 1]->appendData($text);
		} else {
			$node = $this->document->createTextNode($text);
			$this->current->appendChild($node);
		}
	}

	/**
	 * Processes a token with the rules of the given insertion mode
	 *
	 * @param	int		$token		Token to process
	 * @param	mixed	$data		Token's data
	 * @param	int		$mode		Insertion mode to use
	 * @return	void
	 * @access	protected
	 */
	protected function _processTokenWithRulesFor ($token, $data, $mode)
	{
		//When the algorithm says that the user agent is to do something
		//"using the rules for the m insertion mode", where m is one of
		//these modes, the user agent must use the rules described under
		//the m insertion mode's section, but must leave the insertion mode
		//unchanged unless the rules in m themselves switch the insertion
		//mode to a new value.
		$this->_emitToken($token, $data, $mode);
	}

	/**
	 * Pops (or find the given element and remove it from) the stack array
	 *
	 * @param	HTMLElement	$element	Element to remove. By default is the
	 *									current one
	 * @return	void
	 * @access	protected
	 */
	protected function _popStack ($element = null)
	{
		$count = count($this->_stack);
		if (!$count) {
			return;
		}
		if (!$element) {
			array_pop($this->_stack);
			$count--;
		} else {
			for ($i = $count - 1; $i >= 0; $i--) {
				if ($this->_stack[$i]->isSameNode($element)) {
					array_splice($this->_stack, $i, 1);
					$count--;
					break;
				}
			}
		}
		if ($count) {
			$this->_setCurrentElement($this->_stack[$count - 1]);
		} else {
			$this->current = null;
		}
	}
	
	/**
	 * Pops the stack until an element with the given tag name has been found
	 *
	 * @param	array|string	$tag		Tag name or array of tag names to search
	 * @param	bool			$popFound	If it's false the element that will be
	 *										found won't be popped out of the stack
	 * @return	void
	 * @access	protected
	 */
	protected function _popStackUntil ($tag, $popFound = true)
	{
		if (is_string($tag)) {
			$tag = array($tag);
		}
		for ($i = count($this->_stack) - 1; $i >= 0; $i--) {
			if (in_array($this->current->tagName, $tag)) {
				if ($popFound) {
					$i = - 1;
				} else {
					break;
				}
			}
			$this->_popStack();
		}
	}

	/**
	 * Checks if the given element is in the stack of open elements
	 *
	 * @param	HTMLElement	$element	Element to search
	 * @param	mixed		&$key		If this variabile is given it will be
	 *									filled with the key at which the element
	 *									is located in the stack
	 * @return	bool		True if the element is in the stack of open elements
	 *						otherwise false
	 * @access	protected
	 */
	protected function _elementInStack ($element, & $key = null)
	{
		for ($i = count($this->_stack) - 1; $i >= 0; $i--) {
			if ($this->_stack[$i]->isSameNode($element)) {
				$key = $i;
				return true;
			}
		}
		return false;
	}

	/**
	 * Applies attributes from a token's data to an element
	 *
	 * @param	HTMLElement	$element	Element
	 * @param	array		$data		Data array
	 * @return	void
	 * @access	protected
	 */
	protected function _applyAttributesToElement ($element, $data)
	{
		if (!isset($data["attributes"]) || !count($data["attributes"])) {
			return false;
		}
		foreach ($data["attributes"] as $name => $attr) {
			/*Handle namespaced attributes in a different way*/
			if (isset($attr["namespace"])) {
				$ns = $attr["namespace"];
				if ($element->hasAttributeNS($ns, $name)) {
					continue;
				}
				$newattr = $this->document->createAttributeNS($ns, $name);
				$newattr->value = $attr["value"];
				if (isset($attr["prefix"])) {
					$newattr->prefix = $attr["prefix"];
				}
				$element->setAttributeNodeNS($newattr);
			} elseif (!$element->hasAttribute($name)) {
				$element->setAttribute($name, $attr["value"]);
			}
		}
	}

	/**
	 * Sets the given element as the current one
	 *
	 * @param	HTMLElement	$element	Element that become the current
	 * @return	void
	 * @access	protected
	 */
	function _setCurrentElement ($element)
	{
		$this->current = $element;
	}

	/**
	 * Checks if an element with the given tag name is present in the
	 * stack of open elements
	 *
	 * @param	array	$check		Tag names to search
	 * @param	bool	$reverse	If this parameter is true the search
	 *								condition is reversed and the function
	 *								will return true only if it finds one
	 *								element that has a tag name that is not
	 *								in the given array
	 * @return	bool	Test result
	 * @access	protected
	 */
	protected function _isTagNameInStack ($check, $reverse = false)
	{
		if (is_string($check)) {
			$check = array($check);
		}
		if (count($this->_stack)) {
			foreach ($this->_stack as $element) {
				$res = in_array($element->tagName, $check);
				/* If the element is not in the given tag names array and the
				search condition is reversed or the search condition is not
				reversed and the element's tag name is one of those in the tag
				names array return true*/
				if ((!$res && $reverse) || ($res && !$reverse)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Clears the stack back to a table row context
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function __clearStackBackToTableRow ()
	{
		//When the steps above require the UA to clear the stack back to a 
		//table row context, it means that the UA must, while the current node
		//is not a tr element or an html element, pop elements from the stack
		//of open elements.
		$this->_popStackUntil(array("tr", "html"), false);
		//The current node being an html element after this process is a
		//fragment case.
	}

	/**
	 * Clears the stack back to a table body context
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function __clearStackBackToTableBody ()
	{
		//When the steps above require the UA to clear the stack back to a
		//table body context, it means that the UA must, while the current
		//node is not a tbody, tfoot, thead, or html element, pop elements
		//from the stack of open elements.
		$this->_popStackUntil(array("tbody", "tfoot", "thead", "html"), false);
		//The current node being an html element after this process is a
		//fragment case.
	}

	/**
	 * Clears the stack back to a table context
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function _clearStackBackToTable ()
	{
		//When the steps above require the UA to clear the stack back to a
		//table context, it means that the UA must, while the current node is
		//not a table element or an html element, pop elements from the stack
		//of open elements.
		$this->_popStackUntil(array("table", "html"), false);
	}

	/**
	 * Generates implied end tags
	 *
	 * @param	array	$exclude	Element's to exclude
	 * @return	void
	 * @access	protected
	 */
	protected function _generateImpliedEndTags ($exclude = array())
	{
		$list = array("dd", "dt", "li", "option", "optgroup", "p", "rp", "rt");
		//When the steps below require the UA to generate implied end tags, 
		//then, while the current node is a dd element, a dt element, an li
		//element, an option element, an optgroup element, a p element, an rp
		//element, or an rt element, the UA must pop the current node off the
		//stack of open elements. If a step requires the UA to generate
		//implied end tags but lists an element to exclude from the process,
		//then the UA must perform the above steps as if that element was not
		//in the above list.
		for ($i = count($this->_stack) - 1; $i >= 0; $i--) {
			$tag = $this->_stack[$i]->tagName;
			if (in_array($tag, $exclude) || !in_array($tag, $list)) {
				return;
			}
			$this->_popStack();
		}
	}

	/**
	 * Adjusts SVG attributes
	 *
	 * @param	array	$data	Tokens data
	 * @return	void
	 * @access	protected
	 */
	protected function _adjustSVGAttributes ($data)
	{
		if (isset($data["attributes"])) {
			$newatt = array();
			foreach ($data["attributes"] as $name => $att) {
				if (isset(ParserHTML::$SVGAttributes[$name])) {
					$name = ParserHTML::$SVGAttributes[$name];
				}
				$newatt[$name] = $att;
			}
			$data["attributes"] = $newatt;
		}
		return $data;
	}

	/**
	 * Adjusts MathML attributes
	 *
	 * @param	array	$data	Tokens data
	 * @return	void
	 * @access	protected
	 */
	protected function _adjustMathMLAttributes ($data)
	{
		if (isset($data["attributes"])) {
			$newatt = array();
			foreach ($data["attributes"] as $name => $att) {
				if (isset(ParserHTML::$MATHMLAttributes[$name])) {
					$name = ParserHTML::$MATHMLAttributes[$name];
				}
				$newatt[$name] = $att;
			}
			$data["attributes"] = $newatt;
		}
		return $data;
	}

	/**
	 * Adjusts foreign attributes
	 *
	 * @param	array	$data	Tokens data
	 * @return	void
	 * @access	protected
	 */
	protected function _adjustForeignAttributes ($data)
	{
		$xlinkAtt = array(
				"xlink:actuate" => 1,
				"xlink:arcrole" => 1,
				"xlink:href" => 1,
				"xlink:role" => 1,
				"xlink:show" => 1,
				"xlink:title" => 1,
				"xlink:type" => 1
			);
		$xmlAtt = array("xml:base" => 1, "xml:lang" => 1, "xml:space" => 1);
		if (isset($data["attributes"])) {
			$newatt = array();
			foreach ($data["attributes"] as $name => $att) {
				if ($name === "xmlns") {
					$att["namespace"] = self::XMLNS_NAMESPACE;
				} elseif ($name === "xmlns:xlink") {
					$name = "xlink";
					$att["prefix"] = "xmlns";
					$att["namespace"] = self::XMLNS_NAMESPACE;
				} elseif (isset($xmlAtt[$name])) {
					list($prefix, $aname) = explode(":", $name);
					$name = $aname;
					$att["prefix"] = "xml";
					$att["namespace"] = self::XML_NAMESPACE;
				} elseif (isset($xlinkAtt[$name])) {
					list($prefix, $aname) = explode(":", $name);
					$name = $aname;
					$att["prefix"] = "xlink";
					$att["namespace"] = self::XLINK_NAMESPACE;
				}
				$newatt[$name] = $att;
			}
			$data["attributes"] = $newatt;
		}
		return $data;
	}

	/**
	 * Checks if an element with one of the given tag names is in scope
	 *
	 * @param	array	$tag		Tag names to search
	 * @param	string	$scope		Optional scope to search into. One of
	 *								"list", "button", "table".
	 * @return	bool	Test result
	 * @access	protected
	 */
	function _hasElementInScope ($tag, $scope = null)
	{
		$svg = $math = false;
		if (!$scope || $scope === "list" || $scope === "button") {
			$svg = array("foreignObject", "desc", "title");
			$math = array("annotation-xml", "mi", "mo", "mn", "ms", "mtext");
			$list = array(	"applet", "caption", "html", "table", "td", "th",
							"marquee", "object");
			if ($scope === "list") {
				$list[] = "ol";
				$list[] = "ul";
			} elseif ($scope === "button") {
				$list[] = "button";
			}
		} elseif ($scope === "table") {
			$list = array("html", "table");
		} else {
			$list = array();
		}
		if (is_string($tag)) {
			$tag = array($tag);
		}
		//1. Initialize node to be the current node (the bottommost node of
		//the stack).
		$index = count($this->_stack) - 1;
		for (;;) {
			$node = $this->_stack[$index];
			//2. If node is the target node, terminate in a match state.
			if (in_array($node->tagName, $tag)) {
				return true;
			}
			//3. Otherwise, if node is one of the element types in list,
			//terminate in a failure state.
			elseif ($svg && $node->namespaceURI === self::SVG_NAMESPACE &&
					in_array($node->tagName, $svg)) {
				return false;
			} elseif ($math && $node->namespaceURI === self::MATHML_NAMESPACE &&
					in_array($node->tagName, $math)) {
				return false;
			} elseif ($node->namespaceURI === self::HTML_NAMESPACE &&
					(($scope === "select" && $node->tagName !== "option" &&
					$node->tagName !== "optgroup") ||
					in_array($node->tagName, $list))) {
				return false;
			}
			//4. Otherwise, set node to the previous entry in the stack of
			//open elements and return to step 2. (This will never fail, since
			//the loop will always terminate in the previous step if the top
			//of the stack — an html  element — is reached.)
			$index--;
		}
	}

	/**
	 * Checks if an element with a namespace different from the html namespace
	 * is in scope
	 *
	 * @return	bool	Test result
	 * @access	protected
	 */
	protected function _hasElementInScopeNoHTML ()
	{
		for ($i = count($this->_stack) - 1; $i >= 0; $i--) {
			if ($this->_stack[$i]->namespaceURI !== self::HTML_NAMESPACE) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Closes an open cell
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function _closeCell ()
	{
		//Where the steps above say to close the cell, they mean to run the
		//following algorithm:
		//1. If the stack of open elements has a td element in table scope,
		//then act as if an end tag token with the tag name "td" had been seen.
		if ($this->_hasElementInScope("td", "table")) {
			$this->_emitToken(self::END_TAG, array("tagname" => "td"));
		}
		//2. Otherwise, the stack of open elements will have a th element in
		//table scope; act as if an end tag token with the tag name "th" had
		//been seen.
		else {
			$this->_emitToken(self::END_TAG, array("tagname" => "th"));
		}
		//The stack of open elements cannot have both a td and a th element in
		//table scope at the same time, nor can it have neither when the close
		//the cell algorithm is invoked.
	}

	/**
	 * Applies the forster parenting (it happens when content is misnested in
	 * tables. )
	 *
	 * @param	HTMLElement	$node		Element to insert
	 * @return	void
	 * @access	protected
	 */
	protected function _fosterParent ($node)
	{
		//Foster parenting happens when content is misnested in tables.
		//When a node node is to be foster parented, the node node must be
		//inserted into the foster parent element. The foster parent element
		//is the parent element of the last table element in the stack of open
		//elements, if there is a table element and it has such a parent
		//element.
		$fosterParent = 1;
		$fp = $table = null;
		for ($i = count($this->_stack) - 1; $i >= 0; $i--) {
			if ($this->_stack[$i]->tagName === "table") {
				$table = $this->_stack[$i];
				$fosterParent = $this->_stack[$i]->parentNode;
				$fp = $i;
				break;
			}
		}
		//If there is no table element in the stack of open elements
		//(fragment case), then the foster parent element is the first element
		//in the stack of open elements (the html element). Otherwise, if there
		//is a table element in the stack of open elements, but the last table
		//element in the stack of open elements has no parent, or its parent
		//node is not an element, then the foster parent element is the element
		//before the last table element in the stack of open elements. If the
		//foster parent element is the parent element of the last table element
		//in the stack of open elements, then node must be inserted into the
		//foster parent element, immediately before the last table element in
		//the stack of open elements; otherwise, node must be appended to the
		//foster parent element.
		if ($fosterParent === 1) {
			$this->_stack[0]->appendChild($node);
		} elseif (!$fosterParent || $fosterParent->nodeType !== 1) {
			$this->_stack[$fp - 1]->appendChild($node);
		} else {
			$fosterParent->insertBefore($node, $table);
		}
	}

	/**
	 * Checks if the given element is in the active formatting elements list
	 *
	 * @param	HTMLElement	$element		Element to search
	 * @param	mixed		&$key			If this variabile is given it will be
	 *										filled with the key at which the element
	 *										is located in the list
	 * @return	bool		True if the element is present otherwise false
	 * @access	protected
	 */
	protected function _isInActiveFormattingElements ($element, & $key = null)
	{
		for ($i = count($this->_AFElements) - 1; $i >= 0; $i--) {
			if ($this->_AFElements[$i]["element"]->isSameNode($element)) {
				$key = $i;
				return true;
			}
		}
		return false;
	}

	/**
	 * Reconstructs the active formatting elements, if any
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function _reconstructActiveFormattingElements ()
	{
		$count = count($this->_AFElements);
		//1. If there are no entries in the list of active formatting elements,
		//then there is nothing to reconstruct; stop this algorithm.
		if (!$count) {
			return;
		}
		//3. Let entry be the last (most recently added) element in the list of
		//active formatting elements.
		$entry = $this->_AFElements[$count - 1];
		//2. If the last (most recently added) entry in the list of active
		//formatting elements is a marker, or if it is an element that is in
		//the stack of open elements, then there is nothing to reconstruct;
		//stop this algorithm.
		if ($entry === self::MARKER || $this->_elementInStack($entry["element"])) {
			return;
		}
		$execStep = true;
		$index = $count - 1;
		while (true) {
			//4. If there are no entries before entry in the list of active
			//formatting elements, then jump to step 8.
			if ($index === 0) {
				$execStep = false;
				break;
			}
			//5. Let entry be the entry one earlier than entry in the list of
			//active formatting elements.
			$entry = $this->_AFElements[--$index];
			//6. If entry is neither a marker nor an element that is also in the
			//stack of open elements, go to step 4.
			if ($entry === self::MARKER || $this->_elementInStack($entry["element"])) {
				break;
			}
		}
		for (;;) {
			//7. Let entry be the element one later than entry in the list of
			//active formatting elements.
			if ($execStep) {
				$entry = $this->_AFElements[++$index];
			} else {
				$execStep = true;
			}
			//8. Create an element for the token for which the element entry
			//was created, to obtain new element.
			//9. Append new element to the current node and push it onto the
			//stack of open elements so that it is the new current node.
			$ns = $entry["element"]->namespaceURI;
			$element = $this->_insertElement($entry["data"], $ns);
			//10. Replace the entry for entry in the list with an entry for
			//new element.
			array_splice(
				$this->_AFElements,
				$index,
				1,
				array(
					array(
						"data" => $entry["data"],
						"element" => $element
					)
				)
			);
			//11. If the entry for new element in the list of active
			//formatting elements is not the last entry in the list, return to step 7.
			if ($index === $count - 1) {
				return;
			}
		}
	}

	/**
	 * Clears the list of active formatting elements up to
	 * the last marker.
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function _clearActiveFormattingElementsList ()
	{
		//When the steps below require the UA to clear the list of active
		//formatting elements up to the last marker, the UA must perform the
		//following steps:
		$count = count($this->_AFElements);
		while ($count >= 0) {
			//1. Let entry be the last (most recently added) entry in the list
			//of active formatting elements.
			//2. Remove entry from the list of active formatting elements.
			$entry = array_pop($this->_AFElements);
			$count--;
			//3. If entry was a marker, then stop the algorithm at this point.
			//The list has been cleared up to the last marker.
			if ($entry === self::MARKER) {
				return;
			}
			//4. Go to step 1.
		}
	}
	
	/**
	 * Push the given element onto the list of active formattin elements
	 *
	 * @param	array	$data		Token's data
	 * @param	Element	$element	Element to push onto the list
	 * @return	void
	 * @access	protected
	 */
	protected function _pushOntoActiveFormattingElementsList ($data, $element)
	{
		//When the steps below require the UA to push onto the list of
		//active formatting elements an element element, the UA must perform
		//the following steps:
		//If there are already three elements in the list of active formatting
		//elements after the last list marker, if any, or anywhere in the list
		//if there are no list markers, that have the same tag name, namespace,
		//and attributes as element, then remove the earliest such element from
		//the list of active formatting elements. For these purposes, the
		//attributes must be compared as they were when the elements were created
		//by the parser; two elements have the same attributes if all their parsed
		//attributes can be paired such that the two attributes in each pair have
		//identical names, namespaces, and values (the order of the attributes does
		//not matter).
		//This is the Noah's Ark clause. But with three per family instead of two. 
		$count = 0;
		$remove = false;
		for ($i = count($this->_AFElements) - 1; $i >= 0; $i--) {
			if ($this->_AFElements[$i] === self::MARKER) {
				break;
			}
			$afe = $this->_AFElements[$i]["element"];
			$adata = $this->_AFElements[$i]["data"];
			$attribs = isset($data["attributes"]) ? count($data["attributes"]) : 0;
			$afeAtt = isset($adata["attributes"]) ? count($adata["attributes"]) : 0;
			if ($afe->tagName !== $element->tagName ||
				$afe->namespaceURI !== $element->namespaceURI ||
				$afeAtt !== $attribs) {
				continue;
			}
			if ($attribs) {
				foreach ($data["attributes"] as $name => $att) {
					if (!isset($adata["attributes"][$name])) {
						continue 2;
					}
					$aatt = $adata["attributes"][$name];
					$hasNS = isset($att["namespace"]);
					$hasPrefix = isset($att["prefix"]);
					if ($aatt["value"] !== $att["value"] ||
						isset($aatt["namespace"]) !== $hasNS ||
						isset($aatt["prefix"]) !== $hasPrefix ||
						($hasNS && $aatt["namespace"] !== $att["namespace"]) ||
						($hasPrefix && $aatt["prefix"] !== $att["prefix"])) {
						continue 2;
					}
				}
			}
			if ($remove === false) {
				$remove = $i;
			}
			if (++$count === 3) {
				array_splice($this->_AFElements, $remove, 1);
				break;
			}
		}
		//Add element to the list of active formatting elements.
		$this->_AFElements[] = array("data" => $data, "element" => $element);
	}

	/**
	 * Resets the insertion mode appropriately.
	 *
	 * @param	HTMLElement		$context	Optional context element
	 * @return	void
	 * @access	protected
	 */
	protected function _resetInsertionMode ($context = null)
	{
		//When the steps below require the UA to reset the insertion mode
		//appropriately, it means the UA must follow these steps:
		//1. Let last be false.
		$last = false;
		$count = count($this->_stack);
		for ($i = $count - 1; $i >= 0; $i--) {
			//2. Let node be the last node in the stack of open elements.
			$node = $this->_stack[$i];
			if ($node !== null) {
				//3. Loop: If node is the first node in the stack of open
				//elements, then set last to true and set node to the context
				//element.(fragment case)
				if ($this->_stack[0]->isSameNode($node)) {
					$last = true;
					$node = $context;
				}
				//4. If node is a select element, then switch the insertion
				//mode to "in select" and abort these steps. (fragment case)
				if ($node->tagName === "select") {
					$this->_mode = self::IN_SELECT_MODE;
					break;
				}
				//5. If node is a td or th element and last is false, then
				//switch the insertion mode to "in cell" and abort these steps.
				//(fragment case)
				elseif ($node->tagName === "td" || $node->tagName === "th") {
					$this->_mode = self::IN_CELL_MODE;
					break;
				}
				//6. If node is a tr element, then switch the insertion mode
				//to "in row" and abort these steps. (fragment case)
				elseif ($node->tagName === "tr") {
					$this->_mode = self::IN_ROW_MODE;
					break;
				}
				//7. If node is a tbody, thead, or tfoot element, then switch
				//the insertion mode to "in table body" and abort these steps.
				//(fragment case)
				elseif ($node->tagName === "tbody" ||
						$node->tagName === "thead" ||
						$node->tagName === "tfoot") {
					$this->_mode = self::IN_TABLE_BODY_MODE;
					break;
				}
				//8. If node is a caption element, then switch the insertion
				//mode to "in caption" and abort these steps. (fragment case)
				elseif ($node->tagName === "caption") {
					$this->_mode = self::IN_CAPTION_MODE;
					break;
				}
				//9. If node is a colgroup element, then switch the insertion
				//mode to "in column group" and abort these steps.
				//(fragment case)
				elseif ($node->tagName === "colgroup") {
					$this->_mode = self::IN_COLUMN_GROUP_MODE;
					break;
				}
				//10. If node is a table element, then switch the insertion mode
				//to "in table" and abort these steps. (fragment case)
				elseif ($node->tagName === "table") {
					$this->_mode = self::IN_TABLE_MODE;
					break;
				}
				//11. If node is a head element, then switch the insertion mode to
				//"in body" ("in body"! not "in head"!) and abort these steps. 
				//(fragment case)
				//12. If node is a body element, then switch the insertion mode
				//to "in body" and abort these steps. (fragment case)
				elseif ($node->tagName === "head" || $node->tagName === "body") {
					$this->_mode = self::IN_BODY_MODE;
					break;
				}
				//13. If node is a frameset element, then switch the insertion
				//mode to "in frameset" and abort these steps. (fragment case)
				elseif ($node->tagName === "frameset") {
					$this->_mode = self::IN_FRAMESET_MODE;
					break;
				}
				//14. If node is an html element, then switch the insertion
				//mode to "before head" Then, abort these steps. (fragment case)
				elseif ($node->tagName === "html") {
					$this->_mode = self::BEFORE_HEAD_MODE;
					break;
				}
			}
			//15. If last is true, then switch the insertion mode to "in body"
			//and abort these steps. (fragment case)
			if ($last) {
				$this->_mode = self::IN_BODY_MODE;
				break;
			}
			//16. Let node now be the node before node in the stack of open
			//elements.
			//17. Return to the step labeled loop.
		}
	}
	
	/**
	 * Checks if the given node is a special element (elements that have varying levels 
	 * of special parsing rules)
	 *
	 * @param	HTMLElement	$el		Element
	 * @return	bool		True if it's a special element otherwise false
	 * @access	protected
	 */
	protected function _isSpecialElement ($el)
	{
		static $specials;
		if (!isset($specials)) {
			$specials = array(
				self::HTML_NAMESPACE => array("address", "applet", "area",
						"article", "aside", "base", "basefont", "bgsound",
						"blockquote", "body", "br", "button", "caption",
						"center", "col", "colgroup", "command", "dd",
						"details", "dir", "div", "dl", "dt", "embed",
						"fieldset", "figcaption", "figure", "footer", "form",
						"frame", "frameset", "h1", "h2", "h3", "h4", "h5", "h6",
						"head", "header", "hgroup", "hr", "html", "iframe", "img",
						"input", "isindex", "li", "link", "listing", "marquee",
						"menu", "meta", "nav", "noembed", "noframes", "noscript",
						"object", "ol", "p", "param", "plaintext", "pre",
						"script","section", "select","style", "summary",
						"table", "tbody", "td", "textarea", "tfoot", "th",
						"thead", "title", "tr", "ul", "wbr", "xmp"),
				self::MATHML_NAMESPACE => array(	"mi", "mo", "mn", "ms", "mtext",
												"annotation-xml"),
				self::SVG_NAMESPACE => array("foreignObject", "desc", "title")
			);
		}
		return 	isset($specials[$el->namespaceURI]) && 
				in_array($el->tagName, $specials[$el->namespaceURI]);
	}

	/**
	 * Generic RCDATA or Text element parsing algorithm
	 *
	 * @param	array	$data		Token data
	 * @param	bool	$rcdata		True if it's used to parse rcdata
	 * @return	void
	 * @access	protected
	 */
	protected function _parseGenericRCDATAOrText ($data, $rcdata = false)
	{
		//The generic raw text element parsing algorithm and the generic RCDATA
		//element parsing algorithm consist of the following steps.
		//These algorithms are always invoked in response to a start tag token.
		//Insert an HTML element for the token.
		$this->_insertElement($data, self::HTML_NAMESPACE);
		//If the algorithm that was invoked is the generic raw text element
		//parsing algorithm, switch the tokenizer to the RAWTEXT state;
		//otherwise the algorithm invoked was the generic RCDATA element
		//parsing algorithm, switch the tokenizer to the RCDATA state.
		$this->state = $rcdata ? self::RCDATA_STATE : self::RAWTEXT_STATE;
		//Let the original insertion mode be the current insertion mode.
		$this->_originalInsertionMode = $this->_mode;
		//Then, switch the insertion mode to "text".
		$this->_mode = self::TEXT_MODE;
	}
	
	/**
	 * Checks that the current node is a MathML text integration point
	 *
	 * @return	bool	True if it is a MathML text integration point
	 * @access	protected
	 */
	protected function _isMathTextIntegrationPoint ()
	{
		//The current node is a MathML text integration point if it is one of
		//the following elements:
		//An mi element in the MathML namespace, An mo element in the MathML
		//namespace, An mn element in the MathML namespace, An ms element in
		//the MathML namespace, An mtext element in the MathML namespace
		$allowed = array("mi", "mo", "mn", "ms", "mtext");
		return $this->current->namespaceURI === self::MATHML_NAMESPACE &&
				in_array($this->current->tagName, $allowed);
	}
	
	/**
	 * Checks that the current node is a HTML integration point
	 *
	 * @return	bool	True if it is a HTML integration point
	 * @access	protected
	 */
	protected function _isHTMLIntegrationPoint ()
	{
		//The current node is an HTML integration point if it is one of the
		//following elements:
		//An annotation-xml element in the MathML namespace whose start tag
		//token had an attribute with the name "encoding" whose value was an
		//ASCII case-insensitive match for the string "text/html", An
		//annotation-xml element in the MathML namespace whose start tag token
		//had an attribute with the name "encoding" whose value was an ASCII 
		//case-insensitive match for the string "application/xhtml+xml", A
		//foreignObject element in the SVG namespace, A desc element in the
		//SVG namespace, A title element in the SVG namespace
		$tag = $this->current->tagName;
		$ns = $this->current->namespaceURI;
		if ($ns === self::SVG_NAMESPACE && ($tag === "foreignObject" ||
			$tag === "desc" || $tag === "title")) {
			return true;
		}
		return 	$ns === self::MATHML_NAMESPACE && $tag === "annotation-xml" && 
				preg_match(
					"#^text/html|application/xhtml\+xml$#i", 
					"" . $this->current->getAttribute("encoding")
				);
	}
	
	/**
	 * Parse the token in foreign content mode
	 *
	 * @param	int		$token		Token to emit
	 * @param	array	$data		Token's data array
	 * @param	string	$tagname	Token's tag name
	 * @return	mixed	Nothing or false if the token was ignored
	 * @access	protected
	 */
	protected function _parseTokenInForeignContent ($token, $data, $tagname)
	{
		//A character token
		if ($token === self::CHAR) {
			//Insert the token's character into the current node.
			$this->_insertText($data);
			//If the token is not one of U+0009 CHARACTER TABULATION,
			//U+000A LINE FEED (LF), U+000C FORM FEED (FF), U+000D
			//CARRIAGE RETURN (CR), or U+0020 SPACE, then set the
			//frameset-ok flag to "not ok".
			if ($data !== "\x09" && $data !== "\x0A" &&
				$data !== "\x0C" && $data !== "\x0D" &&
				$data !== "\x20") {
				$this->framesetOkFlag = "not ok";
			}
		}
		//A comment token: Append a Comment node to the current node
		//with the data attribute set to the data given in the comment
		//token.
		elseif ($token === self::COMMENT) {
			$this->_insertComment($data);
		}
		//A DOCTYPE token: Parse error. Ignore the token.
		elseif ($token === self::DOCTYPE) {
			return false;
		} else {
			//A start tag whose tag name is one of: "b", "big",
			//"blockquote", "body", "br", "center", "code", "dd",
			//"div", "dl", "dt", "em", "embed", "h1", "h2", "h3", "h4",
			//"h5", "h6", "head", "hr", "i", "img", "li", "listing",
			//"menu", "meta", "nobr", "ol", "p", "pre", "ruby", "s",
			//"small", "span", "strong", "strike", "sub", "sup",
			//"table", "tt", "u", "ul", "var", A start tag whose tag
			//name is "font", if the token has any attributes named
			//"color", "face", or "size"
			$acceptedTags = array("b", "big", "blockquote", "body", "br",
			"center", "code", "dd", "div", "dl", "dt", "em", "embed",
			"h1", "h2", "h3", "h4", "h5", "h6", "head", "hr", "i",
			"img", "li", "listing", "menu", "meta", "nobr", "ol", "p",
			"pre", "ruby", "s", "small", "span", "strong", "strike",
			"sub", "sup", "table", "tt", "u", "ul", "var");
			if (($tagname === "form" && isset($data["attributes"]) &&
				(isset($data["attributes"]["color"]) ||
				isset($data["attributes"]["face"]) ||
				isset($data["attributes"]["size"]))) ||
				($token === self::START_TAG &&
				in_array($tagname, $acceptedTags))) {
				//Parse error.
				//Pop an element from the stack of open elements, and
				//then keep popping more elements from the stack of
				//open elements until the current node is a MathML
				//text integration point, an HTML integration point,
				//or an element in the HTML namespace.
				for (;;) {
					$this->_popStack();
					if ($this->current->namespaceURI === self::HTML_NAMESPACE ||
						$this->_isMathTextIntegrationPoint() ||
						$this->_isHTMLIntegrationPoint()) {
						break;
					}
				}
				//Then, reset the insertion mode appropriately and
				//reprocess the token.
				$this->_resetInsertionMode();
				$this->_emitToken($token, $data);
			}
			//Any other start tag
			elseif ($token === self::START_TAG) {
				//If the current node is an element in the MathML
				//namespace, adjust MathML attributes for the token.
				//(This fixes the case of MathML attributes that are
				//not all lowercase.)
				if ($this->current->namespaceURI === self::MATHML_NAMESPACE) {
					$data = $this->_adjustMathMLAttributes($data);
				}
				//If the current node is an element in the SVG
				//namespace, and the token's tag name is one of the
				//ones in the first column of the following table,
				//change the tag name to the name given in the
				//corresponding cell in the second column. (This fixes
				//the case of SVG elements that are not all lowercase.)
				elseif ($this->current->namespaceURI === self::SVG_NAMESPACE) {
					//If the current node is an element in the SVG
					//namespace, adjust SVG attributes for the token.
					//(This fixes the case of SVG attributes that are
					//not all lowercase.)
					$data = $this->_adjustSVGAttributes($data);
				}
				//Adjust foreign attributes for the token. (This fixes
				//the use of namespaced attributes, in particular XLink
				//in SVG.)
				$data = $this->_adjustForeignAttributes($data);
				//Insert a foreign element for the token, in the same
				//namespace as the current node.
				$this->_insertElement($data, $this->current->namespaceURI);
				//If the token has its self-closing flag set, pop the
				//current node off the stack of open elements
				if (isset($data["self-closing"]) && $data["self-closing"]) {
					$this->_popStack();
				}
			}
			//An end tag whose tag name is "script", if the current
			//node is a script element in the SVG namespace.
			elseif ($token === self::END_TAG && $tagname === "script" &&
					$this->current->tagName === "script" &&
					$this->current->namespaceURI === self::SVG_NAMESPACE) {
				//Pop the current node off the stack of open elements.
				$this->_popStack();
			}
			//Any other end tag
			elseif ($token === self::END_TAG) {
				//Run these steps:
				//1. Initialize node to be the current node (the
				//bottommost node of the stack).
				$i = count($this->_stack) - 1;
				$node = $this->_stack[$i];
				//2. If node is not an element with the same tag name
				//as the token, then this is a parse error.
				while ($i >= 0) {
					//3. Loop: If node's tag name, converted to
					//ASCII lowercase, is the same as as the tag name
					//of the token, pop elements from the stack of open
					//elements until node has been popped from the
					//stack, and then jump to the last step of this
					//list of steps.
					if (strtolower($node->tagName) === $tagname) {
						while (!$this->current->isSameNode($node)) {
							$this->_popStack();
						}
						$this->_popStack();
						break;
					}
					//4. Set node to the previous entry in the stack
					//of open elements.
					$i--;
					$node = $this->_stack[$i];
					//5. If node is not an element in the HTML
					//namespace, return to the step labeled loop.
					if ($node->namespaceURI !== self::HTML_NAMESPACE) {
						continue;
					}
					//Otherwise, process the token according to the rules
					//given in the section corresponding to the current
					//insertion mode in HTML content.
					return $this->_emitToken($token, $data);
				}
			}
		}
	}
}