<?php
class NodeTest extends PAHDITest
{
	function testIsSameNode ()
	{
		$document = $this->getEmptyDocument();
		$div = $document->createElement("div");
		$span = $document->createElement("span");
		$timer = self::startTimer();
		$this->assertFalse($div->isSameNode($span));
		self::markTime($timer, "isSameNode false");
		$this->assertTrue($div->isSameNode($div));
		self::markTime($timer, "isSameNode true");
	}
	
	function testAppendChild ()
	{
		$document = $this->getEmptyDocument();
		$div = $document->createElement("div");
		$span = $document->createElement("span");
		$timer = self::startTimer();
		$span->appendChild($div);
		self::markTime($timer, "AppendChild");
		$this->assertTrue($div->parentNode->isSameNode($span));
		$this->assertTrue($div->isSameNode($span->childNodes[0]));
	}
	
	/**
	 * @expectedException 	DomException
     */
	function testAppendChildThrowsExceptionWithAttributes ()
	{
		$document = $this->getEmptyDocument();
		$div = $document->createElement("div");
		$attr = $document->createAttribute("att");
		$div->appendChild($attr);
	}
	
	function testRemoveChild ()
	{
		$document = $this->getEmptyDocument();
		$container = $document->createElement("div");
		$div = $document->createElement("div");
		$span = $document->createElement("span");
		$container->appendChild($div);
		$container->appendChild($span);
		$this->assertEquals($container->childNodes->length, 2);
		$timer = self::startTimer();
		$container->removeChild($div);
		self::markTime($timer, "RemoveChild");
		$this->assertEquals($div->parentNode, null);
		$this->assertTrue($container->childNodes[0]->isSameNode($span));
	}
	
	function testInsertBefore ()
	{
		$document = $this->getEmptyDocument();
		$container = $document->createElement("div");
		$div = $document->createElement("div");
		$container->appendChild($div);
		$span = $document->createElement("span");
		$this->assertTrue($container->childNodes[0]->isSameNode($div));
		$timer = self::startTimer();
		$container->insertBefore($span, $div);
		self::markTime($timer, "InsertBefore");
		$this->assertTrue($container->childNodes[0]->isSameNode($span));
		$this->assertTrue($container->childNodes[1]->isSameNode($div));
		$this->assertEquals($container->childNodes->length, 2);
		$this->assertTrue($span->parentNode->isSameNode($container));
	}
	
	function testAppendingDocumentFragments ()
	{
		$document = $this->getEmptyDocument();
		$frag = $document->createDocumentFragment();
		$div = $document->createElement("div");
		$container = $document->createElement("div");
		$frag->appendChild($div);
		$container->appendChild($frag);
		$this->assertEquals($container->childNodes->length, 1);
		$this->assertTrue($container->childNodes[0]->isSameNode($div));
		$frag2 = $document->createDocumentFragment();
		$div2 = $document->createElement("div");
		$frag2->appendChild($div2);
		$container->insertBefore($frag2, $div);
		$this->assertEquals($container->childNodes->length, 2);
		$this->assertTrue($container->childNodes[0]->isSameNode($div2));
		$this->assertEquals($frag->childNodes->length, 0);
		$this->assertEquals($frag2->childNodes->length, 0);
	}
	
	function testReplaceChild ()
	{
		$document = $this->getEmptyDocument();
		$parent = $document->createElement("div");
		$node1 = $document->createElement("div");
		$parent->appendChild($node1);
		$node2 = $document->createElement("div");
		$parent->appendChild($node2);
		$node3 = $document->createElement("div");
		$parent->appendChild($node3);
		$repl = $document->createElement("div");
		$timer = self::startTimer();
		$parent->replaceChild($repl, $node2);
		self::markTime($timer, "ReplaceChild");
		$this->assertTrue($repl->isSameNode($parent->childNodes[1]));
		$this->assertTrue($repl->previousSibling->isSameNode($node1));
		$this->assertTrue($repl->nextSibling->isSameNode($node3));
		$this->assertEquals($node2->parentNode, null);
	}
	
