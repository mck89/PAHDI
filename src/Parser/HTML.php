<?php
/**
 * This file is part of the PAHDI (PHP Advanced HTML Dom Implementation)
 * library, for the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @category    PAHDI
 * @package     PAHDI-Parser
 * @author      Marco Marchiò
 * @license     http://opensource.org/licenses/bsd-license.php The BSD License
 * @copyright	Copyright (c) 2013, Marco Marchiò
 */
 
/**
 * HTML parser that follows W3C standards
 * (http://www.whatwg.org/specs/web-apps/current-work/multipage/tokenization.html)
 *
 * @category    	PAHDI
 * @package     	PAHDI-Parser
 */
class ParserHTML extends ParserHTMLBuilder
{
	/**
	 * Scripting flag
	 *
	 * @var		bool
	 */
	public $scriptingFlag = true;
	
	/**
	 * Flag to indicate if the parser has been created in the fragment case
	 *
	 * @var		bool
	 */
	public $fragmentCase = false;

	/**
	 * Flag to indicate if the parser has been created for an iframe srcdoc
	 * attribute
	 *
	 * @var		bool
	 */
	public $srcdoc = false;

	/**
	 * Form-associated elements
	 *
	 * @static
	 * @var		array
	 */
	static $formAssociated = array(
		"button", "fieldset", "input", "keygen", "label", "meter", "object",
		"output", "progress", "select", "textarea"
	);
	
	/**
	 * Focusable elements
	 *
	 * @static
	 * @var		array
	 */
	static $focusableElements = array(
		"a", "area", "button", "frame", "iframe", "input", "select", "textarea"
	);
	
