<?php
/**
 * This file is part of the PAHDI (PHP Advanced HTML Dom Implementation)
 * library, for the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @category    Utilities
 * @package     PAHDI-Utilities
 * @author      Marco Marchiò
 * @license     http://opensource.org/licenses/bsd-license.php The BSD License
 * @copyright	Copyright (c) 2011, Marco Marchiò
 */
 
/**
 * This class provides methods to search for elements in
 * a given DOM structure
 *
 * @category    	Utilities
 * @package     	PAHDI-Utilities
 * @property-read	int		$length		Number of current nodes
 */
class PAHDISearch implements ArrayAccess, IteratorAggregate
{		
	//Search type constants
	/**
	 * Search through current nodes descendants
	 *
	 * @const		int
	 */
	const DESCENDANTS = 1;
	
	/**
	 * Search through current nodes children
	 *
	 * @const		int
	 */
	const CHILDREN = 2;
	
	/**
	 * Search through current nodes next siblings
	 *
	 * @const		int
	 */
	const NEXT_SIBLINGS = 3;
	
	/**
	 * Search through current nodes previous siblings
	 *
	 * @const		int
	 */
	const PREVIOUS_SIBLINGS = 4;
	
	/**
	 * Search through current nodes siblings
	 *
	 * @const		int
	 */
	const SIBLINGS = 5;
	
	/**
	 * Search through current nodes ancestors
	 *
	 * @const		int
	 */
	const ANCESTORS = 6;
	
	/**
	 * Current nodes
	 *
	 * @var		array
	 * @access	protected
	 * @ignore
	 */
	protected $_nodes = array();
	
	/**
	 * Class constructor. Sets the root nodes.
	 *
	 * @param	mixed	$root	Root nodes. It can be a Node, an array
	 *							of nodes or an instance of NodeList or
	 *							PAHDISearch
	 */
	function __construct ($root)
	{
		$this->add($root);
	}
	
