<?php
class Config {
	private $data = array();

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function has($key) {
		return isset($this->data[$key]);
	}

	public function load($filename) {
		$file = DIR_CONFIG . $filename . '.php';

		if (file_exists($file)) {
			$_ = array();

			require($file);

			$this->data = array_merge($this->data, $_);
		} else {
			trigger_error('Error: Could not load config ' . $filename . '!');
			exit();
		}
	}
	public function breadcrums($breadcrums = array()){ // Change function by Manish Sharma
			$temp = array_chunk($breadcrums,2,false); // Breaking each breadcrum into 2 variable chunk array
			$links = array();
			foreach($temp as $part) { //Assign array into one array.
					$links[] = array(
							'text' 	=> $part[0],
							'href'	=> $part[1]
						);
			}
		return $links;
	}
}