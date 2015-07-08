<?php
class ControllerAffiliateEdit extends Controller {
	private $error = array();

	public function index() {
		if (!$this->affiliate->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/edit', '', 'SSL');

			$this->response->redirect($this->url->link('affiliate/login', '', 'SSL'));
		}

		$this->data = $this->load->language('affiliate/edit');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('affiliate/affiliate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_affiliate_affiliate->editAffiliate($this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			// Add to activity log
			$this->load->model('affiliate/activity');

			$activity_data = array(
				'affiliate_id' => $this->affiliate->getId(),
				'name'         => $this->affiliate->getFirstName() . ' ' . $this->affiliate->getLastName()
			);

			$this->model_affiliate_activity->addActivity('edit', $activity_data);

			$this->response->redirect($this->url->link('affiliate/account', '', 'SSL'));
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),	// Link URL
							$this->data['text_edit'],
							$this->url->link('affiliate/edit', '', 'SSL')
						));
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_firstname'] =  (isset($this->error['firstname'])?$this->error['firstname']:'');

		$this->data['error_lastname'] =  (isset($this->error['lastname'])?$this->error['lastname']:'');

		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');

		$this->data['error_telephone'] =  (isset($this->error['telephone'])?$this->error['telephone']:'');

		$this->data['error_address_1'] =  (isset($this->error['address_1'])?$this->error['address_1']:'');
		
		$this->data['error_city'] =  (isset($this->error['city'])?$this->error['city']:'');

		$this->data['error_postcode'] =  (isset($this->error['postcode'])?$this->error['postcode']:'');

		$this->data['error_country'] =  (isset($this->error['country'])?$this->error['country']:'');

		$this->data['error_zone'] =  (isset($this->error['zone'])?$this->error['zone']:'');

		$this->data['action'] = $this->url->link('affiliate/edit', '', 'SSL');

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$affiliate_info = $this->model_affiliate_affiliate->getAffiliate($this->affiliate->getId());
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['firstname'] = $affiliate_info['firstname'];
		} else {
			$this->data['firstname'] = $this->request->post['firstname'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['lastname'] = $affiliate_info['lastname'];
		} else {
			$this->data['lastname'] = $this->request->post['lastname'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['email'] = $affiliate_info['email'];
		} else {
			$this->data['email'] = $this->request->post['email'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['telephone'] = $affiliate_info['telephone'];
		} else {
			$this->data['telephone'] = $this->request->post['telephone'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['fax'] = $affiliate_info['fax'];
		} else {
			$this->data['fax'] = $this->request->post['fax'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['company'] = $affiliate_info['company'];
		} else {
			$this->data['company'] = $this->request->post['company'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['website'] = $affiliate_info['website'];
		} else {
			$this->data['website'] = $this->request->post['website'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['address_1'] = $affiliate_info['address_1'];
		} else {
			$this->data['address_1'] = $this->request->post['address_1'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['address_2'] = $affiliate_info['address_2'];
		} else {
			$this->data['address_2'] = $this->request->post['address_2'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['postcode'] = $affiliate_info['postcode'];
		} else {
			$this->data['postcode'] = $this->request->post['postcode'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['city'] = $affiliate_info['city'];
		} else {
			$this->data['city'] = $this->request->post['city'];
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['country_id'] = $affiliate_info['country_id'];
		} else {
			$this->data['country_id'] = $this->request->post('country_id',$this->config->get('config_country_id'));
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['zone_id'] = $affiliate_info['zone_id'];
		} else {
			$this->data['zone_id'] = $this->request->post['zone_id'];
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->data['back'] = $this->url->link('affiliate/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/edit.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/affiliate/edit.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/affiliate/edit.tpl', $this->data));
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

		if (($this->affiliate->getEmail() != $this->request->post['email']) && $this->model_affiliate_affiliate->getTotalAffiliatesByEmail($this->request->post['email'])) {
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