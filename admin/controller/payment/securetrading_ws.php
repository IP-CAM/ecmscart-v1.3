<?php

class ControllerPaymentSecureTradingWs extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('setting/setting');
		$this->load->model('localisation/geo_zone');
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/currency');
		$this->data = $this->load->language('payment/securetrading_ws');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->request->post['securetrading_ws_site_reference'] = trim($this->request->post['securetrading_ws_site_reference']);
			$this->request->post['securetrading_ws_username'] = trim($this->request->post['securetrading_ws_username']);

			$this->model_setting_setting->editSetting('securetrading_ws', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['securetrading_ws_site_reference'] = $this->request->post('securetrading_ws_site_reference',$this->config->get('securetrading_ws_site_reference'));
		
		$this->data['securetrading_ws_username'] = $this->request->post('securetrading_ws_username',$this->config->get('securetrading_ws_username'));	

		$this->data['securetrading_ws_password'] = $this->request->post('securetrading_ws_password',$this->config->get('securetrading_ws_password'));

		$this->data['securetrading_ws_csv_username'] = $this->request->post('securetrading_ws_csv_username',$this->config->get('securetrading_ws_csv_username'));

		$this->data['securetrading_ws_csv_password'] = $this->request->post('securetrading_ws_csv_password',$this->config->get('securetrading_ws_csv_password'));

		$this->config->set('securetrading_ws_3d_secure', 1);
		
		$this->data['securetrading_ws_3d_secure'] = $this->request->post('securetrading_ws_3d_secure',$this->config->get('securetrading_ws_3d_secure'));

		if (isset($this->request->post['securetrading_ws_cards_accepted'])) {
			$this->data['securetrading_ws_cards_accepted'] = $this->request->post['securetrading_ws_cards_accepted'];
		} else {
			$this->data['securetrading_ws_cards_accepted'] = $this->config->get('securetrading_ws_cards_accepted');

			if ($this->data['securetrading_ws_cards_accepted'] == null) {
				$this->data['securetrading_ws_cards_accepted'] = array();
			}
		}

		if ($this->config->get('securetrading_ws_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_ws_order_status_id'] = $this->config->get('securetrading_ws_order_status_id');
		} else {
			$this->data['securetrading_ws_order_status_id'] =$this->request->post('securetrading_ws_order_status_id', 1);
		}

		if ($this->config->get('securetrading_ws_failed_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_ws_failed_order_status_id'] = $this->config->get('securetrading_ws_failed_order_status_id');
		} else {
			$this->data['securetrading_ws_failed_order_status_id'] = $this->request->post('securetrading_ws_failed_order_status_id',10);
		}

		if ($this->config->get('securetrading_ws_declined_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_ws_declined_order_status_id'] = $this->config->get('securetrading_ws_declined_order_status_id');
		} else {
			$this->data['securetrading_ws_declined_order_status_id'] = $this->request->post('securetrading_ws_declined_order_status_id',8);
		}

		if ($this->config->get('securetrading_ws_refunded_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_ws_refunded_order_status_id'] = $this->config->get('securetrading_ws_refunded_order_status_id');
		} else {
			$this->data['securetrading_ws_refunded_order_status_id'] = $this->request->post('securetrading_ws_refunded_order_status_id',11);
		}

		if ($this->config->get('securetrading_ws_authorisation_reversed_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_ws_authorisation_reversed_order_status_id'] = $this->config->get('securetrading_ws_authorisation_reversed_order_status_id');
		} else {
			$this->data['securetrading_ws_authorisation_reversed_order_status_id'] =$this->request->post('securetrading_ws_authorisation_reversed_order_status_id', 12);
		}

		$this->data['securetrading_ws_settle_status'] = $this->request->post('securetrading_ws_settle_status',$this->config->get('securetrading_ws_settle_status'));
		
		$this->data['securetrading_ws_settle_due_date'] = $this->request->post('securetrading_ws_settle_due_date',$this->config->get('securetrading_ws_settle_due_date'));	
		
		$this->data['securetrading_ws_geo_zone'] = $this->request->post('securetrading_ws_geo_zone',$this->config->get('securetrading_ws_geo_zone'));
		
		$this->data['securetrading_ws_status'] = $this->request->post('securetrading_ws_status',$this->config->get('securetrading_ws_status'));
		
		$this->data['securetrading_ws_sort_order'] = $this->request->post('securetrading_ws_sort_order',$this->config->get('securetrading_ws_sort_order'));
		
		$this->data['securetrading_ws_total'] = $this->request->post('securetrading_ws_total',$this->config->get('securetrading_ws_total'));
		
		$this->document->setTitle($this->data['heading_title']);

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_site_reference'] =  (isset($this->error['site_reference'])?$this->error['site_reference']:'');

		$this->data['error_username'] =  (isset($this->error['username'])?$this->error['username']:'');

		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');

		$this->data['error_cards_accepted'] =  (isset($this->error['cards_accepted'])?$this->error['cards_accepted']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/securetrading_ws', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['cards'] = array(
			'AMEX' => 'American Express',
			'VISA' => 'Visa',
			'DELTA' => 'Visa Debit',
			'ELECTRON' => 'Visa Electron',
			'PURCHASING' => 'Visa Purchasing',
			'VPAY' => 'V Pay',
			'MASTERCARD' => 'MasterCard',
			'MASTERCARDDEBIT' => 'MasterCard Debit',
			'MAESTRO' => 'Maestro',
			'PAYPAL' => 'PayPal',
		);

		$this->data['settlement_statuses'] = array(
			'0' => $this->data['text_pending_settlement'],
			'1' => $this->data['text_pending_settlement_manually_overriden'],
			'2' => $this->data['text_pending_suspended'],
			'100' => $this->data['text_pending_settled'],
		);

		$this->data['action'] = $this->url->link('payment/securetrading_ws', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token']);

		$this->data['myst_status'] = !empty($this->data['securetrading_ws_csv_username']) && !empty($this->data['securetrading_ws_csv_password']);
		$this->data['hours'] = array();

		for ($i = 0; $i < 24; $i++) {
			$this->data['hours'][] = str_pad($i, 2, '0', STR_PAD_LEFT);
		}

		$this->data['minutes'] = array();

		for ($i = 0; $i < 60; $i++) {
			$this->data['minutes'][] = str_pad($i, 2, '0', STR_PAD_LEFT);
		}

		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/securetrading_ws.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/securetrading_ws');
		$this->model_payment_securetrading_ws->install();
	}

	public function uninstall() {
		$this->load->model('payment/securetrading_ws');
		$this->model_payment_securetrading_ws->uninstall();
	}

	public function downloadTransactions() {
		$this->load->model('payment/securetrading_ws');
		$this->data = $this->load->language('payment/securetrading_ws');

		$csv_data = $this->request->post;
		$csv_data['detail'] = true;

		$response = $this->model_payment_securetrading_ws->getCsv($csv_data);

		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $this->data['text_transactions'] . '.csv"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . strlen($response));

		if (ob_get_level()) {
			ob_end_clean();
		}

		echo $response;
		exit();
	}

	public function showTransactions() {
		$this->load->model('payment/securetrading_ws');
		$this->data = $this->load->language('payment/securetrading_ws');

		$csv_data = $this->request->post;
		$csv_data['detail'] = false;

		$response = $this->model_payment_securetrading_ws->getCsv($csv_data);

		$this->data['transactions'] = array();

		$status_mapping = array(
			'0' => $this->data['text_ok'],
			'70000' => $this->data['text_denied'],
		);

		$settle_status_mapping = array(
			'0' => $this->data['text_pending_settlement'],
			'1' => $this->data['text_manual_settlement'],
			'2' => $this->data['text_suspended'],
			'3' => $this->data['text_cancelled'],
			'10' => $this->data['text_settling'],
			'100' => $this->data['text_settled'],
		);

		if ($response) {
			$lines = array_filter(explode("\n", $response));

			$csv = array();
			$keys = str_getcsv($lines[0]);

			for ($i = 1; $i < count($lines); $i++) {
				$csv[] = array_combine($keys, str_getcsv($lines[$i]));
			}

			foreach ($csv as $row) {
				$this->data['transactions'][] = array(
					'order_id' => $row['orderreference'],
					'order_href' => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $row['orderreference'], 'SSL'),
					'transaction_reference' => $row['transactionreference'],
					'customer' => $row['billingfirstname'] . ' ' . $row['billinglastname'],
					'total' => $row['mainamount'],
					'currency' => $row['currencyiso3a'],
					'settle_status' => $settle_status_mapping[$row['settlestatus']],
					'status' => $status_mapping[$row['errorcode']],
					'type' => $row['requesttypedescription'],
					'payment_type' => $row['paymenttypedescription'],
				);
			}
		}

		$this->template = 'payment/securetrading_ws_transactions.tpl';

		$this->response->setOutput($this->render());
	}

	public function orderAction() {

		if ($this->config->get('securetrading_ws_status')) {
			$this->load->model('payment/securetrading_ws');

			$securetrading_ws_order = $this->model_payment_securetrading_ws->getOrder($this->request->get['order_id']);

			if (!empty($securetrading_ws_order)) {
				$this->data = $this->load->language('payment/securetrading_ws');

				$securetrading_ws_order['total_released'] = $this->model_payment_securetrading_ws->getTotalReleased($securetrading_ws_order['securetrading_ws_order_id']);

				$securetrading_ws_order['total_formatted'] = $this->currency->format($securetrading_ws_order['total'], $securetrading_ws_order['currency_code'], false, false);
				$securetrading_ws_order['total_released_formatted'] = $this->currency->format($securetrading_ws_order['total_released'], $securetrading_ws_order['currency_code'], false, false);

				$this->data['securetrading_ws_order'] = $securetrading_ws_order;

				$this->data['auto_settle'] = $securetrading_ws_order['settle_type'];

				$this->data['order_id'] = $this->request->get['order_id'];
				
				$this->data['token'] = $this->request->get['token'];

				$this->template = 'payment/securetrading_ws_order.tpl';

				$this->response->setOutput($this->render());
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/securetrading_ws');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/securetrading_ws');

			$securetrading_ws_order = $this->model_payment_securetrading_ws->getOrder($this->request->post['order_id']);

			$void_response = $this->model_payment_securetrading_ws->void($this->request->post['order_id']);

			$this->model_payment_securetrading_ws->logger('Void result:\r\n' . print_r($void_response, 1));

			if ($void_response !== False) {
				$response_xml = simplexml_load_string($void_response);

				if ($response_xml->response['type'] == 'ERROR' || (string)$response_xml->response->error->code != '0') {
					$json['msg'] = (string)$response_xml->response->error->message;
					$json['error'] = true;
				} else {

					$this->model_payment_securetrading_ws->addTransaction($securetrading_ws_order['securetrading_ws_order_id'], 'reversed', 0.00);
					$this->model_payment_securetrading_ws->updateVoidStatus($securetrading_ws_order['securetrading_ws_order_id'], 1);

					$this->data = array(
						'order_status_id' => $this->config->get('securetrading_ws_authorisation_reversed_order_status_id'),
						'notify' => False,
						'comment' => '',
					);

					$this->load->model('sale/order');

					$this->model_sale_order->addOrderHistory($this->request->post['order_id'], $this->data);

					$json['msg'] = $this->data['text_authorisation_reversed'];
					$json['data']['created'] = date("Y-m-d H:i:s");
					$json['error'] = false;
				}
			} else {
				$json['msg'] = $this->data['error_connection'];
				$json['error'] = true;
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->setOutput(json_encode($json));
	}

	public function release() {
		$this->data = $this->load->language('payment/securetrading_ws');
		$json = array();

		$amount = number_format($this->request->post['amount'], 2);

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($amount) && $amount > 0) {
			$this->load->model('payment/securetrading_ws');

			$securetrading_ws_order = $this->model_payment_securetrading_ws->getOrder($this->request->post['order_id']);

			$release_response = $this->model_payment_securetrading_ws->release($this->request->post['order_id'], $amount);

			$this->model_payment_securetrading_ws->logger('Release result:\r\n' . print_r($release_response, 1));

			if ($release_response !== False) {
				$response_xml = simplexml_load_string($release_response);

				if ($response_xml->response['type'] == 'ERROR' || (string)$response_xml->response->error->code != '0') {
					$json['error'] = true;
					$json['msg'] = (string)$response_xml->response->error->message;
				} else {
					$this->model_payment_securetrading_ws->addTransaction($securetrading_ws_order['securetrading_ws_order_id'], 'payment', $amount);

					$total_released = $this->model_payment_securetrading_ws->getTotalReleased($securetrading_ws_order['securetrading_ws_order_id']);

					if ($total_released >= $securetrading_ws_order['total'] || $securetrading_ws_order['settle_type'] == 100) {
						$this->model_payment_securetrading_ws->updateReleaseStatus($securetrading_ws_order['securetrading_ws_order_id'], 1);
						$release_status = 1;
						$json['msg'] = $this->data['text_release_ok_order'];

						$this->load->model('sale/order');

						$history = array();
						$history['order_status_id'] = $this->config->get('securetrading_ws_order_status_success_settled_id');
						$history['comment'] = '';
						$history['notify'] = '';

						$this->model_sale_order->addOrderHistory($this->request->post['order_id'], $history);
					} else {
						$release_status = 0;
						$json['msg'] = $this->data['text_release_ok'];
					}

					$json['data'] = array();
					$json['data']['created'] = date("Y-m-d H:i:s");
					$json['data']['amount'] = $amount;
					$json['data']['release_status'] = $release_status;
					$json['data']['total'] = (double)$total_released;
					$json['error'] = false;
				}
			} else {
				$json['error'] = true;
				$json['msg'] = $this->data['error_connection'];
			}
		} else {
			$json['error'] = true;
			$json['msg'] = $this->data['error_data_missing'];
		}

		$this->response->setOutput(json_encode($json));
	}

	public function rebate() {
		$this->data = $this->load->language('payment/securetrading_ws');
		$json = array();

		if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])) {
			$this->load->model('payment/securetrading_ws');

			$securetrading_ws_order = $this->model_payment_securetrading_ws->getOrder($this->request->post['order_id']);

			$amount = number_format($this->request->post['amount'], 2);

			$rebate_response = $this->model_payment_securetrading_ws->rebate($this->request->post['order_id'], $amount);

			$this->model_payment_securetrading_ws->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

			if ($rebate_response !== False) {
				$response_xml = simplexml_load_string($rebate_response);

				$error_code = (string)$response_xml->response->error->code;

				if ($error_code == '0') {

					$this->model_payment_securetrading_ws->addTransaction($securetrading_ws_order['securetrading_ws_order_id'], 'rebate', $amount * -1);

					$total_rebated = $this->model_payment_securetrading_ws->getTotalRebated($securetrading_ws_order['securetrading_ws_order_id']);
					$total_released = $this->model_payment_securetrading_ws->getTotalReleased($securetrading_ws_order['securetrading_ws_order_id']);

					if ($total_released <= 0 && $securetrading_ws_order['release_status'] == 1) {
						$json['status'] = 1;
						$json['message'] = $this->data['text_refund_issued'];


						$this->model_payment_securetrading_ws->updateRebateStatus($securetrading_ws_order['securetrading_ws_order_id'], 1);
						$rebate_status = 1;
						$json['msg'] = $this->data['text_rebate_ok_order'];

						$this->load->model('sale/order');

						$history = array();
						$history['order_status_id'] = $this->config->get('securetrading_ws_refunded_order_status_id');
						$history['comment'] = '';
						$history['notify'] = '';

						$this->model_sale_order->addOrderHistory($this->request->post['order_id'], $history);
					} else {
						$rebate_status = 0;
						$json['msg'] = $this->data['text_rebate_ok'];
					}

					$json['data'] = array();
					$json['data']['created'] = date("Y-m-d H:i:s");
					$json['data']['amount'] = $amount * -1;
					$json['data']['total_released'] = (double)$total_released;
					$json['data']['total_rebated'] = (double)$total_rebated;
					$json['data']['rebate_status'] = $rebate_status;
					$json['error'] = false;
				} else {
					$json['error'] = true;
					$json['msg'] = (string)$response_xml->response->error->message;
				}
			} else {
				$json['status'] = 0;
				$json['message'] = $this->data['error_connection'];
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/securetrading_pp')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['securetrading_ws_site_reference']) {
			$this->error['site_reference'] = $this->data['error_site_reference'];
		}

		if (!$this->request->post['securetrading_ws_username']) {
			$this->error['username'] = $this->data['error_username'];
		}

		if (!$this->request->post['securetrading_ws_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		if (empty($this->request->post['securetrading_ws_cards_accepted'])) {
			$this->error['cards_accepted'] = $this->data['error_cards_accepted'];
		}

		return !$this->error;
	}

//	protected function validate() {
//		$this->load->model('localisation/currency');
//
//		if (!$this->user->hasPermission('modify', 'payment/securetrading_ws')) {
//			$this->errors[] = $this->data['error_permission');
//		}
//
//		if (empty($this->request->post['securetrading_ws_site_reference'])) {
//			$this->errors[] = $this->data['error_site_reference');
//		}
//
//		if (empty($this->request->post['securetrading_ws_username'])) {
//			$this->errors[] = $this->data['error_username');
//		}
//
//		if (empty($this->request->post['securetrading_ws_password'])) {
//			$this->errors[] = $this->data['error_password');
//		}
//
//		if (empty($this->request->post['securetrading_ws_cards_accepted'])) {
//			$this->errors[] = $this->data['error_cards_accepted');
//		}
//
//		return empty($this->errors);
//	}
}
