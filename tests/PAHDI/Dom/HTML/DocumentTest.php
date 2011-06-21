<?php
class DocumentTest extends PAHDITest
{
	/**
	 * @dataProvider tagProvider
	 */
	function testElementsCreation ($tags, $ns, $initNS)
	{
		$document = $this->getEmptyDocument();
		$result = true;
		foreach ($tags as $class=>$tagNames) {
			foreach ($tagNames as $name) {
				if ($initNS) {
					$element = $document->createElementNS($ns, $name);
				} else {
					$element = $document->createElement($name);
				}
				$result = $result && $element->tagName === $name;
				$result = $result && $element->namespaceURI === $ns;
				$result = $result && get_class($element) === $class;
			}
		}
		$this->assertTrue($result);
	}
	
	function tagProvider ()
	{
		return array(
			array(
				array(
					"HTMLAnchorElement"=>array("a"),
					"HTMLElement"=>array(
						"abbr", "acronym", "address",
						"article","aside", "b", "bdo",
						"bgsound", "big", "center",
						"cite", "code", "command",
						"datagrid", "datalist",
						"dcell", "dcol", "drow", "dd",
						"details", "dfn", "dt","em",
						"figcaption", "figure", "footer",
						"header", "hgroup", "i", "kbd",
						"layer", "mark", "nav", "nobr",
						"noembed", "noframes", "nolayer",
						"noscript", "plaintext","rp", "rt",
						"ruby", "s", "samp", "section",
						"small", "span", "strike", "strong",
						"sub", "summary", "sup", "track",
						"tt", "u", "var", "wbr", "listing",
						"xmp"
					),
					"HTMLAppletElement"=>array("applet"),
					"HTMLAreaElement"=>array("area"),
					"HTMLAudioElement"=>array("audio"),					
					"HTMLBaseElement"=>array("base"),
					"HTMLBaseFontElement"=>array("basefont"),
					"HTMLBlockquoteElement"=>array("blockquote"),
					"HTMLBodyElement"=>array("body"),
					"HTMLBRElement"=>array("br"),
					"HTMLButtonElement"=>array("button"),
					"HTMLCanvasElement"=>array("canvas"),
					"HTMLTableCaptionElement"=>array("caption"),
					"HTMLTableColElement"=>array("col", "colgroup"),
					"HTMLModElement"=>array("del", "ins"),
					"HTMLDirectoryElement"=>array("dir"),
					"HTMLDivElement"=>array("div"),
					"HTMLDListElement"=>array("dl"),
					"HTMLEmbedElement"=>array("embed"),
					"HTMLFieldSetElement"=>array("fieldset"),
					"HTMLFontElement"=>array("font"),
					"HTMLFormElement"=>array("form"),
					"HTMLFrameElement"=>array("frame"),
					"HTMLFrameSetElement"=>array("frameset"),
					"HTMLHeadingElement"=>array(
						"h1", "h2", "h3", "h4", "h5", "h6"
					),
					"HTMLHeadElement"=>array("head"),
					"HTMLHRElement"=>array("hr"),
					"HTMLHtmlElement"=>array("html"),
					"HTMLIFrameElement"=>array("iframe"),
					"HTMLImageElement"=>array("image", "img"),
					"HTMLInputElement"=>array("input"),
					"HTMLIsIndexElement"=>array("isindex"),
					"HTMLSelectElement"=>array("keygen", "select"),
					"HTMLLabelElement"=>array("label"),
					"HTMLLegendElement"=>array("legend"),
					"HTMLLIElement"=>array("li"),
					"HTMLLinkElement"=>array("link"),
					"HTMLPreElement"=>array("pre"),
					"HTMLMapElement"=>array("map"),
					"HTMLMarqueeElement"=>array("marquee"),
					"HTMLMenuElement"=>array("menu"),
					"HTMLMetaElement"=>array("meta"),
					"HTMLMeterElement"=>array("meter"),
					"HTMLObjectElement"=>array("object"),
					"HTMLOListElement"=>array("ol"),
					"HTMLOptGroupElement"=>array("optgroup"),
					"HTMLOptionElement"=>array("option"),
					"HTMLParagraphElement"=>array("p"),
					"HTMLParamElement"=>array("param"),
					"HTMLProgressElement"=>array("progress"),
					"HTMLQuoteElement"=>array("q"),
					"HTMLScriptElement"=>array("script"),
					"HTMLSourceElement"=>array("source"),
					"HTMLStyleElement"=>array("style"),
					"HTMLTableElement"=>array("table"),
					"HTMLTableSectionElement"=>array(
						"tbody", "tfoot", "thead"
					),
					"HTMLTableCellElement"=>array("td", "th"),
					"HTMLTextAreaElement"=>array("textarea"),
					"HTMLTitleElement"=>array("title"),
					"HTMLTableRowElement"=>array("tr"),
					"HTMLUListElement"=>array("ul"),
					"HTMLVideoElement"=>array("video")
				),
				ParserHTML::HTML_NAMESPACE,
				false
			),
			array(
				array(
					"SVGAElement"=>array("a"),
					"SVGAltGlyphElement"=>array("altGlyph"),
					"SVGElement"=>array(
						"altGlyphDef", "altGlyphItem",
						"animateMotion", "color_profile",
						"font_face", "font_face_format",
						"font_face_name", "font_face_src",
						"font_face_uri", "glyphRef",
						"hkern", "missing_glyph", "mpath"
					),
					"SVGAnimateElement"=>array("animate"),
					"SVGAnimateColorElement"=>array("animateColor"),
					"SVGAnimateTransformElement"=>array("animateTransform"),
					"SVGSetElement"=>array("set"),
					"SVGCircleElement"=>array("circle"),
					"SVGClipPathElement"=>array("clipPath"),
					"SVGCursorElement"=>array("cursor"),
					"SVGDefsElement"=>array("defs"),
					"SVGDescElement"=>array("desc"),
					"SVGEllipseElement"=>array("ellipse"),
					"SVGFEBlendElement"=>array("feBlend"),
					"SVGFEColorMatrixElement"=>array("feColorMatrix"),
					"SVGFEComponentTransferElement"=>array("feComponentTransfer"),
					"SVGFECompositeElement"=>array("feComposite"),
					"SVGFEConvolveMatrixElement"=>array("feConvolveMatrix"),
					"SVGFEDiffuseLightingElement"=>array("feDiffuseLighting"),
					"SVGFEDisplacementMapElement"=>array("feDisplacementMap"),
					"SVGFEDistantLightElement"=>array("feDistantLight"),
					"SVGFEFloodElement"=>array("feFlood"),
					"SVGFEFuncAElement"=>array("feFuncA"),
					"SVGFEFuncBElement"=>array("feFuncB"),
					"SVGFEFuncGElement"=>array("feFuncG"),
					"SVGFEFuncRElement"=>array("feFuncR"),
					"SVGFEGaussianBlurElement"=>array("feGaussianBlur"),
					"SVGFEImageElement"=>array("feImage"),
					"SVGFEMergeElement"=>array("feMerge"),
					"SVGFEMergeNodeElement"=>array("feMergeNode"),
					"SVGFEMorphologyElement"=>array("feMorphology"),
					"SVGFEOffsetElement"=>array("feOffset"),
					"SVGFEPointLightElement"=>array("fePointLight"),
					"SVGFESpecularLightingElement"=>array("feSpecularLighting"),
					"SVGFESpotLightElement"=>array("feSpotLight"),
					"SVGFETileElement"=>array("feTile"),
					"SVGFETurbulenceElement"=>array("feTurbulence"),
					"SVGFilterElement"=>array("filter"),
					"SVGFontElement"=>array("font"),
					"SVGForeignObjectElement"=>array("foreignObject"),
					"SVGGElement"=>array("g"),
					"SVGGlyphElement"=>array("glyph"),
					"SVGImageElement"=>array("image"),
					"SVGLineElement"=>array("line"),
					"SVGLinearGradientElement"=>array("linearGradient"),
					"SVGMarkerElement"=>array("marker"),
					"SVGMaskElement"=>array("mask"),
					"SVGMetadataElement"=>array("metadata"),
					"SVGPathElement"=>array("path"),
					"SVGPatternElement"=>array("pattern"),
					"SVGPolygonElement"=>array("polygon"),
					"SVGPolylineElement"=>array("polyline"),
					"SVGRadialGradientElement"=>array("radialGradient"),
					"SVGRectElement"=>array("rect"),
					"SVGScriptElement"=>array("script"),
					"SVGStopElement"=>array("stop"),
					"SVGStyleElement"=>array("style"),
					"SVGSVGElement"=>array("svg"),
					"SVGSwitchElement"=>array("switch"),
					"SVGSymbolElement"=>array("symbol"),
					"SVGTextElement"=>array("text"),
					"SVGTextPathElement"=>array("textPath"),
					"SVGTitleElement"=>array("title"),
					"SVGTRefElement"=>array("tref"),
					"SVGTSpanElement"=>array("tspan"),
					"SVGUseElement"=>array("use"),
					"SVGViewElement"=>array("view"),
					"SVGVKernElement"=>array("vkern")
				),
				ParserHTML::SVG_NAMESPACE,
				true
			),
			array(
				array(
					"Element"=>array("div")
				),
				ParserHTML::MATHML_NAMESPACE,
				true
			)
		);
	}
	
