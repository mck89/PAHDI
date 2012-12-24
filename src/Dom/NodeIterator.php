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
 * The Node Iterator class allows you to iterate over the members of a list
 * of the nodes in a subtree of the DOM, in document order.
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		Node	$referenceNode		Reference to the node from which
 *												the iteration will start searching
 *												when one of its methods will be called
 * @property		Node	$root				Root node
 * @property		int		$whatToShow			Integer that describes which type of
 *												nodes to consider
 * @property		Closure	$filter				Filter
 * @property		bool	pointerBeforeReferenceNode	A boolean value that indicates
 *												whether the NodeIterator is anchored
 *												before or after the referenceNode
 */
class NodeIterator extends DomObject
{
	/**
	 * Root node
	 *
	 * @var		Node
	 * @access	protected
	 * @ignore
	 */
	protected $_root;
	
	/**
	 * Integer that describes which type of nodes to consider
	 *
	 * @var		int
	 * @access	protected
	 * @ignore
	 */
	protected $_whatToShow;
	
	/**
	 * Filter
	 *
	 * @var		mixed
	 * @access	protected
	 * @ignore
	 */
	protected $_filter;
	
	/**
	 * Filter function
	 *
	 * @var		Closure
	 * @access	protected
	 * @ignore
	 */
	protected $_filterFn;
	
	/**
	 * Reference node
	 *
	 * @var		Node
	 * @access	protected
	 * @ignore
	 */
	protected $_referenceNode;
	
	/**
	 * A boolean value that indicates whether the NodeIterator
	 * is anchored before or after the referenceNode
	 *
	 * @var		bool
	 * @access	protected
	 * @ignore
	 */
	protected $_pointerBeforeReferenceNode = true;
	
	/**
	 * Boolean that indicates if the iterator is detached
	 *
	 * @var		bool
	 * @access	protected
	 * @ignore
	 */
	protected $_isDetached = false;
	
	/**
	 * Class constructor
	 *
	 * @param	Node	$root		Root node. Node from which the iteration will
	 *								start.
	 * @param	int		$whatToShow	A sum of NodeFilter "what to show" constants
	 *								that specify which type of nodes should be
	 *								considered in the iteration. If
	 *								NodeFilter::SHOW_ALL is used, you can't add
	 *								other constants.
	 * @param	mixed	$filter		Closure, function name or any object with a
	 *								method named 'acceptNode'. This function
	 *								receives the node as argument and it must
	 *								return a filter constant from the NodeFilter
	 *								class. It must return
	 *								NodeFilter::FILTER_ACCEPT if the node
	 *								must be included in the iteration.
	 */
	function __construct (Node $root, $whatToShow, $filter)
	{
		$this->_root = $this->_referenceNode = $root;
		$this->_whatToShow = $whatToShow;
		$this->_filter = $filter;
		if ($filter instanceof Closure ||
			(is_string($filter) && function_exists($filter))) {
			$this->_filterFn = $filter;
		} elseif (is_object($filter) &&
				method_exists($filter, "acceptNode")) {
			$this->_filterFn = function ($node) use ($filter) {
				return $filter->acceptNode($node);
			};
		} else {
			$this->_filterFn = function () {
				return NodeFilter::FILTER_ACCEPT;
			};
		}
	}
	
	/**
	 * Return the next node in the iteration process
	 *
	 * @return	mixed	Next node or null if there are
	 *					no next nodes
	 */
	function nextNode ()
	{
		if ($this->_isDetached) {
			$msg =  "An attempt was made to use an object " .
					"that is not, or is no longer, usable";
			throw new DomException($msg);
		}
		$node = $this->_referenceNode;
		$isRoot = $node->isSameNode($this->_root);
		if ($this->_pointerBeforeReferenceNode) {
			$this->_pointerBeforeReferenceNode = false;
			if ($this->_test($node)) {
				return $node;
			}
		}
		$ret = $this->_checkChildNodes($node);
		if (!$ret && !$isRoot) {
			$ret = $this->_checkNextSiblings($node->nextSibling);
		}
		if (!$ret && !$isRoot) {
			$par = $node->parentNode;
			while ($par && !$par->isSameNode($this->root)) {
				if ($ret = $this->_checkNextSiblings($par->nextSibling)) {
					break;
				}
				$par = $par->parentNode;
			}
		}
		if ($ret) {
			$this->_referenceNode = $ret;
		}
		return $ret;
	}
	
