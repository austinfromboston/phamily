<?php

require_once( 'test_helper.php');

class TestOfNestedContentParser extends UnitTestCase {

    function setUp( ){
        $this->parser = new PhamilyParser;
    }

    function test_parse_inline_content( ) {
        $result = $this->parser->parse( "%html blah" );
        $this->assertEqual( $result, '<html>blah</html>');
    }

    function test_parse_nested_content( ) {
        $result = $this->parser->parse( "%html\n  blah" );
        $this->assertEqual( $result, '<html>\n  blah\n</html>');

    }
}
