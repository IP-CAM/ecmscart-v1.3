<?php
class ControllerAffiliateForgotten extends Controller {
	private $error = array();

	public function index() {
		if ($this->affiliate->isLogged()) {
			$this->response->redirect($this->url->link('affiliate/account', '', 'SSL'));
		}

		$this->data = $this->load->language('affiliate/forgotten');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('affiliate/affiliate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->data = $this->load->language('mail/forgotten');

			$password = substr(md5(mt_rand()), 0, 10);

			$this->model_affiliate_affiliate->editPassword($this->request->post['email'], $password);

			$subject = sprintf($this->data['text_subject'], $this->config->get('config_name'));

			$message  = sprintf($this->data['text_greeting'], $this->config->get('config_name')) . "\n\n";
			$message .= $this->data['text_password'] . "\n\n";
			$message .= $password;

			$mail = new Mail($this->config->get('config_mail'));
			$mail->setTo($this->request->post['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();

			$this->session->data['success'] = $this->data['text_success'];

			// Add to activity log
			$affiliate_info = $this->model_account_affiliate->getAffiliateByEmail($this->request->post['email']);

			if ($affiliate_info) {
				$this->load->model('affiliate/activity');

				$activity_data = array(
					'affiliate_id' => $affiliate_info['affiliate_id'],
					'name'         => $affiliate_info['firstname'] . ' ' . $affiliate_info['lastname']
				);

				$this->model_affiliate_activity->addActivity('forgotten', $activity_data);
			}

			$this->response->redirect($this->url->link('affiliate/login', '', 'SSL'));
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),
							$this->data['text_forgotten'],	// Link URL
							$this->url->link('affiliate/forgotten', '', 'SSL')
						));

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['action'] = $this->url->link('affiliate/forgotten', '', 'SSL');

		$this->data['back'] = $this->url->link('affiliate/login', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/forgotten.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/affiliate/forgotten.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/affiliate/forgotten.tpl', $this->data));
		}
	}

	protected function validate() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->data['error_email'];
		} elseif (!$this->model_affiliate_affiliate->getTotalAffiliatesByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->data['error_email'];
		}

		return !$this->error;
	}
}