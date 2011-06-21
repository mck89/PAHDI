<?php
class ParserCSSTest extends PAHDITest
{
	static $link;
	static $missingLink;
	static $invalidLink;
	static $style;
	static $el;
	
	static function setUpBeforeClass ()
	{
		$sourcePath = PAHDI_TEST_SOURCE_DIR . DRS . "CSS" . DRS . "source.html";
		$document = PAHDI::parseLocalSource($sourcePath, array("cssPrefix"=>"-webkit-"));
		$links = $document->getElementsByTagName("link");
		$style = $document->getElementsByTagName("style");
		$div = $document->getElementsByTagName("div");
		self::$link = $links[0];
		self::$missingLink = $links[1];
		self::$invalidLink = $links[2];
		self::$style = $style[0];
		self::$el = $div[0];
	}
	
	function testMissingCSSGivesEmptyStyleSheet ()
	{
		$this->assertTrue(self::$missingLink->sheet instanceof CSSStyleSheet);
		$this->assertEquals(self::$missingLink->sheet->cssRules->length, 0);
	}
	
	function testLinkNotStylesheetHasNullSheet ()
	{
		$this->assertEquals(self::$invalidLink->sheet, null);
	}
	
	function testElementStyle ()
	{
		$timer = self::startTimer();
		$style = self::$el->style;
		self::markTime($timer, "Style attribute parsing");
		$this->assertEquals($style->length, 2);
		$this->assertEquals($style->color, "red");
		$this->assertEquals($style->backgroundColor, "white");
		$this->assertEquals($style->getPropertyPriority("background-color"), "important");
		$this->assertEquals($style->getPropertyValue("background-color"), "white");
		$this->assertEquals($style->getPropertyPriority("color"), "");
		$style->setProperty("font-size", "10px", "important");
		$this->assertEquals($style->getPropertyPriority("font-size"), "important");
		$this->assertEquals($style->fontSize, "10px");
		$this->assertEquals($style->length, 3);
		$style->removeProperty("font-size");
		$this->assertEquals($style->length, 2);
		$this->assertEquals($style->fontSize, null);
		$this->assertEquals($style->item(0), "color");
		$this->assertEquals($style->item(10), "");
		$style->setProperty("float", "left", "important");
		$this->assertEquals($style->cssFloat, "left");
		$cssText = "color:red;background-color:white!important;float:left!important;";
		$this->assertEquals($style->cssText, $cssText);
		$this->assertEquals(self::$el->getAttribute("style"), $cssText);
		$style->height = "20px";
		$this->assertEquals($style->length, 4);		
		$this->assertEquals($style["height"], "20px");
		$this->assertEquals($style[3], "20px");
		foreach ($style as $k=>$v) {
			$this->assertEquals($k, "color");
			$this->assertEquals($v, "red");
			break;
		}
		$style->setProperty("-moz-invalid", "left", "");
		$this->assertEquals($style->length, 4);
		$style->setProperty("-webkit-valid", "left", "");
		$this->assertEquals($style->length, 5);
		$style->cssText = "height:20px;width:20px;";
		$this->assertEquals($style->length, 2);
		$attr = self::$el->getAttributeNode("style");
		$attr->value = "width:200px";
		$this->assertEquals($style->length, 1);
	}
	
	function testStyleElement ()
	{
		$timer = self::startTimer();
		$sheet = self::$style->sheet;
		self::markTime($timer, "Style element content parsing");
		$this->assertTrue($sheet instanceof CSSStyleSheet);
		$this->assertTrue($sheet->ownerNode->isSameNode(self::$style));
		$this->assertEquals($sheet->cssRules->length, 2);
		$this->assertEquals($sheet->ownerRule, null);
		$this->assertEquals($sheet->parentStyleSheet, null);
		$this->assertEquals($sheet->title, "");
		$this->assertEquals($sheet->type, "text/css");
		$this->assertTrue($sheet->cssRules[0] instanceof CSSPageRule);
		$this->assertEquals($sheet->cssRules[0]->selectorText, "");
		$this->assertTrue($sheet->cssRules[1] instanceof CSSStyleRule);
		$this->assertEquals($sheet->cssRules[1]->selectorText, "div");
		$this->assertEquals($sheet->cssRules[1]->style->length, 2);
		$this->assertEquals($sheet->cssRules[1]->style->parentRule, $sheet->cssRules[1]);
	}
	
