<?php
class HTMLElementTest extends PAHDITest
{
	function testFormPointer ()
	{
		$document = $this->getEmptyDocument();
		$form = $document->createElement("form");
		$container = $document->createElement("div");
		$input = $document->createElement("input");
		$div = $document->createElement("div");
		$form->appendChild($container);
		$container->appendChild($input);
		$container->appendChild($div);
		$this->assertTrue($input->form->isSameNode($form));
		$this->assertEquals($div->form, null);
	}
	
	function testInsertAdjacentElement ()
	{
		$document = $this->parseHTML("<div>test</div>");
		$divs = $document->getElementsByTagName("div");
		$div = $divs[0];
		$el1 = $document->createElement("a");
		$el2 = $document->createElement("b");
		$el3 = $document->createElement("i");
		$el4 = $document->createElement("span");
		$div->insertAdjacentElement("beforeBegin", $el1);
		$div->insertAdjacentElement("afterBegin", $el2);
		$div->insertAdjacentElement("beforeEnd", $el3);
		$div->insertAdjacentElement("afterEnd", $el4);
		$this->assertTrue($div->previousSibling->isSameNode($el1));
		$this->assertTrue($div->firstChild->isSameNode($el2));
		$this->assertTrue($div->lastChild->isSameNode($el3));
		$this->assertTrue($div->nextSibling->isSameNode($el4));
	}
	
	function testInsertAdjacentText ()
	{
		$document = $this->parseHTML("<div>test</div>");
		$divs = $document->getElementsByTagName("div");
		$div = $divs[0];
		$div->insertAdjacentText("beforeBegin", "a");
		$div->insertAdjacentText("afterBegin", "b");
		$div->insertAdjacentText("beforeEnd", "c");
		$div->insertAdjacentText("afterEnd", "d");
		$this->assertEquals($div->previousSibling->data, "a");
		$this->assertEquals($div->firstChild->data, "b");
		$this->assertEquals($div->lastChild->data, "c");
		$this->assertEquals($div->nextSibling->data, "d");
	}
	
	function testGetInnerHTML ()
	{
		$html = '<div>test</div><span att="value">foo<a href="test.html">foobar</a>bar</span>abc';
		$document = $this->parseHTML($html);
		$this->assertEquals($document->body->innerHTML, $html);
	}
	
	function testSetInnerHTML ()
	{
		$html = '<a href="index.html">a</a>-<b>b</b>-<i att="2">c</i>d';
		$document = $this->parseHTML("");
		$document->body->innerHTML = $html;
		$this->assertEquals($document->body->innerHTML, $html);
	}
	
	function testOuterHTML ()
	{
		$html = '<div id="el"><div>test</div><span att="value">foo<a href="test.html">foobar</a>bar</span>abc</div>';
		$document = $this->parseHTML($html);
		$el = $document->getElementById("el");
		$this->assertEquals($el->outerHTML, $html);
		$html = '<a href="index.html">a</a>-<b>b</b>-<i att="2">c</i>d';
		$el->outerHTML = $html;
		$this->assertEquals($document->body->innerHTML, $html);
	}
	
	function testOuterText ()
	{
		$html = '<div id="el">abc</div>';
		$document = $this->parseHTML($html);
		$el = $document->getElementById("el");
		$this->assertEquals($el->outerText, "abc");
		$el->outerText = "test";
		$this->assertEquals($document->body->innerHTML, "test");
	}
	
	function testInsertAdjacentHTML ()
	{
		$html = '<div id="el"><b>test</b></div>';
		$h1 = '<a>1</a>';
		$h2 = '<b>2</b>';
		$h3 = '<u>3</u>';
		$h4 = '<span>4</span>';
		$document = $this->parseHTML($html);
		$el = $document->getElementById("el");
		$el->insertAdjacentHTML("beforeBegin", $h1);
		$el->insertAdjacentHTML("afterBegin", $h2);
		$el->insertAdjacentHTML("beforeEnd", $h3);
		$el->insertAdjacentHTML("afterEnd", $h4);
		$test = $h1 . $html . $h4;
		$test = str_replace("</b>", "</b>$h3", $test);
		$test = str_replace("<b>", "$h2<b>", $test);
		$this->assertEquals($document->body->innerHTML, $test);
	}
}