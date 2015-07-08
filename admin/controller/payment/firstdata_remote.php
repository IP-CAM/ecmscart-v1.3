<?php
class ControllerPaymentFirstdataRemote extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/firstdata_remote');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('firstdata_remote', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_merchant_id'] =  (isset($this->error['error_merchant_id'])?$this->error['error_merchant_id']:'');
		
		$this->data['error_user_id'] =  (isset($this->error['error_user_id'])?$this->error['error_user_id']:'');
		
		$this->data['error_password'] =  (isset($this->error['error_password'])?$this->error['error_password']:'');

		$this->data['error_certificate'] =  (isset($this->error['error_certificate'])?$this->error['error_certificate']:'');

		$this->data['error_key'] =  (isset($this->error['error_key'])?$this->error['error_key']:'');

		$this->data['error_key_pw'] =  (isset($this->error['error_key_pw'])?$this->error['error_key_pw']:'');

		$this->data['error_ca'] =  (isset($this->error['error_ca'])?$this->error['error_ca']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/firstdata_remote', 'token=' . $this->session->data['token'], 'SSL')
						));
		
		$this->data['action'] = $this->url->link('payment/firstdata_remote', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['firstdata_remote_merchant_id'] = $this->request->post('firstdata_remote_merchant_id',$this->config->get('firstdata_remote_merchant_id'));
		
		$this->data['firstdata_remote_user_id'] = $this->request->post('firstdata_remote_user_id',$this->config->get('firstdata_remote_user_id'));	
		
		$this->data['firstdata_remote_password'] = $this->request->post('firstdata_remote_password',$this->config->get('firstdata_remote_password'));
		
		$this->data['firstdata_remote_certificate'] = $this->request->post('firstdata_remote_certificate',$this->config->get('firstdata_remote_certificate'));
		
		$this->data['firstdata_remote_key'] = $this->request->post('firstdata_remote_key',$this->config->get('firstdata_remote_key'));
		
		$this->data['firstdata_remote_key_pw'] = $this->request->post('firstdata_remote_key_pw',$this->config->get('firstdata_remote_key_pw'));
		
		$this->data['firstdata_remote_ca'] = $this->request->post('firstdata_remote_ca',$this->config->get('firstdata_remote_ca'));

		$this->data['firstdata_remote_geo_zone_id'] = $this->request->post('firstdata_remote_geo_zone_id',$this->config->get('firstdata_remote_geo_zone_id'));
		
		$this->data['firstdata_remote_total'] = $this->request->post('firstdata_remote_total',$this->config->get('firstdata_remote_total'));
		
		$this->data['firstdata_remote_sort_order'] = $this->request->post('firstdata_remote_sort_order',$this->config->get('firstdata_remote_sort_order'));
		
		$this->data['firstdata_remote_status'] = $this->request->post('firstdata_remote_status',$this->config->get('firstdata_remote_status'));
		
		$this->data['firstdata_remote_debug'] = $this->request->post('firstdata_remote_debug',$this->config->get('firstdata_remote_debug'));

		if ($this->config->get('firstdata_remote_auto_settle') != '' && !$this->error) {
			$this->data['firstdata_remote_auto_settle'] = $this->config->get('firstdata_remote_auto_settle');
		} else {
			$this->data['firstdata_remote_auto_settle'] = $this->request->post('firstdata_remote_auto_settle',1);
		}
		
		$this->data['firstdata_remote_3d'] = $this->request->post('firstdata_remote_3d',$this->config->get('firstdata_remote_3d'));
		
		$this->data['firstdata_remote_liability'] = $this->request->post('firstdata_remote_liability',$this->config->get('firstdata_remote_liability'));
		
		$this->data['firstdata_remote_order_status_success_settled_id'] = $this->request->post('firstdata_remote_order_status_success_settled_id',$this->config->get('firstdata_remote_order_status_success_settled_id'));

		$this->data['firstdata_remote_order_status_success_unsettled_id'] = $this->request->post('firstdata_remote_order_status_success_unsettled_id',$this->config->get('firstdata_remote_order_status_success_unsettled_id'));

		$this->data['firstdata_remote_order_status_decline_id'] = $this->request->post('firstdata_remote_order_status_decline_id',$this->config->get('firstdata_remote_order_status_decline_id'));

		$this->data['firstdata_remote_order_status_void_id'] = $this->request->post('firstdata_remote_order_status_void_id',$this->config->get('firstdata_remote_order_status_void_id'));

		$this->data['firstdata_remote_order_status_refunded_id'] = $this->request->post('firstdata_remote_order_status_refunded_id',$this->config->get('firstdata_remote_order_status_refunded_id'));

		$this->data['firstdata_remote_card_storage'] = $this->request->post('firstdata_remote_card_storage',$this->config->get('firstdata_remote_card_storage'));

		$this->data['cards'] = array();

		$this->data['cards'][] = array(
			'text'  => $this->data['text_mastercard'],
			'value' => 'mastercard'
		);

		$this->data['cards'][] = array(
			'text'  => $this->data['text_visa'],
			'value' => 'visa'
		);

		$this->data['cards'][] = array(
			'text'  => $this->data['text_diners'],
			'value' => 'diners'
		);

		$this->data['cards'][] = array(
			'text'  => $this->data['text_amex'],
			'value' => 'amex'
		);

		$this->data['cards'][] = array(
			'text'  => $this->data['text_maestro'],
			'value' => 'maestro'
		);

		if ($this->config->get('firstdata_remote_cards_accepted') && !$this->error) {
			$this->data['firstdata_remote_cards_accepted'] = $this->config->get('firstdata_remote_cards_accepted');
		} else {
			$this->data['firstdata_remote_cards_accepted'] =$this->request->post('firstdata_remote_cards_accepted', array());
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/firstdata_remote.tpl', $this->data));
	}

	public function orderAction() {
		if ($this->config->get('firstdata_remote_status')) {
			$this->load->model('payment/firstdata_remote');

			$firstdata_order = $this->model_payment_firstdata_remote->getOrder($this->request->get['order_id']);

			if (!empty($firstdata_order)) {
				$this->data = $this->load->language('payment/firstdata_remote');

				$firstdata_order['total_captured'] = $this->model_payment_firstdata_remote->getTotalCaptured($firstdata_order['firstdata_remote_order_id']);

				$firstdata_order['total_formatted'] = $this->currency->format($firstdata_order['total'], $firstdata_order['currency_code'], 1, true);
				$firstdata_order['total_captured_formatted'] = $this->currency->format($firstdata_order['total_captured'], $firstdata_order['currency_code'], 1, true);

				$this->data['order_id'] = $this->request->get['order_id'];
				$this->data['token'] = $this->request->get['token'];

				return $this->load->view('payment/firstdata_remote_order.tpl', $this->data);
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/firstdata_remote');

		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/firstdata_remote');

			$firstdata_order = $this->model_payment_firstdata_remote->getOrder($this->request->post['order_id']);

			$void_response = $this->model_payment_firstdata_remote->void($firstdata_order['order_ref'], $firstdata_order['tdate']);

			$this->model_payment_firstdata_remote->logger('Void result:\r\n' . print_r($void_response, 1));

			if (strtoupper($void_response['transaction_result']) == 'APPROVED') {
				$this->model_payment_firstdata_remote->addTransaction($firstdata_order['firstdata_remote_order_id'], 'void', 0.00);

				$this->model_payment_firstdata_remote->updateVoidStatus($firstdata_order['firstdata_remote_order_id'], 1);

				$json['msg'] = $this->data['text_void_ok'];
				$json['data'] = array();
				$json['data']['column_date_added'] = date('Y-m-d H:i:s');
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($void_response['error']) && !empty($void_response['error']) ? (string)$void_response['error'] : 'Unable to void';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function capture() {
		$this->data = $this->load->language('payment/firstdata');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/firstdata_remote');

			$firstdata_order = $this->model_payment_firstdata_remote->getOrder($this->request->post['order_id']);

			$capture_response = $this->model_payment_firstdata_remote->capture($firstdata_order['order_ref'], $firstdata_order['total'], $firstdata_order['currency_code']);

			$this->model_payment_firstdata_remote->logger('Settle result:\r\n' . print_r($capture_response, 1));

			if (strtoupper($capture_response['transaction_result']) == 'APPROVED') {
				$this->model_payment_firstdata_remote->addTransaction($firstdata_order['firstdata_remote_order_id'], 'payment', $firstdata_order['total']);
				$total_captured = $this->model_payment_firstdata_remote->getTotalCaptured($firstdata_order['firstdata_remote_order_id']);

				$this->model_payment_firstdata_remote->updateCaptureStatus($firstdata_order['firstdata_remote_order_id'], 1);
				$capture_status = 1;
				$json['msg'] = $this->data['text_capture_ok_order'];
				$json['data'] = array();
				$json['data']['column_date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = (float)$firstdata_order['total'];
				$json['data']['capture_status'] = $capture_status;
				$json['data']['total'] = (float)$total_captured;
				$json['data']['total_formatted'] = $this->currency->format($total_captured, $firstdata_order['currency_code'], 1, true);
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($capture_response['error']) && !empty($capture_response['error']) ? (string)$capture_response['error'] : 'Unable to capture';

			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function refund() {
		$this->data = $this->load->language('payment/firstdata_remote');

		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/firstdata_remote');

			$firstdata_order = $this->model_payment_firstdata_remote->getOrder($this->request->post['order_id']);

			$refund_response = $this->model_payment_firstdata_remote->refund($firstdata_order['order_ref'], $firstdata_order['total'], $firstdata_order['currency_code']);

			$this->model_payment_firstdata_remote->logger('Refund result:\r\n' . print_r($refund_response, 1));

			if (strtoupper($refund_response['transaction_result']) == 'APPROVED') {
				$this->model_payment_firstdata_remote->addTransaction($firstdata_order['firstdata_remote_order_id'], 'refund', $firstdata_order['total'] * -1);

				$total_refunded = $this->model_payment_firstdata_remote->getTotalRefunded($firstdata_order['firstdata_remote_order_id']);
				$total_captured = $this->model_payment_firstdata_remote->getTotalCaptured($firstdata_order['firstdata_remote_order_id']);

				if ($total_captured <= 0 && $firstdata_order['capture_status'] == 1) {
					$this->model_payment_firstdata_remote->updateRefundStatus($firstdata_order['firstdata_remote_order_id'], 1);
					$refund_status = 1;
					$json['msg'] = $this->data['text_refund_ok_order'];
				} else {
					$refund_status = 0;
					$json['msg'] = $this->data['text_refund_ok'];
				}

				$json['data'] = array();
				$json['data']['column_date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $firstdata_order['total'] * -1;
				$json['data']['total_captured'] = (float)$total_captured;
				$json['data']['total_refunded'] = (float)$total_refunded;
				$json['data']['refund_status'] = $refund_status;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($refund_response['error']) && !empty($refund_response['error']) ? (string)$refund_response['error'] : 'Unable to refund';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/firstdata_remote')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['firstdata_remote_merchant_id']) {
			$this->error['error_merchant_id'] = $this->data['error_merchant_id'];
		}

		if (!$this->request->post['firstdata_remote_user_id']) {
			$this->error['error_user_id'] = $this->data['error_user_id'];
		}

		if (!$this->request->post['firstdata_remote_password']) {
			$this->error['error_password'] = $this->data['error_password'];
		}

		if (!$this->request->post['firstdata_remote_certificate']) {
			$this->error['error_certificate'] = $this->data['error_certificate'];
		}

		if (!$this->request->post['firstdata_remote_key']) {
			$this->error['error_key'] = $this->data['error_key'];
		}

		if (!$this->request->post['firstdata_remote_key_pw']) {
			$this->error['error_key_pw'] = $this->data['error_key_pw'];
		}

		if (!$this->request->post['firstdata_remote_ca']) {
			$this->error['error_ca'] = $this->data['error_ca'];
		}

		return !$this->error;
	}
}