	/**
	 * Tests the given function on the nodes matched by the given search
	 * type starting from the current nodes and replace the current nodes
	 * array with the ones on which the function returns true if they are
	 * passed as argument.
	 *
	 * @param	function	$fn			Filter function. If it returns false
	 *									the node will be rejected otherwise it
	 *									will be accepted. The arguments passed
	 *									to this function will be the node to
	 *									analyze, its index, its index relative
	 *									to siblings of the same type, the
	 *									instance of the class and the index at
	 *									which the node that started the search
	 *									is stored in the class instance.
	 * @param	int			$type		Search type. It must be a search type
	 *									constant of this class.
	 * @param	int|array	$nodeTypes	Allowed node types. By default the
	 *									function will consider only elements,
	 *									but giving a node type or an array of
	 *									node types it will consider every node
	 *									whose type is one of the given. If null
	 *									every node will be considered.
	 * @param	int			$limit		Maximum number of results. Null for no limit
	 * @param	bool		$general	If this argument is false the limit argument
	 *									will be considered as a limit for the search
	 *									on the single node instead of a general limit.
	 *									For example if there are two current nodes,
	 *									the search type is PAHDISearch::CHILDREN,
	 *									the limit is 1 and both nodes have at least
	 *									a child that passes the filter function,
	 *									then if this parameter is true the result
	 *									will contain one node (first matching child
	 *									of the first node), if its false the result
	 *									will contain two nodes (first matching
	 *									child of the first node and first matching
	 *									child of the second node)
	 * @return	PAHDISearch	Current instance
	 */
	function find ($fn, $type = self::DESCENDANTS, $nodeTypes = 1, $limit = null, $general = true)
	{
		if (!is_array($nodeTypes)) {
			$nodeTypes = (array) $nodeTypes;
		}
		$nodeTypes = array_flip($nodeTypes);
		$count = count($this->_nodes);
		if ($count) {
			$result = array();
			$found = 0;
			for ($c = 0; $c < $count; $c++) {
				$node = $this->_nodes[$c];
				switch ($type) {
					//Descendants
					case self::DESCENDANTS:
						$this->_checkDescentants($node, $nodeTypes, $fn, $result, $found, $limit, $c);
					break;
					//Child nodes
					case self::CHILDREN:
						$length = $node->childNodes->length;
						$index = - 1;
						for ($i = 0; $i < $length; $i++) {
							$child = $node->childNodes[$i];
							if (!isset($nodeTypes[$child->nodeType])) {
								continue;
							}
							$index++;
							if (!call_user_func($fn, $child, $i, $index, $this, $c)) {
								continue;
							}
							$result[] = $child;
							$found++;
							if ($found === $limit) {
								break;
							}
						}
					break;
					//Next siblings
					case self::NEXT_SIBLINGS:
						list($ni, $index) = $this->_findNodeIndex($node, $nodeTypes);
						if ($ni !== null) {
							$sib = $node->parentNode->childNodes;
							$len = $sib->length;
							$inc = isset($nodeTypes[$node->nodeType]) ? 1 : 0;
							for ($i = $ni + $inc; $i < $len; $i++) {
								$child = $sib[$i];
								if (!isset($nodeTypes[$child->nodeType])) {
									continue;
								}
								$index++;
								if (!call_user_func($fn, $child, $i, $index, $this, $c)) {
									continue;
								}
								$result[] = $child;
								$found++;
								if ($found === $limit) {
									break;
								}
							}
						}
					break;
					//Previous siblings
					case self::PREVIOUS_SIBLINGS:
						if ($node->parentNode) {
							$sib = $node->parentNode->childNodes;
							$len = $sib->length;
							$index = - 1;
							for ($i = 0; $i < $len; $i++) {
								$child = $sib[$i];
								if ($child->isSameNode($node)) {
									break;
								}
								if (!isset($nodeTypes[$child->nodeType])) {
									continue;
								}
								$index++;
								if (!call_user_func($fn, $child, $i, $index, $this, $c)) {
									continue;
								}
								$result[] = $child;
								$found++;
								if ($found === $limit) {
									break;
								}
							}
						}
					break;
					//Siblings
					case self::SIBLINGS:
						if ($node->parentNode) {
							$sib = $node->parentNode->childNodes;
							$len = $sib->length;
							$index = - 1;
							for ($i = 0; $i < $len; $i++) {
								$child = $sib[$i];
								if (!isset($nodeTypes[$child->nodeType])) {
									continue;
								}
								$index++;
								if ($node->isSameNode($child) ||
									!call_user_func($fn, $child, $i, $index, $this, $c)) {
									continue;
								}
								$result[] = $child;
								$found++;
								if ($found === $limit) {
									break;
								}
							}
						}
					break;
					//Ancestors
					case self::ANCESTORS:
						$par = $node;
						while ($par = $par->parentNode) {
							if (!isset($nodeTypes[$par->nodeType])) {
								continue;
							}
							list($i, $index) = $this->_findNodeIndex($node, $nodeTypes);
							if (!call_user_func($fn, $par, $i, $index, $this, $c)) {
								continue;
							}
							$result[] = $par;
							$found++;
							if ($found === $limit) {
								break;
							}
						}
					break;
				}
				//If the limit is not a general limit then reset the results counter
				//othwerwise if the results counter has reached the limit exit the
				//loop
				if (!$general) {
					$found = 0;
				} elseif ($found === $limit) {
					break;
				}
			}
			$this->_nodes = $result;
		}
		return $this;
	}
	
	/**
	 * Filter the current nodes with the given function. If the function
	 * returns true the node will be preserved otherwise it will be removed.
	 *
	 * @param	function	$fn		Filter function
	 * @return	PAHDISearch	Current instance
	 */
	function filter ($fn)
	{
		if (count($this->_nodes)) {
			$filtered = array_filter($this->_nodes, $fn);
			$this->_nodes = array_values($filtered);
		}
		return $this;
	}
	
