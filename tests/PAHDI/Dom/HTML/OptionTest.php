<?php
class HTMLOptionTest extends PAHDITest
{
	function testOptionValue ()
	{
		$document = $this->parseHTML("<select><option>a</option><option value='b'>c</option></select>");
		$opts = $document->getElementsByTagName("option");
		$this->assertEquals($opts[0]->value, "a");
		$this->assertEquals($opts[1]->value, "b");
	}
}