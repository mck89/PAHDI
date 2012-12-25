<?php
class HTMLProgressTest extends PAHDITest
{
	function testAttributes ()
	{
		$document = $this->getEmptyDocument();
		$progress = $document->createElement("progress");
		$this->assertEquals($progress->value, 0);
		$this->assertEquals($progress->max, 1);
		$progress->max = 10;
		$progress->value = 20;
		$this->assertEquals($progress->value, 10);
		$this->assertEquals($progress->max, 10);
	}
}