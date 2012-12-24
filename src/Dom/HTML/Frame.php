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
 * DOM HTML Frame element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string			$name				Element's name
 * @property		string			$src				Element's source
 * @property		string			$longDesc			Element's longDesc
 * @property		bool			$noResize			Element's no resize state
 * @property		string			$frameBorder		Element's frame border
 * @property		string			$marginHeight		Element's margin height
 * @property		string			$marginWidth		Element's margin width
 * @property		string			$scrolling			Element's scrolling
 * @property		HTMLDocument	$contentDocument	Content document
 */
class HTMLFrameElement extends HTMLElement
{
	/**
	 * Content document
	 *
	 * @var		HTMLDocument
	 * @access	protected
	 * @ignore
	 */
	protected $_contentDocument;
	
	/**
	 * Content document path
	 *
	 * @var		string
	 * @access	protected
	 * @ignore
	 */
	protected $_contentDocPath;
	
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
			case "longDesc":
			case "src":
				return $this->_getProperty($name, "path");
			break;
			case "name":
			case "frameBorder":
			case "marginHeight":
			case "marginWidth":
			case "scrolling":
				return $this->_getProperty($name);
			break;
			case "contentDocument":
				$src = $this->src;
				if ($src === null) {
					$this->_contentDocument = null;
				} elseif (!$this->_contentDocument ||
						$this->_contentDocPath !== $src) {
					$options = $this->ownerDocument->_implementation;
					//If it's a data uri parse its content otherwise
					//parse the url or the path
					if (PAHDIPath::isDataURI($src)) {
						$data = PAHDIPath::getDataURIParts($src);
						$this->_contentDocument = PAHDI::parseString(
							$data["data"],
							$options
						);
					} elseif (PAHDIPath::isURL($src)) {
						$this->_contentDocument = PAHDI::parseRemoteSource(
							$src,
							$options
						);
					} else {
						$this->_contentDocument = PAHDI::parseLocalSource(
							$src,
							$options
						);
					}
					$this->_contentDocPath = $src;
				}
				return $this->_contentDocument;
			break;
			default:
				if ($name === "noResize" && $this->tagName === "frame") {
					return $this->_getProperty($name, "bool");
				}
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
			case "src":
			case "name":
			case "longDesc":
			case "frameBorder":
			case "marginHeight":
			case "marginWidth":
			case "scrolling":
				$this->_setProperty($name, $value);
			break;
			case "contentDocument":
				$msg = "Setting a property that has only a getter";
				throw new DomException($msg);
			break;
			default:
				if ($name === "noResize" && $this->tagName === "frame") {
					$this->_setProperty($name, $value, "bool");
				} else {
					parent::__set($name, $value);
				}
			break;
		}
	}
}