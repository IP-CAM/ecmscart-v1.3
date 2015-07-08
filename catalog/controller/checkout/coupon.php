<?php
class ControllerCheckoutCoupon extends Controller {
	public function index() {
		if ($this->config->get('coupon_status')) {
			$this->data = $this->load->language('checkout/coupon');

			$this->data['heading_title'] = $this->data['heading_title'];
			
			$this->data['coupon'] =  (isset($this->session->data['coupon'])? $this->session->data['coupon']:'');

			$this->data['redirect'] = $this->request->get('redirect',$this->url->link('checkout/cart'));

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/coupon.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/checkout/coupon.tpl', $this->data);
			} else {
				return $this->load->view('default/template/checkout/coupon.tpl', $this->data);
			}
		}
	}

	public function coupon() {
		$this->data = $this->load->language('checkout/coupon');

		$json = array();

		$this->load->model('checkout/coupon');

		$coupon = $this->request->post('coupon','');
		
		$coupon_info = $this->model_checkout_coupon->getCoupon($coupon);

		if (empty($this->request->post['coupon'])) {
			$json['error'] = $this->data['error_empty'];
		} elseif ($coupon_info) {
			$this->session->data['coupon'] = $this->request->post['coupon'];

			$this->session->data['success'] = $this->data['text_success'];

			$json['redirect'] = $this->url->link('checkout/cart');
		} else {
			$json['error'] = $this->data['error_coupon'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}