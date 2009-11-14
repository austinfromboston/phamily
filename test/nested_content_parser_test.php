<?php

require_once( 'test_helper.php');

class TestOfNestedContentParser extends UnitTestCase {

    function setUp( ){
        $this->parser = new PhamilyParser;
    }

    function test_parse_inline_content( ) {
        $result = $this->parser->parse( "%html blah" );
        $this->assertEqual( $result, '<html>blah</html>'."\n");
    }

    function test_parse_nested_content( ) {
        $result = $this->parser->parse( "%html\n  blah" );
        $this->assertEqual( $result, "<html>\n  blah\n</html>\n");

    }
    function test_parse_multiline_nested_content( ) {
        $result = $this->parser->parse( "%html\n  blah\n  mah\n  fah" );
        $this->assertEqual( $result, "<html>\n  blah\n  mah\n  fah\n</html>\n");

    }
    function test_nested_content_parsing( ) {
        $result = $this->parser->parse_nested_content( array( "  %head", "    blah", "  %body", "    wah" ));
        $this->assertEqual( $result['content'], "    blah\n");
    }
    function test_parse_double_nested_content( ) {
        $result = $this->parser->parse( "%html\n  %head\n    blah\n  %body\n    wah" );
        $this->assertEqual( $result, "<html>\n  <head>\n    blah\n  </head>\n  <body>\n    wah\n  </body>\n</html>\n");

    }
}
