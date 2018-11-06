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


/**
* Response
*/
class Response {


	public $headers;



    public function __construct($args) {

        // Container
        $this->headers = $args['headers'];
    }



	public function write($headers, $body) {

		$this->withHead($headers);

		return $this->withBody($body);
	}



	public function withBody($data) {

        ob_start();

        echo $data;

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
	}



	public function withHead($headers) {

		// Set headers
		foreach( $headers as $name => $value ) {

			$this->headers->set($name, $value);
		}
	}



	public function withJSON($data) {

		return $this->write(['Content-Type' => 'application/json charset=utf-8'], json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
	}



	public function withStatus($code) {

		$this->headers->set('Status', $code);
	}



	public function withRedirect($value) {

		$this->headers->set('Location', $value);
	}


}