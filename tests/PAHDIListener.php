<?php
class PAHDIListener implements PHPUnit_Framework_TestListener
{
	static protected $_logs = array();
	
	function addError (PHPUnit_Framework_Test $test, Exception $e, $time)
	{
	}

	function addFailure (PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
	{
	}

	function addIncompleteTest (PHPUnit_Framework_Test $test,  Exception $e,  $time)
	{
	}

	function addSkippedTest (PHPUnit_Framework_Test $test, Exception $e, $time)
	{
	}

	function startTest (PHPUnit_Framework_Test $test)
	{
	}

	function endTest (PHPUnit_Framework_Test $test, $time)
	{
	}

	function startTestSuite (PHPUnit_Framework_TestSuite $suite)
	{
	}

	function endTestSuite (PHPUnit_Framework_TestSuite $suite)
	{
		if (PAHDI_SHOW_TIMERS && count(self::$_logs)) {
			echo "\n\n" . implode("\n", self::$_logs) . "\n\n";
		}
		self::$_logs = array();
	}
	
	static function registerLogMessage ($msg)
	{
		self::$_logs[] = $msg;
	}
}