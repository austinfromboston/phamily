<?php

require_once( 'test_helper.php');

class TestOfNestedContentParser extends UnitTestCase {

    function setUp( ){
        $this->parser = new PhamilyParser;
    }

    function test_parse_inline_content( ) {
        $result = $this->parser->render( "%html blah" );
        $this->assertEqual( $result, '<html>blah</html>'."\n");
    }

    function test_parse_nested_content( ) {
        $result = $this->parser->render( "%html\n  blah" );
        $this->assertEqual( $result, "<html>\n  blah\n</html>\n");

    }
    function test_parse_multiline_nested_content( ) {
        $result = $this->parser->render( "%html\n  blah\n  mah\n  fah" );
        $this->assertEqual( $result, "<html>\n  blah\n  mah\n  fah\n</html>\n");

    }
    function test_nested_content_parsing( ) {
        $result = $this->parser->parse_nested_content( array( "  %head", "    blah", "  %body", "    wah" ));
        $this->assertEqual( $result['content'], "    blah\n");
    }
    function test_parse_double_nested_content( ) {
        $result = $this->parser->render( "%html\n  %head\n    blah\n  %body\n    wah" );
        $this->assertEqual( $result, "<html>\n  <head>\n    blah\n  </head>\n  <body>\n    wah\n  </body>\n</html>\n");

    }
    function test_parse_super_nested_content( ) {
        $result = $this->parser->render( "%html\n  %head\n    blah\n  %body\n    wah\n    #header.active narf\n    %h2\n      Known Problems\n      and Complications\n    %h2 Solutions\n    plus" );
        $this->assertEqual( $result, "<html>\n  <head>\n    blah\n  </head>\n  <body>\n    wah\n    <div class='active' id='header'>narf</div>\n    <h2>\n      Known Problems\n      and Complications\n    </h2>\n    <h2>Solutions</h2>\n    plus\n  </body>\n</html>\n");

    }
}
