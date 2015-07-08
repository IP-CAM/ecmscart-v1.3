<?php
class ControllerPaymentCheque extends Controller {
	public function index() {
		$this->data = $this->load->language('payment/cheque');

		$this->data['payable'] = $this->config->get('cheque_payable');
		$this->data['address'] = nl2br($this->config->get('config_address'));

		$this->data['continue'] = $this->url->link('checkout/success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/cheque.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/cheque.tpl', $this->data);
		} else {
			return $this->load->view('default/template/payment/cheque.tpl', $this->data);
		}
	}

	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'cheque') {
			$this->data = $this->load->language('payment/cheque');

			$this->load->model('checkout/order');

			$comment  = $this->data['text_payable'] . "\n";
			$comment .= $this->config->get('cheque_payable') . "\n\n";
			$comment .= $this->data['text_address'] . "\n";
			$comment .= $this->config->get('config_address') . "\n\n";
			$comment .= $this->data['text_payment'] . "\n";

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('cheque_order_status_id'), $comment, true);
		}
	}
}