	/**
	 * Array that associates html tags to their classes
	 *
	 * @ignore
	 * @static
	 * @var		array
	 */
	static $HTMLTags = array(
		"a" => "HTMLAnchorElement",
		"abbr" => "",
		"acronym" => "",
		"address" => "",
		"applet" => "HTMLAppletElement",
		"area" => "HTMLAreaElement",
		"article" => "",
		"aside" => "",
		"audio" => "HTMLAudioElement",
		"b" => "",
		"base" => "HTMLBaseElement",
		"basefont" => "HTMLBaseFontElement",
		"bdo" => "",
		"bgsound" => "",
		"big" => "",
		"blockquote" => "HTMLBlockquoteElement",
		"body" => "HTMLBodyElement",
		"br" => "HTMLBRElement",
		"button" => "HTMLButtonElement",
		"canvas" => "HTMLCanvasElement",
		"caption" => "HTMLTableCaptionElement",
		"center" => "",
		"cite" => "",
		"code" => "",
		"col" => "HTMLTableColElement",
		"colgroup" => "HTMLTableColElement",
		"command" => "",
		"datagrid" => "",
		"datalist" => "",
		"dcell" => "",
		"dcol" => "",
		"drow" => "",
		"dd" => "",
		"del" => "HTMLModElement",
		"details" => "",
		"dfn" => "",
		"dir" => "HTMLDirectoryElement",
		"div" => "HTMLDivElement",
		"dl" => "HTMLDListElement",
		"dt" => "",
		"em" => "",
		"embed" => "HTMLEmbedElement",
		"fieldset" => "HTMLFieldSetElement",
		"figcaption" => "",
		"figure" => "",
		"font" => "HTMLFontElement",
		"footer" => "",
		"form" => "HTMLFormElement",
		"frame" => "HTMLFrameElement",
		"frameset" => "HTMLFrameSetElement",
		"h1" => "HTMLHeadingElement",
		"h2" => "HTMLHeadingElement",
		"h3" => "HTMLHeadingElement",
		"h4" => "HTMLHeadingElement",
		"h5" => "HTMLHeadingElement",
		"h6" => "HTMLHeadingElement",
		"head" => "HTMLHeadElement",
		"header" => "",
		"hgroup" => "",
		"hr" => "HTMLHRElement",
		"html" => "HTMLHtmlElement",
		"i" => "",
		"iframe" => "HTMLIFrameElement",
		"image" => "HTMLImageElement",
		"img" => "HTMLImageElement",
		"input" => "HTMLInputElement",
		"ins" => "HTMLModElement",
		"isindex" => "HTMLIsIndexElement",
		"kbd" => "",
		"keygen" => "HTMLSelectElement",
		"label" => "HTMLLabelElement",
		"layer" => "",
		"legend" => "HTMLLegendElement",
		"li" => "HTMLLIElement",
		"link" => "HTMLLinkElement",
		"listing" => "",
		"map" => "HTMLMapElement",
		"mark" => "",
		"marquee" => "HTMLMarqueeElement",
		"menu" => "HTMLMenuElement",
		"meta" => "HTMLMetaElement",
		"meter" => "HTMLMeterElement",
		"nav" => "",
		"nobr" => "",
		"noembed" => "",
		"noframes" => "",
		"nolayer" => "",
		"noscript" => "",
		"object" => "HTMLObjectElement",
		"ol" => "HTMLOListElement",
		"optgroup" => "HTMLOptGroupElement",
		"option" => "HTMLOptionElement",
		"p" => "HTMLParagraphElement",
		"param" => "HTMLParamElement",
		"plaintext" => "",
		"pre" => "HTMLPreElement",
		"progress" => "HTMLProgressElement",
		"q" => "HTMLQuoteElement",
		"rp" => "",
		"rt" => "",
		"ruby" => "",
		"s" => "",
		"samp" => "",
		"script" => "HTMLScriptElement",
		"section" => "",
		"select" => "HTMLSelectElement",
		"small" => "",
		"source" => "HTMLSourceElement",
		"span" => "",
		"strike" => "",
		"strong" => "",
		"style" => "HTMLStyleElement",
		"sub" => "",
		"summary" => "",
		"sup" => "",
		"table" => "HTMLTableElement",
		"tbody" => "HTMLTableSectionElement",
		"td" => "HTMLTableCellElement",
		"textarea" => "HTMLTextAreaElement",
		"tfoot" => "HTMLTableSectionElement",
		"th" => "HTMLTableCellElement",
		"thead" => "HTMLTableSectionElement",
		"title" => "HTMLTitleElement",
		"tr" => "HTMLTableRowElement",
		"track" => "",
		"tt" => "",
		"u" => "",
		"ul" => "HTMLUListElement",
		"var" => "",
		"video" => "HTMLVideoElement",
		"wbr" => "",
		"xmp" => ""
	);
	
	/**
	 * HTML elements that should not contain child nodes
	 *
	 * @ignore
	 * @static
	 * @var		array
	 */
	static $HTMLTagsNoChildren = array(
		"area", "base", "basefont", "bgsound", "br", "col", "command",
		"embed", "frame", "hr", "img", "input", "keygen", "link",
		"meta", "param", "source", "track", "wbr"
	);
	
	/**
	 * HTML elements that require a line feed to be inserted
	 * before the content
	 *
	 * @ignore
	 * @static
	 * @var		array
	 */
	static $HTMLTagsLineFeed = array(
		"pre", "textarea", "listing"
	);
	
	/**
	 * HTML elements whose text content should not be
	 * escaped
	 *
	 * @ignore
	 * @static
	 * @var		array
	 */
	static $noQuoteText = array(
		"style", "script", "xmp", "iframe", "noembed", "noframes",
		"plaintext"
	);
	