	function testNodeValue ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$timer = self::startTimer();
		$val = $el->nodeValue;
		self::markTime($timer, "Get nodeValue");
		$this->assertEquals($val, null);
		$text = $document->createTextNode("test");
		$val2 = $text->nodeValue;
		$this->assertEquals($val2, "test");
		$timer = self::startTimer();
		$text->nodeValue = "test2";
		self::markTime($timer, "Set nodeValue");
		$this->assertEquals($text->data, "test2");
		$att = $document->createAttribute("foo");
		$att->value = "bar";
		$val3 = $att->nodeValue;
		$att->nodeValue = "bar2";
		$this->assertEquals($val3, "bar");
		$this->assertEquals($att->value, "bar2");
	}
	
	function testFirstAndLastChild ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$this->assertEquals($el->firstChild, null);
		$this->assertEquals($el->lastChild, null);
		$ch1 = $document->createElement("div");
		$ch2 = $document->createElement("div");
		$el->appendChild($ch1);
		$this->assertTrue($el->firstChild->isSameNode($ch1));
		$this->assertTrue($el->lastChild->isSameNode($ch1));		
		$el->appendChild($ch2);
		$timer = self::startTimer();
		$first = $el->firstChild;
		self::markTime($timer, "FirstChild");
		$last = $el->lastChild;
		self::markTime($timer, "LastChild");
		$this->assertTrue($first->isSameNode($ch1));
		$this->assertTrue($last->isSameNode($ch2));
		try {
			$el->firstChild = 1;
			$this->assertTrue(false, "No exception has been thrown when setting the firstChild property");
		} catch(Exception $e) {
		}
	}
	
	function testPreviousNextSibling ()
	{
		$document = $this->getEmptyDocument();
		$cont = $document->createElement("div");
		$el = $document->createElement("span");
		$cont->appendChild($el);
		$this->assertEquals($el->previousSibling, null);
		$this->assertEquals($el->nextSibling, null);
		$el2 = $document->createElement("span");
		$cont->appendChild($el2);
		$this->assertEquals($el->previousSibling, null);
		$this->assertTrue($el->nextSibling->isSameNode($el2));
		$this->assertTrue($el2->previousSibling->isSameNode($el));
		$this->assertEquals($el2->nextSibling, null);
		$el3 = $document->createElement("span");
		$cont->appendChild($el3);
		$this->assertEquals($el->previousSibling, null);
		$this->assertTrue($el->nextSibling->isSameNode($el2));
		$timer = self::startTimer();
		$prev = $el2->previousSibling;
		self::markTime($timer, "NextChild");
		$next = $el2->nextSibling;
		self::markTime($timer, "PreviousChild");
		$this->assertTrue($prev->isSameNode($el));
		$this->assertTrue($next->isSameNode($el3));
		$this->assertTrue($el3->previousSibling->isSameNode($el2));
		$this->assertEquals($el3->nextSibling, null);	
		try {
			$el->previousSibling = 1;
			$this->assertTrue(false, "No exception has been thrown when setting the previousSibling property");
		} catch(Exception $e) {
		}
	}
	
	function testNormalize ()
	{
		/*	STRUCTURE							NORMALIZED
			Document							Document
				Element								Element
					Empty Text							Element
					Element									Text
						Text							Text
						Text							Element
					Text									Text
					Empty Text						Element
					Text							Element
					Element
						Text
						Empty Text
				Element
					Empty Text
				Element
					Empty Text
					Empty Text
		*/
		$document = $this->getEmptyDocument();
		$container = $document->createElement("div");
			$cont1 = $document->createElement("div");
			$container->appendChild($cont1);
				$tn = $document->createTextNode("");
				$cont1->appendChild($tn);
				$el11 = $document->createElement("div");
				$cont1->appendChild($el11);
					$tn111 = $document->createTextNode("foo");
					$tn112 = $document->createTextNode("bar");
					$el11->appendChild($tn111);
					$el11->appendChild($tn112);
				$tn11 = $document->createTextNode("test");
				$tn12 = $document->createTextNode("");
				$tn13 = $document->createTextNode("test2");
				$cont1->appendChild($tn11);
				$cont1->appendChild($tn12);
				$cont1->appendChild($tn13);
				$el12 = $document->createElement("div");
				$cont1->appendChild($el12);
					$tn121 = $document->createTextNode("foo");
					$tn122 = $document->createTextNode("");
					$el12->appendChild($tn121);
					$el12->appendChild($tn122);
			$cont2 = $document->createElement("div");
			$container->appendChild($cont2);
				$tn21 = $document->createTextNode("");
				$cont2->appendChild($tn21);
			$cont3 = $document->createElement("div");
			$container->appendChild($cont3);
				$tn31 = $document->createTextNode("");
				$tn32 = $document->createTextNode("");
				$cont3->appendChild($tn31);
				$cont3->appendChild($tn32);
		$timer = self::startTimer();
		$container->normalize();
		self::markTime($timer, "Normalize");
		$this->assertEquals($cont1->childNodes->length, 3);
		$this->assertTrue($cont1->childNodes[0]->isSameNode($el11));
		$this->assertEquals($el11->childNodes->length, 1);
		$this->assertEquals($el11->childNodes[0]->data, "foobar");
		$this->assertEquals($cont1->childNodes[1]->nodeType, 3);
		$this->assertEquals($cont1->childNodes[1]->data, "testtest2");
		$this->assertTrue($cont1->childNodes[2]->isSameNode($el12));
		$this->assertEquals($el12->childNodes->length, 1);
		$this->assertEquals($el12->childNodes[0]->data, "foo");
		$this->assertEquals($cont2->childNodes->length, 0);
		$this->assertEquals($cont3->childNodes->length, 0);
	}
	
	function testIsEqualNode ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$el2 = $document->createElement("div");
		$timer = self::startTimer();
		$res = $el->isEqualNode($el2);
		self::markTime($timer, "isEqualNode");
		$this->assertTrue($res);
		$el->setAttribute("name", "val");
		$this->assertFalse($el->isEqualNode($el2));
		$el2->setAttribute("name", "val");
		$this->assertTrue($el->isEqualNode($el2));
		$child1 = $document->createElement("span");
		$child2 = $document->createElement("span");
		$el->appendChild($child1);
		$el2->appendChild($child2);
		$timer = self::startTimer();
		$r = $el->isEqualNode($el2);
		self::markTime($timer, "isEqualNode for nodes with attributes and children");
		$this->assertTrue($r);
	}
	
	function testTextContent ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$node1 = $document->createTextNode("this ");
		$node2 = $document->createElement("div");
		$node3 = $document->createTextNode("is ");
		$node4 = $document->createElement("div");
		$node5 = $document->createTextNode("a ");
		$node6 = $document->createTextNode("test");
		$el->appendChild($node1);
		$el->appendChild($node2);
		$el->childNodes[1]->appendChild($node3);
		$el->childNodes[1]->appendChild($node4);
		$el->childNodes[1]->childNodes[1]->appendChild($node5);
		$el->appendChild($node6);
		$timer = self::startTimer();
		$text = $el->textContent;
		self::markTime($timer, "Getting textContent");	
		$el->textContent = "new content";
		self::markTime($timer, "Setting textContent");
		$this->assertEquals($text, "this is a test");
		$this->assertEquals($el->childNodes->length, 1);
		$this->assertEquals($el->childNodes[0]->nodeType, 3);
		$this->assertEquals($el->childNodes[0]->data, "new content");
	}
	
	function testCompareDocumentPostion ()
	{
		$document = $this->getEmptyDocument();
		$a1 = $document->createElement("div");
		$a2 = $document->createElement("div");
		$a3 = $document->createElement("div");
		$a4 = $document->createElement("div");
		$a1->appendChild($a2);
		$a3->appendChild($a4);
		$document->appendChild($a1);
		$document->appendChild($a3);
		$el1 = $document->createElement("div");
		$frag = $document->createDocumentFragment();
		$el2 = $document->createElement("div");
		$frag->appendChild($el1);
		$att1 = $document->createAttribute("id");
		$att2 = $document->createAttribute("id");

		$this->assertEquals($a1->compareDocumentPosition($a1), null);
		$timer = self::startTimer();
		$this->assertEquals($a1->compareDocumentPosition($a2), $a1::DOCUMENT_POSITION_FOLLOWING | $a1::DOCUMENT_POSITION_CONTAINED_BY);
		self::markTime($timer, "Element > descendant");
		$this->assertEquals($a2->compareDocumentPosition($a1), $a1::DOCUMENT_POSITION_PRECEDING | $a1::DOCUMENT_POSITION_CONTAINS);
		self::markTime($timer, "Descendant > parent");
		$this->assertEquals($a1->compareDocumentPosition($a3), $a1::DOCUMENT_POSITION_FOLLOWING);
		self::markTime($timer, "Element > next sibling");
		$this->assertEquals($a3->compareDocumentPosition($a1), $a1::DOCUMENT_POSITION_PRECEDING);
		self::markTime($timer, "Element > previous sibling");
		$this->assertEquals($a1->compareDocumentPosition($a4), $a1::DOCUMENT_POSITION_FOLLOWING);
		self::markTime($timer, "Element > next sibling child");
		$this->assertEquals($a4->compareDocumentPosition($a1), $a1::DOCUMENT_POSITION_PRECEDING);
		self::markTime($timer, "Element > parent previous sibling");
		$this->assertEquals($a2->compareDocumentPosition($a4), $a1::DOCUMENT_POSITION_FOLLOWING);
		self::markTime($timer, "Element > parent next sibling child");
		$this->assertEquals($a4->compareDocumentPosition($a2), $a1::DOCUMENT_POSITION_PRECEDING);
		self::markTime($timer, "Element > parent previous sibling child");
		$this->assertEquals($a2->compareDocumentPosition($a3), $a1::DOCUMENT_POSITION_FOLLOWING);
		self::markTime($timer, "Element > parent next sibling");
		$this->assertEquals($a3->compareDocumentPosition($a2), $a1::DOCUMENT_POSITION_PRECEDING);
		self::markTime($timer, "Element > previous sibling child");
		
		$this->assertEquals($a1->compareDocumentPosition($el1), $a1::DOCUMENT_POSITION_DISCONNECTED | $a1::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC);
		self::markTime($timer, "Element > Element in fragment");
		$this->assertEquals($a1->compareDocumentPosition($el2), $a1::DOCUMENT_POSITION_DISCONNECTED | $a1::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC);
		self::markTime($timer, "Element > Disconnected element");
		
		$this->assertEquals($a1->compareDocumentPosition($att1), $a1::DOCUMENT_POSITION_DISCONNECTED | $a1::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC);
		self::markTime($timer, "Element > Disconnected attribute");
		$a1->setAttributeNode($att1);
		$a2->setAttributeNode($att2);
		
		$this->assertEquals($a1->compareDocumentPosition($att1), $a1::DOCUMENT_POSITION_FOLLOWING | $a1::DOCUMENT_POSITION_CONTAINED_BY);
		self::markTime($timer, "Element > Own attrubte");
		$this->assertEquals($att1->compareDocumentPosition($a1), $a1::DOCUMENT_POSITION_PRECEDING | $a1::DOCUMENT_POSITION_CONTAINS);
		self::markTime($timer, "Attribute > Owner element");

		$this->assertEquals($a2->compareDocumentPosition($att1), $a1::DOCUMENT_POSITION_PRECEDING);
		self::markTime($timer, "Element > Parent node attribute");
		$this->assertEquals($att1->compareDocumentPosition($a2), $a1::DOCUMENT_POSITION_FOLLOWING);
		self::markTime($timer, "Attribute > Owner element child");
		
		$this->assertEquals($att2->compareDocumentPosition($att1), $a1::DOCUMENT_POSITION_PRECEDING);
		self::markTime($timer, "Attribute > Owner element previous sibling attribute");
		$this->assertEquals($att1->compareDocumentPosition($att2), $a1::DOCUMENT_POSITION_FOLLOWING);
		self::markTime($timer, "Attribute > Owner element next sibling attribute");
		
		$this->assertEquals($a1->compareDocumentPosition($document,true), $a1::DOCUMENT_POSITION_PRECEDING | $a1::DOCUMENT_POSITION_CONTAINS);
		self::markTime($timer, "Element > Document");
		$this->assertEquals($document->compareDocumentPosition($a1), $a1::DOCUMENT_POSITION_FOLLOWING | $a1::DOCUMENT_POSITION_CONTAINED_BY);
		self::markTime($timer, "Document > Element");
		
		$discEl1 = $document->createElement("div");
		$discEl2 = $document->createElement("div");
		$discEl1->appendChild($discEl2);
		$this->assertEquals($discEl1->compareDocumentPosition($discEl2), $a1::DOCUMENT_POSITION_FOLLOWING | $a1::DOCUMENT_POSITION_CONTAINED_BY);
		
		$discEl3 = $document->createElement("div");
		$discEl2->appendChild($discEl3);
		$this->assertEquals($discEl3->compareDocumentPosition($discEl1), $a1::DOCUMENT_POSITION_PRECEDING | $a1::DOCUMENT_POSITION_CONTAINS);
		
		$discEl4 = $document->createElement("div");
		$discEl5 = $document->createElement("div");
		$discEl1->appendChild($discEl4);
		$discEl4->appendChild($discEl5);
		$this->assertEquals($discEl3->compareDocumentPosition($discEl5), $a1::DOCUMENT_POSITION_FOLLOWING);
	}
	
	function testCloneNode ()
	{
		$document = $this->getEmptyDocument();
		$container = $document->createElement("div");
		$el = $document->createElement("div");
		$text = $document->createTextNode("test");
		$attr = $document->createAttribute("foo");
		$attr->value = "bar";
		$el->setAttributeNode($attr);
		$container->appendChild($el);
		$container->appendChild($text);
		$timer = self::startTimer();
		$clone = $el->cloneNode(false);
		self::markTime($timer, "cloneNode");
		$cloneText = $text->cloneNode(false);
		$this->assertEquals($clone->parentNode, null);
		$this->assertEquals($clone->childNodes->length, 0);
		$this->assertEquals($clone->attributes->length, $el->attributes->length);
		$this->assertFalse($clone->attributes[0]->isSameNode($el->attributes[0]));
		$this->assertTrue($clone->attributes[0]->ownerElement->isSameNode($clone));
		$this->assertEquals($cloneText->parentNode, null);
		$this->assertEquals($cloneText->data, $text->data);
		$child = $document->createElement("div");
		$el->appendChild($child);
		$clone2 = $el->cloneNode(true);
		$this->assertEquals($clone2->childNodes->length, 1);
		$this->assertTrue($clone2->childNodes[0]->parentNode->isSameNode($clone2));
		$this->assertFalse($child->parentNode->isSameNode($clone2->childNodes[0]->parentNode));
	}
	
	function testDocumentURIandBaseURI ()
	{
		$fakeUrl = "http://www.fakesiteurltest.com";
		$doc = $this->parseHTML("", array("baseURL"=>$fakeUrl));
		$this->assertEquals($doc->documentURI, $fakeUrl);
		$this->assertEquals($doc->baseURI, $fakeUrl);
		$this->assertEquals($doc->body->baseURI, $fakeUrl);
	}
	
	function testDocumentURIandBaseUriWithBaseTag ()
	{
		$fakeUrl = "http://www.fakesiteurltest.com";
		$fakeUrl2 = "http://www.fakesiteurltest2.com";
		$doc = $this->parseHTML("<base href='$fakeUrl2'>", array("baseURL"=>$fakeUrl));
		$this->assertEquals($doc->documentURI, $fakeUrl);
		$this->assertEquals($doc->baseURI, $fakeUrl2);
		$this->assertEquals($doc->body->baseURI, $fakeUrl2);
	}
	
	function testDocumentURIandBaseUriWithMultipleBaseTags ()
	{
		$fakeUrl = "http://www.fakesiteurltest.com";
		$fakeUrl2 = "http://www.fakesiteurltest2.com";
		$fakeUrl3 = "http://www.fakesiteurltest3.com";
		$doc = $this->parseHTML("<base href='$fakeUrl2'><base href='$fakeUrl3'>", array("baseURL"=>$fakeUrl));
		$this->assertEquals($doc->documentURI, $fakeUrl);
		$this->assertEquals($doc->baseURI, $fakeUrl3);
		$this->assertEquals($doc->body->baseURI, $fakeUrl3);
	}
	
	function testRelativePathInBaseTagIsIgnored ()
	{
		$fakeUrl = "relative/path";
		$doc = $this->parseHTML("<base href='$fakeUrl'>");
		$this->assertTrue($doc->baseURI !== $fakeUrl);
	}
}