<?php
class HTMLMeterTest extends PAHDITest
{
	function testAttributes ()
	{
		$document = $this->getEmptyDocument();
		$meter = $document->createElement("meter");
		$this->assertEquals($meter->value, 0);
		$this->assertEquals($meter->min, 0);
		$this->assertEquals($meter->max, 1);
		$this->assertEquals($meter->low, 0);
		$this->assertEquals($meter->high, 1);
		$this->assertEquals($meter->optimum, 0.5);
		$meter->min = 10;
		$meter->max = 9;
		$this->assertEquals($meter->min, 10);
		$this->assertEquals($meter->max, 10);
		$this->assertEquals($meter->high, 10);
		$this->assertEquals($meter->low, 10);
		$this->assertEquals($meter->value, 10);
		$this->assertEquals($meter->optimum, 10);
		$meter->min = 10;
		$meter->max = 39;
		$this->assertEquals($meter->optimum, 24.5);
	}
}