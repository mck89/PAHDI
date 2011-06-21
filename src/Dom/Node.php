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
 * A NodeList is a list of nodes in document order
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		mixed	$nodeValue			Node value
 * @property-read	Node	$firstChild			First child node
 * @property-read	Node	$lastChild			Last child node
 * @property-read	Node	$previousSibling	Previous sibling node
 * @property-read	Node	$nextSibling		Next sibling node
 * @property-read	string	$baseURI			Current base uri
 * @property		string	$textContent		Text content
 */
class Node extends DomObject
{
	/**
	 * Namespace URI
	 *
	 * @var		string
	 */
	public $namespaceURI;
	
	/**
	 * Child nodes list
	 *
	 * @var		NodeList
	 */
	public $childNodes;
		
	/**
	 * Attributes collection. This property is null
	 * on every node except elements.
	 *
	 * @var		NamedNodeMap
	 */
	public $attributes;
	
	/**
	 * Parent node
	 *
	 * @var		Node
	 */
	public $parentNode;
	
	/**
	 * Node type
	 *
	 * @var		int
	 */
	public $nodeType;
	
	/**
	 * Node name
	 *
	 * @var		string
	 */
	public $nodeName;
	
	/**
	 * Node local name
	 *
	 * @var		string
	 */
	public $localName;
	
	/**
	 * Node prefix
	 *
	 * @var		string
	 */
	public $prefix;
	
	/**
	 * Node owner document
	 *
	 * @var		HTMLDocument
	 */
	public $ownerDocument;
	
	//Document type constants
	/**
	 * Element nodes type
	 *
	 * @const		int
	 */
	const ELEMENT_NODE = 1;
	
	/**
	 * Attribute nodes type
	 *
	 * @const		int
	 */
	const ATTRIBUTE_NODE = 2;
	
	/**
	 * Text nodes type
	 *
	 * @const		int
	 */
	const TEXT_NODE = 3;
	
	/**
	 * CDATA nodes type
	 *
	 * @const		int
	 */
	const CDATA_SECTION_NODE = 4;
	
	/**
	 * Entity reference nodes type
	 *
	 * @const		int
	 */
	const ENTITY_REFERENCE_NODE = 5;
	
	/**
	 * Entity nodes type
	 *
	 * @const		int
	 */
	const ENTITY_NODE = 6;
	
	/**
	 * Processing instruction nodes type
	 *
	 * @const		int
	 */
	const PROCESSING_INSTRUCTION_NODE = 7;
	
	/**
	 * Comment nodes type
	 *
	 * @const		int
	 */
	const COMMENT_NODE = 8;
	
	/**
	 * Document nodes type
	 *
	 * @const		int
	 */
	const DOCUMENT_NODE = 9;
	
	/**
	 * Document type nodes type
	 *
	 * @const		int
	 */
	const DOCUMENT_TYPE_NODE = 10;
	
	/**
	 * Document fragment nodes type
	 *
	 * @const		int
	 */
	const DOCUMENT_FRAGMENT_NODE = 11;
	
	/**
	 * Notation nodes type
	 *
	 * @const		int
	 */
	const NOTATION_NODE = 12;
	
	//Document position constants
	/**
	 * Document position disconnected. 
	 * Useful when working with compareDocumentPosition.
	 *
	 * @const		int
	 */
	const DOCUMENT_POSITION_DISCONNECTED = 1;
	
	/**
	 * Document position preceding. 
	 * Useful when working with compareDocumentPosition.
	 *
	 * @const		int
	 */
	const DOCUMENT_POSITION_PRECEDING = 2;
	
	/**
	 * Document position following. 
	 * Useful when working with compareDocumentPosition.
	 *
	 * @const		int
	 */
	const DOCUMENT_POSITION_FOLLOWING = 4;
	
	/**
	 * Document position contains. 
	 * Useful when working with compareDocumentPosition.
	 *
	 * @const		int
	 */
	const DOCUMENT_POSITION_CONTAINS = 8;
	
	/**
	 * Document position contained by. 
	 * Useful when working with compareDocumentPosition.
	 *
	 * @const		int
	 */
	const DOCUMENT_POSITION_CONTAINED_BY = 16;
	
