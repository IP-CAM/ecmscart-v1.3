<?php
class ControllerCheckoutVoucher extends Controller {
	public function index() {
		if ($this->config->get('voucher_status')) {
			$this->data = $this->load->language('checkout/voucher');
			
			$this->data['voucher'] =  (isset($this->session->data['voucher'])?$this->session->data['voucher']:'');

			if (isset($this->request->get['redirect']) && !empty($this->request->get['redirect'])) {
				$this->data['redirect'] = $this->request->get['redirect'];
			} else {
				$this->data['redirect'] = $this->url->link('checkout/cart');
			}

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/voucher.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/checkout/voucher.tpl', $this->data);
			} else {
				return $this->load->view('default/template/checkout/voucher.tpl', $this->data);
			}
		}
	}

	public function voucher() {
		$this->data = $this->load->language('checkout/voucher');

		$json = array();

		$this->load->model('checkout/voucher');

		$voucher = $this->request->post('voucher','');
		
		$voucher_info = $this->model_checkout_voucher->getVoucher($voucher);

		if (empty($this->request->post['voucher'])) {
			$json['error'] = $this->data['error_empty'];
		} elseif ($voucher_info) {
			$this->session->data['voucher'] = $this->request->post['voucher'];

			$this->session->data['success'] = $this->data['text_success'];

			$json['redirect'] = $this->url->link('checkout/cart');
		} else {
			$json['error'] = $this->data['error_voucher'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}