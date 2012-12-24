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
 * DOM document fragment class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class DocumentFragment extends Node
{
	/**
	 * Node type
	 *
	 * @var		int
	 */
	public $nodeType = self::DOCUMENT_FRAGMENT_NODE;
	
	/**
	 * Node name
	 *
	 * @var		string
	 */
	public $nodeName = "#document-fragment";
	
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
}