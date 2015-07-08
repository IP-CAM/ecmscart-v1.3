<?php

class ControllerPaymentSecureTradingPp extends Controller {

	public function index() {
		$this->load->model('checkout/order');
		$this->load->model('localisation/country');
		$this->load->model('localisation/zone');
		$this->data = $this->load->language('payment/securetrading_pp');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
			$this->data['order_info'] = $order_info;
			$this->data['site_reference'] = $this->config->get('securetrading_pp_site_reference');
			$this->data['parent_css'] = $this->config->get('securetrading_pp_parent_css');
			$this->data['child_css'] = $this->config->get('securetrading_pp_child_css');
			$this->data['currency'] = $order_info['currency_code'];
			$this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
			$this->data['settle_due_date'] = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $this->config->get('securetrading_pp_settle_due_date') . ' days'));
			$this->data['settle_status'] = $this->config->get('securetrading_pp_settle_status');

			$payment_country = $this->model_localisation_country->getCountry($order_info['payment_country_id']);
			$payment_zone = $this->model_localisation_zone->getZone($order_info['payment_zone_id']);

			$shipping_country = $this->model_localisation_country->getCountry($order_info['shipping_country_id']);
			$shipping_zone = $this->model_localisation_zone->getZone($order_info['shipping_zone_id']);

			if ($payment_country['iso_code_3'] == 'USA') {
				$this->data['billing_county'] = $payment_zone['code'];
			} else {
				$this->data['billing_county'] = $order_info['payment_zone'];
			}

			if (isset($shipping_country['iso_code_3']) && $shipping_country['iso_code_3'] == 'USA') {
				$this->data['shipping_county'] = $shipping_zone['code'];
			} else {
				$this->data['shipping_county'] = $order_info['shipping_zone'];
			}

			if (!isset($shipping_country['iso_code_2'])) {
				$shipping_country['iso_code_2'] = $payment_country['iso_code_2'];
			}

			$this->data['payment_country'] = $payment_country;
			$this->data['shipping_country'] = $shipping_country;

			if ($this->config->get('securetrading_pp_site_security_status')) {
				$this->data['site_security'] = hash('sha256', $order_info['currency_code'] . $this->data['total'] . $this->data['site_reference'] . $this->data['settle_status'] . $this->data['settle_due_date'] . $order_info['order_id'] . $this->config->get('securetrading_pp_site_security_password'));
			} else {
				$this->data['site_security'] = false;
			}

			$cards = array(
				'AMEX' => 'American Express',
				'VISA' => 'Visa',
				'DELTA' => 'Visa Debit',
				'ELECTRON' => 'Visa Electron',
				'PURCHASING' => 'Visa Purchasing',
				'VPAY' => 'V Pay',
				'MASTERCARD' => 'MasterCard',
				'MASTERCARDDEBIT' => 'MasterCard Debit',
				'MAESTRO' => 'Maestro',
				'PAYPAL' => 'PayPal',
			);

			$this->data['cards'] = array();

			foreach ($cards as $key => $value) {
				if (in_array($key, $this->config->get('securetrading_pp_cards_accepted'))) {
					$this->data['cards'][$key] = $value;
				}
			}

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/securetrading_pp.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/payment/securetrading_pp.tpl', $this->data);
			} else {
				return $this->load->view('default/template/payment/securetrading_pp.tpl', $this->data);
			}
		}
	}

	public function ipn() {
		$this->load->model('checkout/order');
		$this->load->model('payment/securetrading_pp');
		$this->data = $this->load->language('payment/securetrading_pp');

		$keys = array_keys($this->request->post);
		sort($keys);

		$keys_ignore = array('notificationreference', 'responsesitesecurity');

		$string_to_hash = '';

		foreach ($keys as $key) {
			if (!in_array($key, $keys_ignore)) {
				$string_to_hash .= $this->request->post[$key];
			}
		}

		$string_to_hash .= $this->config->get('securetrading_pp_notification_password');

		if (hash('sha256', $string_to_hash) == $this->request->post['responsesitesecurity'] && $this->request->post['sitereference'] == $this->config->get('securetrading_pp_site_reference')) {
			$order_info = $this->model_checkout_order->getOrder($this->request->post['orderreference']);

			if ($order_info) {
				$order_total = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

				if ($order_total == $this->request->post['mainamount'] && $order_info['currency_code'] == $this->request->post['currencyiso3a'] && $order_info['payment_code'] == 'securetrading_pp') {
					$status_code_mapping = array(
						0 => $this->data['text_not_given'],
						1 => $this->data['text_not_checked'],
						2 => $this->data['text_match'],
						4 => $this->data['text_not_match'],
					);
					$shipping_country = $this->model_payment_securetrading_pp->getCountry($this->request->post['customercountryiso2a']);
					$payment_country = $this->model_payment_securetrading_pp->getCountry($this->request->post['billingcountryiso2a']);

					$order_info['payment_firstname'] = $this->request->post['billingfirstname'];
					$order_info['payment_lastname'] = $this->request->post['billinglastname'];
					$order_info['payment_address_1'] = $this->request->post['billingpremise'];
					$order_info['payment_address_2'] = $this->request->post['billingstreet'];
					$order_info['payment_city'] = $this->request->post['billingtown'];
					$order_info['payment_zone'] = $this->request->post['billingcounty'];
					$order_info['payment_zone_id'] = 0;
					$order_info['payment_country'] = $payment_country['name'];
					$order_info['payment_country_id'] = $payment_country['country_id'];
					$order_info['payment_postcode'] = $this->request->post['billingpostcode'];

					$order_info['shipping_firstname'] = $this->request->post['customerfirstname'];
					$order_info['shipping_lastname'] = $this->request->post['customerlastname'];
					$order_info['shipping_address_1'] = $this->request->post['customerpremise'];
					$order_info['shipping_address_2'] = $this->request->post['customerstreet'];
					$order_info['shipping_city'] = $this->request->post['customertown'];
					$order_info['shipping_zone'] = $this->request->post['customercounty'];
					$order_info['shipping_zone_id'] = 0;
					$order_info['shipping_country'] = $shipping_country['name'];
					$order_info['shipping_country_id'] = $shipping_country['country_id'];
					$order_info['shipping_postcode'] = $this->request->post['customerpostcode'];

					$this->model_payment_securetrading_pp->editOrder($order_info['order_id'], $order_info);

					$postcode_status = $this->request->post['securityresponsepostcode'];
					$security_code_status = $this->request->post['securityresponsesecuritycode'];
					$address_status = $this->request->post['securityresponseaddress'];

					$message = sprintf($this->data['text_postcode_check'], $status_code_mapping[$postcode_status]) . "\n";
					$message .= sprintf($this->data['text_security_code_check'], $status_code_mapping[$security_code_status]) . "\n";
					$message .= sprintf($this->data['text_address_check'], $status_code_mapping[$address_status]) . "\n";

				
					$transactionreference = $this->request->post('transactionreference','');
					
					$this->model_payment_securetrading_pp->addReference($order_info['order_id'], $transactionreference);

					if ($this->request->post['errorcode'] == '0') {
						$order_status_id = $this->config->get('securetrading_pp_order_status_id');

						$this->model_payment_securetrading_pp->confirmOrder($order_info['order_id'], $order_status_id);
						$this->model_payment_securetrading_pp->updateOrder($order_info['order_id'], $order_status_id, $message);
					} elseif ($this->request->post['errorcode'] == '70000') {
						$order_status_id = $this->config->get('securetrading_pp_declined_order_status_id');

						$this->model_payment_securetrading_pp->updateOrder($order_info['order_id'], $order_status_id, $message);
					}
				}
			}
		}
	}

}
