<?php

require_once( 'test_helper.php');

class TestOfTagParser extends UnitTestCase {

    function setUp( ){
        $this->parser = new PhamilyParser;
    }

    function test_parse_tag( ) {
        $result = $this->parser->parse( "%html" );
        $this->assertEqual( $result, '<html></html>');
    }

    function test_parse_classes( ) {
        $result = $this->parser->parse( "%div.header.active" );
        $this->assertEqual( $result, "<div class='header active'></div>");
    }

    function test_parse_id( ) {
        $result = $this->parser->parse( "%div#header" );
        $this->assertEqual( $result, "<div id='header'></div>");
    }

    function test_parse_id_and_classes( ) {
        $result = $this->parser->parse( "%div#header.active.fast" );
        $this->assertEqual( $result, "<div class='active fast' id='header'></div>");
        $result = $this->parser->parse( "%div.active.fast#header" );
        $this->assertEqual( $result, "<div class='active fast' id='header'></div>");
    }

    function test_implicit_div( ) {
        $result = $this->parser->parse( "#header.active.fast" );
        $this->assertEqual( $result, "<div class='active fast' id='header'></div>");
        $result = $this->parser->parse( ".active" );
        $this->assertEqual( $result, "<div class='active'></div>");
        $result = $this->parser->parse( ".active#footer" );
        $this->assertEqual( $result, "<div class='active' id='footer'></div>");
    }

    function test_explicit_attributes( ) {
        $result = $this->parser->parse( "%div{ 'class' => 'active' }" );
        $this->assertEqual( $result, "<div class='active'></div>");
        $result = $this->parser->parse( "%div{ 'class' => 'active fast', 'id' => 'header' }" );
        $this->assertEqual( $result, "<div class='active fast' id='header'></div>");
    }

    function test_merging_attributes( ) {
        $result = $this->parser->parse( "#header{ 'class' => 'active' }" );
        $this->assertEqual( $result, "<div class='active' id='header'></div>");
        $result = $this->parser->parse( "#header.fast{ 'class' => 'active' }" );
        $this->assertEqual( $result, "<div class='fast active' id='header'></div>");
    }


}
