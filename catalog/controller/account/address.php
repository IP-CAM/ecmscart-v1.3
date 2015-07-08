<?php
class ControllerAccountAddress extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->load->language('account/address');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('account/address');

		$this->getList();
	}

	public function add() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->load->language('account/address');

		$this->document->setTitle($this->data['heading_title']);

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$this->load->model('account/address');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_account_address->addAddress($this->request->post);

			$this->session->data['success'] = $this->data['text_add'];

			// Add to activity log
			$this->load->model('account/activity');

			$activity_data = array(
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			);

			$this->model_account_activity->addActivity('address_add', $activity_data);

			$this->response->redirect($this->url->link('account/address', '', 'SSL'));
		}

		$this->getForm();
	}

	public function edit() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->load->language('account/address');

		$this->document->setTitle($this->data['heading_title']);

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$this->load->model('account/address');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_account_address->editAddress($this->request->get['address_id'], $this->request->post);

			// Default Shipping Address
			if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
				$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->request->get['address_id']);

				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
			}

			// Default Payment Address
			if (isset($this->session->data['payment_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address']['address_id'])) {
				$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->request->get['address_id']);

				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
			}

			$this->session->data['success'] = $this->data['text_edit'];

			// Add to activity log
			$this->load->model('account/activity');

			$activity_data = array(
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			);

			$this->model_account_activity->addActivity('address_edit', $activity_data);

			$this->response->redirect($this->url->link('account/address', '', 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/address', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->load->language('account/address');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('account/address');

		if (isset($this->request->get['address_id']) && $this->validateDelete()) {
			$this->model_account_address->deleteAddress($this->request->get['address_id']);

			// Default Shipping Address
			if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
				unset($this->session->data['shipping_address']);
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
			}

			// Default Payment Address
			if (isset($this->session->data['payment_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address']['address_id'])) {
				unset($this->session->data['payment_address']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);
			}

			$this->session->data['success'] = $this->data['text_delete'];

			// Add to activity log
			$this->load->model('account/activity');

			$activity_data = array(
				'customer_id' => $this->customer->getId(),
				'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
			);

			$this->model_account_activity->addActivity('address_delete', $activity_data);

			$this->response->redirect($this->url->link('account/address', '', 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('account/address', '', 'SSL')
						));
						
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
				
		$this->data['addresses'] = array();

		$results = $this->model_account_address->getAddresses();

		foreach ($results as $result) {
			if ($result['address_format']) {
				$format = $result['address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $result['firstname'],
				'lastname'  => $result['lastname'],
				'company'   => $result['company'],
				'address_1' => $result['address_1'],
				'address_2' => $result['address_2'],
				'city'      => $result['city'],
				'postcode'  => $result['postcode'],
				'zone'      => $result['zone'],
				'zone_code' => $result['zone_code'],
				'country'   => $result['country']
			);

			$this->data['addresses'][] = array(
				'address_id' => $result['address_id'],
				'address'    => str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format)))),
				'update'     => $this->url->link('account/address/edit', 'address_id=' . $result['address_id'], 'SSL'),
				'delete'     => $this->url->link('account/address/delete', 'address_id=' . $result['address_id'], 'SSL')
			);
		}

		$this->data['add'] = $this->url->link('account/address/add', '', 'SSL');
		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/address_list.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/address_list.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/address_list.tpl', $this->data));
		}
	}

	protected function getForm() {
		if (!isset($this->request->get['address_id'])) {
			$edit_url = $this->url->link('account/address/add', '', 'SSL');
		} else {
			$edit_url = $this->url->link('account/address/edit', 'address_id=' . $this->request->get['address_id'], 'SSL');
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('account/address', '', 'SSL'),
							$this->data['text_edit_address'],
							$edit_url
						));

	

		$this->data['error_firstname'] =  (isset($this->error['firstname'])?$this->error['firstname']:'');

		$this->data['error_lastname'] =  (isset($this->error['lastname'])?$this->error['lastname']:'');

		$this->data['error_address_1'] =  (isset($this->error['address_1'])?$this->error['address_1']:'');

		$this->data['error_city'] =  (isset($this->error['city'])?$this->error['city']:'');

		$this->data['error_postcode'] =  (isset($this->error['postcode'])?$this->error['postcode']:'');

		$this->data['error_country'] =  (isset($this->error['country'])?$this->error['country']:'');

		$this->data['error_zone'] =  (isset($this->error['zone'])?$this->error['zone']:'');

		$this->data['error_custom_field'] =  (isset($this->error['custom_field'])?$this->error['custom_field']:'');
		if (!isset($this->request->get['address_id'])) {
			$this->data['action'] = $this->url->link('account/address/add', '', 'SSL');
		} else {
			$this->data['action'] = $this->url->link('account/address/edit', 'address_id=' . $this->request->get['address_id'], 'SSL');
		}

		if (isset($this->request->get['address_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$address_info = $this->model_account_address->getAddress($this->request->get['address_id']);
		}

		if (!empty($address_info) && !$this->error) {
			$this->data['firstname'] = $address_info['firstname'];
		} else {
			$this->data['firstname'] = $this->request->post('firstname','');
		}
		
		if (!empty($address_info) && !$this->error) {
			$this->data['lastname'] = $address_info['lastname'];
		} else {
			$this->data['lastname'] = $this->request->post('lastname','');
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['company'] = $address_info['company'];
		} else {
			$this->data['company'] = $this->request->post('company','');
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['address_1'] = $address_info['address_1'];
		} else {
			$this->data['address_1'] = $this->request->post('address_1','');
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['address_2'] = $address_info['address_2'];
		} else {
			$this->data['address_2'] = $this->request->post('address_2','');
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['postcode'] = $address_info['postcode'];
		} else {
			$this->data['postcode'] = $this->request->post('postcode','');
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['city'] = $address_info['city'];
		} else {
			$this->data['city'] = $this->request->post('city','');
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['country_id'] = $address_info['country_id'];
		} else {
			$this->data['country_id'] = $this->request->post('country_id','');
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['zone_id'] = $address_info['zone_id'];
		} else {
			$this->data['zone_id'] = $this->request->post('zone_id','');
		}
		
		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		// Custom fields
		$this->load->model('account/custom_field');

		$this->data['custom_fields'] = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));
		
		if (!empty($address_info) && !$this->error) {
			$this->data['address_custom_field'] = $address_info['address_custom_field'];
		} else {
			$this->data['address_custom_field'] = $this->request->post('address_custom_field',array());
		}
		if (!empty($address_info) && !$this->error) {
			$this->data['default'] = $address_info['default'];
		} else {
			$this->data['default'] = $this->request->post('default',false);
		}
		
		$this->data['back'] = $this->url->link('account/address', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/address_form.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/address_form.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/address_form.tpl', $this->data));
		}
	}

	protected function validateForm() {
		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->data['error_firstname'];
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->data['error_lastname'];
		}

		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$this->error['address_1'] = $this->data['error_address_1'];
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$this->error['city'] = $this->data['error_city'];
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$this->error['postcode'] = $this->data['error_postcode'];
		}

		if ($this->request->post['country_id'] == '') {
			$this->error['country'] = $this->data['error_country'];
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
			$this->error['zone'] = $this->data['error_zone'];
		}

		// Custom field validation
		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

		foreach ($custom_fields as $custom_field) {
			if (($custom_field['location'] == 'address') && $custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
				$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->data['error_custom_field'], $custom_field['name']);
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if ($this->model_account_address->getTotalAddresses() == 1) {
			$this->error['warning'] = $this->data['error_delete'];
		}

		if ($this->customer->getAddressId() == $this->request->get['address_id']) {
			$this->error['warning'] = $this->data['error_default'];
		}

		return !$this->error;
	}
}