	/**
	 * Array that associates svg tags to their classes
	 *
	 * @ignore
	 * @static
	 * @var		array
	 */
	static $SVGTags = array(
		"a" => "SVGAElement",
		"altGlyph" => "SVGAltGlyphElement",
		"altGlyphDef" => "",
		"altGlyphItem" => "",
		"animate" => "SVGAnimateElement",
		"animateColor" => "SVGAnimateColorElement",
		"animateMotion" => "",
		"animateTransform" => "SVGAnimateTransformElement",
		"set" => "SVGSetElement",
		"circle" => "SVGCircleElement",
		"clipPath" => "SVGClipPathElement",
		"color_profile" => "",
		"cursor" => "SVGCursorElement",
		"defs" => "SVGDefsElement",
		"desc" => "SVGDescElement",
		"ellipse" => "SVGEllipseElement",
		"feBlend" => "SVGFEBlendElement",
		"feColorMatrix" => "SVGFEColorMatrixElement",
		"feComponentTransfer" => "SVGFEComponentTransferElement",
		"feComposite" => "SVGFECompositeElement",
		"feConvolveMatrix" => "SVGFEConvolveMatrixElement",
		"feDiffuseLighting" => "SVGFEDiffuseLightingElement",
		"feDisplacementMap" => "SVGFEDisplacementMapElement",
		"feDistantLight" => "SVGFEDistantLightElement",
		"feFlood" => "SVGFEFloodElement",
		"feFuncA" => "SVGFEFuncAElement",
		"feFuncB" => "SVGFEFuncBElement",
		"feFuncG" => "SVGFEFuncGElement",
		"feFuncR" => "SVGFEFuncRElement",
		"feGaussianBlur" => "SVGFEGaussianBlurElement",
		"feImage" => "SVGFEImageElement",
		"feMerge" => "SVGFEMergeElement",
		"feMergeNode" => "SVGFEMergeNodeElement",
		"feMorphology" => "SVGFEMorphologyElement",
		"feOffset" => "SVGFEOffsetElement",
		"fePointLight" => "SVGFEPointLightElement",
		"feSpecularLighting" => "SVGFESpecularLightingElement",
		"feSpotLight" => "SVGFESpotLightElement",
		"feTile" => "SVGFETileElement",
		"feTurbulence" => "SVGFETurbulenceElement",
		"filter" => "SVGFilterElement",
		"font" => "SVGFontElement",
		"font_face" => "",
		"font_face_format" => "",
		"font_face_name" => "",
		"font_face_src" => "",
		"font_face_uri" => "",
		"foreignObject" => "SVGForeignObjectElement",
		"g" => "SVGGElement",
		"glyph" => "SVGGlyphElement",
		"glyphRef" => "",
		"hkern" => "",
		"image" => "SVGImageElement",
		"line" => "SVGLineElement",
		"linearGradient" => "SVGLinearGradientElement",
		"marker" => "SVGMarkerElement",
		"mask" => "SVGMaskElement",
		"metadata" => "SVGMetadataElement",
		"missing_glyph" => "",
		"mpath" => "",
		"path" => "SVGPathElement",
		"pattern" => "SVGPatternElement",
		"polygon" => "SVGPolygonElement",
		"polyline" => "SVGPolylineElement",
		"radialGradient" => "SVGRadialGradientElement",
		"rect" => "SVGRectElement",
		"script" => "SVGScriptElement",
		"stop" => "SVGStopElement",
		"style" => "SVGStyleElement",
		"svg" => "SVGSVGElement",
		"switch" => "SVGSwitchElement",
		"symbol" => "SVGSymbolElement",
		"text" => "SVGTextElement",
		"textPath" => "SVGTextPathElement",
		"title" => "SVGTitleElement",
		"tref" => "SVGTRefElement",
		"tspan" => "SVGTSpanElement",
		"use" => "SVGUseElement",
		"view" => "SVGViewElement",
		"vkern" => "SVGVKernElement"
	);
	
