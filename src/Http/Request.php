<?php

/**
 * Scissor (http://craftsware.net/scissor)
 *
 * @author    Ruel Mindo
 * @link      https://github.com/craftsware/scissor
 * @license   https://github.com/craftsware/scissor/LICENSE.md (MIT License)
 * @copyright Copyright (c) 2017 Ruel Mindo
 * @since     0.0.1
 * 
 */

namespace Craftsware\Http;


/**
* Request
*/
class Request {
	


    public $headers;



    public function __construct($headers) {
        // Container
        $this->headers = $headers;
    }



    public function url() {

        return rtrim($this->home(), '/') . $this->uri();
    }



    public function uri() {

        if(isset($_SERVER['QUERY_STRING'])) {

            if(!empty($string = $_SERVER['QUERY_STRING'])) {

                return $this->path() . '?' . $string;

            } else {

                return $this->path();
            }
        }
    }



    public function name() {
        
        return basename( $this->path() );
    }



    public function path() {
        
        return '/' . implode( '/', $this->segments() );
    }



    public function home($uri = null) {


        $protocol = 443 == $_SERVER['SERVER_PORT'] && $_SERVER['HTTPS'] != 'off' || !empty( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';


        if( $_SERVER['DOCUMENT_ROOT'] == dirname( $_SERVER['SCRIPT_FILENAME'] ) ) {

            return $protocol . $_SERVER['HTTP_HOST'] .'/'. $uri;

        } else {

            $local = null;

            if( count( array_filter( explode( '/', $_SERVER['SCRIPT_NAME'] ) ) ) > 1 ) {

                $local = ltrim( dirname( $_SERVER['SCRIPT_NAME'] ), '/' ) . '/';
            }

            return $protocol . $_SERVER['HTTP_HOST'] . '/' . $local . $uri;
        }
    }
    


	public function client() {

        if( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {

            $agent = array_slice( explode( ' ', $_SERVER['HTTP_USER_AGENT'] ), -2, 2 );


            if( strstr( $agent[0], 'Chrome' ) ) {

                return 'chrome';

            } else {

                return strtolower( explode( '/', end( $agent ) )[0] );
            }
        }

        return 'chrome';
    }




    public function pagename() {

        if( $this->path() == '/' ) {

            return 'home';
        }

        if( count( $this->segments() ) === 1 ) {

            return $this->segments(0);

        } else {

            return basename( $this->path() );
        }
    }



    public function segments( $index = null ) {

        $seg = [];

 
        if( isset( $_SERVER['REDIRECT_URL'] ) ) {

            $url = $_SERVER['REDIRECT_URL'];

        } else {

            $url = parse_url( $_SERVER['REQUEST_URI'] );
            $url = ltrim( rtrim( $url['path'], '/' ), '/' );
        }

        $dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
        $seg = explode( basename( $dir ), $url );
        $seg = explode( '/', ltrim( end( $seg ), '/' ) );


        // $segment = array_values( array_filter( $segment, function( $value ) {

        //     return $value !== '';

        // }));



        if( isset( $index ) ) {

            return isset( $seg[$index] ) ? $seg[$index] : $seg[0];
        
        } else {

            return array_filter( $seg );
        }
    }



}