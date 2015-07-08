<?php
class ControllerCommonForgotten extends Controller {
	private $error = array();

	public function index() {
		if ($this->user->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$this->response->redirect($this->url->link('common/dashboard', '', 'SSL'));
		}

		if (!$this->config->get('config_password')) {
			$this->response->redirect($this->url->link('common/login', '', 'SSL'));
		}

		$this->data = $this->load->language('common/forgotten');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('user/user');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->data = $this->load->language('mail/forgotten');

			$code = sha1(uniqid(mt_rand(), true));

			$this->model_user_user->editCode($this->request->post['email'], $code);

			$subject = sprintf($this->data['text_subject'], $this->config->get('config_name'));

			$message  = sprintf($this->data['text_greeting'], $this->config->get('config_name')) . "\n\n";
			$message .= $this->data['text_change'] . "\n\n";
			$message .= $this->url->link('common/reset', 'code=' . $code, 'SSL') . "\n\n";
			$message .= sprintf($this->data['text_ip'], $this->request->server['REMOTE_ADDR']) . "\n\n";

			$mail = new Mail($this->config->get('config_mail'));
			$mail->setTo($this->request->post('email', ''));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject($subject);
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('common/login', '', 'SSL'));
		}

		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->error['warning']: '';
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', '', 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/forgetten', 'token=' .'', 'SSL')	// Link URL
						));
		
		$this->data['action'] = $this->url->link('common/forgotten', '', 'SSL');

		$this->data['cancel'] = $this->url->link('common/login', '', 'SSL');

		$this->data['email'] = $this->request->post('email', '');
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('common/forgotten.tpl', $this->data));
	}

	protected function validate() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->data['error_email'];
		} elseif (!$this->model_user_user->getTotalUsersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->data['error_email'];
		}

		return !$this->error;
	}
}
