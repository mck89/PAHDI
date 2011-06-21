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
 * DOM HTML element class
 *
 * @category    	PAHDI
 * @package    		PAHDI-DOM
 * @property		string			$title				Element's title
 * @property		string			$lang				Element's lang
 * @property		string			$dir				Element's text direction
 * @property		int				$tabIndex			Element's tabindex
 * @property		string			$contentEditable	Element's content editable state
 * @property		bool			$isContentEditable	Element's contentEditable boolean state
 * @property		string			$draggable			Element's draggable state
 * @property		string			$innerHTML			Element's inner HTML
 * @property		string			$outerHTML			Element's outer HTML
 * @property		string			$innerText			Element's inner text
 * @property		string			$outerText			Element's outer text
 * @property		HTMLFormElement	$form				Form element that contains the element or
 *														null if not present. This property is
 *														available only on for form-associated elements
 */
class HTMLElement extends Element
{
	/**
	 * Namespace uri
	 *
	 * @var		string
	 */
	public $namespaceURI = ParserHTML::HTML_NAMESPACE;
	
	/**
	 * Boolean that sets the visibility of the element
	 *
	 * @var		bool
	 */
	public $hidden = false;
	
	/**
	 * Inserts the given element at the specified location relative
	 * to the current one
	 *
	 * @param	string		$where		Location. Allowed values are:
	 *									- "beforeBegin": inserts the
	 *									  element before the  current
	 *									  one
	 *									- "afterBegin": inserts the
	 *									  element as first child of
	 *									  the current one
	 *									- "beforeEnd": inserts the
	 *									  element as last child of
	 *									  the current one
	 *									- "afterEnd": inserts the
	 *									  element after the current
	 *									  one
	 * @param	Element		$el			Element to insert
	 * @return	Element		Inserted element
	 */
	function insertAdjacentElement ($where, Element $el)
	{
		$where = strtolower($where);
		switch ($where) {
			case "beforebegin":
				if ($this->parentNode) {
					$this->parentNode->insertBefore($el, $this);
				}
			break;
			case "afterbegin":
				if ($this->childNodes->length) {
					$this->insertBefore($el, $this->firstChild);
				} else {
					$this->appendChild($el);
				}
			break;
			case "beforeend":
				$this->appendChild($el);
			break;
			case "afterend":
				if ($this->parentNode) {
					$next = $this->nextSibling;
					if ($next) {
						$this->parentNode->insertBefore($el, $next);
					} else {
						$this->parentNode->appendChild($el);
					}
				}
			break;
			default:
				$msg = "Unsupported operation $where";
				throw new DomException($msg);
			break;
		}
		return $el;
	}
	
	/**
	 * Inserts the given text at the specified location relative
	 * to the current element
	 *
	 * @param	string		$where		Location. Look at
	 *									insertAdjacentElement
	 *									documentation to know allowed
	 *									values
	 * @param	string		$text		Text to insert
	 * @return	void
	 */
	function insertAdjacentText ($where, $text)
	{
		$text = $this->ownerDocument->createTextNode($text);
		$where = strtolower($where);
		switch ($where) {
			case "beforebegin":
				if ($this->parentNode) {
					$this->parentNode->insertBefore($text, $this);
				}
			break;
			case "afterbegin":
				if ($this->childNodes->length) {
					$this->insertBefore($text, $this->firstChild);
				} else {
					$this->appendChild($text);
				}
			break;
			case "beforeend":
				$this->appendChild($text);
			break;
			case "afterend":
				if ($this->parentNode) {
					$next = $this->nextSibling;
					if ($next) {
						$this->parentNode->insertBefore($text, $next);
					} else {
						$this->parentNode->appendChild($text);
					}
				}
			break;
			default:
				$msg = "Unsupported operation $where";
				throw new DomException($msg);
			break;
		}
	}
	
