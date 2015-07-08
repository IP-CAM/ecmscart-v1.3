<?php
class ControllerCheckoutReward extends Controller {
	public function index() {
		$points = $this->customer->getRewardPoints();

		$points_total = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}

		if ($points && $points_total && $this->config->get('reward_status')) {
			$this->data = $this->load->language('checkout/reward');

			$this->data['heading_title'] = sprintf($this->data['heading_title'], $points);
			$this->data['entry_reward'] = sprintf($this->data['entry_reward'], $points_total);
			
			$this->data['reward'] =  (isset($this->session->data['reward'])? $this->session->data['reward']:'');
			
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/reward.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/checkout/reward.tpl', $this->data);
			} else {
				return $this->load->view('default/template/checkout/reward.tpl', $this->data);
			}
		}
	}

	public function reward() {
		$this->data = $this->load->language('checkout/reward');

		$json = array();

		$points = $this->customer->getRewardPoints();

		$points_total = 0;

		foreach ($this->cart->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}

		if (empty($this->request->post['reward'])) {
			$json['error'] = $this->data['error_reward'];
		}

		if ($this->request->post['reward'] > $points) {
			$json['error'] = sprintf($this->data['error_points'], $this->request->post['reward']);
		}

		if ($this->request->post['reward'] > $points_total) {
			$json['error'] = sprintf($this->data['error_maximum'], $points_total);
		}

		if (!$json) {
			$this->session->data['reward'] = abs($this->request->post['reward']);

			$this->session->data['success'] = $this->data['text_success'];

			if (isset($this->request->post['redirect'])) {
				$json['redirect'] = $this->url->link($this->request->post['redirect']);
			} else {
				$json['redirect'] = $this->url->link('checkout/cart');	
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
