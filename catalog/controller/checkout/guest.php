<?php
class ControllerCheckoutGuest extends Controller {
	public function index() {
		$this->data = $this->load->language('checkout/checkout');

		$this->data['customer_groups'] = array();

		if (is_array($this->config->get('config_customer_group_display'))) {
			$this->load->model('account/customer_group');

			$customer_groups = $this->model_account_customer_group->getCustomerGroups();

			foreach ($customer_groups as $customer_group) {
				if (in_array($customer_group['customer_group_id'], $this->config->get('config_customer_group_display'))) {
					$this->data['customer_groups'][] = $customer_group;
				}
			}
		}

		

		if (isset($this->session->data['guest']['customer_group_id'])) {
			$this->data['customer_group_id'] = $this->session->data['guest']['customer_group_id'];
		} else {
			$this->data['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		$this->data['firstname'] =  (isset($this->session->data['guest']['firstname'])? $this->session->data['guest']['firstname']:'');

		$this->data['lastname'] =  (isset($this->session->data['guest']['lastname'])? $this->session->data['guest']['lastname']:'');

		$this->data['email'] =  (isset($this->session->data['guest']['email'])? $this->session->data['guest']['email']:'');

		$this->data['telephone'] =  (isset($this->session->data['guest']['telephone'])? $this->session->data['guest']['telephone']:'');

		$this->data['fax'] =  (isset($this->session->data['guest']['fax'])? $this->session->data['guest']['fax']:'');

		$this->data['company'] =  (isset($this->session->data['payment_address']['company'])? $this->session->data['payment_address']['company']:'');

		$this->data['address_1'] =  (isset($this->session->data['payment_address']['address_1'])? $this->session->data['payment_address']['address_1']:'');

		$this->data['address_2'] =  (isset($this->session->data['payment_address']['address_2'])? $this->session->data['payment_address']['address_2']:'');

		if (isset($this->session->data['payment_address']['postcode'])) {
			$this->data['postcode'] = $this->session->data['payment_address']['postcode'];
		} elseif (isset($this->session->data['shipping_address']['postcode'])) {
			$this->data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$this->data['postcode'] = '';
		}
		
		$this->data['city'] =  (isset($this->session->data['payment_address']['city'])? $this->session->data['payment_address']['city']:'');
		
		if (isset($this->session->data['payment_address']['country_id'])) {
			$this->data['country_id'] = $this->session->data['payment_address']['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$this->data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$this->data['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->session->data['payment_address']['zone_id'])) {
			$this->data['zone_id'] = $this->session->data['payment_address']['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$this->data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$this->data['zone_id'] = '';
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		// Custom Fields
		$this->load->model('account/custom_field');

		$this->data['custom_fields'] = $this->model_account_custom_field->getCustomFields();

		if (isset($this->session->data['guest']['custom_field'])) {
			
			$guest_custom_field =  (isset($this->session->data['guest']['custom_field']) ? $this->session->data['guest']['custom_field']:array());
			
			$address_custom_field =  (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field']:array());
			
			$this->data['guest_custom_field'] = $guest_custom_field + $address_custom_field;
		} else {
			$this->data['guest_custom_field'] = array();
		}

		$this->data['shipping_required'] = $this->cart->hasShipping();
		
		$this->data['shipping_address'] =  (isset($this->session->data['guest']['shipping_address'])? $this->session->data['guest']['shipping_address']:true);

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/guest.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/checkout/guest.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/checkout/guest.tpl', $this->data));
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

			if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
				$json['error']['email'] = $this->data['error_email'];
			}

			if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
				$json['error']['telephone'] = $this->data['error_telephone'];
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

			// Customer Group
			if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
				$customer_group_id = $this->request->post['customer_group_id'];
			} else {
				$customer_group_id = $this->config->get('config_customer_group_id');
			}

			// Custom field validation
			$this->load->model('account/custom_field');

			$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
					$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->data['error_custom_field'], $custom_field['name']);
				}
			}
		}

