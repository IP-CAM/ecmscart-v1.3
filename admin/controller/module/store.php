<?php
class ControllerModuleStore extends Controller {
	private $error = array();
	
	public function index() {
		$this->data = $this->load->language('module/store');
		
		$this->document->setTitle($this->data['heading_title']);
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('store', $this->request->post);
			
			$this->session->data['success'] = $this->data['text_success'];
			
			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['error_warning'] = (isset($this->error['warning']))? $this->error['warning']: '';
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_module'],	// Text to display link
							$this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('module/store', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL describe above
						));		
		
		$this->data['action'] = $this->url->link('module/store', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['store_admin'] = $this->request->post('store_admin', $this->config->get('store_admin'));
		
		$this->data['store_status'] = $this->request->post('store_status', $this->config->get('store_status'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('module/store.tpl', $this->data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/store')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
		
		return !$this->error;
	}
}