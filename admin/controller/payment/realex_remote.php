<?php
class ControllerPaymentRealexRemote extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/realex_remote');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('realex_remote', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_merchant_id'] =  (isset($this->error['error_merchant_id'])?$this->error['error_merchant_id']:'');

		$this->data['error_secret'] =  (isset($this->error['error_secret'])?$this->error['error_secret']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/realex_remote', 'token=' . $this->session->data['token'], 'SSL')
							
						));

		$this->data['action'] = $this->url->link('payment/realex_remote', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
	
		$this->data['realex_remote_merchant_id'] = $this->request->post('realex_remote_merchant_id',$this->config->get('realex_remote_merchant_id'));
		
		$this->data['realex_remote_secret'] = $this->request->post('realex_remote_secret',$this->config->get('realex_remote_secret'));
		
		$this->data['realex_remote_rebate_password'] = $this->request->post('realex_remote_rebate_password',$this->config->get('realex_remote_rebate_password'));
		
		$this->data['realex_remote_geo_zone_id'] = $this->request->post('realex_remote_geo_zone_id',$this->config->get('realex_remote_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['realex_remote_total'] = $this->request->post('realex_remote_total',$this->config->get('realex_remote_total'));
		
		$this->data['realex_remote_sort_order'] = $this->request->post('realex_remote_sort_order',$this->config->get('realex_remote_sort_order'));

		$this->data['realex_remote_status'] = $this->request->post('realex_remote_status',$this->config->get('realex_remote_status'));

		$this->data['realex_remote_card_data_status'] = $this->request->post('realex_remote_card_data_status',$this->config->get('realex_remote_card_data_status'));

		$this->data['realex_remote_debug'] = $this->request->post('realex_remote_debug',$this->config->get('realex_remote_debug'));

		$this->data['realex_remote_account'] = $this->request->post('realex_remote_account',$this->config->get('realex_remote_account'));

		$this->data['realex_remote_auto_settle'] = $this->request->post('realex_remote_auto_settle',$this->config->get('realex_remote_auto_settle'));
		
		$this->data['realex_remote_tss_check'] = $this->request->post('realex_remote_tss_check',$this->config->get('realex_remote_tss_check'));

		$this->data['realex_remote_3d'] = $this->request->post('realex_remote_3d',$this->config->get('realex_remote_3d'));
		
		$this->data['realex_remote_liability'] = $this->request->post('realex_remote_liability',$this->config->get('realex_remote_liability'));
		
		$this->data['realex_remote_order_status_success_settled_id'] = $this->request->post('realex_remote_order_status_success_settled_id',$this->config->get('realex_remote_order_status_success_settled_id'));
		
		$this->data['realex_remote_order_status_success_unsettled_id'] = $this->request->post('realex_remote_order_status_success_unsettled_id',$this->config->get('realex_remote_order_status_success_unsettled_id'));

		$this->data['realex_remote_order_status_decline_id'] = $this->request->post('realex_remote_order_status_decline_id',$this->config->get('realex_remote_order_status_decline_id'));

		$this->data['realex_remote_order_status_decline_pending_id'] = $this->request->post('realex_remote_order_status_decline_pending_id',$this->config->get('realex_remote_order_status_decline_pending_id'));

		$this->data['realex_remote_order_status_decline_stolen_id'] = $this->request->post('realex_remote_order_status_decline_stolen_id',$this->config->get('realex_remote_order_status_decline_stolen_id'));
		
		$this->data['realex_remote_order_status_decline_bank_id'] = $this->request->post('realex_remote_order_status_decline_bank_id',$this->config->get('realex_remote_order_status_decline_bank_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/realex_remote.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/realex_remote');
		$this->model_payment_realex_remote->install();
	}

	public function orderAction() {
		if ($this->config->get('realex_remote_status')) {
			$this->load->model('payment/realex_remote');

			$realex_order = $this->model_payment_realex_remote->getOrder($this->request->get['order_id']);

			if (!empty($realex_order)) {
				$this->data = $this->load->language('payment/realex_remote');

				$realex_order['total_captured'] = $this->model_payment_realex_remote->getTotalCaptured($realex_order['realex_remote_order_id']);

				$realex_order['total_formatted'] = $this->currency->format($realex_order['total'], $realex_order['currency_code'], 1, true);
				
				$realex_order['total_captured_formatted'] = $this->currency->format($realex_order['total_captured'], $realex_order['currency_code'], 1, true);

				$this->data['realex_order'] = $realex_order;

				$this->data['auto_settle'] = $realex_order['settle_type'];

				$this->data['order_id'] = $this->request->get['order_id'];
				
				$this->data['token'] = $this->request->get['token'];

				return $this->load->view('payment/realex_remote_order.tpl', $this->data);
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/realex_remote');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/realex_remote');

			$realex_order = $this->model_payment_realex_remote->getOrder($this->request->post['order_id']);

			$void_response = $this->model_payment_realex_remote->void($this->request->post['order_id']);

			$this->model_payment_realex_remote->logger('Void result:\r\n' . print_r($void_response, 1));

			if (isset($void_response->result) && $void_response->result == '00') {
				$this->model_payment_realex_remote->addTransaction($realex_order['realex_remote_order_id'], 'void', 0.00);
				$this->model_payment_realex_remote->updateVoidStatus($realex_order['realex_remote_order_id'], 1);

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
			$this->load->model('payment/realex_remote');

			$realex_order = $this->model_payment_realex_remote->getOrder($this->request->post['order_id']);

			$capture_response = $this->model_payment_realex_remote->capture($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_realex_remote->logger('Settle result:\r\n' . print_r($capture_response, 1));

			if (isset($capture_response->result) && $capture_response->result == '00') {
				$this->model_payment_realex_remote->addTransaction($realex_order['realex_remote_order_id'], 'payment', $this->request->post['amount']);
				$total_captured = $this->model_payment_realex_remote->getTotalCaptured($realex_order['realex_remote_order_id']);

				if ($total_captured >= $realex_order['total'] || $realex_order['settle_type'] == 0) {
					$this->model_payment_realex_remote->updateCaptureStatus($realex_order['realex_remote_order_id'], 1);
					$capture_status = 1;
					$json['msg'] = $this->data['text_capture_ok_order'];
				} else {
					$capture_status = 0;
					$json['msg'] = $this->data['text_capture_ok'];
				}

				$this->model_payment_realex_remote->updateForRebate($realex_order['realex_remote_order_id'], $capture_response->pasref, $capture_response->orderid);

				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = (float)$this->request->post['amount'];
				$json['data']['capture_status'] = $capture_status;
				$json['data']['total'] = (float)$total_captured;
				$json['data']['total_formatted'] = $this->currency->format($total_captured, $realex_order['currency_code'], 1, true);
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($capture_response->message) && !empty($capture_response->message) ? (string)$capture_response->message : 'Unable to capture';

			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function rebate() {
		$this->data = $this->load->language('payment/realex_remote');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/realex_remote');

			$realex_order = $this->model_payment_realex_remote->getOrder($this->request->post['order_id']);

			$rebate_response = $this->model_payment_realex_remote->rebate($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_realex_remote->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

			if (isset($rebate_response->result) && $rebate_response->result == '00') {
				$this->model_payment_realex_remote->addTransaction($realex_order['realex_remote_order_id'], 'rebate', $this->request->post['amount']*-1);

				$total_rebated = $this->model_payment_realex_remote->getTotalRebated($realex_order['realex_remote_order_id']);
				$total_captured = $this->model_payment_realex_remote->getTotalCaptured($realex_order['realex_remote_order_id']);

				if ($total_captured <= 0 && $realex_order['capture_status'] == 1) {
					$this->model_payment_realex_remote->updateRebateStatus($realex_order['realex_remote_order_id'], 1);
					$rebate_status = 1;
					$json['msg'] = $this->data['text_rebate_ok_order'];
				} else {
					$rebate_status = 0;
					$json['msg'] = $this->data['text_rebate_ok'];
				}

				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->request->post['amount'] * -1;
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
		if (!$this->user->hasPermission('modify', 'payment/realex_remote')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['realex_remote_merchant_id']) {
			$this->error['error_merchant_id'] = $this->data['error_merchant_id'];
		}

		if (!$this->request->post['realex_remote_secret']) {
			$this->error['error_secret'] = $this->data['error_secret'];
		}

		return !$this->error;
	}
}