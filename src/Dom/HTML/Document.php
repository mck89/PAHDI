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
 * DOM document class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	DocumentType	$doctype			The doctype node
 * @property-read	HTMLHtmlElement	$documentElement	The html element
 * @property-read	HTMLBodyElement	$body				The body element
 * @property-read	HTMLHeadElement	$head				The head element
 * @property-read	HTMLCollection	$applets			Applets collection
 * @property-read	HTMLCollection	$embeds				Embeds collection
 * @property-read	HTMLCollection	$forms				Forms collection
 * @property-read	HTMLCollection	$images				Images collection
 * @property-read	HTMLCollection	$plugins			Embed collection
 * @property-read	HTMLCollection	$anchors			Anchors collection
 * @property-read	HTMLCollection	$links				Links collection
 * @property-read	StyleSheetList	$styleSheets		Style sheets list
 * @property-read	string			$URL				Document URL
 * @property		string			$title				Document title
 * @property		string			$fgColor			Foreground color
 * @property		string			$bgColor			Background color
 */
class HTMLDocument extends Node
{	
	/**
	 * Node type
	 *
	 * @var		int
	 */
	public $nodeType = self::DOCUMENT_NODE;
	
	/**
	 * Node name
	 *
	 * @var		string
	 */
	public $nodeName = "#document";
	
	/**
	 * Document uri
	 *
	 * @var		string
	 */
	public $documentURI = __FILE__;
	
	/**
	 * Compat mode
	 *
	 * @var		string
	 */
	public $compatMode;
	
	/**
	 * Original encoding of the document
	 *
	 * @var		string
	 */
	public $inputEncoding;
	
	/**
	 * Render encoding of the current document
	 *
	 * @var		string
	 */
	public $characterSet;
	
	/**
	 * Parsing options
	 *
	 * @var		array
	 * @ignore
	 */
	public $_implementation;
	
	/**
	 * Creates an element in the HTML namespace
	 *
	 * @param	string	$tag	Tag name
	 * @return	object	New element
	 */
	function createElement ($tag)
	{
		$node = $this->createElementNS(ParserHTML::HTML_NAMESPACE, $tag);
		return $node;
	}
	
	/**
	 * Creates an element in the given namespace
	 *
	 * @param	string		$namespace	Namespace
	 * @param	string		$tag		Tag name
	 * @return	object		New element
	 */
	function createElementNS ($namespace, $tag)
	{
		if (!$tag || preg_match("#[^\p{L}\p{N}:\.\-_\d]#u", utf8_encode($tag))) {
			throw new DomException("String contains an invalid character");
		}
		//Fix the tag name and optionally convert it if it's
		//a camelcased svg tag name
		$tag = strtolower($tag);
		if (isset(ParserHTML::$SVGTagConv[$tag])) {
			$tag = ParserHTML::$SVGTagConv[$tag];
		}
		//Get the appropriate class name for the element
		$elClass = $this->_findElementClass($namespace, $tag);
		$node = new $elClass($tag);
		$node->ownerDocument = $this;
		$node->namespaceURI = $namespace;
		return $node;
	}
	
	/**
	 * Creates a new comment node
	 *
	 * @param	string		$data	Node data
	 * @return	object		New comment node
	 */
	function createComment ($data)
	{
		$node = new Comment($data);
		$node->ownerDocument = $this;
		return $node;
	}
	
	/**
	 * Creates a new text node
	 *
	 * @param	string		$data	Node data
	 * @return	object		New text node
	 */
	function createTextNode ($data)
	{
		$node = new Text($data);
		$node->ownerDocument = $this;
		return $node;
	}
	
	/**
	 * Creates a new attribute node
	 *
	 * @param	string		$name	Attribute name
	 * @return	object		New attribute node
	 */
	function createAttribute ($name)
	{
		$node = new Attr($name);
		$node->ownerDocument = $this;
		return $node;
	}
	
	/**
	 * Creates a new attribute node in the given namespace
	 *
	 * @param	string		$namespace	Namespace
	 * @param	string		$name		Attribute name
	 * @return	object		New attribute node
	 */
	function createAttributeNS ($namespace, $name)
	{
		$node = $this->createAttribute($name);
		$node->namespaceURI = $namespace;
		return $node;
	}
	
