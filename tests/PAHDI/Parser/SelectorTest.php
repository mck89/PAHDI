<?php
class ParserSelectorTest extends PAHDITest
{	
	static $sources = array(
		"<input type='checkbox' checked lang='en' rel='test'>
		<input type='checkbox' lang='en-UK'><input type='checkbox' rel='ab test' disabled>",
		"<div id='1'></div><div id='2'></div><div id='3'></div><div id='4'></div><div id='5'></div>
		<div id='6'></div><div id='7'></div><div id='8'></div><div id='9'></div><div id='10'></div>
		<div id='11'></div><div id='12'></div><div id='13'></div><div id='14'></div><div id='15'></div>
		<div id='16'></div><div id='17'></div><div id='18'></div><div id='19'></div><div id='20'></div>",
		"<div id='1'></div><b></b><div id='2'></div><div id='3'></div><b></b><div id='4'></div><div id='5'></div><b></b>
		<div id='6'></div><div id='7'></div><b></b><div id='8'></div><b></b><div id='9'></div><div id='10'></div><b></b>
		<div id='11'></div><div id='12'></div><b></b><div id='13'></div><b></b><div id='14'></div><div id='15'></div><b></b>
		<div id='16'></div><div id='17'></div><b></b><div id='18'></div><b></b><div id='19'></div><div id='20'></div><b></b>",
		"<div id='foo.bar'></div><div rel='foo]bar.test'></div>"
	);
	
	/**
	 * @dataProvider selectorProvider
	 */
	function testSelectors ($selector, $len, $source = null, $ids = null)
	{
		if ($source === null) {
			$document = ParserHTMLTest::$documents["HTML3"];
		} else {
			$src = self::$sources[$source];
			if (is_string($src)) {
				$src = self::$sources[$source] = $this->parseHTML($src);
			}
			$document = $src;
		}
		$timer = self::startTimer();
		$list = $document->querySelectorAll($selector);
		self::markTime($timer, "Parsed selector \"$selector\"");
		$this->assertEquals($list->length, $len);
		if ($ids) {
			$eq = array();
			foreach ($list as $k => $n) {
				$eq[] = (int) $n->id;
			}
			$this->assertEquals($ids, $eq);
		}
	}
	
