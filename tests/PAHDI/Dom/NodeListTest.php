<?php
class NodeListTest extends PAHDITest
{
	static $list;
	static $a;
	static $b;
	
	function testInitialLength ()
	{
		self::$list = new NodeList;
		self::$a = new Node();
		self::$b = new Node();
		$this->assertEquals(self::$list->length, 0);
	}
	
	/**
     * @depends testInitialLength
     */
	function testAppendNode ()
	{
		$timer = self::startTimer();
		self::$list->_appendNode(self::$a);
		self::markTime($timer, "appendNode");
		$this->assertTrue(self::$list->item(0)->isSameNode(self::$a));
		$this->assertEquals(self::$list->length, 1);
		
	}
	
	/**
     * @depends testAppendNode
     */
	function testNodeListWorksWithCount ()
	{
		$this->assertEquals(count(self::$list->length), 1);
	}
	
	/**
     * @depends testNodeListWorksWithCount
     */
	function testAddNodeAtIndex ()
	{
		$timer = self::startTimer();
		self::$list->_addNodeAt(self::$b, 0);
		self::markTime($timer, "addNode at specified index");
		$this->assertTrue(self::$list->item(0)->isSameNode(self::$b));
		$this->assertEquals(self::$list->length, 2);
	}
	
	/**
     * @depends testAddNodeAtIndex
     */
	function testArrayAccess ()
	{
		$timer = self::startTimer();
		$el = self::$list[0];
		self::markTime($timer, "Array Access");
		$this->assertTrue($el->isSameNode(self::$b));
		$this->assertTrue(self::$list[1]->isSameNode(self::$a));
	}
	
	/**
     * @depends testArrayAccess
     */
	function testForeachIteration ()
	{
		foreach (self::$list as $k=>&$v) {
			$exp = $k === 0 ? self::$b : self::$a;
			$this->assertTrue($v->isSameNode($exp));
		}
	}
	
	/**
     * @depends testForeachIteration
     */
	function testRemoveNodeAt ()
	{
		$timer = self::startTimer();
		self::$list->_removeNodeAt(0);
		self::markTime($timer, "_removeNode");
		$this->assertTrue(self::$list[0]->isSameNode(self::$a));
		$this->assertEquals(self::$list->length, 1);
	}
	
	/**
     * @depends testRemoveNodeAt
     */
	function testArrayLikeManipIgnored ()
	{
		self::$list[0] = null;
		$this->assertTrue(self::$list[0]->isSameNode(self::$a));
		unset(self::$list[0]);
		$this->assertTrue(self::$list[0]->isSameNode(self::$a));
	}
}