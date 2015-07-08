<?php
class ControllerCommonSearch extends Controller {
	public function index() {
		$this->data = $this->load->language('common/search');

		$this->data['text_search'] = $this->data['text_search'];

		$this->data['search'] = $this->request->get('search','');
		

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/search.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/common/search.tpl', $this->data);
		} else {
			return $this->load->view('default/template/common/search.tpl', $this->data);
		}
	}
}