<?php
class ControllerCheckoutGuestShipping extends Controller {
	public function index() {
		$this->data = $this->load->language('checkout/checkout');

		$this->data['firstname'] =  (isset($this->session->data['shipping_address']['firstname'])? $this->session->data['shipping_address']['firstname']: '');

		$this->data['lastname'] =  (isset($this->session->data['shipping_address']['lastname'])? $this->session->data['shipping_address']['lastname']: '');

		$this->data['company'] =  (isset($this->session->data['shipping_address']['company'])? $this->session->data['shipping_address']['company']: '');

		$this->data['address_1'] =  (isset($this->session->data['shipping_address']['address_1'])? $this->session->data['shipping_address']['address_1']: '');

		$this->data['address_2'] =  (isset($this->session->data['shipping_address']['address_2'])? $this->session->data['shipping_address']['address_2']: '');
		
		$this->data['postcode'] =  (isset($this->session->data['shipping_address']['postcode'])? $this->session->data['shipping_address']['postcode']: '');	
		
		$this->data['city'] =  (isset($this->session->data['shipping_address']['city'])? $this->session->data['shipping_address']['city']: '');	
		
		$this->data['country_id'] =  (isset($this->session->data['shipping_address']['country_id'])? $this->session->data['shipping_address']['country_id']: $this->config->get('config_country_id'));	
		
		$this->data['zone_id'] =  (isset($this->session->data['shipping_address']['zone_id'])? $this->session->data['shipping_address']['zone_id']: '');
		
		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		// Custom Fields
		$this->load->model('account/custom_field');

		$this->data['custom_fields'] = $this->model_account_custom_field->getCustomFields($this->session->data['guest']['customer_group_id']);
		
		$this->data['address_custom_field'] =  (isset($this->session->data['shipping_address']['custom_field'])? $this->session->data['shipping_address']['custom_field']: array());

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/guest_shipping.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/checkout/guest_shipping.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/checkout/guest_shipping.tpl', $this->data));
		}
	}

	public function save() {
		$this->data = $this->load->language('checkout/checkout');

		$json = array();

		// Validate if customer is logged in.
		if ($this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		// Check if guest checkout is available.
		if (!$this->config->get('config_checkout_guest') || $this->config->get('config_customer_price') || $this->cart->hasDownload()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}

		if (!$json) {
			if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
				$json['error']['firstname'] = $this->data['error_firstname'];
			}

			if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
				$json['error']['lastname'] = $this->data['error_lastname'];
			}

			if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
				$json['error']['address_1'] = $this->data['error_address_1'];
			}

			if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
				$json['error']['city'] = $this->data['error_city'];
			}

			$this->load->model('localisation/country');

			$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

			if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
				$json['error']['postcode'] = $this->data['error_postcode'];
			}

			if ($this->request->post['country_id'] == '') {
				$json['error']['country'] = $this->data['error_country'];
			}

			if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
				$json['error']['zone'] = $this->data['error_zone'];
			}

			// Custom field validation
			$this->load->model('account/custom_field');

			$custom_fields = $this->model_account_custom_field->getCustomFields($this->session->data['guest']['customer_group_id']);

			foreach ($custom_fields as $custom_field) {
				if (($custom_field['location'] == 'address') && $custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
					$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->data['error_custom_field'], $custom_field['name']);
				}
			}
		}

		if (!$json) {
			$this->session->data['shipping_address']['firstname'] = $this->request->post['firstname'];
			$this->session->data['shipping_address']['lastname'] = $this->request->post['lastname'];
			$this->session->data['shipping_address']['company'] = $this->request->post['company'];
			$this->session->data['shipping_address']['address_1'] = $this->request->post['address_1'];
			$this->session->data['shipping_address']['address_2'] = $this->request->post['address_2'];
			$this->session->data['shipping_address']['postcode'] = $this->request->post['postcode'];
			$this->session->data['shipping_address']['city'] = $this->request->post['city'];
			$this->session->data['shipping_address']['country_id'] = $this->request->post['country_id'];
			$this->session->data['shipping_address']['zone_id'] = $this->request->post['zone_id'];

			$this->load->model('localisation/country');

			$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

			if ($country_info) {
				$this->session->data['shipping_address']['country'] = $country_info['name'];
				$this->session->data['shipping_address']['iso_code_2'] = $country_info['iso_code_2'];
				$this->session->data['shipping_address']['iso_code_3'] = $country_info['iso_code_3'];
				$this->session->data['shipping_address']['address_format'] = $country_info['address_format'];
			} else {
				$this->session->data['shipping_address']['country'] = '';
				$this->session->data['shipping_address']['iso_code_2'] = '';
				$this->session->data['shipping_address']['iso_code_3'] = '';
				$this->session->data['shipping_address']['address_format'] = '';
			}

			$this->load->model('localisation/zone');

			$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

			if ($zone_info) {
				$this->session->data['shipping_address']['zone'] = $zone_info['name'];
				$this->session->data['shipping_address']['zone_code'] = $zone_info['code'];
			} else {
				$this->session->data['shipping_address']['zone'] = '';
				$this->session->data['shipping_address']['zone_code'] = '';
			}
			
			if (isset($this->request->post['custom_field'])) {
				$this->session->data['shipping_address']['custom_field'] = $this->request->post['custom_field'];
			} else {
				$this->session->data['shipping_address']['custom_field'] = array();
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}