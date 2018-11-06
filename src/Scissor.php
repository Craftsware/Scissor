<?php

/**
 * Scissor (http://craftsware.net/scissor)
 *
 * @link      https://github.com/craftsware/scissor
 * @license   https://github.com/craftsware/scissor/LICENSE.md (MIT License)
 * @copyright Copyright (c) 2017 Ruel Mindo
 *
 */

namespace Craftsware;


use Craftsware\Http\Router;
use Craftsware\Http\Headers;
use Craftsware\Http\Request;
use Craftsware\Http\Response;

/**
 * App
 *
 */
class Scissor {



    /**
    * Module
    *
    * @var object $module
    */
    protected $module;



    /**
    * Library
    *
    * @var object $collection
    */
    protected $library = [
        'Craftsware\Lib\Time',
        'Craftsware\Lib\Database'
    ];


    /**
    * Constructor
    *
    * @param string $args
    */
    public function __construct($args = []) {

        $this->module = new Module;

        $lib['headers'] = new Headers;
        $lib['request'] = new Request($lib);
        $lib['response'] = new Response($lib);

        // Library
        foreach($this->library as $namespace) {

            $name = strtolower(basename($namespace));

            if(isset($args['library']) && isset($args['library'][$name])) {

                // Handle error if library name already exist
            } else {
                    
                if(isset($args['settings'][$name])) {

                    $lib[$name] = new $namespace($args['settings'][$name]);

                } else {

                    $lib[$name] = new $namespace($args['library']);
                }
            }
        }

        // Library
        foreach($args['library'] as $name => $namespace) {

            $lib[$name] = new $namespace($lib);
        }


        $this->set('lib', $lib);
    }


    /**
    * Get Collection using magic method
    *
    * @param string $name
    * @return object $this
    */
    public function __get($name) {

        if($get = $this->get($name)) {

            return $get;

        } else {

            $this->set('module', [
                'name' => ucfirst($name),
                'path' => $this->app('Modules/' . ucfirst($name))
            ]);

            return $this;
        }
    }




    /**
    * Get application path
    *
    * @param string $name
    * @return string
    */
    public function app($name = null) {

        return realpath('../App/'. $name);
    }


    /**
    * Set data
    *
    * @param  string $name
    * @param  mixed $value
    * @return mixed
    */
    public function set($name, $value) {

        return $this->module->set($name, $value);
    }


    /**
    * Get data
    *
    * @param  mixed $value
    * @return array
    */
    public function get($value = null) {

        return $this->module->get($value);
    }



    /**
    * Run the app
    */
    public function run() {

        $router = new Router($this);

        // // Get Route
        if($route = $router->map($this->app('Routes'))) {

            if($route instanceof \Closure) {

                $body = $route->bindTo($this)($this->request, $this->response);
            }

            // Set headers
            foreach((array) $this->headers->get() as $name => $value) {

                if($name == 'Status') {

                    http_response_code($value);

                } else {

                    header(sprintf('%s: %s', $name, $value), false);
                }
            }

            echo $body;
        }
    }



    /**
     * Render
     *
     * @param string $name
     * @param array $args
     * @return string
     */
    public function render($name, $args = []) {

        if(strstr($name, '::')) {

            $name = explode('::', $name);
        
        } else {

            $name = [$name, strtolower($name)];
        }


        if($this->set('module[controller]', $name[0])) {

            $module = $this->get('module');

            if(isset($module['name'])) {

                ob_start();

                if(method_exists($instance = $this->controller($module['name'], $name[0]), $name[1])) {

                    if($ref = new \ReflectionMethod($instance, $name[1])) {

                        $ref->invokeArgs($instance, $args);
                    }
                }

                $content = ob_get_contents();

                ob_end_clean();


                return $content;
            }
        }
    }



    /**
    * Controller
    *
    * @param string $module
    * @param object $injetion
    * @param array $controller
    * @return object $namespace
    *
    */
    private function controller($module, $controller, $injetion = null) {

        // Check if exist
        if(class_exists($namespace = 'App\\Modules\\' . ucfirst($module) . '\\Controllers\\' . $controller)) {

            return (new $namespace($injetion));
        }
    }
}