<?php
class ControllerCommonTime extends Controller {
	public function index() {
		$this->data['token'] = $this->session->data['token'];		

		return $this->load->view('common/time.tpl', $this->data);
	}
}