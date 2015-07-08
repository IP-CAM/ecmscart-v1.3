<?php
class ControllerAffiliateLogin extends Controller {
	private $error = array();

	public function index() {
		if ($this->affiliate->isLogged()) {
			$this->response->redirect($this->url->link('affiliate/account', '', 'SSL'));
		}

		$this->data = $this->load->language('affiliate/login');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('affiliate/affiliate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->request->post['email']) && isset($this->request->post['password']) && $this->validate()) {
			// Add to activity log
			$this->load->model('affiliate/activity');

			$activity_data = array(
				'affiliate_id' => $this->affiliate->getId(),
				'name'         => $this->affiliate->getFirstName() . ' ' . $this->affiliate->getLastName()
			);

			$this->model_affiliate_activity->addActivity('login', $activity_data);

			// Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
			if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
				$this->response->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
			} else {
				$this->response->redirect($this->url->link('affiliate/account', '', 'SSL'));
			}
		}

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),	// Link URL
							$this->data['text_login'],
							$this->url->link('affiliate/login', '', 'SSL')
						));

		$this->data['text_description'] = sprintf($this->data['text_description'], $this->config->get('config_name'), $this->config->get('config_name'), $this->config->get('config_affiliate_commission') . '%');
		
		$this->data['error_warning'] =  (isset($this->error['warning']) ? $this->error['warning']:'');
		
		$this->data['action'] = $this->url->link('affiliate/login', '', 'SSL');
		$this->data['register'] = $this->url->link('affiliate/register', '', 'SSL');
		$this->data['forgotten'] = $this->url->link('affiliate/forgotten', '', 'SSL');

		if (isset($this->session->data['redirect']) && !$this->error) {
			$this->data['redirect'] = $this->session->data['redirect'];

			unset($this->session->data['redirect']);
		} else {
			$this->data['redirect'] = $this->request->post['redirect'];
		}
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['email'] = $this->request->post('email','');
		
		$this->data['password'] = $this->request->post('password','');
		
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/login.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/affiliate/login.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/affiliate/login.tpl', $this->data));
		}
	}

	protected function validate() {
		// Check how many login attempts have been made.
		$login_info = $this->model_affiliate_affiliate->getLoginAttempts($this->request->post['email']);
				
		if ($login_info && ($login_info['total'] > $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->data['error_attempts'];
		}		
		
		// Check if affiliate has been approved.
		$affiliate_info = $this->model_affiliate_affiliate->getAffiliateByEmail($this->request->post['email']);

		if ($affiliate_info && !$affiliate_info['approved']) {
			$this->error['warning'] = $this->data['error_approved'];
		}
		
		if (!$this->error) {
			if (!$this->affiliate->login($this->request->post['email'], $this->request->post['password'])) {
				$this->error['warning'] = $this->data['error_login'];
			
				$this->model_affiliate_affiliate->addLoginAttempt($this->request->post['email']);
			} else {
				$this->model_affiliate_affiliate->deleteLoginAttempts($this->request->post['email']);
			}
		}
		
		return !$this->error;
	}
}