	/**
	 * SVG tags conversion
	 *
	 * @static
	 * @var		array
	 */	
	static $SVGTagConv = array(
		"altglyph" => "altGlyph",
		"altglyphdef" => "altGlyphDef",
		"altglyphitem" => "altGlyphItem",
		"animatecolor" => "animateColor",
		"animatemotion" => "animateMotion",
		"animatetransform" => "animateTransform",
		"clippath" => "clipPath",
		"feblend" => "feBlend",
		"fecolormatrix" => "feColorMatrix",
		"fecomponenttransfer" => "feComponentTransfer",
		"fecomposite" => "feComposite",
		"feconvolvematrix" => "feConvolveMatrix",
		"fediffuselighting" => "feDiffuseLighting",
		"fedisplacementmap" => "feDisplacementMap",
		"fedistantlight" => "feDistantLight",
		"feflood" => "feFlood",
		"fefunca" => "feFuncA",
		"fefuncb" => "feFuncB",
		"fefuncg" => "feFuncG",
		"fefuncr" => "feFuncR",
		"fegaussianblur" => "feGaussianBlur",
		"feimage" => "feImage",
		"femerge" => "feMerge",
		"femergenode" => "feMergeNode",
		"femorphology" => "feMorphology",
		"feoffset" => "feOffset",
		"fepointlight" => "fePointLight",
		"fespecularlighting" => "feSpecularLighting",
		"fespotlight" => "feSpotLight",
		"fetile" => "feTile",
		"feturbulence" => "feTurbulence",
		"foreignobject" => "foreignObject",
		"glyphref" => "glyphRef",
		"lineargradient" => "linearGradient",
		"radialgradient" => "radialGradient",
		"textpath" => "textPath"
	);
	
	/**
	 * SVG camel cased attributes
	 *
	 * @static
	 * @var		array
	 */	
	static $SVGAttributes = array(
		"attributename" => "attributeName",
		"attributetype" => "attributeType",
		"basefrequency" => "baseFrequency",
		"baseprofile" => "baseProfile",
		"calcmode" => "calcMode",
		"clippathunits" => "clipPathUnits",
		"contentscripttype" => "contentScriptType",
		"contentstyletype" => "contentStyleType",
		"diffuseconstant" => "diffuseConstant",
		"edgemode" => "edgeMode",
		"externalresourcesrequired" => "externalResourcesRequired",
		"filterres" => "filterRes",
		"filterunits" => "filterUnits",
		"glyphref" => "glyphRef",
		"gradienttransform" => "gradientTransform",
		"gradientunits" => "gradientUnits",
		"kernelmatrix" => "kernelMatrix",
		"kernelunitlength" => "kernelUnitLength",
		"keypoints" => "keyPoints",
		"keysplines" => "keySplines",
		"keytimes" => "keyTimes",
		"lengthadjust" => "lengthAdjust",
		"limitingconeangle" => "limitingConeAngle",
		"markerheight" => "markerHeight",
		"markerunits" => "markerUnits",
		"markerwidth" => "markerWidth",
		"maskcontentunits" => "maskContentUnits",
		"maskunits" => "maskUnits",
		"numoctaves" => "numOctaves",
		"pathlength" => "pathLength",
		"patterncontentunits" => "patternContentUnits",
		"patterntransform" => "patternTransform",
		"patternunits" => "patternUnits",
		"pointsatx" => "pointsAtX",
		"pointsaty" => "pointsAtY",
		"pointsatz" => "pointsAtZ",
		"preservealpha" => "preserveAlpha",
		"preserveaspectratio" => "preserveAspectRatio",
		"primitiveunits" => "primitiveUnits",
		"refx" => "refX",
		"refy" => "refY",
		"repeatcount" => "repeatCount",
		"repeatdur" => "repeatDur",
		"requiredextensions" => "requiredExtensions",
		"requiredfeatures" => "requiredFeatures",
		"specularconstant" => "specularConstant",
		"specularexponent" => "specularExponent",
		"spreadmethod" => "spreadMethod",
		"startoffset" => "startOffset",
		"stddeviation" => "stdDeviation",
		"stitchtiles" => "stitchTiles",
		"surfacescale" => "surfaceScale",
		"systemlanguage" => "systemLanguage",
		"tablevalues" => "tableValues",
		"targetx" => "targetX",
		"targety" => "targetY",
		"textlength" => "textLength",
		"viewbox" => "viewBox",
		"viewtarget" => "viewTarget",
		"xchannelselector" => "xChannelSelector",
		"ychannelselector" => "yChannelSelector",
		"zoomandpan" => "zoomAndPan"
	);
	
