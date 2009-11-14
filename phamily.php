<?php

class PhamilyParser {
    var $line_start = "^(\s*)";
    var $tag_start = "(\%(\w+))?";
    var $inline_attrs = "(([#\.][-_\w]+)*)";
    var $explicit_attrs = "(\{([^}]+)\})?";
    var $inline_content = "\s*(.*)?$";

    function parse( $template, $starting_line_no = 0 ) {
        $template_lines = explode( "\n", $template );
        $result = "";
        for( $line_no = 0; $line_no < count( $template_lines ); $line_no++ ) {
            $template_line = $template_lines[$line_no];
            $parsed_tag = PhamilyParser::parse_tag( $template_line );
            if( isset( $parsed_tag['tag']) && $parsed_tag['tag']) {
                $nested_contents = PhamilyParser::parse_nested_content( array_slice( $template_lines, $line_no ) );
                if( $nested_contents && isset( $nested_contents['content'])) {
                    $parsed_tag['nested_content'] = $nested_contents['content'];
                    $line_no = $line_no + $nested_contents['length'];
                }
            }
            $result .= PhamilyParser::render_tag( $parsed_tag );
        }
        return $result;
    }

    function parse_nested_content( $template_lines, $starting_line_no=0 ) {
        $testable_lines = array_slice( $template_lines, $starting_line_no+1 );
        preg_match( "/^(\s*)/", $template_lines[0], $matches );
        $spacing = isset( $matches[1]) ? strlen( $matches[1] ) : 0;
        $padding = str_pad( "", $spacing + 2 );
        $nested_lines = array( );
        foreach( $testable_lines as $line_no => $test_line ) {
            if( substr( $test_line, 0, strlen( $padding )) == $padding ) {
                $nested_lines[] = $test_line;
            } else {
                break;
            }
        }
        if( empty( $nested_lines )) return;
        return array( 'content' => PhamilyParser::parse( implode( "\n", $nested_lines ) ), 
                        'length' => count( $nested_lines ) );


    }

    function render_tag( $parsed_tag ) {
        if( !( isset( $parsed_tag['tag']) && $parsed_tag['tag'])) {
            return $parsed_tag['spacing'] . $parsed_tag['inline_content'] . "\n";
        }
        if( !( isset( $parsed_tag['nested_content']) && $parsed_tag['nested_content'])) {
            return "{$parsed_tag['spacing']}<{$parsed_tag['tag']}{$parsed_tag['attr_string']}>{$parsed_tag['inline_content']}</{$parsed_tag['tag']}>\n";
        }
        return "{$parsed_tag['spacing']}<{$parsed_tag['tag']}{$parsed_tag['attr_string']}>\n{$parsed_tag['nested_content']}{$parsed_tag['spacing']}</{$parsed_tag['tag']}>\n";
    }

    function parse_tag( $template ) {
        $matches = array( );
        preg_match( 
            "/{$this->line_start}{$this->tag_start}{$this->inline_attrs}{$this->explicit_attrs}{$this->inline_content}/",
            $template, $matches );
        return PhamilyParser::process_matches( $matches );

    }
    
    function process_matches( $matches ) {
        if( !( isset( $matches[3]) && $matches[3]) && isset( $matches[4]) && $matches[4]) {
            $matches[3] = 'div';
        } 

        $explicit_attrs = false;
        if( isset( $matches[7]) && $matches[7]) {
            eval( "\$explicit_attrs = array( {$matches[7]});");
        }
        
        $inline_attrs = false;
        if( isset( $matches[4]) && $matches[4]) {
            $inline_attrs = 
                array_reduce( 
                    array_chunk( preg_split( '/([\.#])/', $matches[4], -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE ), 2), 
                    array( 'PhamilyParser', 'reduce_inline_attrs'));
                    
        }
        $all_attrs = PhamilyParser::merge_attrs( $inline_attrs, $explicit_attrs );
        #return vsprintf( '%2$s<%4$s'.PhamilyParser::build_attr_string( $all_attrs ).'></%4$s>', $matches );
        return array( 'spacing' => $matches[1], 'tag' => $matches[3], 'attr_string' => PhamilyParser::build_attr_string( $all_attrs ), 'inline_content' => $matches[8] );
    }

    function merge_attrs( $attrs1, $attrs2 ) {
        if( empty( $attrs1 ) && empty( $attrs2 )) return false;
        if( empty( $attrs1 )) return $attrs2;
        if( empty( $attrs2 )) return $attrs1;
        $merged_attrs = $attrs1;
        foreach( $attrs2 as $key => $value ) {
            if( !isset( $merged_attrs[$key])) {
                $merged_attrs[$key] = $value;
                continue;
            }
            $merged_attrs[$key] .= ' ' . $value;
        }
        return $merged_attrs;

    }

    function build_attr_string( $attrs ) {
        if( empty( $attrs )) return;
        $attr_string = '';
        ksort( $attrs );
        foreach( $attrs as $type => $values ) {
            $attr_string .= " $type='$values'";
        }
        return $attr_string;
    }

    function reduce_inline_attrs( $attrs, $new_term ) {
       if( !$new_term[1] )  return $attrs;
       if( $new_term[0] =='#' ) {
           $attrs['id'] = $new_term[1];
           return $attrs;
       }
       if( !isset( $attrs['class'])) {
           $attrs['class'] = $new_term[1];
           return $attrs;
       }

       $attrs['class'] .= " " . $new_term[1];
       return $attrs; 
    }

}
