<?php
class ControllerCommonReset extends Controller {
	private $error = array();

	public function index() {
		if ($this->user->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$this->response->redirect($this->url->link('common/dashboard', '', 'SSL'));
		}

		if (!$this->config->get('config_password')) {
			$this->response->redirect($this->url->link('common/login', '', 'SSL'));
		}

		$code = $this->request->get('code', '');

		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning']: '';

		$this->load->model('user/user');

		$user_info = $this->model_user_user->getUserByCode($code);

		if ($user_info) {
			$this->data = $this->load->language('common/reset');

			$this->document->setTitle($this->data['heading_title']);

			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
				$this->model_user_user->editPassword($user_info['user_id'], $this->request->post['password']);

				$this->session->data['success'] = $this->data['text_success'];

				$this->response->redirect($this->url->link('common/login', '', 'SSL'));
			}
			
			// Breadcrumb array with common function of Text and URL 
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', '', 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/reset', '', 'SSL')	// Link URL
						));

			$this->data['error_password'] = (isset($this->error['password'])) ? $this->error['password']: '';
			
			$this->data['error_confirm'] = (isset($this->error['confirm'])) ? $this->error['confirm']: '';

			$this->data['action'] = $this->url->link('common/reset', 'code=' . $code, 'SSL');

			$this->data['cancel'] = $this->url->link('common/login', '', 'SSL');

			$this->data['password'] = $this->request->post('password', '');
			
			$this->data['confirm'] = $this->request->post('confirm', '');
			
			$this->data['header'] = $this->load->controller('common/header');
			$this->data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('common/reset.tpl', $this->data));
		} else {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSettingValue('config', 'config_password', '0');

			return new Action('common/login');
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