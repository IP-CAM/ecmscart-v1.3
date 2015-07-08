<?php
class ControllerModuleGoogleHangouts extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('module/google_hangouts');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('google_hangouts', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] = (isset($this->error['warning']))? $this->error['warning']: '';
		
		$this->data['error_code'] = (isset($this->error['code']))? $this->error['code']: '';

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_module'],	// Text to display link
							$this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('module/google_hangouts', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL describe above
						));		

		$this->data['action'] = $this->url->link('module/google_hangouts', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['google_hangouts_code'] = $this->request->post('google_hangouts_code', $this->config->get('google_hangouts_code'));

		$this->data['google_hangouts_status'] = $this->request->post('google_hangouts_status', $this->config->get('google_hangouts_status'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/google_hangouts.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/google_hangouts')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['google_hangouts_code']) {
			$this->error['code'] = $this->data['error_code'];
		}

		return !$this->error;
	}
}