<?php
/*
Plugin Name: Utility Functions
Plugin URI: http://www.dtelepathy.com/
Description: General utility functions helpful in any theme or plugin. Can be loaded as a must use plugin (recommended) or a regular plugin
Version: 1.0.0
Author: digital-telepathy
Author URI: http://www.dtelepathy.com
License: GPL3

Copyright 2011 digital-telepathy  (email : support@digital-telepathy.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * Abbreviate a Large Number
 * 
 * Handles the abbreviation of large numbers for more 
 * stylish/usable display of large numbers. It uses an array of
 * ordinals K for 1000 M for 1000000 etc.
 * 
 * @param integer $size An integer with a size of something.
 * @param integer $precision [optional] The number of decimal places to use. 
 * 
 * @return string A string of formatted numbers with an ordinal.
 */
if( !function_exists( 'abbr_number' ) ) {
    function abbr_number( $size, $precision = 0 ) {
        $size = intval( $size );
        $sizes = array( "", "K", "M", "B" );
        $i = log( $size, 1000 );
        if ( $size == 0 ) {
             return( $size ); 
        } else {
            if( $size < 1000 ) {
                return number_format( $size / pow( 1000, ( $i = floor( log( $size, 1000 ) ) ) ), 0 ) . $sizes[$i];
            } else {
                return number_format( $size / pow( 1000, ( $i = floor( log( $size, 1000 ) ) ) ), $precision ) . $sizes[$i];
            }
        }
    }
}

/**
 * Sanitize data
 * 
 * @param mixed $str The data to be sanitized
 * 
 * @uses wp_kses()
 * 
 * @return mixed The sanitized version of the data
 */
if( !function_exists( 'sanitize_data' ) ) {
	function sanitize_data( $str ) {
	    if ( !function_exists( 'wp_kses' ) ) {
	        require_once( ABSPATH . 'wp-includes/kses.php' );
	    }
	    global $allowedposttags;
	    global $allowedprotocols;
	    
	    if ( is_string( $str ) ) {
	        $str = wp_kses( $str, $allowedposttags, $allowedprotocols );
	    } elseif( is_array( $str ) ) {
	        $arr = array();
	        foreach( (array) $str as $key => $val ) {
	            $arr[$key] = sanitize_data( $val );
	        }
	        $str = $arr;
	    }
	    
	    return $str;
	}
}

/**
 * Strip Tags and Trucate
 * 
 * A utility function to clean the HTML tags from 
 * an excerpt and the truncate it to a specified length.
 * 
 * @param string $text The input string
 * @param integer $length [optional] The number of characters to be shown.
 * @param string $truncate_characters [optional] The ellipsis character by default. 
 * 
 * @return string
 */
if( !function_exists( 'strip_tags_truncate' ) ) {
	function strip_tags_truncate( $text, $length = 100, $trucate_characters = '&hellip;' ){
		if( strlen( $text ) > $length ){
			return trim( mb_substr( strip_tags( $text ) ,0 ,$length ) ) . $trucate_characters;
		}
		return $text;
	}
}

/**
 * Debug script
 * 
 * Script used for debugging themes, functions, etc. Will output using print_r
 * by default, but if $verbose is set to boolean(true) it will use var_dump instead.
 * Output is wrapped in a <pre> tag for easy readability.
 * 
 * This script will read the type of the object passed in and intelligently detect 
 * its type and use an appropriate method to format the output:
 *   Array - print_r()
 *   Object - print_r()
 *   Boolean - var_export()
 *   String - echo
 * 
 * @param mixed $var The variable to debug
 * @param boolean $verbose Whether or not to use a verbose output (var_dump) to see information about object type, entity length, etc.
 */
if( !function_exists( '_d' ) ) {
    function _d( $var, $verbose = false ) {
        echo "<pre class=\"debug\">";

        if( $verbose == true ) {
            var_dump( $var );
        } else {
            if( is_array( $var ) || is_object( $var ) ) {
                print_r( $var );
            } elseif( is_bool( $var ) ) {
                echo var_export( $var, true );
            } else {
                echo $var;
            }
        }

        echo "</pre>";
    }
}

/**
 * Create a slug value from a regular string
 * 
 * Takes a regular string phrase and replaces all valid special multi-byte
 * characters with ISO-8859 compatible characters, all spaces with - characters
 * and converts the string to lowercase. Useful for creating DOM IDs and 
 * 
 * @param string $str The string to convert to slug format
 * 
 * @return string
 */
if( !function_exists( 'slugify' ) ) {
	function slugify( $str ) {
	    $multibyte = array( 'À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ' );
	    $singlebyte_replacement = array( 'A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o' );
	    
	    $slug = str_replace( $multibyte, $singlebyte_replacement, $str );
	    $slug = strtolower( preg_replace( array( "/[^a-zA-Z0-9 \-]/", "/[ \-]+/", "/^-|-$/" ), array( "", "-", ""), $str ) );
	    
	    return $slug;
	}
}

/**
 * Truncate text to a specified length
 * 
 * Returns a substring of the text passed in truncated down to the specified length.
 * Does not take into account proper closing of HTML tags.
 * 
 * @param string $str The string to truncate
 * @param integer $length Length to truncate to in characters
 * @param string $suffix The text to append to the end of a truncated string
 */
if( !function_exists( 'truncate_text' ) ) {
    function truncate_text( $str, $length = 55, $suffix = "&hellip;" ) {
        $truncated = trim( substr( $str, 0, $length ) );
        if( strlen( $str ) > $length ) {
            $truncated .= $suffix;
        }
        
        return $truncated;
    }
}

