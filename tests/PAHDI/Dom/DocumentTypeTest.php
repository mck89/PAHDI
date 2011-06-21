<?php
class DocumentTypeTest extends PAHDITest
{
	/**
	 * @expectedException 	DomException
     */
	function testAppendChildThrowsException ()
	{
		$document = $this->getEmptyDocument();
		$doctype = new DocumentType("a", "b", "c");
		$el = $document->createElement("div");
		$doctype->appendChild($el);
	}
}