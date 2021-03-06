<?php
class ControllerPaymentWorldPay extends Controller {
	public function index() {
		$this->data['button_confirm'] = $this->data['button_confirm'];

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if (!$this->config->get('worldpay_test')) {
			$this->data['action'] = 'https://secure.worldpay.com/wcc/purchase';
		} else {
			$this->data['action'] = 'https://secure-test.worldpay.com/wcc/purchase';
		}

		$this->data['merchant'] = $this->config->get('worldpay_merchant');
		$this->data['order_id'] = $order_info['order_id'];
		$this->data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		$this->data['currency'] = $order_info['currency_code'];
		$this->data['description'] = $this->config->get('config_name') . ' - #' . $order_info['order_id'];
		$this->data['name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];

		if (!$order_info['payment_address_2']) {
			$this->data['address'] = $order_info['payment_address_1'] . ', ' . $order_info['payment_city'] . ', ' . $order_info['payment_zone'];
		} else {
			$this->data['address'] = $order_info['payment_address_1'] . ', ' . $order_info['payment_address_2'] . ', ' . $order_info['payment_city'] . ', ' . $order_info['payment_zone'];
		}

		$this->data['postcode'] = $order_info['payment_postcode'];
		$this->data['country'] = $order_info['payment_iso_code_2'];
		$this->data['telephone'] = $order_info['telephone'];
		$this->data['email'] = $order_info['email'];
		$this->data['test'] = $this->config->get('worldpay_test');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/worldpay.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/worldpay.tpl', $this->data);
		} else {
			return $this->load->view('default/template/payment/worldpay.tpl', $this->data);
		}
	}

	public function callback() {
		$this->data = $this->load->language('payment/worldpay');

		$this->data['title'] = sprintf($this->data['heading_title'], $this->config->get('config_name'));

		if (!$this->request->server['HTTPS']) {
			$this->data['base'] = $this->config->get('config_url');
		} else {
			$this->data['base'] = $this->config->get('config_ssl');
		}

		$this->data['heading_title'] = sprintf($this->data['heading_title'], $this->config->get('config_name'));

		$this->data['text_response'] = $this->data['text_response'];
		$this->data['text_success'] = $this->data['text_success'];
		$this->data['text_success_wait'] = sprintf($this->data['text_success_wait'], $this->url->link('checkout/success'));
		$this->data['text_failure'] = $this->data['text_failure'];
		$this->data['text_failure_wait'] = sprintf($this->data['text_failure_wait'], $this->url->link('checkout/checkout', '', 'SSL'));

		if (isset($this->request->post['transStatus']) && $this->request->post['transStatus'] == 'Y') {
			$message = '';

			if (isset($this->request->post['transId'])) {
				$message .= 'transId: ' . $this->request->post['transId'] . "\n";
			}

			if (isset($this->request->post['transStatus'])) {
				$message .= 'transStatus: ' . $this->request->post['transStatus'] . "\n";
			}

			if (isset($this->request->post['countryMatch'])) {
				$message .= 'countryMatch: ' . $this->request->post['countryMatch'] . "\n";
			}

			if (isset($this->request->post['AVS'])) {
				$message .= 'AVS: ' . $this->request->post['AVS'] . "\n";
			}

			if (isset($this->request->post['rawAuthCode'])) {
				$message .= 'rawAuthCode: ' . $this->request->post['rawAuthCode'] . "\n";
			}

			if (isset($this->request->post['authMode'])) {
				$message .= 'authMode: ' . $this->request->post['authMode'] . "\n";
			}

			if (isset($this->request->post['rawAuthMessage'])) {
				$message .= 'rawAuthMessage: ' . $this->request->post['rawAuthMessage'] . "\n";
			}

			if (isset($this->request->post['wafMerchMessage'])) {
				$message .= 'wafMerchMessage: ' . $this->request->post['wafMerchMessage'] . "\n";
			}

			$this->load->model('checkout/order');

			// If returned successful but callbackPW doesn't match, set order to pendind and record reason
			if (isset($this->request->post['callbackPW']) && ($this->request->post['callbackPW'] == $this->config->get('worldpay_password'))) {
				$this->model_checkout_order->addOrderHistory($this->request->post['cartId'], $this->config->get('worldpay_order_status_id'), $message, false);
			} else {
				$this->model_checkout_order->addOrderHistory($this->request->post['cartId'], $this->config->get('config_order_status_id'), $this->data['text_pw_mismatch']);
			}

			$this->data['continue'] = $this->url->link('checkout/success');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/worldpay_success.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/worldpay_success.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/payment/worldpay_success.tpl', $this->data));
			}
		} else {
			$this->data['continue'] = $this->url->link('checkout/cart');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/worldpay_failure.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/worldpay_failure.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/payment/worldpay_failure.tpl', $this->data));
			}
		}
	}
}