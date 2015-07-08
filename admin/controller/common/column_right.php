<?php
class ControllerCommonColumnRight extends Controller {
	public function index() {
		if (isset($this->request->get['token']) && isset($this->session->data['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			
			$this->data['blog_stats'] = $this->load->controller('common/blog_stats');
			$this->data['stats'] = $this->load->controller('common/stats');
			$this->data['time'] = $this->load->controller('common/time');

						
			return $this->load->view('common/column_right.tpl', $this->data);
		}
	}
	
	public function getDateTime(){
		$this->data = $this->load->language('common/column_right');
		
		$json = sprintf($this->data['text_server_date_time'], date('d/M/Y'), date('h:i'), date_default_timezone_get());
		$this->response->setOutput($json);
	}
}