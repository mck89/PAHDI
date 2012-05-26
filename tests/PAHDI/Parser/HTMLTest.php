<?php
class ParserHTMLTest extends PAHDITest
{
	static $documents = array();
	
	/**
	 * @dataProvider sourceProvider
	 */
	function testParseString ($source, $store = false, $encoding = null)
	{
		list($html, $test) = $this->loadTestSource($source);
		$timer = self::startTimer();
		$options = array("encoding"=>$encoding);
		$document = $this->parseHTML($html, $options);
		self::markTime($timer, "Parsed source $source");
		$serialized = $this->serializeNode($document);
		$this->assertEquals($serialized, $test);
		if ($store) {
			self::$documents[$source] = $document;
		}
		unset($document, $serialize, $test, $html);
	}
	
	function loadTestSource ($source)
	{
		$sourcePath = PAHDI_TEST_SOURCE_DIR . DRS . "$source" . DRS;
		$HTML = file_get_contents($sourcePath . "source.html");
		$test = file_get_contents($sourcePath . "test.txt");
		return array($HTML, $test);
	}
	
	function sourceProvider ()
	{
		return array(
			array("HTML1", false, "ISO-8859-1"),
			array("HTML2", false, "ISO-8859-1"),
			array("HTML3", true, "ISO-8859-1"),
			array("HTML4", false, "ISO-8859-1"),
			array("HTML5", false, "ISO-8859-1"),
			array("HTML6", false, "ISO-8859-1"),
			array("HTML7", false, "ISO-8859-1"),
			array("HTML8", false, "ISO-8859-1"),
			array("HTML9", false, "ISO-8859-1"),
			array("HTML10", false, "ISO-8859-1"),
			array("HTML11", false, "ISO-8859-1"),
			array("SVG1", true, "ISO-8859-1"),
			array("MATHML1", false, "ISO-8859-1"),
			array("MATHML2", false, "ISO-8859-1"),
			array("Misnested_tags1", false, "ISO-8859-1"),
			array("Misnested_tags2", false, "ISO-8859-1"),
			array("Misnested_tags3", false, "ISO-8859-1"),
			array("EOF_unclosed_tag", false, "ISO-8859-1"),
			array("EOF_unclosed_attribute_value", false, "ISO-8859-1"),
			array("EOF_unclosed_attribute_name", false, "ISO-8859-1"),
			array("DuplicatedAttributes", false, "ISO-8859-1"),
			array("UTF-8", true),
			array("UTF-8_to_ISO", true, "ISO-8859-1")
		);
	}
	
	function testJavascriptEnableOption ()
	{
		$HTML = "<html><head><noscript>test</noscript></head><body></body></html>";
		
		$document = $this->parseHTML($HTML, array("javascriptEnabled"=>true));
		$serialized = $this->serializeNode($document);
		$this->assertEquals($serialized, "<html><head><noscript></noscript></head><body></body></html>");
		
		$document = $this->parseHTML($HTML, array("javascriptEnabled"=>false));
		$serialized = $this->serializeNode($document);
		$this->assertEquals($serialized, "<html><head><noscript></noscript></head><body>test</body></html>");
	}
	
	function testEncoding ()
	{
		$this->assertEquals(self::$documents["UTF-8"]->inputEncoding, "UTF-8");
		$this->assertEquals(self::$documents["UTF-8"]->characterSet, "UTF-8");
		$this->assertEquals(self::$documents["UTF-8_to_ISO"]->inputEncoding, "UTF-8");
		$this->assertEquals(self::$documents["UTF-8_to_ISO"]->characterSet, "ISO-8859-1");
	}
}