	/**
	 * Document position implementation specific. 
	 * Useful when working with compareDocumentPosition.
	 *
	 * @const		int
	 */
	const DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC = 32;
	
	/**
	 * Class constructor
	 */
	function __construct ()
	{
		$this->childNodes = new NodeList;
	}

	/**
	 * Appends the given node to the current one
	 *
	 * @param	Node	$child	Node to append
	 * @return	Node	Appended node
	 */
	function appendChild (Node $child)
	{
		//Attributes can be inserted
		if ($child instanceof Attr) {
			throw new DomException("Node cannot be inserted at the specified point in the hierarchy");
		}
		//If the node to add is a document fragment insert its child nodes 
		//instead of it and remove them from the fragment
		if ($child->nodeType === 11) {
			$i = $child->childNodes->length;
			while ($i) {
				$removed = $child->removeChild($child->childNodes[0]);
				$this->appendChild($removed);
				$i--;
			}
			return $child;
		}
		//Remove the child from its parent node before continue
		if ($child->parentNode) {
			$child->parentNode->removeChild($child);
		}
		$child->parentNode = $this;
		$this->childNodes->_appendNode($child);
		return $child;
	}
	
	/**
	 * Inserts the given node into the current one before
	 * the reference node
	 *
	 * @param	Node	$child		Node to insert
	 * @param	Node	$reference	Reference node
	 * @return	Node	Inserted node
	 */
	function insertBefore (Node $child, Node $reference)
	{
		//Attributes can be inserted
		if ($child instanceof Attr) {
			throw new DomException("Node cannot be inserted at the specified point in the hierarchy");
		}
		//If the node to add is a document fragment insert its child nodes 
		//instead of it and remove them from the fragment
		if ($child->nodeType === 11) {
			$i = $child->childNodes->length;
			while ($i) {
				$docChild = $child->removeChild($child->childNodes[0]);
				$this->insertBefore($docChild, $reference);
				$i--;
			}
			return $child;
		}
		//See if the current node contains the reference node
		$index = $this->_getChildNodeIndex($reference);
		//If the reference node is not contained into the current one
		//throw an exception
		if ($index === null) {
			throw new DomException("Node was not found");
		} else {
			//Remove the child from its parent node before continue
			if ($child->parentNode) {
				$child->parentNode->removeChild($child);
			}
			$child->parentNode = $this;
			$this->childNodes->_addNodeAt($child, $index);
		}
		return $child;
	}
	
	/**
	 * Removes the given child node from the current node
	 *
	 * @param	Node	$child	Child node to remove
	 * @return	Node	Removed node
	 */
	function removeChild (Node $child)
	{
		//See if the current node contains the node to remove
		$index = $this->_getChildNodeIndex($child);
		//If the reference node is not contained into the current one
		//throw an exception
		if ($index === null) {
			throw new DomException("Node was not found");
		} else {
			$child->parentNode = null;
			$this->childNodes->_removeNodeAt($index);
		}
		return $child;
	}
	
	/**
	 * Replaces a node with another in the child nodes array of the
	 * current node
	 *
	 * @param	Node	$child	Replacement node
	 * @param	Node	$old	Node to replace
	 * @return	Node	Replaced node
	 */
	function replaceChild (Node $child, Node $old)
	{
		$this->insertBefore($child, $old);
		return $this->removeChild($old);
	}
	
	/**
	 * Returns true if the given node is the same as the current one
	 *
	 * @param	Node	$node	Node to compare
	 * @return	bool	True if the given node is the same as the 
	 *					current one otherwise false
	 */
	function isSameNode ($node)
	{
		return $this === $node;
	}
	
