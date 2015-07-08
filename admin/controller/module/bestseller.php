<?php
class ControllerModuleBestSeller extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('module/bestseller');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_extension_module->addModule('bestseller', $this->request->post);
			} else {
				$this->model_extension_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->cache->delete('product');

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] = (isset($this->error['warning']))? $this->error['warning']: '';
		
		$this->data['error_name'] = (isset($this->error['name']))? $this->error['name']: '';
		
		$this->data['error_width'] = (isset($this->error['width']))? $this->error['width']: '';
		
		$this->data['error_height'] = (isset($this->error['height']))? $this->error['height']: '';
		
		$module_url = '';
		if (!isset($this->request->get['module_id'])) {// dyanamic adding module url for module_id
			$module_url = $this->url->link('module/bestseller', 'token=' . $this->session->data['token'], 'SSL');
			
		} else {
			$module_url = $this->url->link('module/bestseller', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');			
		}

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_module'],	// Text to display link
							$this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],	// Text to display link
							$module_url,	// Link URL describe above
						));		

		if (!isset($this->request->get['module_id'])) {
			$this->data['action'] = $this->url->link('module/bestseller', 'token=' . $this->session->data['token'], 'SSL');
		} else {
			$this->data['action'] = $this->url->link('module/bestseller', 'token=' . $this->session->data['token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
		}	
			
		if (!empty($module_info) && !$this->error) {
			$this->data['name'] = $module_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}
				
		if (!empty($module_info) && !$this->error) {
			$this->data['limit'] = $module_info['limit'];
		} else {
			$this->data['limit'] = $this->request->post('limit', 5);
		}	
				
		if (!empty($module_info) && !$this->error) {
			$this->data['width'] = $module_info['width'];
		} else {
			$this->data['width'] = $this->request->post('width', 200);
		}	
			
		if (!empty($module_info) && !$this->error) {
			$this->data['height'] = $module_info['height'];
		} else {
			$this->data['height'] = $this->request->post('height', 200);
		}
				
		if (!empty($module_info) && !$this->error) {
			$this->data['status'] = $module_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', '');
		}
			
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/bestseller.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/bestseller')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
		
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->data['error_name'];
		}
		
		if (!$this->request->post['width']) {
			$this->error['width'] = $this->data['error_width'];
		}
		
		if (!$this->request->post['height']) {
			$this->error['height'] = $this->data['error_height'];
		}	

		return !$this->error;
	}
}