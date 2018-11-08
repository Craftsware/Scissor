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
* Router
*/
class Router {



    public $routes;


    public $headers;


    public $request;


    public $response;



    public function __construct($scissor) {

        $this->headers = $scissor->get('headers');
        $this->request = $scissor->get('request');
        $this->response = $scissor->get('response');
    }



    public function __call($method, $args) {

        $this->add($method, $args);
    }



    public function add($method, $args) {

        $method = strtoupper($method);


        if( isset($args[1]) ) {

            $route = [
                'pattern' => $args[0],
                'callable' => $args[1]
            ];

                
            if( in_array($method, ['BASIC','BEARER']) ) {

                $this->routes['AUTH'][$method] = $route;

            } else {

                if( preg_match( "/^\/\{([0-9]{3})\}$/", $route['pattern'], $error ) ) {

                    $this->routes['ERROR'][$error[1]]['callable'] = $args[1];

                } else {

                    if( $method == 'ANY' ) {

                        $method = $_SERVER['REQUEST_METHOD'];

                    } else {

                        if(isset($_POST['METHOD'])) {

                            $method = $_POST['METHOD'];
                            $_SERVER['REQUEST_METHOD'] = $method;
                        }
                    }

                    $this->routes[$method][] = $route;
                }
            }
            
        }
    }



    public function map($routes) {

        // Routes
        $routes = $this->getRoutes($routes);

        // Mapping Authentication
        if( isset($routes['AUTH']) ) {

            foreach($routes['AUTH'] as $scheme => $auth ) {

                if( $unauthorized = $this->restricted($scheme, $auth) ) {

                    return $this->prepare($unauthorized);
                }
            }
        }

        // Mapping Routes
        foreach( (array) $routes[$_SERVER['REQUEST_METHOD']] as $route ) {

            // Static Route Mapping
            if( $this->request->path() === $route['pattern'] ) {

                return $this->prepare($route);

            } else {

                // Get pattern
                $pattern = $this->getPattern($route['pattern']);

                // Dynamic Route Mapping
                if( preg_match( "/^$pattern$/", $this->request->path(), $matches ) ) {

                    return $this->prepare($route);
                }
            }
        }

        // Page Not Found
        return $this->prepare($routes['ERROR'][404]);
    }



    public function basic($callable) {

        $username = null;
        $password = null;

        if( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {

            if( preg_match( '/Basic\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches ) ) {

                list( $username, $password ) = explode( ':', base64_decode( $matches[1] ), 6 );
            }

        } else {

            if( isset( $_SERVER["PHP_AUTH_USER"] ) && isset( $_SERVER["PHP_AUTH_PW"] ) ) {

                $username = $_SERVER["PHP_AUTH_USER"];
                $password = $_SERVER["PHP_AUTH_PW"];
            }
        }

        return $callable($username, $password);
    }



    
    public function bearer($callable) {

        if( $authorization = $this->headers->get('authorization') ) {

            if( preg_match( '/Bearer\s+(.*)$/i', $authorization, $matches) ) {

                return $callable($matches[1]);
            }
        }

        return $callable('');
    }



    // Get Route
    private function prepare($route) {

        if( isset($route['authorization']) ) {

            return $route['authorization'];

        } else {

            if(is_callable($route['callable'])) {

                return $route['callable'];
            }
        }
    }



    private function getRoutes($routes) {

        foreach(glob($routes . '/*.php') as $path) {

            if(file_exists($path)) {

                require $path;
            }
        }

        return $this->routes;
    }




    // Pattern
    private function getPattern($path) {

        if( preg_match( "/\((.*)\)/", $path, $pattern ) ) {

            return str_replace( '/', '\/', $path );
        }
    }




    private function restricted($scheme, $auth) {

        // Global
        if( ltrim($auth['pattern'], '/') == '*' ) {

            return $this->authorization( $scheme, $auth['callable'] );
        }

        // Static Route Mapping
        if( $this->request->segments(0) == explode('/', ltrim($auth['pattern'], '/'))[0] ) {

            return $this->authorization( $scheme, $auth['callable'] );
        }

        // Check and Get the path pattern from route
        if( $pattern = $this->getPattern( $auth['pattern'] ) ) {
            // Dynamic Route Mapping
            if( preg_match( "/^$pattern$/", rtrim( $this->request->path(), '/' ), $matches ) ) {

                return $this->authorization( $scheme, $auth['callable'] );
            }
        }
    }




    private function authorization($scheme, $callable) {


        if( $callable instanceof \Closure ) {

            $callable = $callable->bindTo( $this->container );


            $result = [];

            // Basic Authentication
            if( $scheme == 'BASIC' ) {

                $result = $this->basic($callable);
            }

            // Token Authentication
            if( $scheme == 'BEARER' ) {

                $result = $this->bearer($callable);
            }


            if( $json = json_decode($result) ) {

                if( isset($json->message) && $json->message !== true ) {

                    return ['authorization' => $result];
                }

                $this->response->withHead(['Status' => 200, 'Content-Type' => 'text/html charset=utf-8']);
            }
        }
    }


}