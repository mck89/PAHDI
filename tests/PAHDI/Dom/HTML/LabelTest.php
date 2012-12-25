<?php
class HTMLLabelTest extends PAHDITest
{
	function testControl ()
	{
		$document = $this->getEmptyDocument();
		$label = $document->createElement("label");
		$input = $document->createElement("input");
		$label->htmlFor = $input->id = "test";
		$document->appendChild($label);
		$document->appendChild($input);
		$this->assertTrue($label->control->isSameNode($input));
		$input->id = "";
		$this->assertEquals($label->control, null);
		$label->appendChild($input);
		$label->htmlFor = "";
		$this->assertTrue($label->control->isSameNode($input));
	}
}