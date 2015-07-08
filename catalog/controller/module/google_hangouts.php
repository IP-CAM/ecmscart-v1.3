<?php
class ControllerModuleGoogleHangouts extends Controller {
	public function index() {
		$this->data = $this->load->language('module/google_hangouts');

		$this->data['heading_title'] = $this->data['heading_title'];

		if ($this->request->server['HTTPS']) {
			$this->data['code'] = str_replace('http', 'https', html_entity_decode($this->config->get('google_hangouts_code')));
		} else {
			$this->data['code'] = html_entity_decode($this->config->get('google_hangouts_code'));
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/google_hangouts.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/module/google_hangouts.tpl', $this->data);
		} else {
			return $this->load->view('default/template/module/google_hangouts.tpl', $this->data);
		}
	}
}