<?php
class TextTest extends PAHDITest
{	
	static $container;
	static $text;
	static $text2;
	
	function testAppendData ()
	{
		$document = $this->getEmptyDocument();
		$text = $document->createTextNode("test");
		$timer = self::startTimer();
		$text->appendData(" test");	
		self::markTime($timer, "AppendData");
		$this->assertEquals($text->data, "test test");
		return $text;
	}
	
	/**
     * @depends testAppendData
     */
	function testDeleteData ($text)
	{
		$timer = self::startTimer();
		$text->deleteData(1, 4);
		self::markTime($timer, "DeleteData");
		$this->assertEquals($text->data, "ttest");
		return $text;
	}
	
	/**
     * @depends testDeleteData
     */
	function testInsertData ($text)
	{
		$timer = self::startTimer();
		$text->insertData(1, "foo");
		self::markTime($timer, "InsertData");
		$this->assertEquals($text->data, "tfootest");
		return $text;
	}
	
	/**
     * @depends testDeleteData
     */
	function testReplaceData ($text)
	{
		$timer = self::startTimer();
		$text->replaceData(3, 2, "new");
		self::markTime($timer, "ReplaceData");
		$this->assertEquals($text->data, "tfonewest");
		return $text;
	}
	
	/**
     * @depends testReplaceData
     */
	function testSubstringData ($text)
	{
		$timer = self::startTimer();
		$data = $text->substringData(2, 2);
		self::markTime($timer, "ReplaceData");
		$this->assertEquals($data, "on");
	}
	
	function testLength ()
	{
		$document = $this->getEmptyDocument();
		self::$container = $document->createElement("div");
		self::$text = $document->createTextNode("test");
		self::$text2 = $document->createTextNode("this is a test");
		$this->assertEquals(self::$text->length, 4);
		$el = $document->createElement("div");
		$el2 = $document->createElement("div");
		$text3 = $document->createTextNode("foo");
		$text4 = $document->createTextNode("bar");
		$text5 = $document->createTextNode("dom");
		$text6 = $document->createTextNode("test");
		self::$container->appendChild(self::$text2);
		self::$container->appendChild($el);
		self::$container->appendChild($text3);
		self::$container->appendChild($text4);
		self::$container->appendChild(self::$text);
		self::$container->appendChild($text5);
		self::$container->appendChild($el2);
		self::$container->appendChild($text6);
	}
	
	/**
     * @depends testLength
     */
	function testWholeText ()
	{
		$timer = self::startTimer();
		$wholeText = self::$text->wholeText;
		self::markTime($timer, "WholeText");
		$this->assertEquals($wholeText, "foobartestdom");
	}
	
	/**
     * @depends testWholeText
     */
	function testSplitText ()
	{
		$timer = self::startTimer();
		$newnode = self::$text2->splitText(5);
		self::markTime($timer, "SplitText");
		$this->assertTrue($newnode->isSameNode(self::$text2->nextSibling));
		$this->assertEquals(self::$text2->data, "this ");
		$this->assertEquals($newnode->data, "is a test");
	}
	
	/**
     * @depends testSplitText
     */
	function testReplaceWholeText ()
	{
		$timer = self::startTimer();
		self::$text->replaceWholeText("new data");
		self::markTime($timer, "ReplaceWholeText");
		$this->assertTrue(self::$container->childNodes[3]->isSameNode(self::$text));
		$this->assertEquals(self::$container->childNodes[3]->data, "new data");
	}
	
	/**
     * @depends testReplaceWholeText
     */
	function testIsElementContentWhitespace ()
	{
		$tx = self::$text->ownerDocument->createTextNode(" ");
		$timer = self::startTimer();
		$white = $tx->isElementContentWhitespace;
		self::markTime($timer, "IsElementContentWhitespace");
		$this->assertTrue($white);
		$this->assertFalse(self::$text->isElementContentWhitespace);
	}
	
	/**
	 * @expectedException 	DomException
     */
	function testAppendChildThrowsException ()
	{
		$document = $this->getEmptyDocument();
		$text = $document->createTextNode("text");
		$el = $document->createElement("div");
		$text->appendChild($el);
	}
	
	function testMBData ()
	{
		if (!function_exists("mb_strlen")) {
			return;
		}
		$doc = ParserHTMLTest::$documents["UTF-8"];
		$text = $doc->body->firstChild;
		$this->assertEquals($text->length, 4);
		$data = $text->substringData(2, 2);
		$this->assertEquals($data, "òù");
		$newnode = $text->splitText(2);
		$this->assertEquals($text->length, 2);
		$this->assertEquals($newnode->length, 2);
	}
}