	/**
	 * @expectedException 	DomException
     */
	function testAttributeInvalidAttributeNameThrowsAnException ()
	{
		$document = $this->getEmptyDocument();
		$document->createAttribute("invalid name");
	}
	
	/**
	 * @expectedException 	DomException
     */
	function testAttributeInvalidAttributeName2ThrowsAnException ()
	{
		$document = $this->getEmptyDocument();
		$document->createAttribute("invalid,tag");
	}
	
	function testAttributeCreationWithValidAttributeName ()
	{
		$document = $this->getEmptyDocument();
		$document->createAttribute("valid-name");
		$this->assertTrue(true);
	}
	
	function testPointers ()
	{
		$HTML = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		$document = $this->parseHTML($HTML);
		$timer = self::startTimer();
		$doctype = $document->doctype;
		self::markTime($timer, "Access doctype pointer");
		$documentElement = $document->documentElement;
		self::markTime($timer, "Access document element pointer");
		$body = $document->body;
		self::markTime($timer, "Access body pointer");
		$this->assertEquals($doctype->nodeType, 10);
		$this->assertEquals($documentElement->nodeType, 1);
		$this->assertEquals($documentElement->tagName, "html");
		$this->assertEquals($body->nodeType, 1);
		$this->assertEquals($body->tagName, "body");
		$head = $document->head;
		$this->assertEquals($head->nodeType, 1);
		$this->assertEquals($head->tagName, "head");
	}
	
