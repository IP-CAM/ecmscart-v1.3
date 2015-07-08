<?php
class Request {
    public $get = array();
    public $post = array();
    public $cookie = array();
    public $files = array();
    public $server = array();
    private $env = array(); 
    //private $request = array();  //

    public function __construct() {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->request = $_REQUEST;
        $this->cookie = $_COOKIE;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->env = $_ENV;
        
    	unset($_GET);
   		unset($_POST);        
    //    unset($_COOKIE);
    //    unset($_FILES);
    //    unset($_SERVER);
    //    unset($_ENV); 
    

    }
    
        public function get($value, $defaultvalue = null, $clean = false) {
            return $this->getValue($this->get, $value, $defaultvalue, $clean);
        }
        
        public function request($value, $defaultvalue = null, $clean = false) {
            return $this->getValue($this->request, $value, $defaultvalue, $clean);
        }        
        
        public function post($value, $defaultvalue = null, $clean = false) {
            return $this->getValue($this->post, $value, $defaultvalue, $clean);
        }
        
        public function cookie($value, $defaultvalue = null, $clean = false) {
            return $this->getValue($this->cookie, $value, $defaultvalue, $clean);
        }
        
        public function files($value, $defaultvalue = null, $clean = false) {
            return $this->getValue($this->files, $value, $defaultvalue, $clean);
        }
        
        public function server($value, $defaultvalue = null, $clean = false) {
            return $this->getValue($this->server, $value, $defaultvalue, $clean);
        }
        
        public function env($value, $defaultvalue = null, $clean = false) {
            return $this->getValue($this->env, $value, $defaultvalue, $clean);
        }

    private function getValue($array, $value, $defaultvalue, $clean) {
			if (isset($defaultvalue)) {
                $data = $defaultvalue;
            }else {
				$data = NULL;
			}
			
            if (isset($array[$value])) {
                $data = $array[$value];
            }
			
            if ($clean) {
                $data = $this->clean($data);
            }
        return $data;
    } 

    public function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                unset($data[$key]);

                $data[$this->clean($key)] = $this->clean($value);
            }
        } else {
            $data = htmlspecialchars($data, ENT_COMPAT, 'UTF-8');
        }

        return $data;
    }
	
	public function getUrl($data){
		$url = '';
		foreach($data as $key => $value){
			if($value == 'encode' && isset($this->get[$key])) {
				$url .='&'. $key .'='. urlencode(html_entity_decode($this->get[$key], ENT_QUOTES, 'UTF-8'));				
			}elseif(isset($this->get[$value])) {
				$url .='&'. $value .'='. $this->get[$value];
			}else {
				$url .= '';
			}
		}
		return $url;
	}
}
