<?php
class ControllerPaymentCod extends Controller {
	public function index() {
		$this->data['button_confirm'] = $this->data['button_confirm'];

		$this->data['continue'] = $this->url->link('checkout/success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/cod.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/cod.tpl', $this->data);
		} else {
			return $this->load->view('default/template/payment/cod.tpl', $this->data);
		}
	}

	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'cod') {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('cod_order_status_id'));
		}
	}
}