	/**
	 * MathML camel cased attributes
	 *
	 * @static
	 * @var		array
	 */	
	static $MATHMLAttributes = array("definitionurl" => "definitionURL");
	
	/**
	 * Association of tag names and attributes that the
	 * itemValue property must reflect
	 *
	 * @static
	 * @var		array
	 */	
	static $itemValueMap = array(
		"meta"=>"content", "audio"=>"src", "embed"=>"src", 
		"iframe"=>"src", "img"=>"src", "source"=>"src", 
		"track"=>"src", "video"=>"src", "a"=>"href", 
		"area"=>"href", "link"=>"href", "object"=>"data",
		"data"=>"value", "time"=>"datetime"
	);
	
	/**
	 * Current document
	 *
	 * @var		HTMLDocument
	 */
	public $document;

	/**
	 * Class constructor. Sets the HTML code to parse.
	 *
	 * @param	string	$code			HTML code
	 * @param	string	$encoding		Output encoding
	 * @param	string	$docEncoding	Encoding of the document.
	 *									If not given it will be
	 *									automatically detected
	 */
	function __construct ($code, $encoding = null, $docEncoding = null)
	{
		parent::__construct($code);
		if (!$docEncoding) {
			$docEncoding = $this->_guessEncodingAndFix();
		}
		$this->_setEncoding($docEncoding, $encoding);
		$this->_preprocess();
		$this->_mode = self::INITIAL_MODE;
		$this->quirksMode = self::NO_QUIRKS_MODE;
		//The state machine must start in the data state
		$this->state = self::DATA_STATE;
		$this->document = new HTMLDocument;
	}

	/**
	 * Starts the parsing process
	 *
	 * @return	HTMLDocument	Generated document
	 */
	function parse ()
	{
		$this->_tokenize();
		return $this->document;
	}

	/**
	 * Starts the parsing process of a srcdoc iframe attribute
	 *
	 * @return	HTMLDocument	Generated document
	 */
	function parseSrcDoc ()
	{
		$this->srcdoc = true;
		$this->_tokenize();
		return $this->document;
	}

	/**
	 * Starts the parsing of an html fragment
	 *
	 * @param	HTMLElement		$context	Context node
	 * @return	HTMLElement		Root node that contains the resulting nodes
	 */
	function parseHTMLFragment (HTMLElement $context)
	{
		$this->document = $context->ownerDocument;
		if ($context->ownerDocument->compatMode === "BackCompat") {
			$this->quirksMode = self::QUIRKS_MODE;
		}
		$this->fragmentCase = true;
		//If there is a context element, run these substeps:
		//1.Set the state of the HTML parser's tokenization stage as follows:
		switch ($context->tagName) {
			//If it is a title or textarea element
			case "title":
			case "textarea":
				//Switch the tokenizer to the RCDATA state.
				$this->state = self::RCDATA_STATE;
			break;
			//If it is a style, xmp, iframe, noembed, or noframes element
			case "style":
			case "xmp":
			case "iframe":
			case "noembed":
			case "noframes":
				//Switch the tokenizer to the RAWTEXT state.
				$this->state = self::RAWTEXT_STATE;
			break;
			//If it is a script element
			case "script":
				//Switch the tokenizer to the script data state.
				$this->state = self::SCRIPT_DATA_STATE;
			break;
			//If it is a noscript element
			case "noscript":
				//If the scripting flag is enabled, switch the tokenizer to
				//the RAWTEXT state. Otherwise, leave the tokenizer in the
				//data state.
				if ($this->scriptingFlag) {
					$this->state = self::RAWTEXT_STATE;
				}
			break;
			//If it is a plaintext element
			case "plaintext":
				//Switch the tokenizer to the PLAINTEXT state.
				if ($this->scriptingFlag) {
					$this->state = self::PLAINTEXT_STATE;
				}
			break;
			//Otherwise
			//Leave the tokenizer in the data state.
		}
		//Let root be a new html element with no attributes.
		//Append the element root to the Document node created above.
		//Set up the parser's stack of open elements so that it contains just
		//the single element root.
		$root = $this->_insertElement(
			array("tagname" => "html"),
			self::HTML_NAMESPACE,
			true,
			true
		);
		$this->_setCurrentElement($root);
		//Reset the parser's insertion mode appropriately.
		$this->_resetInsertionMode($context);
		//Set the parser's form element pointer to the nearest node to the
		//context element that is a form element (going straight up the
		//ancestor chain, and including the element itself, if it is a form
		//element), or, if there is no such form element, to null.
		if ($context->tagName === "form") {
			$this->_formPointer = $context;
		} else {
			$parent = $context->parentNode;
			while ($parent) {
				if ($parent->tagName === "form") {
					$this->_formPointer = $parent;
					break;
				}
				$parent = $parent->parentNode;
			}
		}

		$this->_tokenize();
		return $root;
	}

