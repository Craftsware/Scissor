<?php

/**
 * Scissor (http://craftsware.net/scissor)
 *
 * @link      https://github.com/craftsware/scissor
 * @license   https://github.com/craftsware/scissor/LICENSE.md (MIT License)
 * @copyright Copyright (c) 2017 Ruel Mindo
 *
 */

namespace Craftsware\Module;



/**
 * View
 *
 */
class View {



	/**
	* View
	*
	* @var array
	*/
	public $view = [];


	/**
	* Module
	*
	* @var object
	*/
	public $module;



	/**
	* Constructor
	* @param string $name
	*/
	public function __construct($module) {

		$this->module = $module;
	}



	/**
	* Call method,
	* check if method is being called from controller
	* and store it with the value.
	*
	* @param string $name
	* @param mixed $value
	* @return object
	*
	*/
	public function __call($key, $value) {

		// Set View
		if(isset($value[0])) {
			
			$this->view[$key] = $value[0];
		}

		return $this;
	}



	/**
	* Get data
	* @param string $name
	*/
	public function __get($name) {

		return $this->get($name);
	}




	/**
	* Get Data
	* @param string $name
	*/
	public function get($name) {

		return $this->module->get($name);
	}



	/**
	* Set View
	* @param string $name
	*
	*/
	public function view($name) {
		// Set view
		$this->view['name'] = $name;

		return $this;
	}



	/**
	* Execute the last method
	* @param int $last
	*
	*/
	private function last($last = null) {

		if($last) {

			$this->load($this->view);
		}
	}



	/**
	* Prepare the data
	* path for the requested view
	*
	* @param array $view
	* @param array $data
	*
	*/
	private function data($view) {


		$data['var'] = $this->module->get('var');

		if(isset($view['with']) ) {

			foreach((array) $view['with'] as $name => $value) {

				$data['var'][$name] = $value;
			}
		}

		$data['file'] = $this->getPath($view, $this->module->get('module'));


		return $data;
	}



	/**
	* View the file to the controller
	*
	* @param array $view
	*
	*/
	private function load($view) {

		$data = $this->data($view);

		if(file_exists($data['file'])) {

			extract($data['var']);

			require $data['file'];
		}
	}



	/**
	* File
	*/
	public function chunk($name, $with = null) {

		$this->load(['name' => $name, 'with' => $with]);
	}



	/**
	* Get file path for the requested view name
	*
	* @param array $view
	* @param array $mod
	* @return string
	*/
	private function getPath($view, $module) {


		if(isset($module)) {

			if(isset($view['from'])) {

				return realpath(dirname($module['path']) . '/' . $view['from'] . '/Views/' . $view['name'] . '.php');

			} else {

				return realpath($module['path'] . '/Views/' . $view['name'] . '.php');
			}
		}
	}



	/**
	 * Check the last method if its being called
	 *
	 */
	public function __destruct() {

		$keys = array_keys($this->view);

		foreach($keys as $i => $name) {

			$this->last(count($keys) - 1 == $i);
		}
	}


}