	/**
	 * Removes duplicates from the current nodes array
	 *
	 * @return	PAHDISearch	Current instance
	 */
	function unique ()
	{
		$l = count($this->_nodes);
		$remove = array();
		for ($i = 0; $i < $l - 1; $i++) {
			if (isset($remove[$i])) {
				continue;
			}
			$a = $this->_nodes[$i];
			for ($c = $i + 1; $c < $l; $c++) {
				if (!isset($remove[$c]) &&
					$this->_nodes[$c]->isSameNode($a)) {
					$remove[$c] = true;
				}
			}
		}
		if (count($remove)) {
			$keys = array_keys($remove);
			rsort($keys);
			foreach ($keys as $i) {
				$this->remove($i); 
			}
		}
		return $this;
	}
	
	/**
	 * Sorts the current nodes array with a user defined function
	 *
	 * @param	function	$fn		Sorting function. The two nodes
	 *								to compare are passed as arguments.
	 *								By default nodes are sorted in
	 *								document order. Look at usort
	 *								function documentation to know
	 *								which value the function should
	 *								return.
	 * @return	PAHDISearch	Current instance
	 */
	function sort ($fn = null)
	{
		if (!$fn) {
			$fn = function ($a, $b) {
				$p = $a->compareDocumentPosition($b);
				if ($p === null || $p & 32) {
					return 0;
				} elseif ($p & 4) {
					return - 1;
				} else {
					return 1;
				}
			};
		}
		usort($this->_nodes, $fn);
		return $this;
	}
	
	/**
	 * Adds the given nodes to the current nodes
	 *
	 * @param	mixed	$nodes	Node, array of nodes, instance
	 *							of NodeList or instance of
	 *							PAHDISearch to add to the current
	 *							nodes
	 * @return	PAHDISearch	Current instance
	 */
	function add ($nodes)
	{
		if ($nodes instanceof PAHDISearch) {
			$this->_nodes = array_merge($this->_nodes, $nodes->_nodes);
		} elseif ($nodes instanceof NodeList) {
			if ($nodes->length) {
				foreach ($nodes as $node) {
					$this->_nodes[] = $node;
				}
			}
		} elseif (is_array($nodes)) {
			$this->_nodes = array_merge($this->_nodes, $nodes);
		} elseif ($nodes instanceof Node) {
			$this->_nodes[] = $nodes;
		} else {
			throw new DomException("Invalid nodes");
		}
		return $this;
	}
	
	/**
	 * Removes the given node
	 *
	 * @param	mixed		$nodes	Index of the node to remove,
	 *								node to remove. It can be also
	 *								an array of nodes, an instance of
	 *								NodeList or PAHDISearch, in this
	 *								case every node that is contained
	 *								in the collection will be removed
	 * @return	PAHDISearch	Current instance
	 */
	function remove ($nodes) {
		if (is_numeric($nodes)) {
			array_splice($this->_nodes, $nodes, 1);
		} elseif ($nodes instanceof Node) {
			$count = count($this->_nodes);
			$remove = array();
			for ($i = 0; $i < $count; $i++) {
				if ($this->_nodes[$i]->isSameNode($nodes)) {
					$remove[] = $i;
				}
			}
			if (count($remove)) {
				rsort($remove);
				foreach ($remove as $r) {
					$this->remove($r);
				}
			}
		} elseif ((is_array($nodes) && count($nodes)) ||
				(($nodes instanceof NodeList ||
				$nodes instanceof PAHDISearch) && $nodes->length)) {
			foreach ($nodes as $node) {
				$this->remove($node);
			}
		}
		return $this;
	}
	
	/**
	 * Removes all the current nodes
	 *
	 * @return	PAHDISearch	Current instance
	 */
	function removeAll ()
	{
		$this->_nodes = array();
		return $this;
	}
	
	/**
	 * Returns the current nodes as a simple array
	 *
	 * @return	array	Current nodes
	 */
	function toArray ()
	{
		return $this->_nodes;
	}
	