	/**
	 * Tries to find code encoding while fixing it
	 *
	 * @return	string		Code encoding
	 * @access	protected
	 */
	protected function _guessEncodingAndFix ()
	{
		$enc = parent::_checkAndFixBom();
		
		//Replace XML leading tag
		$regXMLEncoding = "#^\s*<\?xml(.*?)\?>#i";
		if (preg_match($regXMLEncoding, $this->code, $match)) {
			$this->code = preg_replace($regXMLEncoding, "", $this->code);
			if ($enc === null) {
				$hasEncoding = preg_match(
					"#encoding=[\"'](.*?)[\"']#i",
					$match[1],
					$match
				);
				if ($hasEncoding) {
					return $match[1];
				}
			}
		}
		
		if ($enc) {
			return $enc;
		}
		
		//Find the encoding in the meta tags
		$metaReg = "#<meta[^>]+charset=([^'\">]+)#i";
		if (preg_match_all($metaReg, $this->code, $matches)) {
			return end($matches[1]);
		}
		
		return $this->_detectEncoding();
	}

	/**
	 * Preprocesses the code
	 *
	 * @return	void
	 * @access	protected
	 */
	protected function _preprocess ()
	{
		//U+000D CARRIAGE RETURN (CR) characters and U+000A LINE FEED (LF)
		//characters are treated specially. Any CR characters that are followed
		//by LF characters must be removed, and any CR characters not followed
		//by LF characters must be converted to LF characters. Thus, newlines
		//in HTML DOMs are represented by LF characters, and there are never
		//any CR characters in the input to the tokenization stage.
		$replace = array("\r\n", "\r");
		/*Most browsers replace the CR characters that are followed by LF
		characters with a LF characters so let's follow that behaviour*/
		$replacement = array("\n", "\n");
		/*Unicode replacement character*/
		$repl = $this->decode("\xEF\xBF\xBD", null, "UTF-8");
		$this->_unicodeReplacementCharacter = $repl;
		//All U+0000 NULL characters must be replaced by U+FFFD REPLACEMENT
		//CHARACTERs. Any occurrences of such characters and code points are
		//parse errors.
		$replace[] = "\x00";
		$replacement[] = $this->_unicodeReplacementCharacter;
		$this->code = str_replace($replace, $replacement, $this->code);
	}
	
