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
 * DOM element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	mixed			$firstElementChild		First element child or null
 * @property-read	mixed			$lastElementChild		Last element child or null
 * @property-read	int				$childElementCount		Number of element children
 * @property-read	mixed			$previousElementSibling	Previous element sibling or null
 * @property-read	mixed			$nextElementSibling		Next element sibling or null
 * @property-read	CSSStyleDeclaration		$style			Style object
 * @property-read	DomStringMap	$dataset				Dataset object
 * @property		string			$id						Element's id
 * @property		string			$className				Element's class name
 * @property		TokenList		$classList				Element's class list
 * @property		HTMLCollection	$children				Children elements
 */
class Element extends Node
{
	/**
	 * Element tag name
	 *
	 * @var		string
	 */
	public $tagName;
	
	/**
	 * Node type
	 *
	 * @var		int
	 */
	public $nodeType = self::ELEMENT_NODE;
	
	/**
	 * Style object
	 *
	 * @var		CSSStyleDeclaration
	 * @access	protected
	 * @ignore
	 */
	protected $_style;
	
	/**
	 * Dataset object
	 *
	 * @var		DomStringMap
	 * @access	protected
	 * @ignore
	 */
	protected $_dataset;
	
	/**
	 * Class constructor
	 *
	 *  @param	string	$tag	Tag name
	 */
	function __construct ($tag)
	{
		parent::__construct();
		$this->tagName = $this->nodeName = $this->localName = $tag;
		$this->attributes = new NamedNodeMap($this);
	}
	
	/**
	 * Returns true if the current element has the attribute with
	 * the given name
	 *
	 * @param	string		$name		Attribute name
	 * @return	bool		True if the attribute is present on the 
	 *						current element othwerwise false
	 */
	function hasAttribute ($name)
	{
		return $this->attributes->getNamedItem($name) !== null;
	}
	
	/**
	 * Returns true if the current element has the attribute with
	 * the given name and namespace
	 *
	 * @param	string		$ns			Attribute namespace
	 * @param	string		$name		Attribute name
	 * @return	bool		True if the attribute is present on the 
	 *						current element othwerwise false
	 */
	function hasAttributeNS ($ns, $name)
	{
		return $this->attributes->getNamedItemNS($ns, $name) !== null;
	}
	
	/**
	 * Sets a new attribute with the given name and value on the
	 * current element. 
	 *
	 * @param	string		$name		Attribute name
	 * @param	mixed		$value		Attribute value
	 * @return	void
	 */
	function setAttribute ($name, $value)
	{
		$node = $this->attributes->getNamedItem($name);
		if ($node === null) {
			$node = $this->ownerDocument->createAttribute($name);
			$this->attributes->setNamedItem($node);
		}
		$node->value = $value;		
	}
	
	/**
	 * Sets an attribute node on the current element
	 *
	 * @param	Attr		$node	Attribute node
	 * @return	mixed		Replaced attribute node (if any) or null
	 */
	function setAttributeNode (Attr $node)
	{
		$ret = $this->attributes->setNamedItem($node);
		return $ret;
	}
	
	/**
	 * Sets a new attribute with the given name, namespace and value
	 * on the current element. 
	 *
	 * @param	string		$ns			Attribute namespace
	 * @param	string		$name		Attribute name
	 * @param	mixed		$value		Attribute value
	 * @return	void
	 */
	function setAttributeNS ($ns, $name, $value)
	{
		$node = $this->ownerDocument->createAttribute($name);
		$node->value = $value;
		$node->namespaceURI = $ns;
		$this->attributes->setNamedItemNS($node);
	}
	
	/**
	 * Sets a namespaced attribute node on the current element
	 *
	 * @param	Attr		$node	Attribute node
	 * @return	mixed		Replaced attribute node (if any) or null
	 */
	function setAttributeNodeNS (Attr $node)
	{
		$ret = $this->attributes->setNamedItemNS($node);
		return $ret;
	}
	
	/**
	 * Returns the value of the attribute with the given name
	 *
	 * @param	string		$name		Attribute name
	 * @return	mixed		Attribute value or null if the attribute
	 *						is not present
	 */
	function getAttribute ($name)
	{
		$ret = $this->attributes->getNamedItem($name);
		return $ret ? $ret->value : null;
	}
	