	function selectorProvider ()
	{
		return array(
			array("*", 267),
			array("div", 30),
			array("*|div", 30),
			array("#curstatusrhs", 1),
			array(".theme", 5),
			array("div#curstatusrhs", 1),
			array(".theme > li", 17),
			array(".theme a", 15),
			array("h2 ~ ul.theme", 5),
			array("h2 + ul.theme", 4),
			array("h2+ul.theme li a", 10),
			array(".theme, .press_date", 9),
			array(":root", 1),
			array(":empty", 21),
			array(":link", 81),
			array("a:last-child", 59),
			array("a:first-child", 62),
			array("a:only-child", 48),
			array("ul:not(.theme)", 8),
			array("[class]", 62),
			array("[rel=stylesheet]", 3),
			array("[rel='stylesheet']", 3),
			array("[class^=t]", 9),
			array("[class$=e]", 8),
			array("[class*=e]", 44),
			array("a:last-of-type", 67),
			array("div:first-of-type", 14),
			array("a:only-of-type", 60),
			array(":lang(en)", 1),
			array(":enabled", 2, 0),
			array(":disabled", 1, 0),
			array(":checked", 1, 0),
			array('[lang|="en"]', 2, 0),
			array('[rel~="test"]', 2, 0),
			array('#foo\.bar', 1, 3),
			array('[rel="foo]bar.test"]', 1, 3),
			
			array("div:nth-child(2)", 1, 1, array(2)),
			array("div:nth-child(-2)", 0, 1),
			array("div:nth-child(2n)", 10, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-child(n)", 20, 1),
			array("div:nth-child(-n)", 0, 1),
			array("div:nth-child(1n)", 20, 1),
			array("div:nth-child(0n)", 0, 1),
			array("div:nth-child(-2n)", 0, 1),
			array("div:nth-child(2n+1)", 10, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-child(2n+2)", 10, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-child(2n+3)", 9, 1, array(3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-child(2n+4)", 9, 1, array(4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-child(2n+5)", 8, 1, array(5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-child(2n+10)", 6, 1, array(10, 12, 14, 16, 18, 20)),
			array("div:nth-child(2n+20)", 1, 1, array(20)),
			array("div:nth-child(3n+4)", 6, 1, array(4, 7, 10, 13, 16, 19)),
			array("div:nth-child(2n-1)", 10, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-child(2n-2)", 10, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-child(2n-3)", 10, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-child(2n-4)", 10, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-child(3n-1)", 7, 1, array(2, 5, 8, 11, 14, 17, 20)),
			array("div:nth-child(3n-2)", 7, 1, array(1, 4, 7, 10, 13, 16, 19)),
			array("div:nth-child(0n+2)", 1, 1, array(2)),
			array("div:nth-child(0n+0)", 0, 1),
			array("div:nth-child(0n-2)", 0, 1),
			array("div:nth-child(-2n)", 0, 1),
			array("div:nth-child(-2n-2)", 0, 1),
			array("div:nth-child(-2n+0)", 0, 1),
			array("div:nth-child(-2n+1)", 1, 1, array(1)),
			array("div:nth-child(-4n+12)", 3, 1, array(4, 8, 12)),
			array("div:nth-child(-4n+11)", 3, 1, array(3, 7, 11)),
			array("div:nth-child(-2n+2)", 1, 1, array(2)),
			
			array("div:nth-last-child(2)", 1, 1, array(19)),
			array("div:nth-last-child(-2)", 0, 1),
			array("div:nth-last-child(2n)", 10, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-last-child(n)", 20, 1),
			array("div:nth-last-child(-n)", 0, 1),
			array("div:nth-last-child(1n)", 20, 1),
			array("div:nth-last-child(0n)", 0, 1),
			array("div:nth-last-child(-2n)", 0, 1),
			array("div:nth-last-child(2n+1)", 10, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-last-child(2n+2)", 10, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-last-child(2n+3)", 9, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18)),
			array("div:nth-last-child(2n+4)", 9, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17)),
			array("div:nth-last-child(2n+5)", 8, 1, array(2, 4, 6, 8, 10, 12, 14, 16)),
			array("div:nth-last-child(2n+10)", 6, 1, array(1, 3, 5, 7, 9, 11)),
			array("div:nth-last-child(2n+20)", 1, 1, array(1)),
			array("div:nth-last-child(3n+4)", 6, 1, array(2, 5, 8, 11, 14, 17)),
			array("div:nth-last-child(2n-1)", 10, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-last-child(2n-2)", 10, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-last-child(2n-3)", 10, 1, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-last-child(2n-4)", 10, 1, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-last-child(3n-1)", 7, 1, array(1, 4, 7, 10, 13, 16, 19)),
			array("div:nth-last-child(3n-2)", 7, 1, array(2, 5, 8, 11, 14, 17, 20)),
			array("div:nth-last-child(0n+2)", 1, 1, array(19)),
			array("div:nth-last-child(0n+0)", 0, 1),
			array("div:nth-last-child(0n-2)", 0, 1),
			array("div:nth-last-child(-2n)", 0, 1),
			array("div:nth-last-child(-2n-2)", 0, 1),
			array("div:nth-last-child(-2n+0)", 0, 1),
			array("div:nth-last-child(-2n+1)", 1, 1, array(20)),
			array("div:nth-last-child(-4n+12)", 3, 1, array(9, 13, 17)),
			array("div:nth-last-child(-4n+11)", 3, 1, array(10, 14, 18)),
			array("div:nth-last-child(-2n+2)", 1, 1, array(19)),
			
			array("div:nth-of-type(2n+1)", 10, 2, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19)),
			array("div:nth-of-type(2n+2)", 10, 2, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			
			array("div:nth-last-of-type(2n+1)", 10, 2, array(2, 4, 6, 8, 10, 12, 14, 16, 18, 20)),
			array("div:nth-last-of-type(2n+2)", 10, 2, array(1, 3, 5, 7, 9, 11, 13, 15, 17, 19))
		);
	}
	
	function testTargetPseudoSelector ()
	{
		$document = $this->parseHTML("<a name='test'>", array("baseURL"=>"http://www.faketest.com#test"));
		$list = $document->querySelectorAll(":target");
		$this->assertEquals($list->length, 1);
	}
	
	function wrongSelectorProvider ()
	{
		return array(
			array("#foo]bar"),
			array("#foo[bar=test]test2]"),
			array("#foo>+bar"),
			array("#foo()")
		);
	}
	
	/**
	 * @dataProvider wrongSelectorProvider
	 * @expectedException 	DomException
	 */
	function testWrongSelectorThrowsException ($selector)
	{
		$document = ParserHTMLTest::$documents["HTML3"];
		$list = $document->querySelectorAll($selector);
	}
}