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
 * Node filter class. This class is used in conjunction with
 * NodeIterator and TreeWalker.
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @abstract
 */
abstract class NodeFilter extends DomObject
{
	/**
	 * Filter constant. Accept the node.
	 *
	 * @const		int
	 */
	const FILTER_ACCEPT = 1;
	
	/**
	 * Filter constant. Rejects the node. For node iterators
	 * this is the same as FILTER_SKIP, while tree walkers
	 * ignore also its sub-tree if this is returned
	 *
	 * @const		int
	 */
	const FILTER_REJECT = 2;
	
	/**
	 * Filter constant. Rejects the node
	 *
	 * @const		int
	 */
	const FILTER_SKIP = 3;

	/**
	 * "What to show" constant. Get every node
	 *
	 * @const		int
	 */
	const SHOW_ALL = 0xFFFFFFFF;
	
	/**
	 * "What to show" constant. Get element nodes
	 *
	 * @const		int
	 */
	const SHOW_ELEMENT = 0x00000001;
	
	/**
	 * "What to show" constant. Get attribute nodes
	 *
	 * @const		int
	 */
	const SHOW_ATTRIBUTE = 0x00000002;
	
	/**
	 * "What to show" constant. Get text nodes
	 *
	 * @const		int
	 */
	const SHOW_TEXT = 0x00000004;
	
	/**
	 * "What to show" constant. Get cdata nodes
	 *
	 * @const		int
	 */
	const SHOW_CDATA_SECTION = 0x00000008;
	
	/**
	 * "What to show" constant. Get entity reference nodes
	 *
	 * @const		int
	 */
	const SHOW_ENTITY_REFERENCE = 0x00000010;
	
	/**
	 * "What to show" constant. Get entity nodes
	 *
	 * @const		int
	 */
	const SHOW_ENTITY = 0x00000020;
	
	/**
	 * "What to show" constant. Get processing
	 * instruction nodes
	 *
	 * @const		int
	 */
	const SHOW_PROCESSING_INSTRUCTION = 0x00000040;
	
	/**
	 * "What to show" constant. Get comment nodes
	 *
	 * @const		int
	 */
	const SHOW_COMMENT = 0x00000080;
	
	/**
	 * "What to show" constant. Get document nodes
	 *
	 * @const		int
	 */
	const SHOW_DOCUMENT = 0x00000100;
	
	/**
	 * "What to show" constant. Get document type nodes
	 *
	 * @const		int
	 */
	const SHOW_DOCUMENT_TYPE = 0x00000200;
	
	/**
	 * "What to show" constant. Get document fragment nodes
	 *
	 * @const		int
	 */
	const SHOW_DOCUMENT_FRAGMENT = 0x00000400;
	
	/**
	 * "What to show" constant. Get notation nodes.
	 *
	 * @const		int
	 */
	const SHOW_NOTATION = 0x00000800;
	
	/**
	 * Filter function. This function is used by NodeIterator
	 * and tree walker if the class instance is passed as filter.
	 * It must returns one of the filter constants of this class.
	 *
	 * @param	Node	$node	Node to test
	 * @return	int		Filter constant
	 * @abstract
	 */
	abstract function acceptNode(Node $node);
}