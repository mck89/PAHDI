<?php
class PAHDISearchTest extends PAHDITest
{
	static $doc;
	static $search;
	
	function generateStructure ()
	{
		$html = "
			<div id='1'>
				<div id='1-1'>
				</div>
				<div id='1-2'>
				</div>
				<div id='1-3'>
				</div>
			</div>
			<div id='2'>
				<div id='2-1'>
				</div>
				<div id='2-2'>
					<div id='2-2-1'>
					</div>
				</div>
				<div id='2-3'>
				</div>
			</div>
		";
		self::$doc = $this->parseHTML($html);
		self::$search = new PAHDISearch(self::$doc->body);
	}

	function testFindDescendant ()
	{
		$this->generateStructure();
		$fn = function ($node) {
			return $node->tagName === "div";
		};
		self::$search->find($fn, PAHDISearch::DESCENDANTS);
		$this->assertEquals(self::$search->length, 9);
		$test = true;
		$ids = array("1", "1-1", "1-2", "1-3", "2", "2-1", "2-2", "2-2-1", "2-3");
		foreach (self::$search as $k=>$node) {
			if ($node->id !== $ids[$k]) {
				$test = false;
				break;
			}
		}
		$this->assertTrue($test);
	}
	
	function testFilter ()
	{
		$fn = function ($node) {
			return $node->id === "1" || $node->id === "2";
		};
		self::$search->filter($fn);
		$this->assertEquals(self::$search->length, 2);
	}
	
	function testFindChildren ()
	{
		$fn = function ($node) {
			return $node->tagName === "div";
		};
		self::$search->find($fn, PAHDISearch::CHILDREN);
		$this->assertEquals(self::$search->length, 6);
		$test = true;
		$ids = array("1-1", "1-2", "1-3", "2-1", "2-2", "2-3");
		foreach (self::$search as $k=>$node) {
			if ($node->id !== $ids[$k]) {
				$test = false;
				break;
			}
		}
		$this->assertTrue($test);
	}
	
	function testFindNextSiblings ()
	{
		$filter = function ($node) {
			return $node->id === "1-1" || $node->id === "2-1";
		};
		self::$search->filter($filter);
		$fn = function ($node) {
			return $node->tagName === "div";
		};
		self::$search->find($fn, PAHDISearch::NEXT_SIBLINGS);
		$this->assertEquals(self::$search->length, 4);
		$test = true;
		$ids = array("1-2", "1-3", "2-2", "2-3");
		foreach (self::$search as $k=>$node) {
			if ($node->id !== $ids[$k]) {
				$test = false;
				break;
			}
		}
		$this->assertTrue($test);
	}
	
	function testFindPreviousSiblings ()
	{
		$filter = function ($node) {
			return $node->id === "1-3" || $node->id === "2-3";
		};
		self::$search->filter($filter);
		$fn = function ($node) {
			return $node->tagName === "div";
		};
		self::$search->find($fn, PAHDISearch::PREVIOUS_SIBLINGS);
		$this->assertEquals(self::$search->length, 4);
		$test = true;
		$ids = array("1-1", "1-2", "2-1", "2-2");
		foreach (self::$search as $k=>$node) {
			if ($node->id !== $ids[$k]) {
				$test = false;
				break;
			}
		}
		$this->assertTrue($test);
	}
	
	function testFindSiblings ()
	{
		$filter = function ($node) {
			return $node->id === "1-2" || $node->id === "2-2";
		};
		self::$search->filter($filter);
		$fn = function ($node) {
			return $node->tagName === "div";
		};
		self::$search->find($fn, PAHDISearch::SIBLINGS);
		$this->assertEquals(self::$search->length, 4);
		$test = true;
		$ids = array("1-1", "1-3", "2-1", "2-3");
		foreach (self::$search as $k=>$node) {
			if ($node->id !== $ids[$k]) {
				$test = false;
				break;
			}
		}
		$this->assertTrue($test);
	}
	
	function testFindAncestors ()
	{
		$fn = function ($node) {
			return $node->tagName === "div";
		};
		self::$search->find($fn, PAHDISearch::ANCESTORS);
		$this->assertEquals(self::$search->length, 4);
		$test = true;
		$ids = array("1", "1", "2", "2");
		foreach (self::$search as $k=>$node) {
			if ($node->id !== $ids[$k]) {
				$test = false;
				break;
			}
		}
		$this->assertTrue($test);
	}
	
	function testUnique ()
	{
		self::$search->unique();
		$this->assertEquals(self::$search->length, 2);
	}
	
	function testSort ()
	{
		$r = array(self::$doc->body->firstElementChild, self::$doc->body);
		$s = new PAHDISearch($r);
		$s->sort();
		$this->assertEquals($s[0]->tagName, "body");
		$this->assertEquals($s[1]->tagName, "div");
	}
}