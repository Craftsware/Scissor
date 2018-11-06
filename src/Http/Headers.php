<?php

/**
 * Scissor (http://craftsware.net/scissor)
 *
 * @link      https://github.com/craftsware/scissor
 * @license   https://github.com/craftsware/scissor/LICENSE.md (MIT License)
 * @copyright Copyright (c) 2017 Ruel Mindo
 *
 */

namespace Craftsware\Http;



class Headers {


    protected $data = [];



    public function set($name, $value) {

        $this->data[$name] = $value;
    }



    public function get($name = null) {


        if(isset($this->data[$name])) {
            
            return $this->data[$name];
        
        } else {

            if(function_exists('apache_request_headers')) {

                $headers = apache_request_headers();

                if(isset($headers[$name])) {

                    return $headers[$name];
                }
            }
        }

        return $this->data;
    }

}
