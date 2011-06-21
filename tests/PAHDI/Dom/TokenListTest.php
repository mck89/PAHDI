<?php
class TokenListTest extends PAHDITest
{
	static $el;
	
	function testInitialLength ()
	{
		$document = $this->getEmptyDocument();
		self::$el = $document->createElement("div");
		$list = self::$el->classList;
		$this->assertEquals($list->length, 0);
	}
	
	/**
     * @depends testInitialLength
     */
	function testNewLength ()
	{
		self::$el->className = "foo bar";
		$list = self::$el->classList;
		$this->assertEquals($list->length, 2);
	}
	
	/**
     * @depends testNewLength
     */
	function testItem ()
	{
		$list = self::$el->classList;
		$this->assertEquals($list->item(0), "foo");
		$this->assertEquals($list->item(3), null);
	}
	
	/**
     * @depends testItem
     */
	function testContains ()
	{
		$list = self::$el->classList;
		$this->assertTrue($list->contains("foo"));
		$this->assertFalse($list->contains("false"));
	}
	
	/**
     * @depends testContains
     */
	function testAdd ()
	{
		$list = self::$el->classList;
		$list->add("test");
		$this->assertEquals(self::$el->className, "foo bar test");
		$this->assertEquals($list->length, 3);
	}
	
	/**
     * @depends testAdd
     */
	function testRemove ()
	{
		$list = self::$el->classList;
		$list->remove("foo");
		$this->assertEquals(self::$el->className, "bar test");
		$this->assertEquals($list->length, 2);
	}
	
	/**
     * @depends testRemove
     */
	function testToggle ()
	{
		$list = self::$el->classList;
		$list->toggle("foo");
		$this->assertEquals(self::$el->className, "bar test foo");
		$this->assertEquals($list->length, 3);
		$list->toggle("foo");
		$this->assertEquals(self::$el->className, "bar test");
		$this->assertEquals($list->length, 2);
	}
	
	/**
     * @depends testToggle
     */
	function testArrayAccess ()
	{
		$list = self::$el->classList;		
		$this->assertEquals($list[0], "bar");
	}
	
	/**
     * @depends testArrayAccess
     */
	function testArraySet ()
	{
		$list = self::$el->classList;	
		$list[10] = "test2";
		$this->assertEquals($list->length, 2);
		$list[1] = "test2";
		$this->assertEquals(self::$el->className, "bar test2");
		$list[] = "test3";
		$this->assertEquals(self::$el->className, "bar test2 test3");
	}
	
	/**
     * @depends testArraySet
     */
	function testArrayIsset ()
	{
		$list = self::$el->classList;	
		$this->assertTrue(isset($list[1]));
		$this->assertFalse(isset($list[10]));
		$this->assertTrue(isset($list["test2"]));
		$this->assertFalse(isset($list["test22"]));
	}
	
	/**
     * @depends testArrayIsset
     */
	function testArrayUnset ()
	{
		$list = self::$el->classList;	
		unset($list[1]);
		$this->assertEquals(self::$el->className, "bar test3");
		unset($list[10]);
		$this->assertEquals(self::$el->className, "bar test3");
		unset($list["test3"]);
		$this->assertEquals(self::$el->className, "bar");
		unset($list["test4"]);
		$this->assertEquals(self::$el->className, "bar");
	}
	
	/**
     * @depends testArrayUnset
	 * @expectedException 	DomException
     */
	function testInvalidTokenThrowsException ()
	{
		$list = self::$el->classList;	
		$list->add("foo bar");
	}
}