	/**
	 * Creates a document fragment
	 *
	 * @return	object		New document fragment
	 */
	function createDocumentFragment ()
	{
		$node = new DocumentFragment;
		$node->ownerDocument = $this;
		return $node;
	}
	
	/**
	 * Normalizes the document
	 *
	 * @return	void
	 */
	function normalizeDocument ()
	{
		return $this->normalize();
	}
	
	/**
	 * Adopts a node that comes from a different document.
	 * This method removes the node from its document and
	 * change it's ownerDocument to the current one.
	 *
	 * @param	Node	$node	Node to adopt
	 * @return	Node	Adopted node
	 */
	function adoptNode (Node $node)
	{
		if ($node->parentNode) {
			$node->parentNode->removeChild($node);
		}
		$node->ownerDocument = $this;
		return $node;
	}
	
	/**
	 * Clones a nodes that comes from a different document
	 * and imports it into the current one.
	 *
	 * @param	Node	$node	Node to import
	 * @return	Node	Imported node
	 */
	function importNode (Node $node)
	{
		$clone = $node->cloneNode(true);
		return $clone->adoptNode();
	}
	
	/**
	 * Create an instance of a node iterator class. The NodeIterator
	 * class allows you to iterate over the members of a list of the
	 * nodes in a subtree of the DOM, in document order.
	 *
	 * @param	Node	$root		Root node. Node from which the
	 *								iteration will start.
	 * @param	int		$whatToShow	A sum of NodeFilter "what to
	 *								show" constants that specify which
	 *								type of nodes should be considered
	 *								in the iteration. If NodeFilter::SHOW_ALL
	 *								is used, you can't add other constants.
	 * @param	mixed	$filter		Closure, function name or any object with a
	 *								method named 'acceptNode'. This function
	 *								receives the node as argument and it must
	 *								return a filter constant from the NodeFilter
	 *								class. It must return
	 *								NodeFilter::FILTER_ACCEPT if the node
	 *								must be included in the iteration.
	 * @return	NodeIterator		Node iterator
	 */
	function createNodeIterator (Node $root, $whatToShow, $filter)
	{
		return new NodeIterator($root, $whatToShow, $filter);
	}
	
	/**
	 * Create an instance of a tree walker class. The TreeWalker
	 * class is very similar to the NodeIterator class except that
	 * it has additional methods to traverse the filtered nodes
	 *
	 * @param	Node	$root		Root node. Node from which the
	 *								iteration will start.
	 * @param	int		$whatToShow	A sum of NodeFilter "what to
	 *								show" constants that specify which
	 *								type of nodes should be considered
	 *								in the iteration. If NodeFilter::SHOW_ALL
	 *								is used, you can't add other constants.
	 * @param	mixed	$filter		Closure, function name or any object with a
	 *								method named 'acceptNode'. This function
	 *								receives the node as argument and it must
	 *								return a filter constant from the NodeFilter
	 *								class. It must return
	 *								NodeFilter::FILTER_ACCEPT if the node
	 *								must be included in the iteration.
	 * @return	TreeWalker			Tree Walker
	 */
	function createTreeWalker (Node $root, $whatToShow, $filter)
	{
		return new TreeWalker($root, $whatToShow, $filter);
	}
	
