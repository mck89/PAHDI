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
 * @copyright	Copyright (c) 2013, Marco Marchiò
 */
 
/**
 * Url parsing class as described in RFC 3986 spec
 *
 * @category    	Utilities
 * @package     	PAHDI-Utilities
 * @property-read	int		$length		Number of current nodes
 */
class PAHDIUrl
{
	/**
	 * Url scheme
	 *
	 * @var		string
	 */
	public $scheme;
	
	/**
	 * Url host
	 *
	 * @var		string
	 */
	public $host;
	
	/**
	 * Url port
	 *
	 * @var		string
	 */
	public $port;
	
	/**
	 * Url username
	 *
	 * @var		string
	 */
	public $user;
	
	/**
	 * Url password
	 *
	 * @var		string
	 */
	public $pass;
	
	/**
	 * Url path
	 *
	 * @var		string
	 */
	public $path;
	
	/**
	 * Url query string
	 *
	 * @var		string
	 */
	public $query;
	
	/**
	 * Url fragment
	 *
	 * @var		string
	 */
	public $fragment;

	/**
	 * Class constructor. Sets the url.
	 *
	 * @param	string		$url	Url to partse
	 */
	function __construct ($url)
	{
		$parts = parse_url($url);
		if ($parts) {
			foreach ($parts as $k => $v) {
				$this->$k = $v;
			}
		}
	}
	
	/**
	 * Checks if the url is absolute
	 *
	 * @return	bool	True if the url is absolute otherwise
	 *					false
	 */
	function isAbsolute ()
	{
		return $this->scheme !== null;
	}
	
	/**
	 * Returns the authority part of the url
	 *
	 * @return	string		Authority
	 */
	function getAuthority ()
	{
		$ret = "";
		//Continue only if the host is set
		if ($this->host !== null) {
			//Generate user information part
			if ($this->user !== null || $this->pass !== null) {
				$ret .= "{$this->user}:{$this->pass}@";
			}
			//Add the host
			$ret .= $this->host;
			//Add the port
			if ($this->port !== null) {
				$ret .= ":{$this->port}";
			}
		}
		return $ret;
	}
	
	/**
	 * Sets the authority part of the url
	 *
	 * @param	string		$auth	Authority
	 */
	function setAuthority ($auth)
	{
		//Match the authority parts
		if (preg_match("#^([^@]*@)?([^:]+)(:\d*)?$#", $auth, $match)) {
			//Unset optional properties
			$this->user = $this->pass = $this->port = null;
			//Set user information if given
			if ($match[1]) {
				//Remove the trailing @
				$userinfo = substr($match[1], 0, - 1);
				list ($this->user, $this->pass) = explode(":", $userinfo);
			}
			//Set the host
			$this->host = $match[2];
			//Set the port if given
			if (isset($match[3])) {
				$this->port = substr($match[3], 1);
			}
		}
	}
	
	/**
	 * Returns the url as a string. 
	 *
	 * @return	string		Url
	 */
	function getUrl ()
	{
		$ret = "";
		//Add the scheme
		if ($this->scheme !== null) {
			$ret .= "{$this->scheme}:";
		}
		//Add the authority part
		$auth = $this->getAuthority();
		if ($auth) {
			$ret .= "//$auth";
		}
		//Add the path
		$ret .= $this->path;
		//Add the query string
		if ($this->query !== null) {
			$ret .= "?{$this->query}";
		}
		//Add the fragment
		if ($this->fragment !== null) {
			$ret .= "#{$this->fragment}";
		}
		return $ret;
	}
	
	/**
	 * Resolves an url relative to the current one
	 *
	 * @param	string|PAHDIUrl		$relative	Url to resolve
	 * @return	PAHDIUrl|bool		Resulting url or false on error
	 */
	function resolve ($relative)
	{
		if (!$relative instanceof PAHDIUrl) {
			$relative = new PAHDIUrl ($relative);
		}
		//If the current url is not absolute then exit
		if (!$this->isAbsolute()) {
			return false;
		}
		$ret = new PAHDIUrl("");
		$rAuth = $relative->getAuthority();
		if ($relative->scheme !== null) {
			$ret->scheme = $relative->scheme;
			$ret->setAuthority($rAuth);
			$ret->path = $this->_removeDotSegments($relative->path);
			$ret->query = $relative->query;
		} else {
			if ($rAuth) {
				$ret->setAuthority($rAuth);
				$ret->path = $this->_removeDotSegments($relative->path);
				$ret->query = $relative->query;
			} else {
				if ($relative->path === "") {
					$ret->path = $relative->path;
					$ret->query = 	$relative->query ?
									$relative->query :
									$this->query;
				} else {
					if (isset($relative->path[0]) && $relative->path[0] === "/") {
						$ret->path = $this->_removeDotSegments($relative->path);
					} else {
						$ret->path = $this->_merge($relative->path);
						$ret->path = $this->_removeDotSegments($ret->path);
					}
					$ret->query = $relative->query;
				}
				$ret->setAuthority($this->getAuthority());
			}
			$ret->scheme = $this->scheme;
		}
		if ($relative->fragment !== null) {
			$ret->fragment = $relative->fragment;
		}
		return $ret;
	}
	
	/**
	 * Returns the merged version of the current url with the
	 * given path
	 *
	 * @param	string		$path		Path to merge
	 * @return	string		Resulting url
	 * @access	protected
	 */
	protected function _merge ($path)
	{
		if ($this->getAuthority() && $this->path === null) {
			return "/$path";
		}
		$pos = strrpos($this->path, "/");
		if ($pos === false) {
			return $path;
		}
		$base = substr($this->path, 0, $pos);
		return "$base/$path";
	}
	
	/**
	 * Removes the dot segments from the given path
	 *
	 * @param	string		$path	Path
	 * @return	string		Resulting path
	 * @access	protected
	 */
	protected function _removeDotSegments ($path)
	{
		$ret = "";
		while (strlen($path)) {
			$pre3 = substr($path, 0, 3);
			if ($pre3 === "../") {
				$path = substr($path, 3);
			} elseif (substr($path, 0, 2) === "./") {
				$path = substr($path, 2);
			} elseif ($path === "/.") {
				$path = "/";
			} elseif ($pre3 === "/./") {
				$path = "/" . substr($path, 3);
			} elseif (substr($path, 0, 4) === "/../" ||
					$path === "/..") {
				$path = "/" . ($path === "/.." ? "" : substr($path, 4));
				$pos = strrpos($ret, "/");
				$ret = $pos !== false ? substr($ret, 0, $pos) : "";
			} elseif ($path === "." || $path === "..") {
				$path = "";
			} else {
				$pos = strpos($path, "/");
				if ($pos === 0) {
					$pos = strpos($path, "/", 1);
				}
				if ($pos === false) {
					$ret .= $path;
					$path = "";
				} else {
					$ret .= substr($path, 0, $pos);
					$path = substr($path, $pos);
				}
			}
		}
		return $ret;
	}
}