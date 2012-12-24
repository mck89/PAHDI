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
 * DOM document type class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 */
class DocumentType extends Node
{
	/**
	 * Node type
	 *
	 * @var		int
	 */
	public $nodeType = self::DOCUMENT_TYPE_NODE;
	
	/**
	 * Document type name
	 *
	 * @var		string
	 */
	public $name = "";
	
	/**
	 * Document type public id
	 *
	 * @var		string
	 */
	public $publicId = "";
	
	/**
	 * Document type system id
	 *
	 * @var		string
	 */
	public $systemId = "";
	
	/**
	 * Class constructor
	 *
	 * @param	string	$name	Node name
	 * @param	string	$public	Public id
	 * @param	string	$system	System id
	 */
	function __construct ($name, $public, $system)
	{
		parent::__construct();
		$this->name = $this->nodeName = strtoupper($name);
		$this->publicId = $public;
		$this->systemId = $system;
	}
	
	/**
	 * Appends the given node to the current one
	 *
	 * @param	Node	$child	Node to append
	 * @return	Node	Appended node
	 */
	function appendChild (Node $child)
	{
		throw new DomException("Node cannot be inserted at the specified point in the hierarchy");
	}
}