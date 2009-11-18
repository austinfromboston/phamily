<?php
require_once( '../lib/simpletest/autorun.php');
require_once( '../lib/simpletest/reporter.php');

$test = &new TestSuite( 'All tests');
$test->addTestFile( 'tag_parser_test.php');
$test->addTestFile( 'script_parser_test.php');
$test->addTestFile( 'nested_content_parser_test.php');
$test->run( new TextReporter( ));


?>