	/**
	 * Returns true if the given node is the equal to the current one
	 *
	 * @param	Node	$node	Node to compare
	 * @return	bool	True if the given node is equal to 
	 *					current one otherwise false
	 */
	function isEqualNode (Node $node)
	{
		if ($this->nodeType !== $node->nodeType ||
			$this->nodeName !== $node->nodeName ||
			$this->localName !== $node->localName ||
			$this->prefix !== $node->prefix ||
			$this->namespaceURI !== $node->namespaceURI ||
			$this->nodeValue !== $node->nodeValue) {
			return false;
		}
		if ($this->nodeType === 10 &&
			($this->publicId !== $node->publicId ||
			$this->systemId !== $node->systemId)) {
			return false;
		}
		$nodeL = $node->attributes ? $node->attributes->length : null;
		$length = $this->attributes ? $this->attributes->length : null;
		if ($nodeL !== $length) {
			return false;
		}
		if ($length) {
			for ($i = 0; $i < $length; $i++) {
				$att = $this->attributes[$i];
				$ret = $node->attributes->getNamedItemNS(
					$att->namespaceURI,
					$att->localName
				);
				if (!$ret || ($ret->value !== $att->value)) {
					return false;
				}
			}
		}
		
		$nodeL = $node->childNodes->length;
		$length = $this->childNodes->length;
		if ($nodeL !== $length) {
			return false;
		}
		
		if ($length) {
			for ($i = 0; $i < $length; $i++) {
				$el = $this->childNodes[$i];
				if (!$el->isEqualNode($node->childNodes[$i])) {
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Returns true if the node has at least one attribute
	 *
	 * @return	bool	True if the given node has any attribute
	 *					otherwise false
	 */
	function hasAttributes ()
	{
		return 	$this->attributes && $this->attributes->length > 0 ?
				true :
				false;
	}
	
	/**
	 * Returns true if the node has at least one child node
	 *
	 * @return	bool	True if the given node has any child node
	 *					otherwise false
	 */
	function hasChildNodes ()
	{
		return $this->childNodes->length > 0;
	}
	
	/**
	 * Returns true if the given namespace is the default namespace 
	 * on the current node or false if not.
	 *
	 * @param	string	$ns		Namespace
	 * @return	bool	True if the given namespace is the default
	 *					namespace otherwise false
	 */
	function isDefaultNamespace ($ns)
	{
		switch ($this->nodeType) {
			case 1:
				if (!$this->prefix) {
					return $this->namespaceURI === $ns;
				}
				if ($this->attributes->length) {
					$xmlns = $this->attributes->getNamedItem("xmlns");
					if ($xmlns !== null) {
						return $xmlns === $ns;
					}
				}
				if ($this->parentNode) {
					return $this->parentNode->isDefaultNamespace($ns);
				}
			break;
			case 9:
				if ($this->documentElement) {
					return $this->documentElement->isDefaultNamespace($ns);
				}
			break;
			case 2:
				if ($this->ownerElement) {
					return $this->ownerElement->isDefaultNamespace($ns);
				}
			break;
			case 3:
			case 4:
			case 5:
			case 7:
			case 8:
				if ($this->parentNode) {
					return $this->parentNode->isDefaultNamespace($ns);
				}
			break;
		}
		return false;
	}
	
	/**
	 * Takes a prefix and returns the namespaceURI associated with
	 * it on the current node
	 *
	 * @param	string	$prefix		Prefix
	 * @return	mixed	NamespaceURI associated with the given prefix
	 *					on the current node or null if not found
	 */
	function lookupNamespaceURI ($prefix)
	{
		switch ($this->nodeType) {
			case 1:
				if ($this->namespaceURI !== null &&
					$this->prefix === $prefix) {
					return $this->namespaceURI;
				}
				if ($this->attributes->length) {
					foreach ($this->attributes as $at) {
						if (($at->name === "xmlns" &&
							!$prefix) ||
							(preg_match("#^xmlns:(.*)$#", $at->name, $match) &&
							$match[1] === $prefix)) {
							if ($at->value) {
								return $at->value;
							} else {
								return null;
							}
						}
					}
				}
				if ($this->parentNode) {
					return $this->parentNode->lookupNamespaceURI($prefix);
				}
			break;
			case 9:
				if ($this->documentElement) {
					return $this->documentElement->lookupNamespaceURI($prefix);
				}
			break;
			case 2:
				if ($this->ownerElement) {
					return $this->ownerElement->lookupNamespaceURI($prefix);
				}
			break;
			case 3:
			case 4:
			case 5:
			case 7:
			case 8:
				if ($this->parentNode) {
					return $this->parentNode->lookupNamespaceURI($prefix);
				}
			break;
		}
		return null;
	}
	
	/**
	 * Returns the prefix for the given namespaceURI on the current node
	 *
	 * @param	string	$ns		Namespace
	 * @return	mixed	Prefix for the given namespace or null if not
	 *					found
	 */
	function lookupPrefix ($ns)
	{
		if (!$ns) {
			return null;
		}
		switch ($this->nodeType) {
			case 1:
				if ($this->namespaceURI === $ns &&
					$this->prefix &&
					$this->lookupNamespaceURI($this->prefix) === $ns) {
					return $this->prefix;
				}
				if ($this->attributes->length) {
					foreach ($this->attributes as $at) {
						if ($at->value === $ns &&
							preg_match("#^xmlns:(.*)$#", $at->name, $match) &&
							$this->lookupNamespaceURI($match[1]) === $ns) {
							return $match[1];
						}
					}
				}
				if ($this->parentNode) {
					return $this->parentNode->lookupPrefix($prefix);
				}
			break;
			case 9:
				if ($this->documentElement) {
					return $this->documentElement->lookupPrefix($prefix);
				}
			break;
			case 2:
				if ($this->ownerElement) {
					return $this->ownerElement->lookupPrefix($prefix);
				}
			break;
			case 3:
			case 4:
			case 5:
			case 7:
			case 8:
				if ($this->parentNode) {
					return $this->parentNode->lookupPrefix($prefix);
				}
			break;
		}
		return null;
	}
	
	/**
	 * Normalizes the current node's subtree. When a node
	 * is normalized it does not contain any empty or adjacent
	 * text nodes
	 *
	 * @return	void
	 */
	function normalize ()
	{
		$length = $this->childNodes->length;
		if (!$length) {
			return;
		}
		$remove = array();
		for ($i = 0; $i < $length; $i++) {
			if ($this->childNodes[$i]->nodeType === 3) {
				$checkEmptyIndex = $i;
				//First check if there are adjacent text nodes
				if (isset($this->childNodes[$i + 1]) &&
					$this->childNodes[$i + 1]->nodeType === 3) {
					$checkEmptyIndex = $i;
					$data = "";
					//For each text node that has been found, append its data
					//to the current node and add its index to stack of elements
					//to remove
					do {
						$i++;
						$remove[] = $i;
						$data .= $this->childNodes[$i]->data;
					} while (isset($this->childNodes[$i + 1]) &&
							$this->childNodes[$i + 1]->nodeType === 3);
					$this->childNodes[$checkEmptyIndex]->appendData($data);
				}
				//Then check if the current node is empty, and if it is remove it
				if (!$this->childNodes[$checkEmptyIndex]->data) {
					$remove[] = $checkEmptyIndex;
				}
			} else {
				$this->childNodes[$i]->normalize();
			}
		}
		if (!count($remove)) {
			return;
		}
		rsort($remove);
		foreach ($remove as $i) {
			$this->removeChild($this->childNodes[$i]);
		}
	}
	
	/**
	 * Compare the position of the current node against the given one.
	 * This method follows Webkit implementation.
	 *
	 * @param	Node	$node		Comparison node
	 * @return	int		A bitmask of document position constants
	 */
	function compareDocumentPosition (Node $node)
	{
		//Check if both nodes are references of the same node
		if ($this->isSameNode($node)) {
			return null;
		}
		
		$nodeAtt = $node->nodeType === self::ATTRIBUTE_NODE;
		$isAtt = $this->nodeType === self::ATTRIBUTE_NODE;
		$nodeIsDoc = $node->nodeType === self::DOCUMENT_NODE;
		$isDoc = $this->nodeType === self::DOCUMENT_NODE;
		
		//Nodes from different documents or attributes node without
		//ownerElement are disconnected and implementation specific
		if (($nodeAtt && !$node->ownerElement) ||
			($isAtt && !$this->ownerElement) ||
			($nodeIsDoc && !$this->ownerDocument->isSameNode($node)) ||
			($isDoc && !$this->isSameNode($node->ownerDocument)) ||
			(!$nodeIsDoc && !$isDoc &&
			!$node->ownerDocument->isSameNode($this->ownerDocument))) {
			return	self::DOCUMENT_POSITION_DISCONNECTED |
					self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
		}
		
		if ($nodeAtt || $isAtt) {
			//If they are both attributes compare their ownerElement
			if ($nodeAtt && $isAtt) {
				//If the ownerElement is the same find the attribute that comes first
				if ($node->ownerElement->isSameNode($this->ownerElement)) {
					$count = $this->ownerElement->attributes;
					for ($i = 0; $i < $count; $i++) {
						if ($this->ownerElement->attributes[$i]->isSameNode($node)) {
							return 	self::DOCUMENT_POSITION_FOLLOWING |
									self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
						} elseif ($this->ownerElement->attributes[$i]->isSameNode($this)) {
							return 	self::DOCUMENT_POSITION_PRECEDING |
									self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
						}
					}
				}
				//Otherwise compare their ownerElement
				$ref = $this->ownerElement->compareDocumentPosition($node->ownerElement);
			}
			//If only one of them is an attribute compare its ownerElement to the other element
			elseif ($nodeAtt) {
				$ref = $this->compareDocumentPosition($node->ownerElement);
				if ($ref === null) {
					return 	self::DOCUMENT_POSITION_FOLLOWING |
							self::DOCUMENT_POSITION_CONTAINED_BY;
				}
			} else {
				$ref = $this->ownerElement->compareDocumentPosition($node);
				if ($ref === null) {
					return 	self::DOCUMENT_POSITION_PRECEDING |
							self::DOCUMENT_POSITION_CONTAINS;
				}
			}
			
			//Sanitize the result by removing the contains or contained by relations
			if ($ref & self::DOCUMENT_POSITION_CONTAINS) {
				$ref = $ref ^ self::DOCUMENT_POSITION_CONTAINS;
			} elseif ($ref & self::DOCUMENT_POSITION_CONTAINED_BY) {
				$ref = $ref ^ self::DOCUMENT_POSITION_CONTAINED_BY;
			}
			return $ref;
		}
		//If the parentNode is the same for both elements find which of them comes
		//first in the parentNode child nodes collection
		elseif ($node->parentNode && $node->parentNode->isSameNode($this->parentNode)) {
			$count = $this->parentNode->childNodes->length;
			for ($i = 0; $i < $count; $i++) {
				$child = $this->parentNode->childNodes[$i];
				if ($child->isSameNode($node)) {
					return self::DOCUMENT_POSITION_PRECEDING;
				} elseif ($child->isSameNode($this)) {
					return self::DOCUMENT_POSITION_FOLLOWING;
				}
			}
		} else {
			//Collect the given node's parents
			$nodeParents = array($node);
			$parent = $node->parentNode;
			while ($parent) {
				//In the meantime check if the node is in a fragment
				if ($parent->nodeType === self::DOCUMENT_FRAGMENT_NODE) {
					return 	self::DOCUMENT_POSITION_DISCONNECTED |
							self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
				}
				//Check if the current node is one of the given node's parents
				elseif ($parent->isSameNode($this)) {
					return 	self::DOCUMENT_POSITION_FOLLOWING |
							self::DOCUMENT_POSITION_CONTAINED_BY;
				}
				$nodeParents[] = $parent;
				$parent = $parent->parentNode;
			}
			
			//Collect the current node's parents
			$currentParents = array($this);
			$parent = $this->parentNode;
			while ($parent) {				
				if ($parent->nodeType === self::DOCUMENT_FRAGMENT_NODE) {
					return 	self::DOCUMENT_POSITION_DISCONNECTED |
							self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
				}
				//Check if the given node is one of the current node's parents
				elseif ($parent->isSameNode($node)) {
					return 	self::DOCUMENT_POSITION_PRECEDING |
							self::DOCUMENT_POSITION_CONTAINS;
				}
				$currentParents[] = $parent;
				$parent = $parent->parentNode;
			}
			
			
			$currentParents = array_reverse($currentParents);
			$nodeParents = array_reverse($nodeParents);
			
			//If there's at least one disconnected node and they come from
			//different trees they are disconnected
			if (($currentParents[0]->nodeType !== self::DOCUMENT_NODE ||
				$nodeParents[0]->nodeType !== self::DOCUMENT_NODE) &&
				!$currentParents[0]->isSameNode($nodeParents[0])) {
				return 	self::DOCUMENT_POSITION_DISCONNECTED |
						self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
			}
			
			//Otherwise compare the first different parents starting from the root
			$count = count($currentParents);
			for ($i = 0; $i < $count; $i++) {
				if (!$currentParents[$i]->isSameNode($nodeParents[$i])) {
					return $currentParents[$i]->compareDocumentPosition(
						$nodeParents[$i]
					);
				}
			}
		}
	}
	
	/**
	 * Returns a copy of the current node
	 *
	 * @param	bool	$deep	True to clone child nodes too or
	 *							false to clone only the current node
	 * @return	Node	Cloned node
	 */
	function cloneNode ($deep)
	{
		$ret = clone $this;
		$ret->parentNode = null;
		if ($this->attributes) {
			$ret->attributes = new NamedNodeMap($ret);
			$l = $this->attributes->length;
			for ($i = 0; $i < $l; $i++) {
				$attr = clone $this->attributes[$i];
				$attr->ownerElement = null;
				$ret->setAttributeNode($attr);
			}
		}
		$ret->childNodes = new NodeList;
		if ($deep) {
			$l = $this->childNodes->length;
			for ($i = 0; $i < $l; $i++) {
				$child = clone $this->childNodes[$i];
				$child->parentNode = null;
				$ret->appendChild($child);
			}
		}
		return $ret;
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
			case "nodeValue":
				if ($this instanceof CharacterData) {
					return $this->data;
				} elseif ($this instanceof Attr) {
					return $this->value;
				}
			break;
			case "firstChild":
			case "lastChild":
				$length = $this->childNodes->length;
				if ($length) {
					$index = $name === "firstChild" ? 0 : $length - 1;
					return $this->childNodes[$index];
				}
			break;
			case "nextSibling":
			case "previousSibling":
				if ($this->parentNode) {
					$ret = null;
					$length = $this->parentNode->childNodes->length;
					if ($length > 1) {
						$start = 0;
						$inc = 1;
						if ($name === "nextSibling") {						
							$start = $length - 1;
							$inc = - 1;
						}
						for ($i = $start; $i >= 0 && $i < $length; $i += $inc) {
							$node = $this->parentNode->childNodes[$i];
							if ($node->isSameNode($this)) {
								break;
							}
							$ret = $node;
						}
					}
					return $ret;
				}
			break;
			case "textContent":
				$ret = "";
				$length = $this->childNodes->length;
				if ($length) {
					for ($i = 0; $i < $length; $i++) {
						$node = $this->childNodes[$i];
						$type = $node->nodeType;
						if ($type === 3) {
							$ret .= $node->data;
						} elseif ($type === 1) {
							$ret .= $node->textContent;
						}
					}
				}
				return $ret;
			break;
			case "baseURI":
				if ($this->nodeType === self::DOCUMENT_NODE) {
					return $this->baseURI;
				}
				return $this->ownerDocument->baseURI;
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
			case "nodeValue":
				if ($this instanceof CharacterData) {
					$this->data = $value;
				} elseif ($this instanceof Attr) {
					$this->value = $value;
				}
			break;
			case "baseURI":
				if ($this->nodeType !== self::DOCUMENT_NODE) {
					$msg = "Setting a property that has only a getter";
					throw new DomException($msg);
				} else {
					$this->baseURI = $value;
				}
			break;
			case "firstChild":
			case "lastChild":
			case "nextSibling":
			case "previousSibling":
			case "baseURI":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			case "textContent":
				$this->_emptyNode();
				if ($this->nodeType === 9) {
					$text = $this->createTextNode($value);
				} else {
					$text = $this->ownerDocument->createTextNode($value);
				}
				$this->appendChild($text);
			break;
			default:
				$this->$name = $value;
			break;
		}
	}
		
	/**
	 * Remove every child node from the current one
	 *
	 * @return void
	 * @access	protected
	 * @ignore
	 */
	protected function _emptyNode ()
	{
		$length = $this->childNodes->length;
		if (!$length) {
			return;
		}
		while ($length) {
			$this->removeChild($this->childNodes[--$length]);
		}
	}
	
	/**
	 * Returns the index of the given node in the child nodes array
	 * of the current node
	 *
	 * @param	Node	$node	Node to search
	 * @return	int		Children index or null if the children is not
	 *					present in the child nodes array
	 * @access	protected
	 * @ignore
	 */
	protected function _getChildNodeIndex (Node $node)
	{
		$count = $this->childNodes->length;
		for ($i = 0; $i < $count; $i++) {
			if ($this->childNodes[$i]->isSameNode($node)) {
				return $i;
			}
		}
		return null;
	}
}