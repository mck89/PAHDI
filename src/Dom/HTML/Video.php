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
 * DOM HTML Video element class
 *
 * @category    	PAHDI
 * @package     	PAHDI-DOM
 * @property		string		$src		Element's source
 * @property		bool		$autoplay	Element's autoplay state
 * @property		bool		$autobuffer	Element's autobuffer state
 * @property		bool		$controls	Element's controls state
 * @property		string		$poster		Element's poster image
 * @property		bool		$muted		Element's muted state
 */
class HTMLVideoElement extends HTMLElement
{
	/**
	 * Audio volume
	 *
	 * @var		int
	 */
	public $volume = 1;
	
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
			case "src":
			case "poster":
				return $this->_getProperty($name, "path");
			break;
			case "autoplay":
			case "autobuffer":
			case "controls":
				return $this->_getProperty($name, "bool");
			break;
			case "muted":
				return $this->volume === 0;
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
			case "src":
			case "poster":
				$this->_setProperty($name, $value);
			break;
			case "autoplay":
			case "autobuffer":
			case "controls":
				$this->_setProperty($name, $value, "bool");
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}
}