	function testGetElementById ()
	{
		$document = ParserHTMLTest::$documents["HTML3"];
		$timer = self::startTimer();
		$el = $document->getElementById("curstatusrhs");
		self::markTime($timer, "GetElementById");
		$this->assertTrue($el !== null);
		$this->assertEquals($el->tagName, "div");
		$missingId = $document->getElementById("b");
		$this->assertEquals($missingId, null);
	}
	
	function testGetElementsByTagName ()
	{
		$document = ParserHTMLTest::$documents["HTML3"];
		$list = $document->getElementsByTagName("*");
		$this->assertEquals($list->length, 267);
	}
	
	function testGetElementsByName ()
	{
		$document = $this->parseHTML("<input name='s'><input name='s'>");
		$timer = self::startTimer();
		$list = $document->getElementsByName("s");
		self::markTime($timer, "GetElementsByName");
		$this->assertEquals($list->length, 2);
		$list2 = $document->getElementsByName("no");
		$this->assertEquals($list2->length, 0);
	}
	
	function testFormPointers ()
	{
		$document = $this->parseHTML("<form name='test'></form>");
		$form = $document->test;
		$this->assertEquals($form->tagName, "form");
		$this->assertEquals($document->notest, null);
	}
	
	function testDocumentTitle ()
	{
		$document = $this->parseHTML("");
		$this->assertEquals($document->title, "");
		$document->title = "test";
		$this->assertEquals($document->title, "test");
	}
	
	function testAnchorsLinks ()
	{
		$document = $this->parseHTML("<a name='a'>1</a><a name='b' href='test.html'>2</a><a href='test.html'>3</a>");
		$this->assertEquals($document->anchors->length, 2);
		$this->assertEquals($document->links->length, 2);
	}
	
	function testStyleSheetsList ()
	{
		$document = $this->parseHTML('<link rel="stylesheet" href="a.css"><link rel="stylesheet" href="b.css"><style></style><link href="c.css">');
		$this->assertEquals($document->styleSheets->length, 3);
	}
}