<?php
class PAHDIPathTest extends PAHDITest
{
	function testResolveURL ()
	{
		$fakeurl = "http://www.faketesturl.com";
		$rel = "test.html";
		$rel2 = "test/test.html";
		$this->assertTrue(PAHDIPath::isURL($fakeurl));
		$this->assertEquals(PAHDIPath::resolve($fakeurl, $rel), "$fakeurl/$rel");
		$this->assertEquals(PAHDIPath::resolve($fakeurl, $rel2), "$fakeurl/$rel2");
	}
	
	function testResolveURL2 ()
	{
		$fakeurl = "http://www.faketesturl.com/a/b/index.html";
		$rel = "/c/test.html";
		$rel2 = "../test.html";
		$this->assertTrue(PAHDIPath::isURL($fakeurl));
		$this->assertEquals(PAHDIPath::resolve($fakeurl, $rel), "http://www.faketesturl.com/c/test.html");
		$this->assertEquals(PAHDIPath::resolve($fakeurl, $rel2), "http://www.faketesturl.com/a/test.html");
	}
	
	function testResolveURL3 ()
	{
		$fakeurl = "http://www.faketesturl.com/ab";
		$rel = "test.html";
		$rel2 = "test/test.html";
		$this->assertEquals(PAHDIPath::resolve($fakeurl, $rel), "$fakeurl/$rel");
		$this->assertEquals(PAHDIPath::resolve($fakeurl, $rel2), "$fakeurl/$rel2");
	}
	
	function testDataURI ()
	{
		$URI = "data:text/html;charset=utf8;base64,data";
		$this->assertTrue(PAHDIPath::isDataURI($URI));
	}
	
	function testDataURIParts ()
	{
		$URI = "data:text/html;charset=utf8;base64,data";
		$parts = PAHDIPath::getDataURIParts($URI);
		$this->assertEquals($parts["mime"], "text/html");
		$this->assertEquals($parts["charset"], "utf8");
		$this->assertTrue($parts["base64"]);
	}
	
	function testDataURIBase64encodedData ()
	{
		$URI = "data:text/html;base64,".base64_encode("test");
		$parts = PAHDIPath::getDataURIParts($URI);
		$this->assertEquals($parts["data"], "test");
	}
}