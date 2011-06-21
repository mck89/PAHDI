<?php
class NamedNodeMapTest extends PAHDITest
{	
	static $document;
	static $map;
	static $attr1;
	static $attr2;
	static $attr3;
	static $attr4;
	
	function testInitialLength ()
	{
		self::$document = $this->getEmptyDocument();
		self::$map = new NamedNodeMap(self::$document);
		$this->assertEquals(self::$map->length, 0);
	}
	
	/**
     * @depends testInitialLength
     */
	function testSetNamedItem ()
	{
		self::$attr1 = self::$document->createAttribute("foo");
		self::$attr1->value = "test";
		self::$attr2 = self::$document->createAttribute("bar");
		$timer = self::startTimer();
		self::$map->setNamedItem(self::$attr1);
		self::markTime($timer, "setNamedItem");
		$this->assertEquals(self::$map->setNamedItem(self::$attr2), null);
		$this->assertEquals(self::$map->length, 2);
	}
	
	/**
     * @depends testSetNamedItem
     */
	function testSetNamedItemWithExistingAttribute ()
	{
		self::$attr3 = self::$document->createAttributeNS("test", "bar");
		$timer = self::startTimer();
		$ret = self::$map->setNamedItem(self::$attr3);
		self::markTime($timer, "setNamedItem with attribute already present");
		$this->assertTrue($ret->isSameNode(self::$attr2));
		$this->assertEquals(self::$map->length, 2);
	}
	
	/**
     * @depends testSetNamedItemWithExistingAttribute
     */
	function testItem ()
	{
		$ret = self::$map->item(1);
		$this->assertTrue($ret->isSameNode(self::$attr3));
		$this->assertEquals(self::$map->item(10), null);
	}
	
	/**
     * @depends testItem
     */
	function testGetNamedItem ()
	{
		$timer = self::startTimer();
		$ret = self::$map->getNamedItem("foo");
		self::markTime($timer, "getNamedItem");
		$this->assertTrue($ret->isSameNode(self::$attr1));
		$this->assertEquals(self::$map->getNamedItem("inexistent"), null);
	}
	
	/**
     * @depends testGetNamedItem
     */
	function testObjectAndArrayAccess ()
	{
		$ret1 = self::$map[0];
		$this->assertTrue($ret1->isSameNode(self::$attr1));
		$ret2 = self::$map->foo;
		$this->assertTrue($ret2->isSameNode(self::$attr1));
		$this->assertTrue(self::$map->FOO->isSameNode(self::$attr1));
		$this->assertTrue($ret2->isSameNode(self::$map["foo"]));
		$this->assertTrue($ret2->isSameNode(self::$map["FOO"]));
	}
	
	/**
     * @depends 			testObjectAndArrayAccess
	 * @expectedException 	DomException
     */
	function testAccessInexistentAttributeThrowsAnException ()
	{
		self::$map->inexistent === null;
	}
	
	/**
     * @depends testAccessInexistentAttributeThrowsAnException
     */
	function testIsset ()
	{
		$this->assertTrue(isset(self::$map[1]));
		$this->assertFalse(isset(self::$map[10]));
		$this->assertTrue(isset(self::$map->foo));
		$this->assertFalse(isset(self::$map->inexistent));
	}
	
	/**
     * @depends testIsset
     */
	function testForeachIteration ()
	{
		$i=0;
		foreach (self::$map as $k=>&$v) {
			$this->assertEquals($k, $i);
			$exp = $i == 0 ? self::$attr1 : self::$attr3;
			$i++;
			$this->assertTrue($v->isSameNode($exp));
		}
	}
	
	/**
     * @depends testForeachIteration
     */
	function testRemoveNamedItem ()
	{
		$timer = self::startTimer();
		$ret = self::$map->removeNamedItem("foo");
		self::markTime($timer, "removeNamedItem");
		$this->assertTrue($ret->isSameNode(self::$attr1));
		$this->assertEquals(self::$map->length, 1);
	}
	
	/**
     * @depends 			testRemoveNamedItem
	 * @expectedException 	DomException
     */
	function testRemoveNamedItemThrowsExceptionForInexistentAttribute ()
	{
		self::$map->removeNamedItem("inexistent");
	}
	
	/**
     * @depends testRemoveNamedItemThrowsExceptionForInexistentAttribute
     */
	function testSetNamedItemNS ()
	{
		self::$map->setNamedItem(self::$attr1);
		$timer = self::startTimer();
		$ret1 = self::$map->setNamedItemNS(self::$attr2);
		self::markTime($timer, "setNamedItemNS");
		$this->assertEquals($ret1, null);
		$this->assertEquals(self::$map->length, 3);
		self::$attr4 = self::$document->createAttribute("bar");
		$ret2 = self::$map->setNamedItemNS(self::$attr4);
		$this->assertTrue($ret2->isSameNode(self::$attr2));
	}
	
	/**
     * @depends testSetNamedItemNS
     */
	function testGetNamedItemNS ()
	{
		$timer = self::startTimer();
		$ret = self::$map->getNamedItemNS("test", "bar");
		self::markTime($timer, "getNamedItemNS");
		$this->assertTrue($ret->isSameNode(self::$attr3));
		$this->assertEquals(self::$map->getNamedItemNS("inexistent", "bar"), null);
	}
	
	/**
     * @depends testGetNamedItemNS
     */
	function testRemoveNamedItemNS ()
	{
		$timer = self::startTimer();
		$ret = self::$map->removeNamedItemNS("test", "bar");
		self::markTime($timer, "removeNamedItemNS");
		$this->assertTrue($ret->isSameNode(self::$attr3));
	}
	
	/**
     * @depends 			testRemoveNamedItemNS
	 * @expectedException 	DomException
     */
	function testRemoveNamedItemNSThrowsExceptionForInexistentAttribute ()
	{
		self::$map->removeNamedItemNS("inexistent","bar");
	}
	
	/**
     * @depends testRemoveNamedItemNSThrowsExceptionForInexistentAttribute
     */
	function testUnset ()
	{
		$this->assertEquals(self::$map->length, 2);
		unset(self::$map->foo);
		$this->assertEquals(self::$map->length, 1);
	}
	
	/**
     * @depends 			testUnset
	 * @expectedException 	DomException
     */
	function testUnsetThrowsExceptionForInexistentAttribute ()
	{
		unset(self::$map->foo2);
	}
	
	/**
     * @depends testUnsetThrowsExceptionForInexistentAttribute
     */
	function testUnsetWithArrayAccess ()
	{
		unset(self::$map[0]);
		$this->assertEquals(self::$map->length, 0);
	}
	
	/**
     * @depends				testUnsetWithArrayAccess
	 * @expectedException 	DomException
     */
	function testArrayStyleSettingThrowsAnException ()
	{
		self::$map[] = 1;
	}
}
?>