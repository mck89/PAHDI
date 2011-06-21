<?php
class HTMLAnchorTest extends PAHDITest
{
	function testGetURLParts ()
	{
		$document = $this->parseHTML("<a id='test' href='https://test.com:80/path?k=v#anchor'></a>");
		$a = $document->getElementById("test");
		$this->assertEquals($a->protocol, "https:");
		$this->assertEquals($a->hostname, "test.com");
		$this->assertEquals($a->host, "test.com");
		$this->assertEquals($a->port, "80");
		$this->assertEquals($a->pathname, "/path");
		$this->assertEquals($a->search, "?k=v");
		$this->assertEquals($a->hash, "#anchor");
	}
	
	function testSetURLParts ()
	{
		$document = $this->parseHTML("<a id='test' href='https://test.com:80/path?k=v#anchor'></a>");
		$a = $document->getElementById("test");
		$a->protocol = "http:";
		$a->hostname = "fake.com";
		$a->port = "70";
		$a->pathname = "/abc";
		$a->search = "?a=2";
		$a->hash = "#frag";
		$this->assertEquals($a->href, "http://fake.com:70/abc?a=2#frag");
	}
}