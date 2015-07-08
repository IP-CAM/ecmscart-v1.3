<?php
class ControllerPaymentFreeCheckout extends Controller {
	public function index() {
		$this->data['button_confirm'] = $this->data['button_confirm'];

		$this->data['continue'] = $this->url->link('checkout/success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/free_checkout.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/free_checkout.tpl', $this->data);
		} else {
			return $this->load->view('default/template/payment/free_checkout.tpl', $this->data);
		}
	}

	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'free_checkout') {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('free_checkout_order_status_id'));
		}
	}
}