	/**
	 * Returns the serialized representation of the given node
	 *
	 * @param	Node	$node	Node to serialize
	 * @return	string	Serialized node
	 * @static
	 */
	static function serialize (Node $node)
	{
		$s = "";
		switch ($node->nodeType) {
			case NODE::ELEMENT_NODE:
				$s .= "<";
				$tagPrefix = $node->prefix ?  $node->prefix . ":" : "";
				$s .= $tagPrefix . $node->tagName;
				$l = $node->attributes->length;
				$ns = $node->namespaceURI;
				for ($i = 0; $i < $l; $i++) {
					$attr = $node->attributes[$i];
					$prefix = $attr->prefix;
					if (!$prefix) {
						$ans = $attr->namespaceURI;
						if ($ns === self::XML_NAMESPACE) {
							$prefix = "xml:";
							
						} elseif ($ans === self::XLINK_NAMESPACE) {
							$prefix = "xlink:";
						} elseif ($ans === self::XMLNS_NAMESPACE &&
								$attr->name !== "xmlns") {
							$prefix = "xmlns:";
						}
					} else {
						$prefix .= ":";
					}
					$value = self::_quoteSerializedData($attr->value, true);
					$name = $attr->name;
					if ($ns === self::SVG_NAMESPACE &&
						isset(self::$SVGAttributes[$name])) {
						$name = self::$SVGAttributes[$name];
					} elseif ($ns === self::MATHML_NAMESPACE &&
							isset(self::$MATHMLAttributes[$name])) {
						$name = self::$MATHMLAttributes[$name];
					}
					$s .= " $prefix" . $name . "=\"$value\"";
				}
				$s .= ">";
				if (!in_array($node->tagName, self::$HTMLTagsNoChildren)) {
					if (in_array($node->tagName, self::$HTMLTagsLineFeed)) {
						$s .= "\n";
					}
					$s .= self::serializeChildNodes($node);
					$s .= "</" . $tagPrefix . $node->tagName . ">";
				}
			break;
			case NODE::TEXT_NODE:
			case NODE::CDATA_SECTION_NODE:
				$parent = $node->parentNode && $node->parentNode->nodeType === 1 ?
						  $node->parentNode->tagName :
						  "";
				$impl = $node->ownerDocument->_implementation;
				if ($parent && in_array($parent, self::$noQuoteText)) {
					$s .= $node->data;
				} elseif ($parent === "noscript" &&
						$impl["javascriptEnabled"]) {
					$s .= $node->data;
				} else {
					$s .= self::_quoteSerializedData($node->data);
				}
			break;
			case NODE::COMMENT_NODE:
				$s .= "<!--" . $node->data . "-->";
			break;
			case NODE::PROCESSING_INSTRUCTION_NODE:
				$s .= "<?" . $node->target . " " . $node->data . ">";
			break;
			case NODE::DOCUMENT_TYPE_NODE:
				$s .= "<!DOCTYPE";
				if ($node->name) {
					$s .= " " . $node->name;
				}
				if ($node->publicId) {
					$s .= " PUBLIC \"{$node->publicId}\"";
				}
				if ($node->systemId) {
					$s .= " \"{$node->systemId}\"";
				}
				$s .= ">";
			break;
			case NODE::DOCUMENT_NODE:
			case NODE::DOCUMENT_FRAGMENT_NODE:
				$s .= self::serializeChildNodes($node);
			break;
			default:
				throw new DomException("The given node can't be serialized");
			break;
		}
		return $s;
	}
	
	/**
	 * Returns the serialized representation of the given
	 * node's children collection
	 *
	 * @param	Node	$node	Node that contains the
	 *							children to serialize
	 * @return	string	Serialized child nodes
	 * @static
	 */
	static function serializeChildNodes (Node $node)
	{
		$s = "";
		$l = $node->childNodes->length;
		for ($i = 0; $i < $l; $i++) {
			$s .= self::serialize($node->childNodes[$i]);
		}
		return $s;
	}
	
	/**
	 * Quotes the data to serialize
	 *
	 * @param	string		$data			Data to quote
	 * @param	bool		$attrMode		Attribute mode
	 * @return	string		Quoted data
	 * @static
	 * @access	protected
	 */
	static protected function _quoteSerializedData ($data, $attrMode = false)
	{
		$replace = array("&", "\xA0");
		$replacement = array("&amp;", "&nbsp;");
		if ($attrMode) {
			$replace[] = '"';
			$replacement[] = "&quot;";
			$replace[] = "<";
			$replacement[] = "&lt;";
			$replace[] = ">";
			$replacement[] = "&gt;";
		}
		return str_replace($replace, $replacement, $data);
	}
}