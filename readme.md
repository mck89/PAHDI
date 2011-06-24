PAHDI
===========

PAHDI (PHP Advanced HTML DOM Implementation) is a library that provides an advanced implementation of the Document Object Model in PHP starting from HTML code.

It follows W3C standards and some browser-specific methods.

It can parse an HTML file or a string and create the resulting DOM structure with the same methods available in the browsers with JavaScript.

Usage
-----

Parse an HTML string:

```php
//Include the class
require_once "PAHDI/src/PAHDI.php";

//Parse the HTML code
$document = PAHDI::parseString("<div id='test' class='foo'></div>");

//Get an element by id
$div = $document->getElementById("test");

//Print element's class name
echo $div->className;  //foo

```

Parse a remote HTML page:

```php
//Include the class
require_once "PAHDI/src/PAHDI.php";

//Parse the HTML code
$document = PAHDI::parseRemoteSource("http://www.urlToParse.com");

```

Parse a local HTML file:

```php
//Include the class
require_once "PAHDI/src/PAHDI.php";

//Parse the HTML code
$document = PAHDI::parseLocalSource("path/to/file.html");

```

Main differences from JavaScript
-------
For performance reasons there are some differences with JavaScript DOM implementation, the most notable are:

* HTMLCollection and NodeList instances are not "live"
* Elements style values are not validated
* Elements tag names are always lowercased


Licensed under BSD license.