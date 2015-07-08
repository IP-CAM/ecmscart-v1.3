<?php
class ControllerOpenbayEbayTemplate extends Controller {
	private $error = array();

	public function listAll() {
		$this->data = $this->load->language('openbay/ebay_template');

		$this->load->model('openbay/ebay_template');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';

		if (isset($this->session->data['error']))			
			unset($this->session->data['error']);

		$this->data['success'] = isset($this->session->data['success']) ? $this->session->data['success']: '';

		if (isset($this->session->data['success']))			
			unset($this->session->data['success']);


		$this->data['save'] = $this->url->link('openbay/ebay_template/save', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['templates'] = $this->model_openbay_ebay_template->getAll();
		$this->data['token'] = $this->session->data['token'];

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay_template/listAll', 'token=' . $this->session->data['token'], 'SSL'),
						));
				
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_template_list.tpl', $this->data));
	}

	public function save() {
		$this->data = $this->load->language('openbay/ebay_template');

		$this->load->model('openbay/ebay_template');

		$this->data['page_title']   = $this->data['heading_title'];
		$this->data['btn_save']     = $this->url->link('openbay/ebay_template/save', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel']       = $this->url->link('openbay/ebay_template/listAll', 'token=' . $this->session->data['token'], 'SSL');

		if ($this->request->post && $this->templateValidate()) {
			if(isset($this->request->post['template_id']) && $this->request->post['template_id']){
				$this->session->data['success'] = $this->data['text_updated'];
				$this->model_openbay_ebay_template->edit($this->request->post['template_id'], $this->request->post);
			}else {
				$this->session->data['success'] = $this->data['text_added'];
				$this->model_openbay_ebay_template->add($this->request->post);
			}

			$this->response->redirect($this->url->link('openbay/ebay_template/listAll&token=' . $this->session->data['token'], 'SSL'));
		}

		$this->templateForm();
	}

	public function delete() {
		$this->data = $this->load->language('openbay/ebay_template');
		$this->load->model('openbay/ebay_template');

		if (!$this->user->hasPermission('modify', 'openbay/ebay_template')) {
			$this->error['warning'] = $this->data['error_permission'];
		} else {
			if (isset($this->request->get['template_id'])) {
				$this->model_openbay_ebay_template->delete($this->request->get['template_id']);

				$this->session->data['success'] = $this->data['text_deleted'];
			}
		}
		$this->response->redirect($this->url->link('openbay/ebay_template/listAll&token=' . $this->session->data['token'], 'SSL'));
	}

	public function templateForm() {
		$this->load->model('openbay/ebay');

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';
		
		if (isset($this->request->get['template_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$template_info = $this->model_openbay_ebay_template->get($this->request->get['template_id']);
			$this->data['text_manage'] = $this->data['text_edit'];
		} else {
			$this->data['text_manage'] = $this->data['text_add'];
		}

		$this->document->setTitle($this->data['page_title']);
		$this->document->addStyle('view/javascript/openbay/css/codemirror.css');
		$this->document->addScript('view/javascript/openbay/js/codemirror.js');
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							'OpenBay Pro',	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							'eBay',
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							'Templates',
							$this->url->link('openbay/ebay_template/listAll', 'token=' . $this->session->data['token'], 'SSL'),
						));

		if (!empty($template_info) && !$this->error) {
			$this->data['name'] = $template_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($template_info) && !$this->error) {
			$this->data['html'] = $template_info['html'];
		} else {
			$this->data['html'] = $this->request->post('html', '');
		}
		
		$this->data['template_id'] = $this->request->get('template_id', '');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_template_form.tpl', $this->data));
	}

	private function templateValidate() {
		if (!$this->user->hasPermission('modify', 'openbay/ebay_template')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ($this->request->post['name'] == '') {
			$this->error['warning'] = $this->data['error_name'];
		}

		return !$this->error;
	}
}