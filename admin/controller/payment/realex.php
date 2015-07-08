<?php
class ControllerPaymentRealex extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/realex');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('realex', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['notify_url'] = HTTPS_CATALOG . 'index.php?route=payment/realex/notify';

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_merchant_id'] =  (isset($this->error['error_merchant_id'])?$this->error['error_merchant_id']:'');

		$this->data['error_secret'] =  (isset($this->error['error_secret'])?$this->error['error_secret']:'');

		$this->data['error_live_url'] =  (isset($this->error['error_live_url'])?$this->error['error_live_url']:'');
		
		$this->data['error_demo_url'] =  (isset($this->error['error_demo_url'])?$this->error['error_demo_url']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/realex', 'token=' . $this->session->data['token'], 'SSL')
					));
		
		$this->data['action'] = $this->url->link('payment/realex', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['realex_merchant_id'] = $this->request->post('realex_merchant_id',$this->config->get('realex_merchant_id'));
		
		$this->data['realex_secret'] = $this->request->post('realex_secret',$this->config->get('realex_secret'));	
		
		$this->data['realex_rebate_password'] = $this->request->post('realex_rebate_password',$this->config->get('realex_rebate_password'));
		
		$this->data['realex_live_demo'] = $this->request->post('realex_live_demo',$this->config->get('realex_live_demo'));
		
		$this->data['realex_geo_zone_id'] = $this->request->post('realex_geo_zone_id',$this->config->get('realex_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['realex_total'] = $this->request->post('realex_total',$this->config->get('realex_total'));

		$this->data['realex_sort_order'] = $this->request->post('realex_sort_order',$this->config->get('realex_sort_order'));

		$this->data['realex_status'] = $this->request->post('realex_status',$this->config->get('realex_status'));
		
		$this->data['realex_debug'] = $this->request->post('realex_debug',$this->config->get('realex_debug'));

		$this->data['realex_account'] = $this->request->post('realex_account',$this->config->get('realex_account'));
		
		$this->data['realex_auto_settle'] = $this->request->post('realex_auto_settle',$this->config->get('realex_auto_settle'));
		
		$this->data['realex_card_select'] = $this->request->post('realex_card_select',$this->config->get('realex_card_select'));
		
		$this->data['realex_tss_check'] = $this->request->post('realex_tss_check',$this->config->get('realex_tss_check'));
		
		$this->data['realex_order_status_success_settled_id'] = $this->request->post('realex_order_status_success_settled_id',$this->config->get('realex_order_status_success_settled_id'));
		
		$this->data['realex_order_status_success_unsettled_id'] = $this->request->post('realex_order_status_success_unsettled_id',$this->config->get('realex_order_status_success_unsettled_id'));

		$this->data['realex_order_status_decline_id'] = $this->request->post('realex_order_status_decline_id',$this->config->get('realex_order_status_decline_id'));

		$this->data['realex_order_status_decline_pending_id'] = $this->request->post('realex_order_status_decline_pending_id',$this->config->get('realex_order_status_decline_pending_id'));
		
		$this->data['realex_order_status_decline_stolen_id'] = $this->request->post('realex_order_status_decline_stolen_id',$this->config->get('realex_order_status_decline_stolen_id'));
		
		$this->data['realex_order_status_decline_bank_id'] = $this->request->post('realex_order_status_decline_bank_id',$this->config->get('realex_order_status_decline_bank_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['realex_live_url'] = $this->request->post('realex_live_url',$this->config->get('realex_live_url'));
		
		if (empty($this->data['realex_live_url'])) 
			$this->data['realex_live_url'] = 'https://hpp.realexpayments.com/pay';
		
		$this->data['realex_demo_url'] = $this->request->post('realex_demo_url',$this->config->get('realex_demo_url'));

		if (empty($this->data['realex_demo_url'])) 
			$this->data['realex_demo_url'] = 'https://hpp.sandbox.realexpayments.com/pay';
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/realex.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/realex');
		
		$this->model_payment_realex->install();
	}

	public function orderAction() {
		if ($this->config->get('realex_status')) {
			$this->load->model('payment/realex');

			$realex_order = $this->model_payment_realex->getOrder($this->request->get['order_id']);

			if (!empty($realex_order)) {
				$this->data = $this->load->language('payment/realex');

				$realex_order['total_captured'] = $this->model_payment_realex->getTotalCaptured($realex_order['realex_order_id']);

				$realex_order['total_formatted'] = $this->currency->format($realex_order['total'], $realex_order['currency_code'], 1, true);
				
				$realex_order['total_captured_formatted'] = $this->currency->format($realex_order['total_captured'], $realex_order['currency_code'], 1, true);

				$this->data['realex_order'] = $realex_order;

				$this->data['auto_settle'] = $realex_order['settle_type'];

				$this->data['order_id'] = $this->request->get['order_id'];
				
				$this->data['token'] = $this->request->get['token'];

				return $this->load->view('payment/realex_order.tpl', $this->data);
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/realex');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/realex');

			$realex_order = $this->model_payment_realex->getOrder($this->request->post['order_id']);

			$void_response = $this->model_payment_realex->void($this->request->post['order_id']);

			$this->model_payment_realex->logger('Void result:\r\n' . print_r($void_response, 1));

			if (isset($void_response->result) && $void_response->result == '00') {
				$this->model_payment_realex->addTransaction($realex_order['realex_order_id'], 'void', 0.00);
				$this->model_payment_realex->updateVoidStatus($realex_order['realex_order_id'], 1);

				$json['msg'] = $this->data['text_void_ok'];
				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($void_response->message) && !empty($void_response->message) ? (string)$void_response->message : 'Unable to void';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function capture() {
		$this->data = $this->load->language('payment/realex');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($this->request->post['amount']) && $this->request->post['amount'] > 0) {
			$this->load->model('payment/realex');

			$realex_order = $this->model_payment_realex->getOrder($this->request->post['order_id']);

			$capture_response = $this->model_payment_realex->capture($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_realex->logger('Settle result:\r\n' . print_r($capture_response, 1));

			if (isset($capture_response->result) && $capture_response->result == '00') {
				$this->model_payment_realex->addTransaction($realex_order['realex_order_id'], 'payment', $this->request->post['amount']);

				$total_captured = $this->model_payment_realex->getTotalCaptured($realex_order['realex_order_id']);

				if ($total_captured >= $realex_order['total'] || $realex_order['settle_type'] == 0) {
					$this->model_payment_realex->updateCaptureStatus($realex_order['realex_order_id'], 1);
					$capture_status = 1;
					$json['msg'] = $this->data['text_capture_ok_order'];
				} else {
					$capture_status = 0;
					$json['msg'] = $this->data['text_capture_ok'];
				}

				$this->model_payment_realex->updateForRebate($realex_order['realex_order_id'], $capture_response->pasref, $capture_response->orderid);

				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->request->post['amount'];
				$json['data']['capture_status'] = $capture_status;
				$json['data']['total'] = (float)$total_captured;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($capture_response->message) && !empty($capture_response->message) ? (string)$capture_response->message : 'Unable to capture';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = $this->data['error_data_missing'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function rebate() {
		$this->data = $this->load->language('payment/realex');
		$json = array();

		if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])) {
			$this->load->model('payment/realex');

			$realex_order = $this->model_payment_realex->getOrder($this->request->post['order_id']);

			$rebate_response = $this->model_payment_realex->rebate($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_realex->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

			if (isset($rebate_response->result) && $rebate_response->result == '00') {
				$this->model_payment_realex->addTransaction($realex_order['realex_order_id'], 'rebate', $this->request->post['amount']*-1);

				$total_rebated = $this->model_payment_realex->getTotalRebated($realex_order['realex_order_id']);
				$total_captured = $this->model_payment_realex->getTotalCaptured($realex_order['realex_order_id']);

				if ($total_captured <= 0 && $realex_order['capture_status'] == 1) {
					$this->model_payment_realex->updateRebateStatus($realex_order['realex_order_id'], 1);
					$rebate_status = 1;
					$json['msg'] = $this->data['text_rebate_ok_order'];
				} else {
					$rebate_status = 0;
					$json['msg'] = $this->data['text_rebate_ok'];
				}

				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->request->post['amount']*-1;
				$json['data']['total_captured'] = (float)$total_captured;
				$json['data']['total_rebated'] = (float)$total_rebated;
				$json['data']['rebate_status'] = $rebate_status;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($rebate_response->message) && !empty($rebate_response->message) ? (string)$rebate_response->message : 'Unable to rebate';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/realex')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['realex_merchant_id']) {
			$this->error['error_merchant_id'] = $this->data['error_merchant_id'];
		}

		if (!$this->request->post['realex_secret']) {
			$this->error['error_secret'] = $this->data['error_secret'];
		}

		if (!$this->request->post['realex_live_url']) {
			$this->error['error_live_url'] = $this->data['error_live_url'];
		}

		if (!$this->request->post['realex_demo_url']) {
			$this->error['error_demo_url'] = $this->data['error_demo_url'];
		}

		return !$this->error;
	}
}