		if (!$json) {
			$this->session->data['account'] = 'guest';

			$this->session->data['guest']['customer_group_id'] = $customer_group_id;
			$this->session->data['guest']['firstname'] = $this->request->post['firstname'];
			$this->session->data['guest']['lastname'] = $this->request->post['lastname'];
			$this->session->data['guest']['email'] = $this->request->post['email'];
			$this->session->data['guest']['telephone'] = $this->request->post['telephone'];
			$this->session->data['guest']['fax'] = $this->request->post['fax'];

			if (isset($this->request->post['custom_field']['account'])) {
				$this->session->data['guest']['custom_field'] = $this->request->post['custom_field']['account'];
			} else {
				$this->session->data['guest']['custom_field'] = array();
			}

			$this->session->data['payment_address']['firstname'] = $this->request->post['firstname'];
			$this->session->data['payment_address']['lastname'] = $this->request->post['lastname'];
			$this->session->data['payment_address']['company'] = $this->request->post['company'];
			$this->session->data['payment_address']['address_1'] = $this->request->post['address_1'];
			$this->session->data['payment_address']['address_2'] = $this->request->post['address_2'];
			$this->session->data['payment_address']['postcode'] = $this->request->post['postcode'];
			$this->session->data['payment_address']['city'] = $this->request->post['city'];
			$this->session->data['payment_address']['country_id'] = $this->request->post['country_id'];
			$this->session->data['payment_address']['zone_id'] = $this->request->post['zone_id'];

			$this->load->model('localisation/country');

			$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

			if ($country_info) {
				$this->session->data['payment_address']['country'] = $country_info['name'];
				$this->session->data['payment_address']['iso_code_2'] = $country_info['iso_code_2'];
				$this->session->data['payment_address']['iso_code_3'] = $country_info['iso_code_3'];
				$this->session->data['payment_address']['address_format'] = $country_info['address_format'];
			} else {
				$this->session->data['payment_address']['country'] = '';
				$this->session->data['payment_address']['iso_code_2'] = '';
				$this->session->data['payment_address']['iso_code_3'] = '';
				$this->session->data['payment_address']['address_format'] = '';
			}
			

			if (isset($this->request->post['custom_field']['address'])) {
				$this->session->data['payment_address']['custom_field'] = $this->request->post['custom_field']['address'];
			} else {
				$this->session->data['payment_address']['custom_field'] = array();
			}

			$this->load->model('localisation/zone');

			$zone_info = $this->model_localisation_zone->getZone($this->request->post['zone_id']);

			if ($zone_info) {
				$this->session->data['payment_address']['zone'] = $zone_info['name'];
				$this->session->data['payment_address']['zone_code'] = $zone_info['code'];
			} else {
				$this->session->data['payment_address']['zone'] = '';
				$this->session->data['payment_address']['zone_code'] = '';
			}

			$this->session->data['guest']['shipping_address'] = $this->request->post('shipping_address',false);
			// Default Payment Address
			if ($this->session->data['guest']['shipping_address']) {
				$this->session->data['shipping_address']['firstname'] = $this->request->post['firstname'];
				$this->session->data['shipping_address']['lastname'] = $this->request->post['lastname'];
				$this->session->data['shipping_address']['company'] = $this->request->post['company'];
				$this->session->data['shipping_address']['address_1'] = $this->request->post['address_1'];
				$this->session->data['shipping_address']['address_2'] = $this->request->post['address_2'];
				$this->session->data['shipping_address']['postcode'] = $this->request->post['postcode'];
				$this->session->data['shipping_address']['city'] = $this->request->post['city'];
				$this->session->data['shipping_address']['country_id'] = $this->request->post['country_id'];
				$this->session->data['shipping_address']['zone_id'] = $this->request->post['zone_id'];

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

				if ($zone_info) {
					$this->session->data['shipping_address']['zone'] = $zone_info['name'];
					$this->session->data['shipping_address']['zone_code'] = $zone_info['code'];
				} else {
					$this->session->data['shipping_address']['zone'] = '';
					$this->session->data['shipping_address']['zone_code'] = '';
				}

				if (isset($this->request->post['custom_field']['address'])) {
					$this->session->data['shipping_address']['custom_field'] = $this->request->post['custom_field']['address'];
				} else {
					$this->session->data['shipping_address']['custom_field'] = array();
				}
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}