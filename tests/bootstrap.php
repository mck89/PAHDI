<?php
define("DRS", DIRECTORY_SEPARATOR);
define("PAHDI_SOURCE_DIR", dirname(__FILE__) . DRS. ".." . DRS . "src");
define("PAHDI_TEST_DIR", dirname(__FILE__));
define("PAHDI_TEST_SOURCE_DIR", dirname(__FILE__) . DRS . "sources");
define("PAHDI_SHOW_TIMERS", 0);

require_once PAHDI_SOURCE_DIR . DRS . "PAHDI.php";
require_once PAHDI_TEST_DIR . DRS . "PAHDITest.php";