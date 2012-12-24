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
 * @copyright	Copyright (c) 2013, Marco Marchiò
 */
 
/**
 * Stream reader
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserStream
{
	/**
	 * HTML code to parse
	 *
	 * @var		string
	 */
	public $code = "";
	
	/**
	 * Output encoding
	 *
	 * @var		string
	 */
	public $encoding;
	
	/**
	 * Code's encoding
	 *
	 * @var		string
	 */
	public $codeEncoding;

	/**
	 * Tokenizer's current index
	 *
	 * @var		int
	 * @access	protected
	 */
	protected $_index = 0;

	/**
	 * Flag to indicate if the tokenizer has reached the end of the code
	 *
	 * @var		bool
	 * @access	protected
	 */
	protected $_isEOF = false;
	
	/**
	 * Class constructor. Set the code to read.
	 *
	 * @param	string	$code		Code
	 */
	function __construct ($code)
	{
		$this->code = $code;
	}
	
	/**
	 * Sets the code encoding
	 *
	 * @param	string|null		$encoding	Encoding. Null to preserve
	 *										the original encoding
	 * @return	void
	 * @access	protected
	 */
	protected function _setEncoding ($inEncoding, $outEncoding)
	{
		$this->codeEncoding = strtoupper($inEncoding);
		if ($outEncoding) {
			$this->encoding = strtoupper($outEncoding);
		} else {
			$this->encoding = $this->codeEncoding;
		}
		$this->code = $this->decode($this->code);
	}
	
	/**
	 * Consumes the next character and returns it
	 *
	 * @return	string		Consumed character or null if the end
	 *						of the code is reached
	 * @access	protected
	 */
	protected function _consume ()
	{
		if (isset($this->code[$this->_index])) {
			return $this->code[$this->_index++];
		} else {
			$this->_isEOF = true;
			return null;
		}
	}

	/**
	 * Consumes every character that matches the given regexp
	 *
	 * @param	string		$regexp		Regexp to test
	 * @return	string		The match or false if it does not match
	 * @access	protected
	 */
	protected function _consumeRegexp ($regexp)
	{
		$nextCode = substr($this->code, $this->_index);
		if (preg_match("#^(?:$regexp)#", $nextCode, $match)) {
			$this->_index += strlen($match[0]);
			if (!isset($this->code[$this->_index])) {
				$this->_isEOF = true;
			}
			return $match[0];
		} else {
			return false;
		}
	}

	/**
	 * Consumes every character until the given one is found
	 *
	 * @param	string		$char		Character(s) to search
	 * @return	string		The match or false if the character to search is
	 * 						not present
	 * @access	protected
	 */
	protected function _consumeUntil ($char)
	{
		$pos = strpos($this->code, $char, $this->_index);
		if ($pos === false) {
			return false;
		}
		$length = $pos - $this->_index;
		$this->_index += $length;
		return substr($this->code, $this->_index, $length);
	}
	
	/**
	 * Consumes every character that is not contained into the given string
	 *
	 * @param	string		$chars		Limit characters
	 * @return	string		Matched characters
	 * @access	protected
	 */
	protected function _consumeUntilFind ($chars)
	{
		$ret = "";
		$char = $this->_consume();
		while ($char !== null && strpos($chars, $char) === false) {
			$ret .= $char;
			$char = $this->_consume();
		}
		if ($char !== null) {
			$this->_unconsume();
		} else {
			$this->_isEOF = false;
		}
		return $ret;
	}

	/**
	 * Consumes every character up to the end of the code and return them. If
	 * the function is already at the end of the code when it is called then
	 * it returns false.
	 *
	 * @return	string		Remaining characters or false
	 * @access	protected
	 */
	protected function _consumeRemaining ()
	{
		if ($this->_isEOF) {
			return false;
		}
		$this->_isEOF = true;
		$ret = substr($this->code, $this->_index);
		$this->_index = strlen($this->code);
		return $ret;
	}

	/**
	 * Unconsumes the given number of characters. By default only the last one
	 * is unconsumed.
	 *
	 * @param	int			$chars	Number of characters to unconsume
	 * @return	void
	 * @access	protected
	 */
	protected function _unconsume ($chars = 1)
	{
		$this->_index -= $chars;
		$this->_isEOF = false;
	}
	
	/**
	 * Fix the code by stripping bom encoding characters and returns the
	 * encoding if it has been found
	 *
	 * @return	mixed		Encoding or null if no encoding is found
	 * @access	protected
	 */
	protected function _checkAndFixBom ()
	{
		$bomChars = array(
			"UTF-32BE" => "\x00\x00\xFE\xFF",
			"UTF-32LE" => "\xFF\xFE\x00\x00",
			"UTF-8" => "\xEF\xBB\xBF",
			"UTF-16BE" => "\xFF\xFF",
			"UTF-16LE" => "\xFF\xFE"
		);
		$start = substr($this->code, 0, 4);
		foreach ($bomChars as $enc => $bom) {
			if (strpos($start, $bom) === 0) {
				$this->code = substr($this->code, strlen($bom));
				return $enc;
			}
		}
		return null;
	}
	
	/**
	 * Tries to detect the encoding of the code using PHP functions
	 *
	 * @return	string		Encoding
	 * @access	protected
	 */
	protected function _detectEncoding ()
	{		
		if (function_exists("mb_detect_encoding")) {
			//If the mb extension is enabled detect the encoding
			return mb_detect_encoding($this->code);
		} elseif (function_exists("iconv_get_encoding")) {
			//If the iconv extension is enabled get the input encoding
			return iconv_get_encoding("input_encoding");
		} else {
			//Fallback encoding
			return "ISO-8859-1";
		}
	}
	
	/**
	 * Decodes the given code according to the given encoding
	 *
	 * @param	string		$code			Code
	 * @param	string		$toEncoding		Output encoding
	 * @param	string		$fromEncoding	Input encoding
	 * @return	string		Decoded code
	 */
	function decode ($code, $toEncoding = null, $fromEncoding = null)
	{
		//Use current encodings if not given
		if (!$toEncoding) {
			$toEncoding = $this->encoding;
		} else {
			$toEncoding = strtoupper($toEncoding);
		}
		if (!$fromEncoding) {
			$fromEncoding = $this->codeEncoding;
		} else {
			$fromEncoding = strtoupper($fromEncoding);
		}
		if ($toEncoding === $fromEncoding) {
			return $code;
		}
		if (function_exists("mb_convert_encoding")) {
			return mb_convert_encoding(
				$code, 
				$toEncoding,
				$fromEncoding
			);
		} elseif (function_exists("iconv")) {
			return iconv(
				$fromEncoding,
				"$toEncoding//TRANSLIT//IGNORE",
				$code
			);
		}
		//If mb and iconv are not available and the encodings
		//are ISO-8859-1 and UTF-8 fallback on utf functions
		$inOut = "$fromEncoding>$toEncoding";
		if ($inOut === "ISO-8859-1>UTF-8") {
			return utf8_encode($code);
		} elseif ($inOut === "UTF-8>ISO-8859-1") {
			return utf8_decode($code);
		}
		return $code;
	}
}