	/**
	 * Returns the current nodes as an HTMLCollection
	 *
	 * @return	HTMLCollection		Resulting collection
	 */
	function toHTMLCollection ()
	{
		$c = new HTMLCollection;
		$c->_merge($this->_nodes);
		return $c;
	}
	
	/**
	 * Returns the current nodes as a NodeList
	 *
	 * @return	NodeList		Resulting list
	 */
	function toNodeList ()
	{
		$c = new NodeList;
		$c->_merge($this->_nodes);
		return $c;
	}
	
	/**
	 * Returns the index of the given node in its parent
	 * node child nodes collection
	 *
	 * @param	Node	$node	Node to test
	 * @param	array	$types	Allowed node types
	 * @return	array	Array containing the index of the
	 *					node and its index relative to the
	 *					allowed node types. Both null if
	 *					the node has no parent node
	 * @access	protected
	 * @ignore
	 */
	protected function _findNodeIndex ($node, $types)
	{
		if (!$node->parentNode) {
			return array(null, null);
		}
		$sib = $node->parentNode->childNodes;
		$tInd = 0;
		for ($i = 0, $l = $sib->length; $i < $l; $i++) {
			if ($sib[$i]->isSameNode($node)) {
				return array($i, $tInd);
			}
			if (isset($types[$sib[$i]->nodeType])) {
				$tInd++;
			}
		}
	}
	
	/**
	 * Check the descendants nodes of the given node
	 *
	 * @param	Node		$node	Node to check
	 * @param	array		$types	Allowed node types array
	 * @param	function	$fn		Filter function
	 * @param	array		$result	Result array
	 * @param	int			$found	Number of results
	 * @param	int			$limit	Maximum number of results
	 * @param	int			$c		The currently processed node key
	 * @return	bool		True if the limit hasn't been reached
	 *						otherwise false
	 * @ignore
	 */
	protected function _checkDescentants ($node, $types, $fn, & $result, & $found, $limit, $c)
	{
		$length = $node->childNodes->length;
		$index = - 1;
		for ($i = 0; $i < $length; $i++) {
			$child = $node->childNodes[$i];
			if (isset($types[$child->nodeType]) &&
				call_user_func($fn, $child, $i, ++$index, $this, $c)) {
				$result[] = $child;
				$found++;
			}
			if ($found === $limit ||
				!$this->_checkDescentants($child, $types, $fn, $result, $found, $limit, $c)) {
				return false;
			}
		}
		return true;
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
		if ($name === "length") {
			return count($this->_nodes);
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
			case "length":
				throw new DomException("Setting a property that has only a getter");
			break;
			default:
				$this->$name = $value;
			break;
		}
	}
	
	/**
	 * Checks if the given offset exists (used to make the class
	 * compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	bool	True if it exists otherwise false
	 * @ignore
	 */
	function offsetExists ($offset)
	{
		return isset($this->_nodes[$offset]);
	}
	
	/**
	 * Sets the item at the given offset with the given 
	 * value (used to make the class compatible with the
	 * the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @param	int		$value		Value to assign
	 * @return	void
	 * @ignore
	 */
	function offsetSet ($offset, $value)
	{
		if (isset($this->_nodes[$offset])) {
			$this->_nodes[$offset] = $value;
		}
	}
	
	/**
	 * Returns the item at the given offset (used to make the
	 * class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	mixed	Item or false if it's not found
	 * @ignore
	 */
	function offsetGet ($offset)
	{
		return $this->_nodes[$offset];
	}
	
	/**
	 * Deletes the item at the given offset (used to make the
	 * class compatible with the ArrayAccess interface).
	 *
	 * @param	int		$offset		Offset
	 * @return	void
	 * @ignore
	 */
	function offsetUnset ($offset)
	{
		$this->remove($offset);
	}
	
	/**
	 * Returns the object iterator (used to make the class
	 * compatible with the IteratorAggregate interface).
	 *
	 * @return	object	Iterator
	 * @ignore
	 */
	function getIterator ()
	{
		return new ArrayIterator($this->_nodes);
    }
}