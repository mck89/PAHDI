<?php
class SVGElementTest extends PAHDITest
{
	function testOwnerSVGElement ()
	{
		$document = ParserHTMLTest::$documents["SVG1"];
		$stops = $document->getElementsByTagName("stop");
		$svg = $document->getElementsByTagName("svg");
		$this->assertTrue($stops[0]->ownerSVGElement->isSameNode($svg[0]));
		$this->assertEquals($svg[0]->ownerSVGElement, null);
	}
}