	/**
	 * Returns the attribute node with the given name
	 *
	 * @param	string		$name		Attribute name
	 * @return	mixed		Attribute node or null if the attribute
	 *						is not present
	 */
	function getAttributeNode ($name)
	{
		$ret = $this->attributes->getNamedItem($name);
		return $ret;
	}
	
	/**
	 * Returns the value of the attribute with the given name and
	 * namespace
	 *
	 * @param	string		$ns			Attribute namespace
	 * @param	string		$name		Attribute name
	 * @return	mixed		Attribute value or null if the attribute
	 *						is not present
	 */
	function getAttributeNS ($ns, $name)
	{
		$ret = $this->attributes->getNamedItemNS($ns, $name);
		return $ret ? $ret->value : null;
	}
	
	/**
	 * Returns the attribute node with the given name and namespace
	 *
	 * @param	string		$ns			Attribute namespace
	 * @param	string		$name		Attribute name
	 * @return	mixed		Attribute node or null if the attribute
	 *						is not present
	 */
	function getAttributeNodeNS ($ns, $name)
	{
		$ret = $this->attributes->getNamedItemNS($ns, $name);
		return $ret;
	}
	
	/**
	 * Removes the attribute with the given name
	 *
	 * @param	string		$name		Attribute name
	 * @return	void
	 */
	function removeAttribute ($name)
	{
		$this->attributes->removeNamedItem($name);
	}
	
	/**
	 * Removes the attribute with the given name and namespace
	 *
	 * @param	string		$ns			Attribute namespace
	 * @param	string		$name		Attribute name
	 * @return	void
	 */
	function removeAttributeNS ($ns, $name)
	{
		$this->attributes->removeNamedItemNS($ns, $name);
	}
	
	/**
	 * Removes the given attribute node from the current
	 * element
	 *
	 * @param	Attr	$node	Attribute node
	 * @return	Attr	Removed attribute node
	 */
	function removeAttributeNode (Attr $node)
	{
		$ret = $this->attributes->removeNamedItemNS(
			$node->namespaceURI,
			$node->name
		);
		return $ret;
	}
	
	/**
	 * Cheks if the current element contains the given
	 * one. For non elements nodes it always returns
	 * false
	 *
	 * @param	Node	$el		Element to test
	 * @return	bool	True if the current element
	 *					contains the given one otherwise
	 *					false
	 */
	function contains (Node $el)
	{
		if ($el->nodeType !== self::ELEMENT_NODE) {
			return false;
		}
		$pos = $this->compareDocumentPosition($el);
		return $pos && ($pos & 16) ? true : false;
	}
	
	/**
	 * Get the list of descendant elements with the
	 * given tag name
	 *
	 * @param	string			$tagname	Tag name to search
	 * @return	HTMLCollection	Nodes collection
	 */
	function getElementsByTagName ($tagname)
	{
		$search = new PAHDISearch($this);
		if ($tagname === "*") {
			$fn = function () {
				return true;
			};
		} else {
			$fn = function ($node) use ($tagname) {
				return $node->tagName === $tagname;
			};
		}
		return $search->find($fn)->toHTMLCollection();
	}
	
	/**
	 * Get the list of descendant elements with the
	 * given tag name in the given namespace
	 *
	 * @param	string			$tagname	Tag name to search
	 * @param	string			$ns			Namespace to search
	 * @return	HTMLCollection	Nodes collection
	 */
	function getElementsByTagNameNS ($ns, $tagname)
	{
		$search = new PAHDISearch($this);
		if ($tagname === "*") {
			$fn = function ($node) use ($ns) {
				return $node->namespaceURI === $ns;
			};
		} else {
			$fn = function ($node) use ($tagname, $ns) {
				return 	$node->tagName === $tagname &&
						$node->namespaceURI === $ns;
			};
		}
		return $search->find($fn)->toHTMLCollection();
	}
	
