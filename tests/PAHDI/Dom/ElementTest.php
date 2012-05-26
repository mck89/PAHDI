<?php
class ElementTest extends PAHDITest
{
	function testSetAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$timer = self::startTimer();
		$el->setAttribute("foo", "bar");
		self::markTime($timer, "SetAttribute");
		$el->setAttribute("bar", "foo");
		$el->setAttribute("bar", "foo2");
		$this->assertEquals($el->attributes->foo->value, "bar");
		$this->assertEquals($el->attributes->bar->value, "foo2");
	}
	
	function testHasAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$el->setAttribute("foo", "bar");
		$timer = self::startTimer();
		$has = $el->hasAttribute("foo");
		self::markTime($timer, "HasAttribute");
		$hasNot = $el->hasAttribute("inexistent");
		$this->assertTrue($has);
		$this->assertFalse($hasNot);
	}
	
	function testGetAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$el->setAttribute("foo", "bar");
		$timer = self::startTimer();
		$get = $el->getAttribute("foo");
		self::markTime($timer, "GetAttribute");
		$get2 = $el->getAttribute("inexistent");
		$this->assertEquals($get, "bar");
		$this->assertEquals($get2, null);
	}
	
	function testRemoveAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$el->setAttribute("foo", "bar");
		$timer = self::startTimer();
		$el->removeAttribute("foo");
		self::markTime($timer, "RemoveAttribute");
		$this->assertEquals($el->getAttribute("foo"), null);
	}
	
	function testSetAttributeNS ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$timer = self::startTimer();
		$el->setAttributeNS("NS", "foo", "bar");
		self::markTime($timer, "SetAttributeNS");
		$el->setAttributeNS("NO", "foo", "bar");
		$this->assertEquals($el->attributes->length, 2);
	}
	
	function testHasAttributeNS ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$el->setAttributeNS("NS", "foo", "bar");
		$timer = self::startTimer();
		$has = $el->hasAttributeNS("NS", "foo");
		self::markTime($timer, "HasAttributeNS");
		$has2 = $el->hasAttributeNS("NO", "foo");
		$this->assertTrue($has);
		$this->assertFalse($has2);
	}
	
	function testGetAttributeNS ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$el->setAttributeNS("NS", "foo", "bar");
		$timer = self::startTimer();
		$get = $el->getAttributeNS("NS", "foo");
		self::markTime($timer, "GetAttributeNS");
		$get2 = $el->getAttributeNS("NO", "foo");
		$this->assertEquals($get, "bar");
		$this->assertEquals($get2, null);
	}
	
	function testRemoveAttributeNS ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$el->setAttributeNS("NS", "foo", "bar");
		$el->setAttributeNS("NO","foo", "bar2");
		$timer = self::startTimer();
		$el->removeAttributeNS("NS", "foo");
		self::markTime($timer, "RemoveAttributeNS");
		$this->assertEquals($el->getAttribute("foo"), "bar2");
	}
	
	function testSetAttributeNode ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$attr = $document->createAttribute("foo");
		$attr->value = "bar";
		$timer = self::startTimer();
		$el->setAttributeNode($attr);
		self::markTime($timer, "SetAttributeNode");
		$this->assertEquals($el->attributes->foo->value, "bar");
		$attr2 = $document->createAttribute("foo");
		$attr2->value = "bar2";
		$el->setAttributeNode($attr2);
		$this->assertEquals($el->attributes->foo->value, "bar2");
	}
	
	function testGetAttributeNode ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$attr = $document->createAttribute("foo");
		$attr->value = "bar";
		$el->setAttributeNode($attr);
		$timer = self::startTimer();
		$get = $el->getAttributeNode("foo");
		self::markTime($timer, "GetAttributeNode");
		$this->assertTrue($get->isSameNode($attr));
		$this->assertEquals($el->getAttributeNode("inexistent"), null);
	}
	
	function testRemoveAttributeNode ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$attr = $document->createAttribute("foo");
		$attr->value = "bar";
		$el->setAttributeNode($attr);
		$timer = self::startTimer();
		$removed=$el->removeAttributeNode($attr);
		self::markTime($timer, "RemoveAttributeNode");
		$this->assertTrue($removed->isSameNode($attr));
		$this->assertEquals($el->getAttribute("foo"), null);
	}
	
	function testSetAttributeNodeNS ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$attr = $document->createAttributeNS("NS", "foo");
		$attr->value = "bar";
		$timer = self::startTimer();
		$el->setAttributeNodeNS($attr);
		self::markTime($timer, "SetAttributeNodeNS");
		$attr2 = $document->createAttributeNS("NO", "foo");
		$attr2->value = "bar2";
		$el->setAttributeNodeNS($attr2);
		$this->assertEquals($el->attributes->length, 2);	
		$attr3 = $document->createAttributeNS("NS", "foo");
		$attr3->value = "bar3";
		$el->setAttributeNodeNS($attr3);
		$this->assertEquals($el->attributes->length, 2);
		$this->assertEquals($el->getAttributeNS("NS", "foo"), "bar3");
	}
	
	function testGetAttributeNodeNS ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$attr = $document->createAttributeNS("NS", "foo");
		$attr->value = "bar";
		$el->setAttributeNodeNS($attr);
		$timer = self::startTimer();
		$get = $el->getAttributeNodeNS("NS","foo");
		self::markTime($timer, "GetAttributeNodeNS");
		$this->assertTrue($get->isSameNode($attr));
		$this->assertEquals($el->getAttributeNodeNS("NO", "foo"), null);
	}
	
	function testFirstLastElementChildAndChildElementCount ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$text = $document->createTextNode("test");
		$el->appendChild($text);
		$this->assertEquals($el->childElementCount, 0);
		$this->assertEquals($el->firstElementChild, null);
		$this->assertEquals($el->lastElementChild, null);
		$ch1 = $document->createElement("div");
		$el->insertBefore($ch1, $text);
		$this->assertEquals($el->childElementCount, 1);
		$this->assertTrue($el->firstElementChild->isSameNode($ch1));
		$this->assertTrue($el->lastElementChild->isSameNode($ch1));
		$ch2 = $document->createElement("div");
		$el->appendChild($ch2);
		$timer = self::startTimer();
		$cc = $el->childElementCount;
		self::markTime($timer, "ChildElementCount");
		$first = $el->firstElementChild;
		self::markTime($timer, "FirstElementChild");
		$last = $el->lastElementChild;
		self::markTime($timer, "LastElementChild");
		$this->assertEquals($cc, 2);
		$this->assertTrue($first->isSameNode($ch1));
		$this->assertTrue($last->isSameNode($ch2));
		try {
			$el->firstElementChild = 1;
			$this->assertTrue(false, "No exception has been thrown when setting the firstElementChild property");
		} catch(Exception $e) {
		}
	}
	
	function testNextPreviousElementSibling ()
	{
		$document = $this->getEmptyDocument();
		$cont = $document->createElement("div");
		$txt = $document->createTextNode("test");
		$el = $document->createElement("span");
		$txt2 = $document->createTextNode("test2");
		$cont->appendChild($txt);
		$cont->appendChild($el);
		$cont->appendChild($txt2);
		$prev=$el->previousElementSibling;
		$next=$el->nextElementSibling;
		$this->assertEquals($el->previousElementSibling, null);
		$this->assertEquals($el->nextElementSibling, null);
		$el2 = $document->createElement("span");
		$cont->appendChild($el2);
		$this->assertEquals($el->previousElementSibling, null);
		$this->assertTrue($el->nextElementSibling->isSameNode($el2));
		$this->assertEquals($el2->nextElementSibling, null);
		$this->assertTrue($el2->previousElementSibling->isSameNode($el));
		$txt3 = $document->createTextNode("test3");
		$cont->appendChild($txt3);
		$el3 = $document->createElement("span");
		$cont->appendChild($el3);
		$this->assertEquals($el->previousElementSibling, null);
		$this->assertTrue($el->nextElementSibling->isSameNode($el2));
		$timer = self::startTimer();
		$prev = $el2->previousElementSibling;
		self::markTime($timer, "NextElementSibling");
		$next = $el2->nextElementSibling;
		self::markTime($timer, "PreviousElementSibling");
		$this->assertTrue($prev->isSameNode($el));
		$this->assertTrue($next->isSameNode($el3));
		$this->assertTrue($el3->previousElementSibling->isSameNode($el2));
		$this->assertEquals($el3->nextElementSibling, null);
		try {
			$el->previousElementSibling = 1;
			$this->assertTrue(false, "No exception has been thrown when setting the previousElementSibling property");
		} catch(Exception $e) {
		}
	}
	
	function testPropertyAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$timer = self::startTimer();
		$res = $el->className==="";
		self::markTime($timer, "Get property without attribute");
		$el->className = "test";
		self::markTime($timer, "Set property without attribute");
		$this->assertEquals($el->attributes->length, 1);
		$this->assertEquals($el->attributes[0]->name, "class");
		$this->assertEquals($el->attributes[0]->value, "test");
		$timer = self::startTimer();
		$class = $el->className;
		self::markTime($timer, "Get property");
		$this->assertEquals($class, "test");
		$timer = self::startTimer();
		$el->className = "test2";
		self::markTime($timer, "Set property");
		$this->assertEquals($el->attributes->length, 1);
		$this->assertEquals($el->attributes[0]->name, "class");
		$this->assertEquals($el->attributes[0]->value, "test2");
	}
	
	function testDirAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$timer = self::startTimer();
		$el->dir = "test";
		self::markTime($timer, "Set invalid dir property");
		$this->assertEquals($el->dir, "");
		$timer = self::startTimer();
		$el->dir = "rtl";
		self::markTime($timer, "Set valid dir property");
		$this->assertEquals($el->dir, "rtl");
	}
	
	function testTabIndex ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$timer = self::startTimer();
		$tabi = $el->tabIndex;
		self::markTime($timer, "Get inexistent tabindex property");
		$el2 = $document->createElement("input");
		$this->assertEquals($tabi, -1);
		$this->assertEquals($el2->tabIndex, 0);
		$timer = self::startTimer();
		$el->tabIndex = "test";
		self::markTime($timer, "Set invalid tabIndex property");
		$this->assertEquals($el->tabIndex, -1);
		$el->tabIndex = "2";
		$this->assertEquals($el->tabIndex, 2);
		$timer = self::startTimer();
		$el->tabIndex = 3;
		self::markTime($timer, "Set valid tabIndex property");
		$this->assertEquals($el->tabIndex, 3);
	}
	
	function testContentEditable ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$timer = self::startTimer();
		$ce = $el->contentEditable;
		self::markTime($timer, "Get inexistent contentEditable property");
		$this->assertFalse($ce);
		try{
			$el->contentEditable = "test";
			$this->assertTrue(false, "No exception has been thrown when setting an invalid contentEditable value");
		}catch(Exception $e){
		}
		$timer = self::startTimer();
		$el->contentEditable = "true";
		self::markTime($timer, "Set valid contentEditable property");
		$this->assertTrue($el->contentEditable);
	}
	
	function testBooleanAttribute ()
	{
		$document = $this->parseHTML("<input draggable>");
		$el = $document->body->firstChild;
		$timer = self::startTimer();
		$res=$el->draggable;
		self::markTime($timer, "Accessing boolean attribute");
		$el->draggable = "test";
		self::markTime($timer, "Setting invalid value for boolean attribute");
		$this->assertTrue($res);
		$this->assertTrue($el->draggable);
		$timer = self::startTimer();
		$el->draggable = false;
		self::markTime($timer, "Setting valid value for boolean attribute");
		$this->assertFalse($el->draggable);
	}
	
	function testIntegerPercentAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("table");
		$timer = self::startTimer();
		$res=$el->width;
		self::markTime($timer, "Accessing int attribute");
		$el->width = "test";
		self::markTime($timer, "Setting invalid value for integer attribute");
		$this->assertEquals($res, "");
		$this->assertEquals($el->width, "");
		$timer = self::startTimer();
		$el->width = "100%";
		self::markTime($timer, "Setting valid value for integer attribute");
		$this->assertEquals($el->width, "100%");
		$el->width = 100;
		$this->assertEquals($el->width, 100);
	}
	
	function testIntegerAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("canvas");
		$timer = self::startTimer();
		$w = $el->width;
		self::markTime($timer, "Accessing int attribute");
		$el->width = "test";
		self::markTime($timer, "Setting invalid value for integer attribute");
		$this->assertTrue(is_numeric($w));
		$this->assertEquals($el->width, 0);
		$timer = self::startTimer();
		$el->width = 100;
		self::markTime($timer, "Setting valid value for integer attribute");
		$this->assertEquals($el->width, 100);
	}
	
	function testColorAttribute ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("hr");
		$timer = self::startTimer();
		$c = $el->color;
		self::markTime($timer, "Accessing int attribute");
		$el->color = "test";
		self::markTime($timer, "Setting invalid value for color attribute");
		$this->assertEquals($el->color, "#000000");
		$timer = self::startTimer();
		$el->color = "#101010";
		self::markTime($timer, "Setting valid value for color attribute");
		$this->assertEquals($el->color, "#101010");
		$el->color = "201010";
		$this->assertEquals($el->color, "#201010");
		$el->color = "BBB";
		$this->assertEquals($el->color, "#bbbbbb");
		$el->color = "rgb(255,255,255)";
		$this->assertEquals($el->color, "#ffffff");
		$el->color="#FFFF";
		$this->assertEquals($el->color, "#000000");
	}
	
	function testAttributeThatUsesTextContent ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("title");
		$text = $document->createTextNode("test");
		$el->appendChild($text);
		$timer = self::startTimer();
		$tx = $el->text;
		self::markTime($timer, "Getting property");
		$this->assertEquals($tx, "test");
		$el->text = "bar";
		$this->assertEquals($el->text, "bar");
		$this->assertEquals($el->textContent, "bar");
		$text2 = $document->createTextNode("foo");
		$el->appendChild($text2);
		$this->assertEquals($el->text, "barfoo");
		$this->assertEquals($el->textContent, "barfoo");
	}
	
	function testAttributeAbsolutePath ()
	{
		$fakeurl = "http://www.fakeurltest.com";
		$relurl = "/test.html";
		$document = $this->parseHTML("<a href='$relurl'></a>", array("baseURL"=>$fakeurl));
		$a = $document->body->firstChild;
		$this->assertEquals($a->getAttribute("href"), $relurl);
		$this->assertEquals($a->href, "$fakeurl$relurl");
	}
	
	function testAttributeAbsolutePathsList ()
	{
		$fakeurl = "http://www.fakeurltest.com";
		$relurl = "/test.html";
		$relurl2 = "/test/test.html";
		$document = $this->parseHTML("<a ping='$relurl $relurl2'></a>", array("baseURL"=>$fakeurl));
		$a = $document->body->firstChild;
		$this->assertEquals($a->getAttribute("ping"), "$relurl $relurl2");
		$this->assertEquals($a->ping, "$fakeurl$relurl $fakeurl$relurl2");
	}
	
	function testGetElementsByTagName ()
	{
		$document = ParserHTMLTest::$documents["HTML3"];
		$timer = self::startTimer();
		$list = $document->body->getElementsByTagName("*");
		self::markTime($timer, "GetElementsByTagName every tag name");
		$list2 = $document->body->getElementsByTagName("div");
		self::markTime($timer, "GetElementsByTagName div");
		$this->assertEquals($list->length, 255);
		$this->assertEquals($list2->length, 30);
	}
	
	function testGetElementsByTagNameNS ()
	{
		$document = ParserHTMLTest::$documents["HTML3"];
		$ns = "http://www.w3.org/1999/xhtml";
		$timer = self::startTimer();
		$list = $document->body->getElementsByTagNameNS($ns, "*");
		self::markTime($timer, "GetElementsByTagNameNS");
		$this->assertEquals($list->length, 255);
	}
	
	function testGetElementsByClassName ()
	{
		$document = ParserHTMLTest::$documents["HTML3"];
		$timer = self::startTimer();
		$list = $document->body->getElementsByClassName("theme");
		self::markTime($timer, "GetElementsByClassName");
		$list2 = $document->body->getElementsByClassName("right-list theme");
		self::markTime($timer, "GetElementsByClassName with multiple classes");
		$this->assertEquals($list->length, 5);
		$this->assertEquals($list2->length, 3);
	}
	
	function testChildren ()
	{
		$document = $this->parseHTML("<div>test</div>");
		$children = $document->body->children;
		$this->assertEquals($children->length, 1);
		$this->assertEquals($children[0]->tagName, "div");
		$this->assertEquals($children[0]->children->length, 0);
	}
	
	function matchesSelector ()
	{
		$document = ParserHTMLTest::$documents["HTML3"];
		$this->assertTrue($document->body->matchesSelector, "html>body");
		$this->assertTrue(!$document->body->matchesSelector, "div");
	}
	
	function testDataset ()
	{
		$document = $this->getEmptyDocument();
		$div = $document->createElement("div");
		$dataset = $div->dataset;
		$div->setAttribute("data-foo-bar", "1");
		$this->assertEquals($dataset->fooBar, "1");
		$this->assertEquals($dataset["fooBar"], "1");
		$this->assertEquals($dataset["foo-bar"], "1");
		$this->assertEquals(isset($dataset->fooBar), true);
		$this->assertEquals(isset($dataset["fooBar"]), true);
		$this->assertEquals(isset($dataset->bar), false);
		$dataset->brandNewAttr = "test";
		$this->assertEquals($dataset->brandNewAttr, "test");
		$this->assertEquals($div->hasAttribute("data-brand-new-attr"), true);
		$test = array();
		foreach ($dataset as $k => $v) {
			$test[$k] = $v;
		}
		$this->assertEquals($test, array("foo-bar" => "1", "brand-new-attr" => "test"));
		unset($dataset->fooBar);
		$this->assertEquals($div->hasAttribute("data-foo-bar"), false);
	}
}