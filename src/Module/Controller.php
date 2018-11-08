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

namespace Craftsware\Module;



/**
 * Controller
 *
 */
class Controller extends \Craftsware\Module {


    /**
     * Get Variable
     * @param $arg1 mixed
     * @return mixed
     */
	public function add($arg1, $arg2 = null) {

        if(isset($arg2)) {

			$this->set("var[$arg1]", $arg2);

            return $arg2;

        } else {

        	if(is_array($arg1)) {

	            foreach($arg1 as $name => $value) {

	                $this->set("var[$name]", $value);
	            }

                return $arg1;
        	}
		}
	}
}
