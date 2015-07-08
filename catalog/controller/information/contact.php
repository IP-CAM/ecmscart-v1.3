<?php
class ControllerInformationContact extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('information/contact');

		$this->document->setTitle($this->data['heading_title']);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			unset($this->session->data['captcha']);

			$mail = new Mail($this->config->get('config_mail'));
			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->request->post['email']);
			$mail->setSender($this->request->post['name']);
			$mail->setSubject(sprintf($this->data['email_subject'], $this->request->post['name']));
			$mail->setText(strip_tags($this->request->post['enquiry']));
			$mail->send();

			$this->response->redirect($this->url->link('information/contact/success'));
		}

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('information/contact')	// Link URL
						));

		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']:'');

		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');

		$this->data['error_enquiry'] =  (isset($this->error['enquiry'])?$this->error['enquiry']:'');
		
		$this->data['error_captcha'] =  (isset($this->error['captcha'])?$this->error['captcha']:'');

		$this->data['action'] = $this->url->link('information/contact');

		$this->load->model('tool/image');

		if ($this->config->get('config_image')) {
			$this->data['image'] = $this->model_tool_image->resize($this->config->get('config_image'), $this->config->get('config_image_location_width'), $this->config->get('config_image_location_height'));
		} else {
			$this->data['image'] = false;
		}

		$this->data['store'] = $this->config->get('config_name');
		$this->data['address'] = nl2br($this->config->get('config_address'));
		$this->data['geocode'] = $this->config->get('config_geocode');
		$this->data['telephone'] = $this->config->get('config_telephone');
		$this->data['fax'] = $this->config->get('config_fax');
		$this->data['open'] = nl2br($this->config->get('config_open'));
		$this->data['comment'] = $this->config->get('config_comment');

		$this->data['locations'] = array();

		$this->load->model('localisation/location');

		foreach((array)$this->config->get('config_location') as $location_id) {
			$location_info = $this->model_localisation_location->getLocation($location_id);

			if ($location_info) {
				if ($location_info['image']) {
					$image = $this->model_tool_image->resize($location_info['image'], $this->config->get('config_image_location_width'), $this->config->get('config_image_location_height'));
				} else {
					$image = false;
				}

				$this->data['locations'][] = array(
					'location_id' => $location_info['location_id'],
					'name'        => $location_info['name'],
					'address'     => nl2br($location_info['address']),
					'geocode'     => $location_info['geocode'],
					'telephone'   => $location_info['telephone'],
					'fax'         => $location_info['fax'],
					'image'       => $image,
					'open'        => nl2br($location_info['open']),
					'comment'     => $location_info['comment']
				);
			}
		}
		
		$this->data['name'] =  $this->request->post('name',$this->customer->getFirstName());

		$this->data['email'] =  $this->request->post('email',$this->customer->getEmail());

		$this->data['enquiry'] =  $this->request->post('enquiry','');

		$this->data['captcha'] =  $this->request->post('captcha','');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/contact.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/information/contact.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/information/contact.tpl', $this->data));
		}
	}

	public function success() {
		$this->data = $this->load->language('information/contact');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							
							$this->data['heading_title'],	// Text to display link
							$this->url->link('information/contact')	// Link URL
						));
						
		$this->data['continue'] = $this->url->link('common/home');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/success.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/common/success.tpl', $this->data));
		}
	}

	protected function validate() {
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->data['error_name'];
		}

		if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->data['error_email'];
		}

		if ((utf8_strlen($this->request->post['enquiry']) < 10) || (utf8_strlen($this->request->post['enquiry']) > 3000)) {
			$this->error['enquiry'] = $this->data['error_enquiry'];
		}

		if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
			$this->error['captcha'] = $this->data['error_captcha'];
		}

		return !$this->error;
	}
}
