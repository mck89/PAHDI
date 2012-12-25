<?php
class HTMLMenuTest extends PAHDITest
{
	function testType ()
	{
		$document = $this->getEmptyDocument();
		$menu = $document->createElement("menu");
		$this->assertEquals($menu->type, "list");
		$menu->type = "test";
		$this->assertEquals($menu->type, "list");
		$this->assertEquals($menu->getAttribute("type"), "test");
		$menu->type = "context";
		$this->assertEquals($menu->type, "context");
	}
}