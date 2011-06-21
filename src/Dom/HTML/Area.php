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
 * DOM HTML Area element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$alt		Element's alt text
 * @property		string		$href		Element's href
 * @property		string		$target		Element's target
 * @property		string		$accessKey	Element's access key
 * @property		string		$coords		Element's map coords
 * @property		string		$shape		Element's map shape
 * @property		string		$ping		Element's ping urls
 * @property		bool		$noHref		Element's nohref state
 * @property		string		$protocol	Element's url protocol
 * @property		string		$host		Element's url host
 * @property		string		$hostname	Element's url host
 * @property		string		$port		Element's url port
 * @property		string		$pathname	Element's url path
 * @property		string		$search		Element's url query
 * @property		string		$hash		Element's url fragment
 */
class HTMLAreaElement extends HTMLElement
{
	/**
	 * Map of url parts
	 *
	 * @var		array
	 * @access	protected
	 * @static
	 * @ignore
	 */
	protected static $_urlParts = array(
		"protocol" => "scheme",
		"host" => "host",
		"hostname" => "host",
		"port" => "port",
		"pathname" => "path",
		"search" => "query",
		"hash" => "fragment"
	);
	
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
			case "href":
				return $this->_getProperty($name, "path");
			break;
			case "alt":
			case "target":
			case "accessKey":
			case "coords":
			case "shape":
				return $this->_getProperty($name);
			break;
			case "ping":
				return $this->_getProperty($name, "pathlist");
			break;
			case "noHref":
				return $this->_getProperty($name, "bool");
			break;
			case "host":
			case "hostname":
			case "protocol":
			case "port":
			case "pathname":
			case "search":
			case "hash":
				if (!$this->hasAttribute("href")) {
					return "";
				}
				$url = PAHDIPath::getURLInstance($this->href);
				$prop = self::$_urlParts[$name];
				$ret = $url->$prop;
				if ($ret === false) {
					$ret = $name === "protocol" ? "http" : "";
				}
				if ($name === "protocol") {
					$ret .= ":";
				} elseif ($name === "search" && $ret) {
					$ret = "?$ret";
				} elseif ($name === "hash" && $ret) {
					$ret = "#$ret";
				}
				return $ret;
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
			case "alt":
			case "href":
			case "target":
			case "accessKey":
			case "coords":
			case "shape":
			case "ping":
				$this->_setProperty($name, $value);
			break;
			case "noHref":
				$this->_setProperty($name, $value, "bool");
			break;
			case "host":
			case "hostname":
			case "protocol":
			case "port":
			case "pathname":
			case "search":
			case "hash":
				if (!$this->hasAttribute("href")) {
					return;
				}
				$url = PAHDIPath::getURLInstance($this->href);
				$prop = self::$_urlParts[$name];
				if ($name === "protocol") {
					$value = preg_replace("#:$#", "", $value);
				} elseif ($name === "search") {
					$value = preg_replace("#^\?#", "", $value);
				} elseif ($name === "hash") {
					$value = preg_replace("@^#@", "", $value);
				}
				$url->$prop = $value;
				$this->href = $url->getURL();
			break;			
			default:
				parent::__set($name, $value);
			break;
		}
	}
}