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


/**
 * App
 *
 */
class Module {



    /**
    * Constructor
    *
    * @param string $args
    */
    public function __construct() {

        $args = [
            'uri' => '',
            'url' => '',
            'name' => '',
            'path' => '',
            'home' => '',
            'path' => '',
            'client' => '',
            'segments' => ''
        ];

        // Initialize variable
        foreach(array_keys($args) as $value) {

            if(method_exists($this->request, $value)) {

                $this->set("var[$value]", $this->request->{$value}());
            }
        }
    }




    /**
    * Get data as property
    *
    * @param string $name
    * @return mixed
    */
    public function __get($name) {

        return $this->get($name);
    }



    /**
    * Set data to the storage and return its value
    *
    * @return array
    */
    public function set() {

        return $this->collection(func_get_args());
    }


    /**
     * Get Variable
     * @param $name string
     * @return mixed
     */
    public function var($name) {

        $var = $this->get('var');

        if(isset($var[$name])) {
            
            return $var[$name];
        }
    }



    /**
    * Get data
    *
    * @param  mixed $value
    * @return array
    */
    public function get($value = null) {

        $data = $this->collection();

        if(is_array($value)) {

            $args = [];

            foreach($value as $name) {

                if(isset($data[$name])) {

                    $args[$name] = $data[$name];
                }
            }

            return $args;

        } else {

            if(isset($data[$value])) {
                
                return $data[$value];

            } else {

                // Get Variables
                if(isset($data['var'][$value])) {

                    return $data['var'][$value];
                }

                // Get library
                if(isset($data['lib'][$value])) {

                    return $data['lib'][$value];
                }
            }
        }
    }


    /**
     * View Content
     *
     * @param string $name
     * @return object
     */
    public function view($name) {

        return (new Module\View($this))->view($name);
    }



    /**
     * Get Config
     *
     * @param string $name
     * @return mixed
     */
    public function config($name) {

        // Set Config
        if(file_exists($conf = realpath('../App/Config/'. ucfirst($name) .'.php'))) {

            return require($conf);
        }
    }



    /**
    * Get model from it's module and other modules
    *
    * @param string $name
    *
    */
    public function model($name, $inject = null) {

        // Model instance
        $instance = function($path) use ($inject) {

            if(class_exists($namespace = 'App\\Modules\\' . str_replace('/', '\\', $path))) {

                return new $namespace($inject);
            }
        };


        // Split module and model name
        if(strstr($name, '/')) {

            $name = explode('/', $name);

            return $instance($name[0] . '/Models/' . $name[1]);
        }


        if(isset($this->module['name'])) {

            if($model = $instance($this->module['name'] . '/Models/'. $name)) {

                return $model;
            }
        }

        // Use module name if model name is not defined
        return $instance($name . '/Models/' . $name);
    }




    /**
    * Collection
    *
    * @param array $args
    * @return object $collection
    *
    */
    private function collection($args = null) {

        static $data = [];


        if(isset($args[1])) {
            
            if(preg_match('/(.*)\[(.*)\]/', $args[0], $matches)) {

                $data[$matches[1]][$matches[2]] = $args[1];

            } else {

                $data[$args[0]] = $args[1];

                return $args[1];
            }
        }

        return $data;
    }
}