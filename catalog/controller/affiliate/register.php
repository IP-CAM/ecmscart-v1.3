<?php
class ControllerAffiliateRegister extends Controller {
	private $error = array();

	public function index() {
		if ($this->affiliate->isLogged()) {
			$this->response->redirect($this->url->link('affiliate/account', '', 'SSL'));
		}

		$this->data = $this->load->language('affiliate/register');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('affiliate/affiliate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$affiliate_id = $this->model_affiliate_affiliate->addAffiliate($this->request->post);

			// Clear any previous login attempts in not registered.
			$this->model_affiliate_affiliate->deleteLoginAttempts($this->request->post['email']);
			
			$this->affiliate->login($this->request->post['email'], $this->request->post['password']);

			// Add to activity log
			$this->load->model('affiliate/activity');

			$activity_data = array(
				'affiliate_id' => $affiliate_id,
				'name'         => $this->request->post['firstname'] . ' ' . $this->request->post['lastname']
			);

			$this->model_affiliate_activity->addActivity('register', $activity_data);

			$this->response->redirect($this->url->link('affiliate/success'));
		}

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),	// Link URL
							$this->data['text_register'],
							$this->url->link('affiliate/register', '', 'SSL')
						));
				
		$this->data['text_account_already'] = sprintf($this->data['text_account_already'], $this->url->link('affiliate/login', '', 'SSL'));
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_firstname'] =  (isset($this->error['firstname'])?$this->error['firstname']:'');

		$this->data['error_lastname'] =  (isset($this->error['lastname'])?$this->error['lastname']:'');

		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');

		$this->data['error_telephone'] =  (isset($this->error['telephone'])?$this->error['telephone']:'');

		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');

		$this->data['error_confirm'] =  (isset($this->error['confirm'])?$this->error['confirm']:'');

		$this->data['error_address_1'] =  (isset($this->error['address_1'])?$this->error['address_1']:'');

		$this->data['error_city'] =  (isset($this->error['city'])?$this->error['city']:'');

		$this->data['error_postcode'] =  (isset($this->error['postcode'])?$this->error['postcode']:'');

		$this->data['error_country'] =  (isset($this->error['country'])?$this->error['country']:'');

		$this->data['error_zone'] =  (isset($this->error['zone'])?$this->error['zone']:'');

		$this->data['action'] = $this->url->link('affiliate/register', '', 'SSL');
		
		$this->data['firstname'] = $this->request->post('firstname','');
		
		$this->data['lastname'] = $this->request->post('lastname','');
		
		$this->data['email'] = $this->request->post('email','');
		
		$this->data['telephone'] = $this->request->post('telephone','');

		$this->data['fax'] = $this->request->post('fax','');

		$this->data['company'] = $this->request->post('company','');
		
		$this->data['website'] = $this->request->post('website','');
		
		$this->data['address_1'] = $this->request->post('address_1','');
		
		$this->data['address_2'] = $this->request->post('address_2','');
		
		$this->data['postcode'] = $this->request->post('postcode','');
		
		$this->data['city'] = $this->request->post('city','');
		
		$this->data['country_id'] = $this->request->post('country_id',$this->config->get('config_country_id'));
		
		$this->data['zone_id'] = $this->request->post('zone_id','');
		
		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();
		
		$this->data['tax'] = $this->request->post('tax','');

		$this->data['payment'] = $this->request->post('payment','cheque');

		$this->data['cheque'] = $this->request->post('cheque','');

		$this->data['paypal'] = $this->request->post('paypal','');

		$this->data['bank_name'] = $this->request->post('bank_name','');

		$this->data['bank_branch_number'] = $this->request->post('bank_branch_number','');

		$this->data['bank_swift_code'] = $this->request->post('bank_swift_code','');

		$this->data['bank_account_name'] = $this->request->post('bank_account_name','');

		$this->data['bank_account_number'] = $this->request->post('bank_account_number','');

		$this->data['password'] = $this->request->post('password','');

		$this->data['confirm'] = $this->request->post('confirm','');

		if ($this->config->get('config_affiliate_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_affiliate_id'));

			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->data['text_agree'], $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_affiliate_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}
		
		$this->data['agree'] = $this->request->post('agree',false);	
		
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/register.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/affiliate/register.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/affiliate/register.tpl', $this->data));
		}
	}

	protected function validate() {
		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->data['error_firstname'];
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->data['error_lastname'];
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->data['error_email'];
		}

		if ($this->model_affiliate_affiliate->getTotalAffiliatesByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->data['error_exists'];
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->data['error_telephone'];
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

		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->data['error_password'];
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->data['error_confirm'];
		}

		if ($this->config->get('config_affiliate_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_affiliate_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->data['error_agree'], $information_info['title']);
			}
		}

		return !$this->error;
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}