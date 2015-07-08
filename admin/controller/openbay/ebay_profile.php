<?php
class ControllerOpenbayEbayProfile extends Controller {
	private $error = array();

	public function profileAll() {
		$this->data = $this->load->language('openbay/ebay_profile');

		$this->load->model('openbay/ebay_profile');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';

		if (isset($this->session->data['error']))			
			unset($this->session->data['error']);

		$this->data['success'] = isset($this->session->data['success']) ? $this->session->data['success']: '';

		if (isset($this->session->data['success']))			
			unset($this->session->data['success']);

		$this->data['save'] = $this->url->link('openbay/ebay_profile/save', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['types'] = $this->model_openbay_ebay_profile->getTypes();
		$this->data['profiles'] = $this->model_openbay_ebay_profile->getAll();
		$this->data['token'] = $this->session->data['token'];

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay_profile/profileAll', 'token=' . $this->session->data['token'], 'SSL'),
						));
						
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_profile_list.tpl', $this->data));

	}

	public function save() {
		$this->data = $this->load->language('openbay/ebay_profile');

		$this->load->model('openbay/ebay_profile');

		$this->data['btn_save'] = $this->url->link('openbay/ebay_profile/save', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('openbay/ebay_profile/profileall', 'token=' . $this->session->data['token'], 'SSL');

		if (!isset($this->request->post['step1'])) {
			if ($this->request->post && $this->profileValidate()) {
				if(isset($this->request->post['ebay_profile_id']) && $this->request->post['ebay_profile_id']){
					$this->session->data['success'] = $this->data['text_updated'];
					$this->model_openbay_ebay_profile->edit($this->request->post['ebay_profile_id'], $this->request->post);
				}else {
					$this->session->data['success'] = $this->data['text_added'];
					$this->model_openbay_ebay_profile->add($this->request->post);
				}

				$this->response->redirect($this->url->link('openbay/ebay_profile/ProfileAll&token=' . $this->session->data['token'], 'SSL'));
			}
		}

		$this->profileForm($this->data);
	}

	public function delete() {
		$this->data = $this->load->model('openbay/ebay_profile');

		if (!$this->user->hasPermission('modify', 'openbay/ebay_profile')) {
			$this->error['warning'] = $this->data['error_permission'];
		} else {
			if (isset($this->request->get['ebay_profile_id'])) {
				$this->model_openbay_ebay_profile->delete($this->request->get['ebay_profile_id']);
			}
		}

		$this->response->redirect($this->url->link('openbay/ebay_profile/profileAll&token=' . $this->session->data['token'], 'SSL'));
	}

	public function profileForm() {
		$this->load->model('openbay/ebay');
		$this->load->model('openbay/ebay_template');

		$this->data['token']                            = $this->session->data['token'];
		$this->data['shipping_international_zones']     = $this->model_openbay_ebay->getShippingLocations();
		$this->data['templates']                        = $this->model_openbay_ebay_template->getAll();
		$this->data['types']                            = $this->model_openbay_ebay_profile->getTypes();

		$setting                                  = array();
		$setting['returns']                       = $this->openbay->ebay->getSetting('returns');
		$setting['dispatch_times']                = $this->openbay->ebay->getSetting('dispatch_time_max');
		$setting['countries']                     = $this->openbay->ebay->getSetting('countries');
		$setting['shipping_types'] 				  = $this->openbay->ebay->getSetting('shipping_types');

		if (empty($setting['dispatch_times']) || empty($setting['countries']) || empty($setting['returns'])){
			$this->session->data['warning'] = $this->data['error_missing_settings'];
			$this->response->redirect($this->url->link('openbay/ebay/syncronise&token=' . $this->session->data['token'], 'SSL'));
		}

		if (is_array($setting['dispatch_times'])) {
			ksort($setting['dispatch_times']);
		}
		if (is_array($setting['countries'])) {
			ksort($setting['countries']);
		}

		$this->data['setting'] = $setting;
		
		$this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning']: '';
		
		$profile_info = array();
		if (isset($this->request->get['ebay_profile_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$profile_info = $this->model_openbay_ebay_profile->get($this->request->get['ebay_profile_id']);
			$this->data['text_manage'] = $this->data['text_edit'];
			$this->data['action'] = $this->url->link('openbay/ebay_profile/save', 'token=' . $this->session->data['token'], 'SSL');
		} else {
			$this->data['action'] = $this->url->link('openbay/ebay_profile/save', 'token=' . $this->session->data['token'], 'SSL');
			$this->data['text_manage'] = $this->data['text_add'];
		}

		$type = $this->request->post('type', $profile_info['type'],false);

		if (!array_key_exists($type, $this->data['types'])) {
			$this->session->data['error'] = $this->data['error_no_template'];

			$this->response->redirect($this->url->link('openbay/ebay_profile/profileall&token=' . $this->session->data['token']));
		}

		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay_profile/profileAll', 'token=' . $this->session->data['token'], 'SSL'),
						));

		if (!empty($profile_info) && !$this->error) {
			$this->data['default'] = $profile_info['default'];
		} else {
			$this->data['default'] = $this->request->post('default', 0);
		}

		if (!empty($profile_info) && !$this->error) {
			$this->data['name'] = $profile_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($profile_info) && !$this->error) {
			$this->data['description'] = $profile_info['description'];
		} else {
			$this->data['description'] = $this->request->post('description', '');
		}
		
		$this->data['type'] = $this->request->post('type', $profile_info['type']);

		$this->data['ebay_profile_id'] = $this->request->post('ebay_profile_id', '');

		if (!empty($profile_info) && !$this->error) {
			$this->data['data'] = $profile_info['data'];
		} else {
			$this->data['data'] = $this->request->post('data', '');
		}

		if ($type == 0) {
			$this->data['zones'] = $this->model_openbay_ebay->getShippingLocations();

			$this->data['data']['national']['calculated']['types'] = $this->model_openbay_ebay->getShippingService('national', 'calculated');
			$this->data['data']['international']['calculated']['types'] = $this->model_openbay_ebay->getShippingService('international', 'calculated');
			$this->data['data']['national']['flat']['types'] = $this->model_openbay_ebay->getShippingService('national', 'flat');
			$this->data['data']['international']['flat']['types'] = $this->model_openbay_ebay->getShippingService('international', 'flat');

			$this->data['data']['national']['calculated']['count']	= isset($this->data['data']['national']['calculated']['service_id']) ? max(array_keys($this->data['data']['national']['calculated']['service_id']))+1 : 0;
			$this->data['data']['national']['flat']['count']	= isset($this->data['data']['national']['flat']['service_id']) ? max(array_keys($this->data['data']['national']['flat']['service_id']))+1 : 0;
			$this->data['data']['international']['calculated']['count']	= isset($this->data['data']['international']['calculated']['service_id']) ? max(array_keys($this->data['data']['international']['calculated']['service_id']))+1 : 0;
			$this->data['data']['international']['flat']['count']	= isset($this->data['data']['international']['flat']['service_id']) ? max(array_keys($this->data['data']['international']['flat']['service_id']))+1 : 0;

			$payment_types = $this->model_openbay_ebay->getPaymentTypes();
			$this->data['cod_surcharge'] = 0;

			foreach($payment_types as $payment) {
				if ($payment['ebay_name'] == 'COD') {
					$this->data['cod_surcharge'] = 1;
				}
			}

			if (!isset($this->data['data']['national']['shipping_type'])) {
				$this->data['data']['national']['shipping_type'] = 'flat';
			}

			if (!isset($this->data['data']['international']['shipping_type'])) {
				$this->data['data']['international']['shipping_type'] = 'flat';
			}

			$this->data['html_national_flat']         		= $this->load->view('openbay/ebay_profile_shipping_national_flat.tpl', $this->data);
			$this->data['html_international_flat']         	= $this->load->view('openbay/ebay_profile_shipping_international_flat.tpl', $this->data);
			$this->data['html_national_calculated']         	= $this->load->view('openbay/ebay_profile_shipping_national_calculated.tpl', $this->data);
			$this->data['html_international_calculated']		= $this->load->view('openbay/ebay_profile_shipping_international_calculated.tpl', $this->data);
		}

		$this->data['cancel'] = $this->url->link('openbay/ebay_profile/profileAll', 'token=' . $this->session->data['token'], 'SSL');

		$this->document->setTitle($this->data['heading_title']);

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->data['types'][$type]['template'], $this->data));
	}

	public function profileGet() {
		$this->load->model('openbay/ebay_profile');
		$this->load->model('openbay/ebay');
		$this->data = $this->load->language('openbay/ebay_profile');

		$profile_info = $this->model_openbay_ebay_profile->get($this->request->get['ebay_profile_id']);
		//$this->data = array();

		if ($profile_info['type'] == 0) {
			$this->data['data'] = $profile_info['data'];
			$this->data['data']['national']['calculated']['types'] = $this->model_openbay_ebay->getShippingService('national', 'calculated');
			$this->data['data']['international']['calculated']['types'] = $this->model_openbay_ebay->getShippingService('international', 'calculated');
			$this->data['data']['national']['flat']['types'] = $this->model_openbay_ebay->getShippingService('national', 'flat');
			$this->data['data']['international']['flat']['types'] = $this->model_openbay_ebay->getShippingService('international', 'flat');

			$this->data['data']['national']['calculated']['count']	= isset($this->data['data']['national']['calculated']['service_id']) ? max(array_keys($this->data['data']['national']['calculated']['service_id']))+1 : 0;
			$this->data['data']['national']['flat']['count']	= isset($this->data['data']['national']['flat']['service_id']) ? max(array_keys($this->data['data']['national']['flat']['service_id']))+1 : 0;
			$this->data['data']['international']['calculated']['count']	= isset($this->data['data']['international']['calculated']['service_id']) ? max(array_keys($this->data['data']['international']['calculated']['service_id']))+1 : 0;
			$this->data['data']['international']['flat']['count']	= isset($this->data['data']['international']['flat']['service_id']) ? max(array_keys($this->data['data']['international']['flat']['service_id']))+1 : 0;

			$this->data['zones'] = $this->model_openbay_ebay->getShippingLocations();

			
			$payment_types = $this->model_openbay_ebay->getPaymentTypes();
			$this->data['cod_surcharge'] = 0;

			if (!empty($payment_types)) {
				foreach($payment_types as $payment) {
					if ($payment['ebay_name'] == 'COD') {
						$this->data['cod_surcharge'] = 1;
					}
				}
			}
			$return['national']['type'] 				= $this->data['data']['national']['shipping_type'];
			$return['international']['type'] 			= $this->data['data']['international']['shipping_type'];

			$return['national_flat_count']   			= (int)$this->data['data']['national']['flat']['count'];
			$return['national_flat']         			= $this->load->view('openbay/ebay_profile_shipping_national_flat.tpl', $this->data);

			$return['international_flat_count']   		= (int)$this->data['data']['international']['flat']['count'];
			$return['international_flat']         		= $this->load->view('openbay/ebay_profile_shipping_international_flat.tpl', $this->data);

			$return['national_calculated_count']   		= (int)$this->data['data']['national']['calculated']['count'];
			$return['national_calculated']         		= $this->load->view('openbay/ebay_profile_shipping_national_calculated.tpl', $this->data);

			$return['international_calculated_count']   = (int)$this->data['data']['international']['flat']['count'];
			$return['international_calculated']         = $this->load->view('openbay/ebay_profile_shipping_international_calculated.tpl', $this->data);

			$profile_info['html']           			= $return;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($profile_info));
	}

	private function profileValidate() {
		if (!$this->user->hasPermission('modify', 'openbay/ebay_profile')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ($this->request->post['name'] == '') {
			$this->error['name'] = $this->data['error_name'];
		}

		return !$this->error;
	}
}