	/**
	 * Inserts the given html at the specified location relative
	 * to the current element
	 *
	 * @param	string		$where		Location. Look at
	 *									insertAdjacentElement
	 *									documentation to know allowed
	 *									values
	 * @param	string		$html		HTML to insert
	 * @return	void
	 */
	function insertAdjacentHTML ($where, $html)
	{
		$encoding = $this->ownerDocument->inputEncoding;
		$parser = new ParserHTML($html, $encoding, $encoding);
		$root = $parser->parseHTMLFragment($this);
		$length = $root->childNodes->length;
		$where = strtolower($where);
		switch ($where) {
			case "beforebegin":
				if ($this->parentNode) {
					while ($length) {
						$this->parentNode->insertBefore(
							$root->childNodes[0],
							$this
						);
						$length--;
					}
				}
			break;
			case "afterbegin":
				if ($this->childNodes->length) {
					$first = $this->firstChild;
					while ($length) {
						$this->insertBefore($root->childNodes[0], $first);
						$length--;
					}
				} else {
					while ($length) {
						$this->appendChild($root->childNodes[0]);
						$length--;
					}
				}
			break;
			case "beforeend":
				while ($length) {
					$this->appendChild($root->childNodes[0]);
					$length--;
				}
			break;
			case "afterend":
				$par = $this->parentNode;
				if ($par) {
					$next = $this->nextSibling;
					if ($next) {
						while ($length) {
							$par->insertBefore(
								$root->childNodes[0],
								$next
							);
							$length--;
						}
					} else {
						while ($length) {
							$this->parentNode->appendChild(
								$root->childNodes[0]
							);
							$length--;
						}
					}
				}
			break;
			default:
				$msg = "Unsupported operation $where";
				throw new DomException($msg);
			break;
		}
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
			case "lang":
				return $this->_getProperty($name);
			break;
			case "dir":
				$ret = $this->_getProperty($name);
				$ret = strtolower($ret);
				if ($ret !== "ltr" && $ret !== "rtl") {
					$ret = "";
				}
				return $ret;
			break;
			case "isContentEditable":
				return $this->contentEditable === "true";
			break;
			case "tabIndex":
				$default = in_array(
					$this->tagName,
					ParserHTML::$focusableElements
				) ? 0 : - 1;
				return (int) $this->_getProperty($name, "int", $default);
			break;
			case "draggable":
			case "contentEditable":
				return $this->_getProperty($name, "bool");
			break;
			case "form":
				if (in_array($this->tagName, ParserHTML::$formAssociated)) {
					$el = $this->parentNode;
					while ($el && $el->tagName !== "form") {
						$el = $el->parentNode;
					}
					if ($el && $el->tagName === "form") {
						return $el;
					}
				}
			break;
			case "innerHTML":
				return ParserHTML::serializeChildNodes($this);
			break;
			case "outerHTML":
				return ParserHTML::serialize($this);
			break;
			case "outerText":
			case "innerText":
				return $this->textContent;
			break;
			default:
				return parent::__get($name);
			break;
		}
		return null;
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
			case "lang":
				$this->_setProperty($name, $value);
			break;
			case "contentEditable":
				//ContentEditable accepts only "true" or "false" as
				//booleans or strings
				$value = strtolower($value);
				if (!is_bool($value) &&
					!in_array($value, array("true", "false"))) {
					$error = "An invalid or illegal string was specified";
					throw new DomException($error);
				}
				$this->_setProperty($name, $value, "bool");
			break;
			case "isContentEditable":
				return;
			break;
			case "dir":
				//The "dir" attribute needs a validation because
				//only "ltr", "rtl" or empty string values are
				//allowed
				$value = strtolower($value);
				if ($value !== "ltr" && $value !== "rtl") {
					$value = "";
				}
				$this->_setProperty($name, $value);
			break;
			case "tabIndex":
				$this->_setProperty($name, $value, "int", - 1);
			break;
			case "draggable":
				$this->_setProperty($name, $value, "bool");
			break;
			case "innerHTML":
			case "outerHTML":
				$encoding = $this->ownerDocument->inputEncoding;
				$parser = new ParserHTML($value, $encoding, $encoding);
				$root = $parser->parseHTMLFragment($this);
				$length = $root->childNodes->length;
				if ($name === "innerHTML") {
					$this->_emptyNode();
					while ($length) {
						$this->appendChild($root->childNodes[0]);
						$length--;
					}
				} else {
					$parent = $this->parentNode;
					if ($parent) {
						while ($length) {
							$parent->insertBefore($root->childNodes[0], $this);
							$length--;
						}
						$parent->removeChild($this);
					}
				}
			break;
			case "innerText":
				$this->textContent = $value;
			break;
			case "outerText":
				$text = $this->ownerDocument->createTextNode($value);
				if ($this->parentNode) {
					$this->parentNode->insertBefore($text, $this);
					$this->parentNode->removeChild($this);
				}
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}