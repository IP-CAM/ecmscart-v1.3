<?php
class ControllerPaymentPaymate extends Controller {
	public function index() {
		$this->data['button_confirm'] = $this->data['button_confirm'];

		if (!$this->config->get('paymate_test')) {
			$this->data['action'] = 'https://www.paymate.com/PayMate/ExpressPayment';
		} else {
			$this->data['action'] = 'https://www.paymate.com.au/PayMate/TestExpressPayment';
		}

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$this->data['mid'] = $this->config->get('paymate_username');
		$this->data['amt'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

		$this->data['currency'] = $order_info['currency_code'];
		$this->data['ref'] = $order_info['order_id'];

		$this->data['pmt_sender_email'] = $order_info['email'];
		$this->data['pmt_contact_firstname'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
		$this->data['pmt_contact_surname'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
		$this->data['pmt_contact_phone'] = $order_info['telephone'];
		$this->data['pmt_country'] = $order_info['payment_iso_code_2'];

		$this->data['regindi_address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_sub'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_state'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
		$this->data['regindi_pcode'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');

		$this->data['return'] = $this->url->link('payment/paymate/callback', 'hash=' . md5($order_info['order_id'] . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . $order_info['currency_code'] . $this->config->get('paymate_password')));

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paymate.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/paymate.tpl', $this->data);
		} else {
			return $this->load->view('default/template/payment/paymate.tpl', $this->data);
		}
	}

	public function callback() {
		$this->data = $this->load->language('payment/paymate');

		$order_id = $this->request->post('ref',0);
		
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$error = '';

			if (!isset($this->request->post['responseCode']) || !isset($this->request->get['hash'])) {
				$error = $this->data['text_unable'];
			} elseif ($this->request->get['hash'] != md5($order_info['order_id'] . $this->currency->format($this->request->post['paymentAmount'], $this->request->post['currency'], 1.0000000, false) . $this->request->post['currency'] . $this->config->get('paymate_password'))) {
				$error = $this->data['text_unable'];
			} elseif ($this->request->post['responseCode'] != 'PA' && $this->request->post['responseCode'] != 'PP') {
				$error = $this->data['text_declined'];
			}
		} else {
			$error = $this->data['text_unable'];
		}

		if ($error) {
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							$this->data['text_basket'],	// Text to display link
							$this->url->link('checkout/cart'),
							$this->data['text_checkout'],
							$this->url->link('checkout/checkout', '', 'SSL'),
							$this->data['text_failed'],
							$this->url->link('checkout/success')
						));
			
			$this->data['heading_title'] = $this->data['text_failed'];

			$this->data['text_message'] = sprintf($this->data['text_failed_message'], $error, $this->url->link('information/contact'));

			$this->data['button_continue'] = $this->data['button_continue'];

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
		} else {
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('paymate_order_status_id'));

			$this->response->redirect($this->url->link('checkout/success'));
		}
	}
}