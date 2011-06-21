<?php
class AttrTest extends PAHDITest
{	
	function testOwnerElement ()
	{
		$document = $this->getEmptyDocument();
		$el = $document->createElement("div");
		$att = $document->createAttribute("att");
		$el->setAttributeNode($att);
		$this->assertTrue($att->ownerElement->isSameNode($el));
		$el2 = $document->createElement("span");
		$ret=false;
		try{
			$el2->setAttributeNode($att);
		}catch(Exception $e){
			$ret=true;
		}
		$this->assertTrue($ret);
		$el->removeAttribute("att");
		$this->assertEquals($att->ownerElement, null);
	}
}