	/**
	 * Returns the first element with the given id
	 *
	 * @param	string	$id		Id to search
	 * @return	mixed	Element with the given id or null if there are no
	 *					elements with that id
	 */
	function getElementById ($id)
	{
		$search = new PAHDISearch($this);
		$fn = function ($node) use ($id) {
			return $node->id === $id;
		};
		$search->find($fn, PAHDISearch::DESCENDANTS, 1, 1);
		return $search->length ? $search[0] : null;
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
	 * Get the list of descendant elements with the
	 * given name
	 *
	 * @param	string			$name	Name to search
	 * @return	HTMLCollection	Nodes collection
	 */
	function getElementsByName ($name)
	{
		$search = new PAHDISearch($this);
		$fn = function ($node) use ($name) {
			return $node->name === $name;
		};
		return $search->find($fn)->toHTMLCollection();
	}
	
	/**
	 * Find elements that match the given selector
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
	 * Provides a way to access some properties
	 *
	 * @param	string	$name	Property name
	 * @return	mixed	Property value
	 * @ignore
	 */
	function __get ($name)
	{
		switch ($name) {
			case "doctype":
			case "documentElement":
				$length = $this->childNodes->length;
				$dc = $name === "doctype";
				$type = $dc ? 10 : 1;
				for ($i = 0; $i < $length; $i++) {
					$node = $this->childNodes[$i];
					if ($node->nodeType === $type &&
						($dc || $node->tagName === "html")) {
						return $node;
					}
				}
			break;
			case "head":
			case "body":
				$length = $this->documentElement->childNodes->length;
				for ($i = 0; $i < $length; $i++) {
					$node = $this->documentElement->childNodes[$i];
					if ($node->nodeType === 1 && 
						$node->tagName === $name) {
						return $node;
					}
				}
			break;
			case "applets":
			case "embeds":
			case "forms":
			case "images":
			case "plugins":
				$tags = array(
					"applets" => "applet",
					"embeds" => "embed",
					"plugins" => "embed",
					"forms" => "form",
					"images" => "img"
				);
				return $this->getElementsByTagName($tags[$name]);
			break;
			case "anchors":
			case "links":
				$search = new PAHDISearch($this);
				$attr = $name === "anchors" ? "name" : "href";
				$fn = function ($node) use ($attr) {
					return  $node->tagName === "a" &&
							$node->hasAttribute($attr);
				};
				return $search->find($fn)->toHTMLCollection();
			break;
			case "title":
				$title = $this->getElementsByTagName("title");
				if ($title->length) {
					return $title[0]->textContent;
				} else {
					return "";
				}
			break;
			case "URL":
				return $this->documentURI;
			break;
			case "bgColor":
			case "fgColor":
				return $this->body->$name;
			break;
			case "styleSheets":
				$search = new PAHDISearch($this);
				$fn = function ($node) {
					return  $node->tagName === "style" ||
							($node->tagName === "link" &&
							$node->rel === "stylesheet");
				};
				$search->find($fn);
				$list = new StyleSheetList;
				if ($search->length) {
					foreach ($search as $node) {
						$list->_appendNode($node->sheet);
					}
				}
				return $list;
			break;
			default:
				if ($ret = parent::__get($name)) {
					return $ret;
				} else {
					$search = new PAHDISearch($this);
					$fn = function ($node) use ($name) {
						return  $node->tagName === "form" &&
								$node->name === $name;
					};
					$search->find($fn, PAHDISearch::DESCENDANTS, 1, 1);
					return $search->length ? $search[0] : null;
				}
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
			case "doctype":
			case "documentElement":
			case "head":
			case "body":
			case "applets":
			case "embeds":
			case "forms":
			case "images":
			case "plugins":
			case "anchors":
			case "links":
			case "URL":
			case "styleSheets":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			case "bgColor":
			case "fgColor":
				return $this->body->$name = $value;
			break;
			case "title":
				$title = $this->getElementsByTagName("title");
				if ($title->length) {
					$title[0]->textContent = $value;
				} else {
					$tit = $this->createElement("title");
					$tit->textContent = $value;
					$this->head->appendChild($tit);
				}
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
	
	/**
	 * Finds the right class name for the given element
	 *
	 * @param	string		$namespace	Namespace
	 * @param	string		$tag		Tag name
	 * @return	string		Class name
	 * @access	protected
	 * @ignore
	 */
	protected function _findElementClass ($namespace, $tag)
	{
		if ($namespace === ParserHTML::HTML_NAMESPACE) {
			if (isset(ParserHTML::$HTMLTags[$tag])) {
				$class = ParserHTML::$HTMLTags[$tag];
				if ($class === "") {
					$class = "HTMLElement";
				}
			} else {
				$class = "HTMLUnknownElement";
			}
		} elseif ($namespace === ParserHTML::SVG_NAMESPACE) {
			if (isset(ParserHTML::$SVGTags[$tag]) &&
				ParserHTML::$SVGTags[$tag] !== "") {
				$class = ParserHTML::$SVGTags[$tag];
			} else {
				$class = "SVGElement";
			}
		} else {
			$class = "Element";
		}
		return $class;
	}
}