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
 * DOM SVG element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property-read	SVGSVGElement	$ownerSVGElement	Nearest ancestor SVG
 * @property-read	SVGSVGElement	$viewportElement	The element which established
 *														the current viewport.
 */
class SVGElement extends Element
{
	/**
	 * Namespace uri
	 *
	 * @var		string
	 */
	public $namespaceURI = ParserHTML::SVG_NAMESPACE;
	
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
			case "ownerSVGElement":
				if ($this->tagName === "svg" || !$this->parentNode) {
					return null;
				} elseif ($this->parentNode->nodeType === self::ELEMENT_NODE &&
					$this->parentNode->tagName === "svg") {
					return $this->parentNode;
				} else {
					return $this->parentNode->ownerSVGElement;
				}
			break;
			case "viewportElement":
				return $this->ownerSVGElement;
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
			case "ownerSVGElement":
			case "viewportElement":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}