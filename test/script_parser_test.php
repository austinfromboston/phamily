<?php

require_once( 'test_helper.php');

class TestOfScriptParser extends UnitTestCase {

    function setUp( ){
        $this->parser = new PhamilyParser;
    }

    function test_parse_return_value( ) {
        $result = $this->parser->render( "#header= 'enjoy' . ' the ride'");
        $this->assertEqual( $result, "<div id='header'>enjoy the ride</div>\n");
    }

    function test_pass_variables_into_runtime( ){
        $result = $this->parser->render( "#header= 'enjoy' . \$action", 
                                            array( 'emotion' => 'enjoy', 'action' => ' the ride'));
        $this->assertEqual( $result, "<div id='header'>enjoy the ride</div>\n");
    }

    function test_nested_evaluation( ) {
        $result = $this->parser->render( "#header\n  = 'feel' . \$action\n  = \"\$emotion the breeze\"", 
                                            array( 'emotion' => 'enjoy', 'action' => ' the ride'));
        $this->assertEqual( $result, "<div id='header'>\n  feel the ride\n  enjoy the breeze\n</div>\n");
    }

    function test_conditional_block( ) {
        $result = $this->parser->parse( 
                    "#header\n  - if( \$show_button )\n    button\n  push");
        $this->assertEqual( $result, "<div id='header'>\n<?php if( \$show_button ) { ?>\n    button\n<?php } ?>\n  push\n</div>\n");

        $result = $this->parser->render( 
                    "#header\n  - if( \$show_button )\n    button\n  push", 
                    array( 'show_button' => true));
        $this->assertEqual( $result, "<div id='header'>\n    button\n  push\n</div>\n");

        $result = $this->parser->render( 
                    "#header\n  - if(\$show_button )\n    button\n  push", 
                    array( 'show_button' => false ));
        $this->assertEqual( $result, "<div id='header'>\n  push\n</div>\n");
    }
        
    function test_for_loop_block( ) {
        $result = $this->parser->render( 
                    "#header\n  - for( \$i=1; \$i<4; \$i++ )\n    %li= \"header \$i\"");
        $this->assertEqual( $result, "<div id='header'>\n    <li>header 1</li>\n    <li>header 2</li>\n    <li>header 3</li>\n</div>\n");

    }
        
}
