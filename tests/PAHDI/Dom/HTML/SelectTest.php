<?php
class HTMLSelectTest extends PAHDITest
{
	static $select;
	static $options;
	
	static function setUpBeforeClass ()
	{
		$document = PAHDITest::parseHTML("<select><option>a</option><option>b</option><option>c</option></select>");
		$sel = $document->getElementsByTagName("select");
		self::$select = $sel[0];
		self::$options = self::$select->getElementsByTagName("option");
	}
	
	function testInitialValue ()
	{
		$this->assertEquals(self::$select->value, "a");
		$this->assertEquals(self::$select->selectedIndex, 0);
	}
	
	/**
     * @depends testInitialValue
     */
	function testChangeValue ()
	{
		self::$select->value = "b";
		$this->assertEquals(self::$select->value, "b");
		$this->assertEquals(self::$select->selectedIndex, 1);
		$this->assertTrue(self::$options[1]->selected);
		$this->assertTrue(!(self::$options[0]->selected && self::$options[2]->selected));
	}
	
	/**
     * @depends testChangeValue
     */
	function testChangeSelectedIndex ()
	{
		self::$select->selectedIndex = 2;
		$this->assertEquals(self::$select->value, "c");
		$this->assertEquals(self::$select->selectedIndex, 2);
		$this->assertTrue(self::$options[2]->selected);
		$this->assertTrue(!(self::$options[0]->selected && self::$options[1]->selected));
	}
	
	/**
     * @depends testChangeSelectedIndex
     */
	function testSelectOption ()
	{
		self::$options[1]->selected = true;
		$this->assertEquals(self::$select->value, "b");
		$this->assertEquals(self::$select->selectedIndex, 1);
		$this->assertTrue(self::$options[1]->selected);
		$this->assertTrue(!(self::$options[0]->selected && self::$options[2]->selected));
	}
	
	function testAddRemove ()
	{
		$document = $this->parseHTML("<select><option>a</option><option>b</option><option>c</option></select>");
		$selects = $document->getElementsByTagName("select");
		$select = $selects[0];
		$newopt = $document->createElement("option");
		$newopt->value = "test";
		$select->add($newopt);
		$this->assertEquals($select->lastChild->value, "test");
		$newopt2 = $document->createElement("option");
		$newopt2->value = "test2";
		$select->add($newopt2, $select->firstChild);
		$this->assertEquals($select->firstChild->value, "test2");
		$length = $select->childNodes->length;
		$select->remove(0);
		$this->assertEquals($select->childNodes->length, $length - 1);
	}
}