<?php
class HTMLFormTest extends PAHDITest
{
	function testFormPointers ()
	{
		$document = $this->parseHTML("<form name='fm'><input name='test'><textarea name='test2'></textarea></form>");
		$form = $document->fm;
		$this->assertEquals($form->test->tagName, "input");
		$this->assertEquals($form->test2->tagName, "textarea");
		$this->assertEquals($form->test3, null);
		$this->assertEquals($form->length, 2);
	}
}