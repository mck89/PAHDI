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
 * DOM HTML Iframe element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$align		Element's alignment
 * @property		string		$width		Element's width
 * @property		string		$height		Element's height
 * @property		string		$sandbox	Element's sandbox attribute
 * @property		string		$srcdoc		Iframe document source
 */
class HTMLIFrameElement extends HTMLFrameElement
{
	/**
	 * Last value of srcdoc
	 *
	 * @var		string
	 * @access	protected
	 * @ignore
	 */
	protected $_lastSrcDoc;
	
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
			case "align":
			case "srcdoc":
				return $this->_getProperty($name);
			break;
			case "width":
			case "height":
				return $this->_getProperty($name, "intperc");
			break;
			case "sandbox":
				return $this->_getProperty($name, "bool");
			break;
			case "contentDocument":
				$srcdoc = $this->getAttribute("srcdoc");
				if ($srcdoc !== null) {
					if ($this->_lastSrcDoc !== $srcdoc) {
						$parser = new ParserHTML($srcdoc);
						$this->_contentDocument = $parser->parseSrcDoc();
					}
					return $this->_contentDocument;
				} else {
					return parent::__get($name);
				}
				$this->_lastSrcDoc = $srcdoc;
			break;
			default:
				return parent::__get($name);
			break;
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
	function __set ($name, $value)
	{
		switch ($name) {
			case "align":
			case "srcdoc":
				$this->_setProperty($name, $value);
			break;
			case "width":
			case "height":
				$this->_setProperty($name, $value, "intperc");
			break;
			case "sandbox":
				$this->_setProperty($name, $value, "bool");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}