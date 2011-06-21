<?php
class HTMLCollectionTest extends PAHDITest
{
	static $collection;
	
	static function setUpBeforeClass ()
	{
		$document = PAHDITest::parseHTML("<input id='a'><input name='b'>");
		self::$collection = $document->getElementsByTagName("input");
	}
	
	function testNamedItem ()
	{
		$first = self::$collection->namedItem("a");
		$second = self::$collection->namedItem("b");
		$null = self::$collection->namedItem("c");
		$this->assertTrue($first !== null);
		$this->assertTrue($second !== null);
		$this->assertEquals($null, null);
	}
	
	function testNamedItemArrayAccess ()
	{
		$first = self::$collection["a"];
		$second = self::$collection["b"];
		$null = self::$collection["c"];
		$this->assertTrue($first !== null);
		$this->assertTrue($second !== null);
		$this->assertEquals($null, null);
	}
}