	/**
	 * Return the previous node in the iteration process
	 *
	 * @return	mixed	Previous node or null if there are
	 *					no previous nodes
	 */
	function previousNode ()
	{
		if ($this->_isDetached) {
			$msg =  "An attempt was made to use an object " .
					"that is not, or is no longer, usable";
			throw new DomException($msg);
		}
		$node = $this->_referenceNode;
		if ($this->_pointerBeforeReferenceNode ||
			$node->isSameNode($this->_root)) {
			$this->_pointerBeforeReferenceNode = true;
			return null;
		}
		$ret = $this->_checkPreviousSiblings($node->previousSibling);
		if (!$ret) {
			$par = $node->parentNode;
			while ($par) {
				if ($this->_test($par)) {
					$ret = $par;
					break;
				}
				if ($par->isSameNode($this->root) ||
					$ret = $this->_checkPreviousSiblings($par->previousSibling)) {
					break;
				}
				$par = $par->parentNode;
			}
		}
		if ($ret) {
			$this->_referenceNode = $ret;
		}
		return $ret;
	}
	
	/**
	 * Detaches the node iterator
	 *
	 * @return	void
	 */
	function detach ()
	{
		$this->_isDetached = true;
	}
	
	/**
	 * Checks if the given node passes the filter function
	 *
	 * @param	Node	$node	Node to test
	 * @return	bool	Test result
	 * @access	protected
	 * @ignore
	 */
	protected function _test ($node)
	{
		$type = $node->nodeType;
		$filter = $this->_filterFn;
		return  ($this->_whatToShow === NodeFilter::SHOW_ALL ||
				(($this->_whatToShow % ($type * 2)) >= $type)) &&
				$filter($node) === NodeFilter::FILTER_ACCEPT;
	}
	
	/**
	 * Checks the given node's children
	 *
	 * @param	Node	$node	Node to test
	 * @return	mixed	The node found or null if nothing has
	 *					been found
	 * @access	protected
	 * @ignore
	 */
	protected function _checkChildNodes ($node)
	{
		for ($i = 0, $l = $node->childNodes->length; $i < $l; $i++) {
			if ($this->_test($node->childNodes[$i])) {
				return $node->childNodes[$i];
			}
			$accepted = $this->_checkChildNodes($node->childNodes[$i]);
			if ($accepted) {
				return $accepted;
			}
		}
		return null;
	}
	
	/**
	 * Checks the given node's children in the reverse order
	 *
	 * @param	Node	$node	Node to test
	 * @return	mixed	The node found or null if nothing has
	 *					been found
	 * @access	protected
	 * @ignore
	 */
	protected function _checkChildNodesReverse ($node)
	{
		for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
			$child = $node->childNodes[$i];
			if ($ret = $this->_checkChildNodesReverse($child)) {
				return $ret;
			} elseif ($this->_test($child)) {
				return $child;
			}
		}
		return null;
	}
	
	/**
	 * Checks the given node and its next siblings
	 *
	 * @param	Node	$node	Node to test
	 * @return	mixed	The node found or null if nothing has
	 *					been found
	 * @access	protected
	 * @ignore
	 */
	protected function _checkNextSiblings ($node)
	{
		if (!$node) {
			return null;
		} elseif ($this->_test($node)) {
			return $node;
		} elseif ($ret = $this->_checkChildNodes($node)) {
			return $ret;
		}
		return $this->_checkNextSiblings($node->nextSibling);
	}
	
	/**
	 * Checks the given node and its previous siblings
	 *
	 * @param	Node	$node	Node to test
	 * @return	mixed	The node found or null if nothing has
	 *					been found
	 * @access	protected
	 * @ignore
	 */
	protected function _checkPreviousSiblings ($node)
	{
		if (!$node) {
			return null;
		} elseif ($ret = $this->_checkChildNodesReverse($node)) {
			return $ret;
		} elseif ($this->_test($node)) {
			return $node;
		}
		return $this->_checkPreviousSiblings($node->previousSibling);
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
		$key = "_$name";
		if (isset($this->$key)) {
			return $this->$key;
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
	function __set ($name, $val)
	{
		$notSettable = array(
			"referenceNode", "root", "filter",
			"whatToShow", "pointerBeforeReferenceNode"
		);
		if (!in_array($name, $notSettable)) {
			$this->$name = $val;
		}
	}
}