	/**
	 * Get the list of descendant elements with the
	 * given class name
	 *
	 * @param	string			$class	Class name to search
	 * @return	HTMLCollection	Nodes collection
	 */
	function getElementsByClassName ($class)
	{
		$search = new PAHDISearch($this);
		$classes = preg_split("#\s+#", trim($class));
		$fn = function ($node) use ($classes) {
			$cl = $node->className;
			if (!$cl) {
				return false;
			}
			$list = $node->classList;
			foreach ($classes as $c) {
				if (!$list->contains($c)) {
					return false;
				}
			}
			return true;
		};
		return $search->find($fn)->toHTMLCollection();
	}
	
	/**
	 * Finds the elements that match the given selector
	 *
	 * @param	string		$selector	Selector
	 * @return	NodeList	List of matching elements
	 */
	function querySelectorAll ($selector)
	{
		$parser = new ParserSelector($selector, $this);
		return $parser->parse();
	}
	
	/**
	 * Finds the first element that matches the given selector
	 *
	 * @param	string		$selector	Selector
	 * @return	mixed		First element that matches the given
	 *						selector or null if not found
	 */
	function querySelector ($selector)
	{
		$list = $this->querySelectorAll($selector);
		return $list->length ? $list[0] : null;
	}
	
	/**
	 * Checks if the current element is matched by the given
	 * selector
	 *
	 * @param	string		$selector	Selector
	 * @return	bool		True if the element matches the
	 *						given selector otherwise false
	 */
	function matchesSelector ($selector)
	{
		$list = $this->onwerDocument->querySelectorAll($selector);
		for ($i = 0; $i < $list->length; $i++) {
			if ($list[$i]->isSameNode($this)) {
				return true;
			}
		}
		return false;
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
			case "firstElementChild":
			case "lastElementChild":
				$length = $this->childNodes->length;
				if ($length) {
					$start = 0;
					$inc = 1;
					if ($name === "lastElementChild") {
						$start = $length - 1;
						$inc = - 1;
					}
					for ($i = $start; $i >= 0 && $i < $length; $i += $inc) {
						$el = $this->childNodes[$i];
						if ($el->nodeType === 1) {
							return $el;
						}
					}
				}
			break;
			case "childElementCount":
				$ret = 0;
				$length = $this->childNodes->length;
				if ($length) {
					for ($i = 0; $i < $length; $i++) {
						if ($this->childNodes[$i]->nodeType === 1) {
							$ret++;
						}
					}
				}
				return $ret;
			break;
			case "nextElementSibling":
			case "previousElementSibling":
				if ($this->parentNode) {
					$ret = null;
					$length = $this->parentNode->childNodes->length;
					if ($length > 1) {
						$start = 0;
						$inc = 1;
						if ($name === "nextElementSibling") {						
							$start = $length - 1;
							$inc = - 1;
						}
						for ($i = $start; $i >= 0 && $i < $length; $i += $inc) {
							$el = $this->parentNode->childNodes[$i];
							if ($el->nodeType !== 1) {
								continue;
							}
							if ($el->isSameNode($this)) {
								break;
							}
							$ret = $el;
						}
					}
					return $ret;
				}
			break;
			case "id":
				return $this->_getProperty($name);
			break;
			case "className":
				return $this->_getProperty("class");
			break;
			case "classList":
				$el = $this;
				$fn = function ($class) use ($el) {
						$el->className = $class;
					};
				return new TokenList($this->_getProperty("class"), $fn);
			break;
			case "children":
				$ret = new HTMLCollection;
				$l = $this->childNodes->length;
				for ($i = 0; $i < $l; $i++) {
					if ($this->childNodes[$i]->nodeType === 1) {
						$ret->_appendNode($this->childNodes[$i]);
					}
				}
				return $ret;
			break;
			case "style":
				if (!$this->_style) {
					$style = $this->getAttributeNode("style");
					if ($style) {
						$css = $style->value;
					} else {
						$style = $document->createAttribute("style");
						$this->setAttributeNode($style);
						$css = "";
					}
					//Unset the attribute value so that its value
					//becomes as magic property
					unset($style->value);
					$parser = new ParserCSS($css, $this);
					$this->_style = $parser->parseStyleAttribute();
				}
				return $this->_style;
			break;
			case "dataset":
				if (!$this->_dataset) {
					$this->_dataset = new DomStringMap($this);
				}
				return $this->_dataset;
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
			case "firstElementChild":
			case "lastElementChild":
			case "childElementCount":
			case "nextElementSibling":
			case "previousElementSibling":
			case "classList":
			case "children":
			case "style":
			case "dataset":
				throw new DomException("Setting a property that has only a getter");
			break;
			case "id":
				$this->_setProperty($name, $value);
			break;
			case "className":
				$this->_setProperty("class", $value);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
	
	/**
	 * Get a property that is linked to an attribute
	 *
	 * @param	string	$name		Property name
	 * @param	string	$type		Property type
	 * @param	mixed	$default	Default value
	 * @return	mixed	Property value
	 * @access	protected
	 * @ignore
	 */
	protected function _getProperty ($name, $type = "", $default = null)
	{
		$ret = $this->getAttribute($name);
		if ($default === null) {
			if ($type === "color") {
				$default = "#000000";
			} elseif ($type === "bool") {
				$default = false;
			} else {
				$default = "";
			}
		}
		if ($type === "path") {
			if ($ret === null) {
				return null;
			}
			$res = PAHDIPath::resolve($this->baseURI, $ret);
			return $res ? $res : $ret;
		} elseif ($type === "pathlist") {
			if ($ret === null) {
				return null;
			}
			$base = $this->baseURI;
			$list = preg_split("#\s+#", $ret);
			$paths = array();
			foreach ($list as $path) {
				$res = PAHDIPath::resolve($base, $path);
				$paths[] = $res ? $res : $ret;
			}
			return implode(" ", $paths);
		} elseif ($type === "color") {
			$valid = is_string($ret) &&
					 preg_match("@^#?[0-9a-f]+$@i", $ret);
			return $valid ? $ret : $default;
		} elseif ($type === "bool") {
			if ($ret === null) {
				return $default;
			}
			return $ret !== false && strtolower($ret) !== "false";
		} elseif ($type === "int" || $type === "intperc") {
			$perc = $type === "intperc";
			$notValid = $ret === null || (!is_numeric($ret) &&
						!preg_match(
							"#^-?[\d\.]+" . ($perc ? "%?" : "") . "$#",
							$ret
						));
			return $notValid ? $default : $ret;
		} else {
			return $ret === null ? $default : $ret;
		}
	}
	
	/**
	 * Set a property that is linked to an attribute
	 *
	 * @param	string	$name		Property name
	 * @param	mixed	$value		Property value
	 * @param	string	$type		Property type
	 * @param	mixed	$default	Default value
	 * @return	void
	 * @access	protected
	 * @ignore
	 */
	protected function _setProperty ($name, $value, $type = "", $default = null)
	{
		$ret = $this->getAttributeNode($name);
		if ($type === "int" || $type === "intperc") {
			$perc = $type === "intperc";
			if (!is_numeric($value) &&
				!preg_match("#^-?[\d\.]+" . ($perc ? "%?" : "") . "$#", $value)) {
				if ($default === null) {
					$default = "";
				}
				$value = $default;
			}
		} elseif ($type === "bool") {
			if ($value === false) {
				$value = "false";
			} else {
				$lw = strtolower($value);
				if ($lw !== "false") {
					$value = "true";
				} else {
					$value = $lw;
				}
			}
		} elseif ($type === "color") {
			if ($default === null) {
				$default = "#000000";
			}
			$rgbaReg = "#^rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$#i";
			if (!is_string($value)) {
				$value = $default;
			} elseif (preg_match("@^#?([0-9a-f]+)$@i", $value, $match)) {
				$len = strlen($match[1]);
				if ($len !== 3 && $len !== 6) {
					$value = $default;
				} else {
					$value = strtolower($match[1]);
					if ($len === 3) {
						$value = $value[0] . $value[0] .
								 $value[1] . $value[1] .
								 $value[2] . $value[2];
					}
					$value = "#" . $value;
				}
			} elseif (preg_match($rgbaReg, $value, $match)) {
				$value = "#";
				for ($i = 1; $i < 4; $i++) {
					$value .= str_pad(dechex($match[$i]), 2, "0");
				}
			} else {
				$value = $default;
			}
		}
		if ($ret === null) {
			$this->setAttribute($name, $value);
		} else {
			$ret->value = $value;
		}
	}
}