	function testLinkElement ()
	{
		$timer = self::startTimer();
		$sheet = self::$link->sheet;
		self::markTime($timer, "External stylesheet parsing");
		$this->assertTrue($sheet instanceof CSSStyleSheet);
		$this->assertTrue($sheet->ownerNode->isSameNode(self::$link));
		$this->assertEquals($sheet->cssRules->length, 7);
		$this->assertEquals($sheet->title, "");
		$this->assertEquals($sheet->type, "text/css");
		$this->assertEquals($sheet->ownerRule, null);
		$this->assertEquals($sheet->parentStyleSheet, null);
		$classes = array(
			"CSSCharsetRule", "CSSImportRule", "CSSMediaRule", "CSSKeyframesRule",
			"CSSFontFaceRule", "CSSPageRule", "CSSStyleRule"
		);
		$res = true;
		foreach ($sheet->cssRules as $k=>$v) {
			if (get_class($v) !== $classes[$k]) {
				$res = false;
				break;
			}
		}
		$this->assertTrue($res);
	}
	
	function testLinkElementMedia ()
	{
		$media = self::$link->sheet->media;
		$this->assertEquals($media->length, 2);
		$this->assertEquals($media[0], "screen");
		$this->assertEquals($media[1], "print");
		$this->assertEquals($media->mediaText, "screen,print");
		$timer = self::startTimer();
		$media->appendMedium("screen");
		self::markTime($timer, "appendMedium");
		$media->appendMedium("new");
		$this->assertEquals($media->length, 3);
		$timer = self::startTimer();
		$media->deleteMedium("new");
		self::markTime($timer, "deleteMedium");
		$this->assertEquals($media->length, 2);
		$media->mediaText = "a,b,c,d";
		$this->assertEquals($media->length, 4);
	}
	
