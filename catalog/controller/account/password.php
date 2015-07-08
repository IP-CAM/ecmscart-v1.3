<?php
class ControllerAccountPassword extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/password', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->load->language('account/password');

		$this->document->setTitle($this->data['heading_title']);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->load->model('account/customer');

			$this->model_account_customer->editPassword($this->customer->getEmail(), $this->request->post['password']);

			$this->session->data['success'] = $this->data['text_success'];

			// Add to activity log
			$this->load->model('account/activity');

			$activity_data = array(
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			);

			$this->model_account_activity->addActivity('password', $activity_data);

			$this->response->redirect($this->url->link('account/account', '', 'SSL'));
		}
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('account/password', '', 'SSL')
						));
		
		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');
		
		$this->data['error_confirm'] =  (isset($this->error['confirm'])?$this->error['confirm']:'');
		
		$this->data['action'] = $this->url->link('account/password', '', 'SSL');

		$this->data['password'] =  $this->request->post('password','');
	
		$this->data['confirm'] =  $this->request->post('confirm','');
		
		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/password.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/password.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/password.tpl', $this->data));
		}
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->data['error_password'];
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->data['error_confirm'];
		}

		return !$this->error;
	}
}