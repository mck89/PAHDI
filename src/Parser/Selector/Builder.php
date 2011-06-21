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
 * Class that uses the tokens from the tokenizer to build the resulting
 * node list.
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserSelectorBuilder extends ParserSelectorTokenizer
{
	/**
	 * Search the elements according to the tokens generated from the
	 * tokenizer
	 *
	 * @return 	NodeList		Resulting NodeList
	 * @access	protected
	 */
	function _build ()
	{
		$list = $requireUnique = null;
		foreach ($this->_groups as $groups) {
			$search = new PAHDISearch($this->_root);
			foreach ($groups as $group) {
				$cond = $nots = array();
				$afterCond = "";
				$limit = null;
				switch ($group["combinator"]) {
					case ">":
						$type = PAHDISearch::CHILDREN;
					break;
					case "+":
						$type = PAHDISearch::NEXT_SIBLINGS;
						$cn = '!$node->previousElementSibling->isSameNode($search[$nodeIndex])';
						$cond[] = $cn;
					break;
					case "~":
						$type = PAHDISearch::NEXT_SIBLINGS;
					break;
					default:
						$type = PAHDISearch::DESCENDANTS;
					break;
				}
				foreach ($group["rules"] as $k => $rule) {
					//Id selector
					if ($rule["type"] === self::ID) {
						$val = $this->_quote($rule["content"]);
						$cond[] = '$node->id!==' . $val;
					}
					//Class name selector
					elseif ($rule["type"] === self::CLASSNAME) {
						$val = $this->_quote($rule["content"]);
						$cond[] = '!$node->classList->contains(' . $val . ')';
					}
					//Tag name selector
					elseif ($rule["type"] === self::TAG) {
						$con = $rule["content"];
						//Get the namespace if given
						if (strpos($con, "|") !== false) {
							list($ns, $con) = explode("|", $con);
							if ($ns !== "*") {
								$ns = $this->_quote($ns);
								$cond[] = '$node->namespaceURI!==' . $ns;
							}
						}
						//Get the prefix if given
						if (strpos($con, ":") !== false) {
							list($pr, $con) = explode(":", $con);
							if ($pr !== "*") {
								$pr = $this->_quote($pr);
								$cond[] = '$node->prefix!==' . $pr;
							}
						}
						if ($con !== "*") {
							$val = $this->_quote($con);
							$cond[] = '$node->tagName!==' . $val;
						}
					}
					//Attribute selectors
					elseif ($rule["type"] === self::ATTR) {
						$name = $rule["name"];
						//Get the prefix if given
						if (strpos($name, ":") !== false) {
							list($pr, $name) = explode(":", $name);
							$name = $this->_quote($name);
							if ($pr !== "*") {
								$pr = $this->_quote($pr);
								$var = '$att' . $k;
								$afterCond .= $var . '=$node->getAttributeNode(' .
											  $name . '); if(!' . $var . ' || ' . $var .
											  '->prefix!==' . $pr . ') return false;';
							}
						} else {
							$name = $this->_quote($name);
						}
						if (!isset($rule["comp"])) {
							$cond[] = '!$node->hasAttribute(' . $name . ')';
							continue;
						}
						$getAttr = '(string) $node->getAttribute(' . $name . ')';
						switch ($rule["comp"]) {
							case "=":
								$val = $this->_quote($rule["value"]);
								$cond[] = "$getAttr!==$val";
							break;
							case "^=":
								$val = $this->_quote($rule["value"]);
								$cond[] = "strpos($getAttr, $val)!==0";
							break;
							case "|=":
								$var = '$a' . $k;
								$afterCond .= $var . '=' . $getAttr . ";";
								$val = $this->_quote($rule["value"]);
								$valh = $this->_quote($rule["value"] . "-");
								$afterCond .= "if($var!==$val && strpos($var, $valh)!==0)";
								$afterCond .= "return false;";
							break;
							case "$=":
							case "*=":
							case "~=":
								$val = preg_quote($rule["value"], "#");
								if ($rule["comp"] === "$=") {
									$val .= "$";
								} elseif ($rule["comp"] === "~=") {
									$val = "(?:^|\s)$val(?:\s|$)";
								}
								$val = $this->_quote("#$val#");
								$cond[] = "!preg_match($val, $getAttr)";
							break;
							default:
								return false;
							break;
						}
					}
					//Pseudo selectors
					elseif ($rule["type"] === self::PSEUDO) {
						switch ($rule["fn"]) {
							case "root":
								$limit = 1;
								$cond[] = '$node->tagName!=="html"';
							break;
							case "empty":
								$cond[] = '$node->childNodes->length';
							break;
							case "link":
								$cond[] = '($node->tagName!=="a" && ' .
										  '$node->tagName!=="area")';
							break;
							case "target":
								//Get the document uri
								if (($this->_root instanceof NodeList ||
									$this->_root instanceof PAHDISearch) &&
									$this->_root->length) {
									$url =	$this->_root[0]->baseURI;
								} elseif ($this->_root instanceof Node) {
									$url =	$this->_root->baseURI;
								} else {
									return false;
								}
								$parts = parse_url($url);
								if ($parts && isset($parts["fragment"]) &&
									$parts["fragment"]) {
									//If there's a fragment in the document uri then
									//the element must be an anchor with the name or
									//the id equal to the fragment
									$cond[] = '$node->tagName!=="a"';
									$frag = $this->_quote($parts["fragment"]);
									$cn = '$node->name!==' . $frag;
									$cn .= '&& $node->id!==' . $frag;
									$cond[] = "($cn)";
								} else {
									//If there's no fragment in the document uri then
									//there is no target element so the result of the
									//search must by empty so that it does not waste
									//extra time to do a useless filter
									$search->removeAll();
								}
							break;
							case "lang":
								if (!isset($rule["args"]) || !$rule["args"]) {
									return false;
								}
								$var = '$b' . $k;
								$afterCond .= $var . '=$node->getAttribute("lang");';
								$afterCond .= $var . '=strtolower("' . $var . '");';
								$val = $this->_quote(strtolower($rule["args"]));
								$valh = $this->_quote(strtolower($rule["args"]) . "-");
								$afterCond .= "if($var!==$val && strpos($var, $valh)!==0)";
								$afterCond .= "return false;";
							break;
							case "checked":
								$cond[] = '$node->tagName!=="input"';
								$cn = '($node->type!=="radio" && ';
								$cn .= '$node->type!=="checkbox")';
								$cond[] = $cn;
								$cond[] = '!$node->checked';
							break;
							case "last-child":
							case "only-child":
							case "first-child":
								if ($rule["fn"] !== "last-child") {
									$cond[] = '$typeIndex!==0';
								}
								if ($rule["fn"] !== "first-child") {
									$cond[] = '$node->nextElementSibling';
								}
							break;
							case "enabled":
							case "disabled":
								$cn = '$node->tagName!=="option" &&';
								$cn .= '!in_array($node->tagName,ParserHTML::$formAssociated)';
								$cond[] = "($cn)";
								$pre = $rule["fn"] === "disabled" ? "!" : "";
								$cond[] = $pre . '$node->disabled';
							break;
							case "last-of-type":
							case "only-of-type":
							case "first-of-type":
								if ($rule["fn"] !== "last-of-type") {
									$afterCond .= '$el=$node;';
									$afterCond .= 'while($el=$el->previousElementSibling)';
									$afterCond .= 'if($el->tagName===$node->tagName) return false;';
								}
								if ($rule["fn"] !== "first-of-type") {
									$afterCond .= '$el=$node;';
									$afterCond .= 'while($el=$el->nextElementSibling)';
									$afterCond .= 'if($el->tagName===$node->tagName) return false;';
								}
							break;
							case "nth-child":
							case "nth-last-child":
								if (!isset($rule["args"]) || !$rule["args"]) {
									return false;
								}
								//For "nth-last-child" build a cache to store the
								//number of children elements of the elements's
								//parent node
								if ($rule["fn"] === "nth-last-child" && 
									strpos($afterCond, '$cacheLC') === false) {
									$afterCond .= '
										static $cacheLC;
										if(!isset($cacheLC)) $cacheLC=array();
										$hash=spl_object_hash($node->parentNode);
										if(!isset($cacheLC[$hash]))
											$cacheLC[$hash]=$node->parentNode->childElementCount;
										$typeLength=$cacheLC[$hash];
									';
								}
								$vl = $rule["fn"] === "nth-last-child" ? '$typeLength' : '';
								$nt = $this->_parsePseudoClassNotation(
									$rule["args"],
									$k,
									'$typeIndex',
									$vl
								);
								if ($nt === false) {
									return false;
								}
								$afterCond .= $nt;
							break;
							case "nth-of-type":
							case "nth-last-of-type":
								if (!isset($rule["args"]) || !$rule["args"]) {
									return false;
								}
								//For the "..of-type" pseudo selectors it must find
								//the index of the element relative to elements with
								//the same tag name
								$afterCond .= '
									static $cacheIOT;
									if(!isset($cacheIOT)) $cacheIOT=array();
									$hash=spl_object_hash($node);
									if(!isset($cacheIOT[$hash])){
										if(!$typeIndex) $cacheIOT[$hash]=0;
										else {
											$el=$node;
											$sTot = 0;
											while($el=$el->previousElementSibling) {
												if($el->tagName===$node->tagName){
													$sTot++;
													$elhash=spl_object_hash($el);
													if(isset($cacheIOT[$elhash]))
													{
														$sTot+=$cacheIOT[$elhash];
														break;
													}
												}
											}
											$cacheIOT[$hash]=$sTot;
										}
									}
									$typeIndexOT=$cacheIOT[$hash];
								';
								if ($rule["fn"] === "nth-last-of-type" && 
									strpos($afterCond, '$cacheOT') === false) {
									$afterCond .= '
										static $cacheOT;
										if(!isset($cacheOT)) $cacheOT=array();
										$hash=spl_object_hash($node->parentNode);
										if(!isset($cacheOT[$hash])){
											$ch=$node->parentNode->children;
											$len=$ch->length;
											$tl=0;
											for($s=0;$s<$len;$s++)
												if($ch[$s]->tagName===$node->tagName)
													$tl++;
											$cacheOT[$hash]=$tl;
										}
										$typeOTLength=$cacheOT[$hash];
									';
								}
								$vl = $rule["fn"] === "nth-last-of-type" ? '$typeOTLength' : '';
								$nt = $this->_parsePseudoClassNotation(
									$rule["args"],
									$k,
									'$typeIndexOT',
									$vl
								);
								if ($nt === false) {
									return false;
								}
								$afterCond .= $nt;
							break;
							case "not":
								if (!isset($rule["args"]) || !$rule["args"]) {
									return false;
								}
								$nots[] = $rule["args"];
							break;
							case "visited":
							case "active":
							case "hover":
							case "focus":
								//Since this condition require a user action they will never
								//match anything but they are still valid
								$afterCond .= "return false;";
							break;
							default:
								return false;
							break;
						}
					}
				}
				//Create the filter function
				$code = "";
				if (count($cond)) {
					$code .= "if(" . implode("||", $cond) . ") return false;";
				}
				$code .= $afterCond;
				$code .= 'return true;';
				$fn = create_function(
					'$node, $childIndex, $typeIndex, $search, $nodeIndex',
					$code
				);
				$search->find($fn, $type, 1, $limit);
				//If there's no matching element skip to the next group of selectors
				if (!$search->length) {
					break;
				}
				//Remove duplicated elements
				$search->unique();
				//If there's one or more ":not" pseudo selectors then the elements
				//can be filtered using this process:
				//- create a new instance of ParserSelector with a list of the
				//  current elements parents as root nodes
				//- Prepend to every sub-selector a ">" so that the matching
				//  elements will be searched in the child nodes collection of
				//  each root node
				//- Join together the sub-selectors using ","
				//- Delete from the current elements the one founded by the
				//  new instance of the parser
				if (count($nots)) {
					$subRoot = array();
					foreach ($search as $node) {
						$subRoot[] = $node->parentNode;
					}
					$sub = ">" . implode(",>", $nots);
					$parser = new ParserSelector($sub, $subRoot);
					$coll = $parser->parse();
					$search->remove($coll);
				}
			}
			if (!$list) {
				$list = $search;
			}
			//Merge the current results with the previous ones
			else {
				$list->add($search);
				$requireUnique = true;
			}
		}
		//If there's more than one group, before return the result, list
		//duplicates must be removed and the list must be sorted in document
		//order
		if ($requireUnique) {
			$list->unique()->sort();
		}
		return $list->toNodeList();
	}
	
	/**
	 * Escapes the given string and wraps it inside quotes
	 *
	 * @param	string		$val		String to quote
	 * @return 	string		Quoted string
	 * @access	protected
	 */
	function _quote ($val)
	{
		return '"' . str_replace('"', '\\"', $val) . '"';
	}
	
	/**
	 * Parse a pseudo class notation
	 *
	 * @param	string		$notation		Notation
	 * @param	int			$k				Unique key
	 * @param	string		$inVar			Variable name to consider as
	 *										node index
	 * @param	string		$lenVar			Variable name to consider as
	 *										nodes length. Useful to revert
	 *										the expression
	 * @return 	mixed		PHP code to match the given notation or false
	 *						on error
	 * @access	protected
	 */
	function _parsePseudoClassNotation ($notation, $k, $inVar, $lenVar)
	{
		$notation = trim($notation);
		//Convert odd and event to the relative notation
		if ($notation === "odd") {
			$notation = "2n+1";
		} elseif ($notation === "even") {
			$notation = "2n";
		}
		//Validation
		$matches = preg_match(
			"#^(?:[\-\+]?\d+|([\-\+]?\d*)n\s*([\-\+]\s*\d+)?)$#",
			$notation,
			$match
		);
		if (!$matches) {
			return false;
		}
		//If the notation does not contain "n" than it's a simple index
		//comparison
		if (strpos($notation, "n") === false) {
			if (!$lenVar) {
				$notation = ((int) $notation) - 1;
				return "if($inVar!==$notation) return false;";
			}
			return "if($inVar!==$lenVar-($notation)) return false;";
		}
		//If the notation is "n" or "1n" it accepts everything so the
		//filter can be skipped
		elseif ($notation === "n" || $notation === "1n" ||
				$notation === "+1n") {
			return "";
		}
		//Fix the number before "n" so that if "n" and "+n" results in 1
		//and "-n" results in -1
		$an = $match[1];
		if ($an === "" || $an === "+" || $an === "-") {
			$an = $an === "-" ? "-1" : "1";
		}
		$an = (int) $an;
		$b = isset($match[2]) ? (int) str_replace(" ", "", $match[2]) : 0;
		//If the number before "n" is 0 than take the other number for a
		//simple index comparison
		if ($an === 0) {
			if (!$lenVar) {
				$b = ((int) $b) - 1;
				return "if($inVar!==$b) return false;";
			}
			return "if($inVar!==$lenVar-($b)) return false;";
		} elseif ($an < 0) {
			//If both are negative nothing will match
			if ($b <= 0) {
				return "return false;";
			}
			$an *= - 1;
			$var = '$diff' . $k;
			if (!$lenVar) {
				$code = "$var=$b-($inVar+1);";
			} else {
				$code = "$var=$b-($lenVar-$inVar);";
			}
			$code .= "if($var<0 || $var%$an) return false;";
		} elseif ($b >= 0) {
			$var = '$diff' . $k;
			if (!$lenVar) {
				$code = "$var=($inVar+1)-$b;";
			} else {
				$code = "$var=($lenVar-$inVar)-$b;";
			}
			$code .= "if($var<0 || $var%$an) return false;";
		} else {
			$b *= - 1;
			if (!$lenVar) {
				$code = "if(($inVar+1+$b)%$an) return false;";
			} else {
				$code = "if(($lenVar+$b-$inVar)%$an) return false;";
			}
		}
		return $code;
	}
}