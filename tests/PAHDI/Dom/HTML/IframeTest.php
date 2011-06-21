<?php
class HTMLIFrameTest extends PAHDITest
{
	function testContentDocument ()
	{
		$sourcePath = PAHDI_TEST_SOURCE_DIR . DRS . "Iframe" . DRS . "index.html";
		$document = PAHDI::parseLocalSource($sourcePath);
		$doc = $document->body->firstChild->contentDocument;
		$this->assertTrue($doc !== null);
		$this->assertEquals($doc->body->className, "test");
	}
	
	function testDataURISrc ()
	{
		$HTML = base64_encode("<p>Test</p>");
		$document = $this->parseHTML("<iframe src='data:text/html;base64,$HTML'>");
		$doc = $document->body->firstChild->contentDocument;
		$this->assertTrue($doc !== null);
		$bodyFirst = $doc->body->firstChild;
		$this->assertEquals($bodyFirst->tagName, "p");
		$this->assertEquals($bodyFirst->textContent, "Test");
	}
	
	function testSrcDoc ()
	{
		$document = $this->parseHTML("<iframe srcdoc='<p>Foo</p>'>");
		$doc = $document->body->firstChild->contentDocument;
		$this->assertTrue($doc !== null);
		$bodyFirst = $doc->body->firstChild;
		$this->assertEquals($bodyFirst->tagName, "p");
		$this->assertEquals($bodyFirst->textContent, "Foo");
	}
}