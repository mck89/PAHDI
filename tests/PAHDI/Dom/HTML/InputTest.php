<?php
class HTMLInputTest extends PAHDITest
{
	function testRadioBehaviour ()
	{
		$document = $this->parseHTML("<input type='radio' name='test' id='t1' checked><input type='radio' name='test' id='t2'>");
		$radio = $document->getElementById("t1");
		$radio2 = $document->getElementById("t2");
		$this->assertTrue($radio->checked);
		$this->assertEquals($radio2->checked, false);
		$radio2->checked = true;
		$this->assertTrue($radio2->checked);
		$this->assertEquals($radio->checked, false);
	}
}