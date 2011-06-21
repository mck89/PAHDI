<?php
class HTMLTextAreaTest extends PAHDITest
{
	function testTextareaValue ()
	{
		$value = "this <b>is</b> a <s>test</s>";
		$document = $this->parseHTML("<textarea>$value</textarea>");
		$textarea = $document->body->childNodes[0];
		$textareaValue = $textarea->childNodes[0]->data;
		$this->assertEquals($textareaValue, $value);
		$this->assertEquals($textareaValue, $textarea->value);
		$textarea->value = "foo";
		$this->assertEquals($textarea->value, "foo");
	}
}