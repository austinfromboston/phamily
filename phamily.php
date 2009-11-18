<?php

class PhamilyParser {
    const spacing = "^(\s*)";
    const tag_start = "(\%(\w+))?";
    const inline_attrs = "(([#\.][-_\w]+)*)";
    const explicit_attrs = "(\{([^}]+)\})?";
    const inline_content = "\s*(.*)?$";

    /* core method for rendering a haml template to html
     */
    function render( $template, $starting_line_no = 0 ) {
        $template_lines = explode( "\n", $template );
        $result = "";
        for( $line_no = 0; $line_no < count( $template_lines ); $line_no++ ) {
            $template_line = $template_lines[$line_no];
            $parsed_tag = self::parse_tag( $template_line );
            if( isset( $parsed_tag['tag']) && $parsed_tag['tag']) {
                $nested_contents = self::parse_nested_content( array_slice( $template_lines, $line_no ) );
                if( $nested_contents && isset( $nested_contents['content'])) {
                    $parsed_tag['nested_content'] = $nested_contents['content'];
                    $line_no = $line_no + $nested_contents['length'];
                }
            }
            $result .= self::render_tag( $parsed_tag );
        }
        return $result;
    }


    /* returns an array describing the contents and length of the current indented section
     */
    function parse_nested_content( $template_lines ) {
        $testable_lines = array_slice( $template_lines, 1 );
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
        return array( 'content' => self::render( implode( "\n", $nested_lines ) ), 
                        'length' => count( $nested_lines ) );


    }

    /* returns html when passed a data array describing a line
     */
    function render_tag( $parsed_tag ) {
        if( !( isset( $parsed_tag['tag']) && $parsed_tag['tag'])) {
            return $parsed_tag['spacing'] . $parsed_tag['inline_content'] . "\n";
        }
        if( !( isset( $parsed_tag['nested_content']) && $parsed_tag['nested_content'])) {
            return "{$parsed_tag['spacing']}<{$parsed_tag['tag']}{$parsed_tag['attr_string']}>{$parsed_tag['inline_content']}</{$parsed_tag['tag']}>\n";
        }
        return "{$parsed_tag['spacing']}<{$parsed_tag['tag']}{$parsed_tag['attr_string']}>\n{$parsed_tag['nested_content']}{$parsed_tag['spacing']}</{$parsed_tag['tag']}>\n";
    }

    /* turns the first line of the passed template into a data array describing the line
     */
    function parse_tag( $template ) {
        $matches = array( );
        preg_match( 
            "/" . self::spacing . self::tag_start . self::inline_attrs . self::explicit_attrs . self::inline_content . "/",
            $template, $matches );

        $all_attrs = self::merge_attrs( 
            self::process_inline_attributes( $matches ),
            self::process_explicit_attributes( $matches ) );

        return array( 
                'spacing' => self::matches( 'spacing', $matches ),
                'tag' => self::process_tag( $matches ),
                'attr_string' => self::build_attr_string( $all_attrs ), 
                'inline_content' => self::matches( 'inline_content', $matches )
            );

    }

    /* returns a particular block from a set of matches
     */
    function matches( $match_type, $matches ) {
        $offsets = array(
            'spacing'               => 1,
            'tag'                   => 3,
            'inline_attributes'     => 4,
            'explicit_attributes'   => 7,
            'inline_content'        => 8 
        );

        return isset( $matches[ $offsets[ $match_type ]]) && $matches[ $offsets[ $match_type ]] ? $matches[ $offsets[$match_type]]
                                                                                                : false;
        
    }
    
    /* parses matches for tag and substitute 'div' as default
     * 
     */
    function process_tag( $matches ) {
        $tag = self::matches( 'tag', $matches );
        if( !$tag && self::matches( 'inline_attributes', $matches )) {
            return 'div';
        } 
        return $tag;
    }

    /* parses matches for attibute array inside curly brackets
     *
     */
    function process_explicit_attributes( $matches ) {
        $explicit_attrs_src = self::matches( 'explicit_attributes', $matches );
        if( !$explicit_attrs_src ) return false;
        $explicit_attrs = false;
        eval( "\$explicit_attrs = array( {$explicit_attrs_src});");
        return $explicit_attrs;

    }

    /* parses matches for inline class and id attributes
     *
     */
    function process_inline_attributes( $matches ) {
        $inline_attrs_src = self::matches( 'inline_attributes', $matches );
        if( !$inline_attrs_src ) return false; 
        return array_reduce( 
                    array_chunk( preg_split( '/([\.#])/', $inline_attrs_src, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE ), 2), 
                    array( 'self', 'reduce_inline_attrs'));

    }


    /* Merges two attribute arrays, combining values with a space
     *
     */
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

    /* Converts an array of attributes into an HTML attribute string
     *
     */
    function build_attr_string( $attrs ) {
        if( empty( $attrs )) return;
        ksort( $attrs );
        $attr_string = '';
        foreach( $attrs as $type => $values ) {
            $attr_string .= " $type='$values'";
        }
        return $attr_string;
    }

    
    /* helper function to parse class and id attributes defined on the tag into an array
     *
     */
    function reduce_inline_attrs( $attrs, $new_term ) {
       if( !( $new_term && $new_term[1] ))  return $attrs;

       // ids are marked by #
       if( $new_term[0] =='#' ) {
           $attrs['id'] = $new_term[1];
       }

       // classes are marked by .
       if( $new_term[0] =='.' ) {
           if( !isset( $attrs['class'])) {
               $attrs['class'] = $new_term[1];
           } else {
               $attrs['class'] .= " " . $new_term[1];
           }
       }

       return $attrs; 
    }

}
