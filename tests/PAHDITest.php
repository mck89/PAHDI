<?php
class PAHDITest extends PHPUnit_Framework_TestCase
{
	function parseHTML ($HTML, $options=array())
	{
		return PAHDI::parseString($HTML, $options);
	}
	
	function serializeNode ($node)
	{
		$res = "";
		$isElement = $node->nodeType === 1;
		$tagName = $isElement ? strtolower($node->tagName) : null;
		
		switch ($node->nodeType) {
			case 1:
				$res .= "<$tagName";
				if ($node->attributes->length) {
					$ns = $node->namespaceURI;
					foreach ($node->attributes as $att) {
						$value = $this->fixSerializedText($att->value);
						$name = $att->name;
						if ($ns === ParserHTML::SVG_NAMESPACE &&
							isset(ParserHTML::$SVGAttributes[$name])) {
							$name = ParserHTML::$SVGAttributes[$name];
						} elseif ($ns === ParserHTML::MATHML_NAMESPACE &&
								isset(ParserHTML::$MATHMLAttributes[$name])) {
							$name = ParserHTML::$MATHMLAttributes[$name];
						}
						$res .= " $name=\"$value\"";
					}
				}
				$res .= ">";
			break;
			case 3: 
				$res .= $this->fixSerializedText($node->data);
			break;
			case 8:
				$res .= "<!--" . $this->fixSerializedText($node->data) . "-->";
			break;
			case 10:
				$res .= "<!DOCTYPE";
				if ($node->name) {
					$res .= " {$node->name}";
				}
				if ($node->publicId) {
					$res .= " PUBLIC \"{$node->publicId}\"";
				}
				if ($node->systemId) {
					$res.=" \"{$node->systemId}\"";
				}
				$res .= ">";
			break;
		}
		
		if (isset($node->childNodes) && $node->childNodes->length &&
			(!$isElement || strtolower($tagName !== "noscript"))) {
			foreach ($node->childNodes as $child) {
				$res .= $this->serializeNode($child);
			}
		}
		if ($isElement) {
			$res .= "</$tagName>";
		}
		
		return $res;
	}
	
	function fixSerializedText ($text)
	{
		return str_replace(array("\t", "\n", " "), array("\\t", "\\n", "\\s"), $text);
	}
	
	function getEmptyDocument ()
	{
		$doc = new HTMLDocument();
		$doc->characterSet = "ISO-8859-1";
		return $doc;
	}
	
	static function startTimer ()
	{
		return array(microtime(true), memory_get_usage());
	}
	
	static function markTime ($timer, $msg)
	{
		$end = microtime(true);
		$endMemory = memory_get_usage();
		list ($start, $memory) = $timer;		
		$log = "*** $msg (Time: " . round($end - $start, 3) . " s, ";
		$log .= "Memory: " . self::convertSize($endMemory - $memory).")\n";
		PAHDIListener::registerLogMessage($log);
	}
	
	static function convertSize($size)
	{
		$unit=array('b','Kb','Mb','Gb','Tb','Pb');
		$i = floor(log($size, 1024));
		return @round($size / pow(1024, $i), 2) . ' ' . $unit[$i];
	}
}