	function testCharsetRule ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[0];
		$this->assertEquals($rule->encoding, "UTF-8");
		$this->assertEquals($rule->cssText, "@charset \"UTF-8\";");
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
	}
	
	function testFontFaceRule ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[4];
		$this->assertEquals($rule->style->length, 1);
		$this->assertEquals($rule->cssText, "@font-face{font-family:Arial;}");
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
	}
	
	function testPageRule ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[5];
		$this->assertEquals($rule->style->length, 1);
		$this->assertEquals($rule->cssText, "@page :left{margin-left:0;}");
		$this->assertEquals($rule->selectorText, ":left");
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
	}
	
	function testStyleRule ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[6];
		$this->assertEquals($rule->style->length, 1);
		$this->assertEquals($rule->cssText, "div{color:red;}");
		$this->assertEquals($rule->selectorText, "div");
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
	}
	
	function testMediaRule ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[2];
		$this->assertEquals($rule->cssRules->length, 2);
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
		$this->assertEquals($rule->media->length, 2);
		$this->assertEquals($rule->cssText, "@media screen and (min-width: 400px),(max-width: 700px){body{margin-right:20px;}div{color:red;width:20%;}}");
		$this->assertEquals($rule->cssRules[0]->selectorText, "body");
		$this->assertEquals($rule->cssRules[0]->style->length, 1);
		$this->assertEquals($rule->cssRules[1]->selectorText, "div");
		$this->assertEquals($rule->cssRules[1]->style->length, 2);
		$this->assertEquals($rule->cssRules[0]->parentStyleSheet, $sheet);
		$this->assertEquals($rule->cssRules[0]->parentRule, $rule);
	}
	
	function testKeyframesRule ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[3];
		$this->assertEquals($rule->cssRules->length, 4);
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
		$this->assertEquals($rule->name, "keyname");
		$this->assertEquals($rule->cssText, "@keyframes keyname{0%{top:0px;left:0px;}30%{top:50px;}70%{left:50px;}100%{top:100px;left:100%;}}");
		$this->assertEquals($rule->cssRules[0]->keyText, "0%");
		$this->assertEquals($rule->cssRules[0]->style->length, 2);
		$this->assertEquals($rule->cssRules[0]->parentStyleSheet, $sheet);
		$this->assertEquals($rule->cssRules[0]->parentRule, $rule);
	}
	
	function testImportRule ()
	{
		$timer = self::startTimer();
		$sheet = self::$link->sheet;
		self::markTime($timer, "Imported stylesheet parsing");
		$rule = $sheet->cssRules[1];
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
		$this->assertEquals($rule->media->length, 2);
		$this->assertEquals($rule->media[0], "screen and (min-width: 400px)");
		$this->assertEquals($rule->media[1], "print");
		$this->assertEquals($rule->cssText, "@import \"fold/test.css\" screen and (min-width: 400px),print;");
	}
	
	function testImportRuleStylesheet ()
	{
		$parentSheet = self::$link->sheet;
		$rule = $parentSheet->cssRules[1];
		$sheet = $rule->styleSheet;
		$this->assertTrue($sheet instanceof CSSStyleSheet);
		$this->assertEquals($sheet->cssRules->length, 3);
		$this->assertEquals($sheet->media->length, 2);
		$this->assertEquals($sheet->ownerNode, null);
		$this->assertEquals($sheet->ownerRule, $rule);
		$this->assertEquals($sheet->parentStyleSheet, $parentSheet);
		$this->assertEquals($sheet->type, "text/css");
		$classes = array(
			"CSSImportRule", "CSSPageRule", "CSSStyleRule"
		);
		$res = true;
		foreach ($sheet->cssRules as $k=>$v) {
			if (get_class($v) !== $classes[$k]) {
				$res = false;
				break;
			}
		}
	}
	
	function testImportRuleStylesheetPageRule ()
	{
		$parentSheet = self::$link->sheet;
		$sheet = $parentSheet->cssRules[1]->styleSheet;
		$rule = $sheet->cssRules[1];
		$this->assertEquals($rule->style->length, 1);
		$this->assertEquals($rule->cssText, "@page{margin-top:0;}");
		$this->assertEquals($rule->selectorText, "");
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
	}
	
	function testImportRuleStylesheetStyleRule ()
	{
		$parentSheet = self::$link->sheet;
		$sheet = $parentSheet->cssRules[1]->styleSheet;
		$rule = $sheet->cssRules[2];
		$this->assertEquals($rule->style->length, 0);
		$this->assertEquals($rule->cssText, "span{}");
		$this->assertEquals($rule->selectorText, "span");
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
	}
	
	function testImportRuleStylesheetImportRule ()
	{
		$parentSheet = self::$link->sheet;
		$sheet = $parentSheet->cssRules[1]->styleSheet;
		$rule = $sheet->cssRules[0];
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
		$this->assertEquals($rule->media->length, 0);
		$this->assertEquals($rule->cssText, "@import \"dir/test2.css\";");
	}
	
	function testImportRuleStylesheetImportRuleStylesheet ()
	{
		$parentSheet = self::$link->sheet->cssRules[1]->styleSheet;
		$rule = $parentSheet->cssRules[0];
		$sheet = $rule->styleSheet;
		$this->assertTrue($sheet instanceof CSSStyleSheet);
		$this->assertEquals($sheet->cssRules->length, 1);
		$this->assertEquals($sheet->media->length, 0);
		$this->assertEquals($sheet->ownerNode, null);
		$this->assertEquals($sheet->ownerRule, $rule);
		$this->assertEquals($sheet->parentStyleSheet, $parentSheet);
		$this->assertEquals($sheet->type, "text/css");
		$this->assertTrue($sheet->cssRules[0] instanceof CSSStyleRule);
	}
	
	function testImportRuleStylesheetImportRuleStylesheetStyleRule ()
	{
		$parentSheet = self::$link->sheet->cssRules[1]->styleSheet;
		$sheet = $parentSheet->cssRules[0]->styleSheet;
		$rule = $sheet->cssRules[0];	
		$this->assertEquals($rule->style->length, 1);
		$this->assertEquals($rule->cssText, "a{background-color:blue;}");
		$this->assertEquals($rule->selectorText, "a");
		$this->assertEquals($rule->parentRule, null);
		$this->assertEquals($rule->parentStyleSheet, $sheet);
	}
	
	function testDisabledAttribute ()
	{
		self::$link->disabled = true;
		$this->assertEquals(self::$link->sheet->disabled, true);
		$this->assertEquals(self::$link->sheet->cssRules[1]->styleSheet->disabled, false);
		self::$link->sheet->disabled = false;
		$this->assertEquals(self::$link->disabled, false);
	}
	
	function testStyleSheetMethods ()
	{
		$sheet = self::$link->sheet;
		$length = $sheet->cssRules->length;
		$timer = self::startTimer();
		$sheet->insertRule("#id{width:10px;}", 1);
		self::markTime($timer, "Stylesheet insertRule");
		$this->assertEquals($sheet->cssRules->length, $length + 1);
		$this->assertTrue($sheet->cssRules[1] instanceof CSSStyleRule);
		$this->assertEquals($sheet->cssRules[1]->selectorText, "#id");
		$this->assertEquals($sheet->cssRules[1]->parentStyleSheet, $sheet);
		$sheet->insertRule("div{width:2px}span{width:2px}", 1);
		$this->assertEquals($sheet->cssRules->length, $length + 1);
		$timer = self::startTimer();
		$sheet->deleteRule(1);
		self::markTime($timer, "Stylesheet deleteRule");
		$this->assertEquals($sheet->cssRules->length, $length);
	}

	function testMediaRuleMethods ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[2];
		$length = $rule->cssRules->length;
		$timer = self::startTimer();
		$rule->insertRule("#id{width:10px;}", 1);
		self::markTime($timer, "MediaRule insertRule");
		$this->assertEquals($rule->cssRules->length, $length + 1);
		$this->assertTrue($rule->cssRules[1] instanceof CSSStyleRule);
		$this->assertEquals($rule->cssRules[1]->parentStyleSheet, $sheet);
		$this->assertEquals($rule->cssRules[1]->parentRule, $rule);
		$rule->insertRule("div{width:2px}span{width:2px}", 1);
		$this->assertEquals($rule->cssRules->length, $length + 1);
		$timer = self::startTimer();
		$rule->deleteRule(1);
		self::markTime($timer, "MediaRule deleteRule");
		$this->assertEquals($rule->cssRules->length, $length);
	}
	
	function testKeyframesRuleMethods ()
	{
		$sheet = self::$link->sheet;
		$rule = $sheet->cssRules[3];
		$length = $rule->cssRules->length;
		$timer = self::startTimer();
		$rule->appendRule("10%{width:10px;}");
		self::markTime($timer, "KeyframesRule appendRule");
		$this->assertEquals($rule->cssRules->length, $length + 1);
		$this->assertTrue($rule->cssRules[$length] instanceof CSSKeyframeRule);
		$this->assertEquals($rule->cssRules[1]->parentStyleSheet, $sheet);
		$this->assertEquals($rule->cssRules[1]->parentRule, $rule);
		$rule->appendRule("10%{width:10px;}20%{width:10px;}");
		$this->assertEquals($rule->cssRules->length, $length + 1);
		$timer = self::startTimer();
		$rule->deleteRule(1);
		self::markTime($timer, "KeyframesRule deleteRule");
		$this->assertEquals($rule->cssRules->length, $length);
		$timer = self::startTimer();
		$foundRule = $rule->findRule("70%");
		self::markTime($timer, "KeyframesRule findRule");
		$this->assertEquals($foundRule->keyText, "70%");
		$this->assertEquals($rule->findRule("80%"), null);
	}
}