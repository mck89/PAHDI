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
 * CSS selectors parser. It follows the implementation at
 * http://www.w3.org/TR/css3-selectors/
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserSelector extends ParserSelectorBuilder
{
	/**
	 * Root node
	 *
	 * @var		Node
	 * @access	protected
	 */
	protected $_root;
	
	/**
	 * Class constructor. Sets the selector to parse.
	 *
	 * @param	string	$selector		Selector
	 * @param	Node	$root			Root node
	 */
	function __construct ($selector, $root)
	{
		parent::__construct(trim($selector));
		$this->_root = $root;
	}
	
	/**
	 * Starts the parsing process and returns the resulting node list
	 *
	 * @return	NodeList	Resulting NodeList
	 */
	function parse ()
	{
		$res = $this->_tokenize() ? $this->_build() : false;
		if (!$res) {
			throw new DomException("An invalid or illegal string was specified");
		}
		return $res;
	}
}