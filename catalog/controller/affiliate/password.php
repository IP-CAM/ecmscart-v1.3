<?php
class ControllerAffiliatePassword extends Controller {
	private $error = array();

	public function index() {
		if (!$this->affiliate->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/password', '', 'SSL');

			$this->response->redirect($this->url->link('affiliate/login', '', 'SSL'));
		}

		$this->data = $this->load->language('affiliate/password');

		$this->document->setTitle($this->data['heading_title']);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->load->model('affiliate/affiliate');

			$this->model_affiliate_affiliate->editPassword($this->affiliate->getEmail(), $this->request->post['password']);

			$this->session->data['success'] = $this->data['text_success'];

			// Add to activity log
			$this->load->model('affiliate/activity');

			$activity_data = array(
				'affiliate_id' => $this->affiliate->getId(),
				'name'         => $this->affiliate->getFirstName() . ' ' . $this->affiliate->getLastName()
			);

			$this->model_affiliate_activity->addActivity('password', $activity_data);

			$this->response->redirect($this->url->link('affiliate/account', '', 'SSL'));
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('affiliate/password', '', 'SSL')
						));
		
		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']:'');
		
		$this->data['error_confirm'] =  (isset($this->error['confirm'])? $this->error['confirm']:'');

		$data['action'] = $this->url->link('affiliate/password', '', 'SSL');
		
		$data['password'] = $this->request->post('password','');
		
		$data['confirm'] = $this->request->post('confirm','');
		
		$data['back'] = $this->url->link('affiliate/account', '', 'SSL');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/password.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/affiliate/password.tpl', $data));
		} else {
			$this->response->setOutput($this->load->view('default/template/affiliate/password.tpl', $data));
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