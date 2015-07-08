<?php
class ControllerPaymentSecureTradingPp extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('setting/setting');
		$this->load->model('localisation/geo_zone');
		$this->load->model('localisation/order_status');
		$this->data = $this->load->language('payment/securetrading_pp');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->request->post['securetrading_pp_site_reference'] = trim($this->request->post['securetrading_pp_site_reference']);

			$this->model_setting_setting->editSetting('securetrading_pp', $this->request->post);

			$this->session->data['success'] =$this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['securetrading_pp_site_reference'] = $this->request->post('securetrading_pp_site_reference',$this->config->get('securetrading_pp_site_reference'));
		
		$this->data['securetrading_pp_username'] = $this->request->post('securetrading_pp_username',$this->config->get('securetrading_pp_username'));	
		
		$this->data['securetrading_pp_password'] = $this->request->post('securetrading_pp_password',$this->config->get('securetrading_pp_password'));
		
		$this->data['securetrading_pp_notification_password'] = $this->request->post('securetrading_pp_notification_password',$this->config->get('securetrading_pp_notification_password'));
		
		$this->data['securetrading_pp_site_security_password'] = $this->request->post('securetrading_pp_site_security_password',$this->config->get('securetrading_pp_site_security_password'));
		
		$this->data['securetrading_pp_site_security_status'] = $this->request->post('securetrading_pp_site_security_status',$this->config->get('securetrading_pp_site_security_status'));
		
		$this->data['securetrading_pp_site_security_password'] = $this->request->post('securetrading_pp_site_security_password',$this->config->get('securetrading_pp_site_security_password'));
		
		$this->data['securetrading_pp_site_security_status'] = $this->request->post('securetrading_pp_site_security_status',$this->config->get('securetrading_pp_site_security_status'));
		
		$this->data['securetrading_pp_webservice_username'] = $this->request->post('securetrading_pp_webservice_username',$this->config->get('securetrading_pp_webservice_username'));
		
		$this->data['securetrading_pp_webservice_password'] = $this->request->post('securetrading_pp_webservice_password',$this->config->get('securetrading_pp_webservice_password'));
		
		if($this->config->get('securetrading_pp_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_pp_order_status_id'] = $this->config->get('securetrading_pp_order_status_id');
		} else {
			$this->data['securetrading_pp_order_status_id'] = $this->request->post('securetrading_pp_order_status_id',1);
		}

		if($this->config->get('securetrading_pp_declined_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_pp_declined_order_status_id'] = $this->config->get('securetrading_pp_declined_order_status_id');
		} else {
			$this->data['securetrading_pp_declined_order_status_id'] = $this->request->post('securetrading_pp_declined_order_status_id',8);
		}

		if($this->config->get('securetrading_pp_refunded_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_pp_refunded_order_status_id'] = $this->config->get('securetrading_pp_refunded_order_status_id');
		} else {
			$this->data['securetrading_pp_refunded_order_status_id'] = $this->request->post('securetrading_pp_refunded_order_status_id',11);
		}

		if($this->config->get('securetrading_pp_authorisation_reversed_order_status_id') != '' && !$this->error) {
			$this->data['securetrading_pp_authorisation_reversed_order_status_id'] = $this->config->get('securetrading_pp_authorisation_reversed_order_status_id');
		} else {
			$this->data['securetrading_pp_authorisation_reversed_order_status_id'] = $this->request->post('securetrading_pp_authorisation_reversed_order_status_id',12);
		}

		$this->data['securetrading_pp_settle_status'] = $this->request->post('securetrading_pp_settle_status',$this->config->get('securetrading_pp_settle_status'));
		
		$this->data['securetrading_pp_settle_due_date'] = $this->request->post('securetrading_pp_settle_due_date',$this->config->get('securetrading_pp_settle_due_date'));

		$this->data['securetrading_pp_geo_zone_id'] = $this->request->post('securetrading_pp_geo_zone_id',$this->config->get('securetrading_pp_geo_zone_id'));

		$this->data['securetrading_pp_status'] = $this->request->post('securetrading_pp_status',$this->config->get('securetrading_pp_status'));

		$this->data['securetrading_pp_sort_order'] = $this->request->post('securetrading_pp_sort_order',$this->config->get('securetrading_pp_sort_order'));

		$this->data['securetrading_pp_total'] = $this->request->post('securetrading_pp_total',$this->config->get('securetrading_pp_total'));

		$this->data['securetrading_pp_parent_css'] = $this->request->post('securetrading_pp_parent_css',$this->config->get('securetrading_pp_parent_css'));

		$this->data['securetrading_pp_child_css'] = $this->request->post('securetrading_pp_child_css',$this->config->get('securetrading_pp_child_css'));

		if (isset($this->request->post['securetrading_pp_cards_accepted'])) {
			$this->data['securetrading_pp_cards_accepted'] = $this->request->post['securetrading_pp_cards_accepted'];
		} else {
			$this->data['securetrading_pp_cards_accepted'] = $this->config->get('securetrading_pp_cards_accepted');

			if ($this->data['securetrading_pp_cards_accepted'] == null) {
				$this->data['securetrading_pp_cards_accepted'] = array();
			}
		}

		$this->document->setTitle($this->data['heading_title']);

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_site_reference'] =  (isset($this->error['site_reference'])?$this->error['site_reference']:'');

		$this->data['error_cards_accepted'] =  (isset($this->error['cards_accepted'])?$this->error['cards_accepted']:'');
		
		$this->data['notification_password'] =  (isset($this->error['notification_password'])?$this->error['notification_password']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/securetrading_pp', 'token=' . $this->session->data['token'], 'SSL')
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
			'0' =>$this->data['text_pending_settlement'],
			'1' =>$this->data['text_pending_settlement_manually_overriden'],
			'2' =>$this->data['text_pending_suspended'],
			'100' =>$this->data['text_pending_settled'],
		);

		$this->data['action'] = $this->url->link('payment/securetrading_pp', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token']);

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/securetrading_pp.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/securetrading_pp');
		$this->model_payment_securetrading_pp->install();
	}

	public function uninstall() {
		$this->load->model('payment/securetrading_pp');
		$this->model_payment_securetrading_pp->uninstall();
	}

	public function orderAction() {

		if ($this->config->get('securetrading_pp_status')) {
			$this->load->model('payment/securetrading_pp');

			$securetrading_pp_order = $this->model_payment_securetrading_pp->getOrder($this->request->get['order_id']);

			if (!empty($securetrading_pp_order)) {
				$this->data = $this->load->language('payment/securetrading_pp');

				$securetrading_pp_order['total_released'] = $this->model_payment_securetrading_pp->getTotalReleased($securetrading_pp_order['securetrading_pp_order_id']);

				$securetrading_pp_order['total_formatted'] = $this->currency->format($securetrading_pp_order['total'], $securetrading_pp_order['currency_code'], false, false);
				$securetrading_pp_order['total_released_formatted'] = $this->currency->format($securetrading_pp_order['total_released'], $securetrading_pp_order['currency_code'], false, false);

				$this->data['securetrading_pp_order'] = $securetrading_pp_order;

				$this->data['auto_settle'] = $securetrading_pp_order['settle_type'];

				$this->data['order_id'] = $this->request->get['order_id'];
				
				$this->data['token'] = $this->request->get['token'];

				$this->template = 'payment/securetrading_pp_order.tpl';

				$this->response->setOutput($this->render());
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/securetrading_pp');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/securetrading_pp');

			$securetrading_pp_order = $this->model_payment_securetrading_pp->getOrder($this->request->post['order_id']);

			$void_response = $this->model_payment_securetrading_pp->void($this->request->post['order_id']);

			$this->model_payment_securetrading_pp->logger('Void result:\r\n' . print_r($void_response, 1));

			if ($void_response !== False) {
				$response_xml = simplexml_load_string($void_response);

				if ($response_xml->response['type'] == 'ERROR' || (string)$response_xml->response->error->code != '0') {
					$json['msg'] = (string)$response_xml->response->error->message;
					$json['error'] = true;
				} else {

					$this->model_payment_securetrading_pp->addTransaction($securetrading_pp_order['securetrading_pp_order_id'], 'reversed', 0.00);
					$this->model_payment_securetrading_pp->updateVoidStatus($securetrading_pp_order['securetrading_pp_order_id'], 1);

					$this->data = array(
						'order_status_id' => $this->config->get('securetrading_pp_authorisation_reversed_order_status_id'),
						'notify' => False,
						'comment' => '',
					);

					$this->load->model('sale/order');

					$this->model_sale_order->addOrderHistory($this->request->post['order_id'], $this->data);

					$json['msg'] =$this->data['text_authorisation_reversed'];
					$json['data']['created'] = date("Y-m-d H:i:s");
					$json['error'] = false;
				}
			} else {
				$json['msg'] =$this->data['error_connection'];
				$json['error'] = true;
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->setOutput(json_encode($json));
	}

	public function release() {
		$this->data = $this->load->language('payment/securetrading_pp');
		$json = array();

		$amount = number_format($this->request->post['amount'], 2);

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($amount) && $amount > 0) {
			$this->load->model('payment/securetrading_pp');

			$securetrading_pp_order = $this->model_payment_securetrading_pp->getOrder($this->request->post['order_id']);

			$release_response = $this->model_payment_securetrading_pp->release($this->request->post['order_id'], $amount);

			$this->model_payment_securetrading_pp->logger('Release result:\r\n' . print_r($release_response, 1));

			if ($release_response !== False) {
				$response_xml = simplexml_load_string($release_response);

				if ($response_xml->response['type'] == 'ERROR' || (string)$response_xml->response->error->code != '0') {
					$json['error'] = true;
					$json['msg'] = (string)$response_xml->response->error->message;
				} else {
					$this->model_payment_securetrading_pp->addTransaction($securetrading_pp_order['securetrading_pp_order_id'], 'payment', $amount);

					$total_released = $this->model_payment_securetrading_pp->getTotalReleased($securetrading_pp_order['securetrading_pp_order_id']);

					if ($total_released >= $securetrading_pp_order['total'] || $securetrading_pp_order['settle_type'] == 100) {
						$this->model_payment_securetrading_pp->updateReleaseStatus($securetrading_pp_order['securetrading_pp_order_id'], 1);
						$release_status = 1;
						$json['msg'] =$this->data['text_release_ok_order'];

						$this->load->model('sale/order');

						$history = array();
						$history['order_status_id'] = $this->config->get('securetrading_pp_order_status_success_settled_id');
						$history['comment'] = '';
						$history['notify'] = '';

						$this->model_sale_order->addOrderHistory($this->request->post['order_id'], $history);
					} else {
						$release_status = 0;
						$json['msg'] =$this->data['text_release_ok'];
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
				$json['msg'] =$this->data['error_connection'];
			}
		} else {
			$json['error'] = true;
			$json['msg'] =$this->data['error_data_missing'];
		}

		$this->response->setOutput(json_encode($json));
	}

	public function rebate() {
		$this->data = $this->load->language('payment/securetrading_pp');
		$json = array();

		if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])) {
			$this->load->model('payment/securetrading_pp');

			$securetrading_pp_order = $this->model_payment_securetrading_pp->getOrder($this->request->post['order_id']);

			$amount = number_format($this->request->post['amount'], 2);

			$rebate_response = $this->model_payment_securetrading_pp->rebate($this->request->post['order_id'], $amount);

			$this->model_payment_securetrading_pp->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

			if ($rebate_response !== False) {
				$response_xml = simplexml_load_string($rebate_response);

				$error_code = (string)$response_xml->response->error->code;

				if ($error_code == '0') {

					$this->model_payment_securetrading_pp->addTransaction($securetrading_pp_order['securetrading_pp_order_id'], 'rebate', $amount * -1);

					$total_rebated = $this->model_payment_securetrading_pp->getTotalRebated($securetrading_pp_order['securetrading_pp_order_id']);
					$total_released = $this->model_payment_securetrading_pp->getTotalReleased($securetrading_pp_order['securetrading_pp_order_id']);

					if ($total_released <= 0 && $securetrading_pp_order['release_status'] == 1) {
						$json['status'] = 1;
						$json['message'] =$this->data['text_refund_issued'];


						$this->model_payment_securetrading_pp->updateRebateStatus($securetrading_pp_order['securetrading_pp_order_id'], 1);
						$rebate_status = 1;
						$json['msg'] =$this->data['text_rebate_ok_order'];

						$this->load->model('sale/order');

						$history = array();
						$history['order_status_id'] = $this->config->get('securetrading_pp_refunded_order_status_id');
						$history['comment'] = '';
						$history['notify'] = '';

						$this->model_sale_order->addOrderHistory($this->request->post['order_id'], $history);
					} else {
						$rebate_status = 0;
						$json['msg'] =$this->data['text_rebate_ok'];
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
				$json['message'] =$this->data['error_connection'];
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/securetrading_pp')) {
			$this->error['warning'] =$this->data['error_permission'];
		}

		if (!$this->request->post['securetrading_pp_site_reference']) {
			$this->error['site_reference'] =$this->data['error_site_reference'];
		}

		if (empty($this->request->post['securetrading_pp_cards_accepted'])) {
			$this->error['cards_accepted'] =$this->data['error_cards_accepted'];
		}

		if (!$this->request->post['securetrading_pp_notification_password']) {
			$this->error['notification_password'] =$this->data['error_notification_password'];
		}

		return !$this->error;
	}


//	protected function validate() {
//		$this->load->model('localisation/currency');
//
//		if (!$this->user->hasPermission('modify', 'payment/securetrading_pp')) {
//			$this->errors[] =$this->data['error_permission');
//		}
//
//		if (empty($this->request->post['securetrading_pp_site_reference'])) {
//			$this->errors[] =$this->data['error_site_reference');
//		}
//		if (empty($this->request->post['securetrading_pp_cards_accepted'])) {
//			$this->errors[] =$this->data['error_cards_accepted');
//		}
//		if (empty($this->request->post['securetrading_pp_notification_password'])) {
//			$this->errors[] =$this->data['error_notification_password');
//		}
//
//		return empty($this->errors);
//	}

}
