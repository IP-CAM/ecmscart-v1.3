<?php
class ControllerCheckoutPaymentAddress extends Controller {
	public function index() {
		$this->data = $this->load->language('checkout/checkout');
		
		$this->data['address_id'] =  (isset($this->session->data['payment_address']['address_id'])? $this->session->data['payment_address']['address_id']: $this->customer->getAddressId());

		$this->load->model('account/address');

		$this->data['addresses'] = $this->model_account_address->getAddresses();
		
		$this->data['country_id'] =  (isset($this->session->data['payment_address']['country_id'])? $this->session->data['payment_address']['country_id']: $this->config->get('config_country_id'));

		$this->data['zone_id'] =  (isset($this->session->data['payment_address']['zone_id'])? $this->session->data['payment_address']['zone_id']: '');

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		// Custom Fields
		$this->load->model('account/custom_field');

		$this->data['custom_fields'] = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));
		
		$this->data['payment_address_custom_field'] =  (isset($this->session->data['payment_address']['custom_field'])? $this->session->data['payment_address']['custom_field']: array());

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/payment_address.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/checkout/payment_address.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/checkout/payment_address.tpl', $this->data));
		}
	}

	public function save() {
		$this->data = $this->load->language('checkout/checkout');

		$json = array();

		// Validate if customer is logged in.
		if (!$this->customer->isLogged()) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}

		// Validate cart has products and has stock.
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');
		}

		// Validate minimum quantity requirements.
		$products = $this->cart->getProducts();

		foreach ($products as $product) {
			$product_total = 0;

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');

				break;
			}
		}

		if (!$json) {
			if (isset($this->request->post['payment_address']) && $this->request->post['payment_address'] == 'existing') {
				$this->load->model('account/address');

				if (empty($this->request->post['address_id'])) {
					$json['error']['warning'] = $this->data['error_address'];
				} elseif (!in_array($this->request->post['address_id'], array_keys($this->model_account_address->getAddresses()))) {
					$json['error']['warning'] = $this->data['error_address'];
				}

				if (!$json) {
					// Default Payment Address
					$this->load->model('account/address');

					$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->request->post['address_id']);

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				}
			} else {
				if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
					$json['error']['firstname'] = $this->data['error_firstname'];
				}

				if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
					$json['error']['lastname'] = $this->data['error_lastname'];
				}

				if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
					$json['error']['address_1'] = $this->data['error_address_1'];
				}

				if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 32)) {
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

				$custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

				foreach ($custom_fields as $custom_field) {
					if (($custom_field['location'] == 'address') && $custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
						$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->data['error_custom_field'], $custom_field['name']);
					}
				}

				if (!$json) {
					// Default Payment Address
					$this->load->model('account/address');

					$address_id = $this->model_account_address->addAddress($this->request->post);

					$this->session->data['payment_address'] = $this->model_account_address->getAddress($address_id);

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);

					$this->load->model('account/activity');

					$activity_data = array(
						'customer_id' => $this->customer->getId(),
						'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
					);

					$this->model_account_activity->addActivity('address_add', $activity_data);
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}