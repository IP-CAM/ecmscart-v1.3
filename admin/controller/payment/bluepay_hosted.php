<?php
class ControllerPaymentBluePayHosted extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/bluepay_hosted');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('bluepay_hosted', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_account_name'] =  (isset($this->error['account_name'])?$this->error['account_name']:'');

		$this->data['error_account_id'] =  (isset($this->error['account_id'])?$this->error['account_id']:'');

		$this->data['error_secret_key'] =  (isset($this->error['secret_key'])?$this->error['secret_key']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/bluepay_hosted', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('payment/bluepay_hosted', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['bluepay_hosted_account_name'] = $this->request->post('bluepay_hosted_account_name', $this->config->get('bluepay_hosted_account_name'));
		
		$this->data['bluepay_hosted_account_id'] = $this->request->post('bluepay_hosted_account_id', $this->config->get('bluepay_hosted_account_id'));
			
		$this->data['bluepay_hosted_secret_key'] = $this->request->post('bluepay_hosted_secret_key', $this->config->get('bluepay_hosted_secret_key'));

		$this->data['bluepay_hosted_test'] = $this->request->post('bluepay_hosted_test', $this->config->get('bluepay_hosted_test'));

		$this->data['bluepay_hosted_transaction'] = $this->request->post('bluepay_hosted_transaction', $this->config->get('bluepay_hosted_transaction'));

		$this->data['bluepay_hosted_amex'] = $this->request->post('bluepay_hosted_amex', $this->config->get('bluepay_hosted_amex'));

		$this->data['bluepay_hosted_discover'] = $this->request->post('bluepay_hosted_discover', $this->config->get('bluepay_hosted_discover'));

		$this->data['bluepay_hosted_total'] = $this->request->post('bluepay_hosted_total', $this->config->get('bluepay_hosted_total'));

		$this->data['bluepay_hosted_order_status_id'] = $this->request->post('bluepay_hosted_order_status_id', $this->config->get('bluepay_hosted_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['bluepay_hosted_geo_zone_id'] = $this->request->post('bluepay_hosted_geo_zone_id', $this->config->get('bluepay_hosted_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['bluepay_hosted_status'] = $this->request->post('bluepay_hosted_status', $this->config->get('bluepay_hosted_status'));

		$this->data['bluepay_hosted_debug'] = $this->request->post('bluepay_hosted_debug', $this->config->get('bluepay_hosted_debug'));
		
		$this->data['bluepay_hosted_sort_order'] = $this->request->post('bluepay_hosted_sort_order', $this->config->get('bluepay_hosted_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/bluepay_hosted.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/bluepay_hosted');
		
		$this->model_payment_bluepay_hosted->install();
	}

	public function uninstall() {
		$this->load->model('payment/bluepay_hosted');
		
		$this->model_payment_bluepay_hosted->uninstall();
	}

	public function orderAction() {
		if ($this->config->get('bluepay_hosted_status')) {
			$this->load->model('payment/bluepay_hosted');

			$bluepay_hosted_order = $this->model_payment_bluepay_hosted->getOrder($this->request->get['order_id']);

			if (!empty($bluepay_hosted_order)) {
				$this->data = $this->load->language('payment/bluepay_hosted');

				$bluepay_hosted_order['total_released'] = $this->model_payment_bluepay_hosted->getTotalReleased($bluepay_hosted_order['bluepay_hosted_order_id']);

				$bluepay_hosted_order['total_formatted'] = $this->currency->format($bluepay_hosted_order['total'], $bluepay_hosted_order['currency_code'], false, false);
				$bluepay_hosted_order['total_released_formatted'] = $this->currency->format($bluepay_hosted_order['total_released'], $bluepay_hosted_order['currency_code'], false, false);

				$this->data['bluepay_hosted_order'] = $bluepay_hosted_order;
				$this->data['order_id'] = $this->request->get['order_id'];
				$this->data['token'] = $this->request->get['token'];

				return $this->load->view('payment/bluepay_hosted_order.tpl', $this->data);
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/bluepay_hosted');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/bluepay_hosted');

			$bluepay_hosted_order = $this->model_payment_bluepay_hosted->getOrder($this->request->post['order_id']);

			$void_response = $this->model_payment_bluepay_hosted->void($this->request->post['order_id']);

			$this->model_payment_bluepay_hosted->logger('Void result:\r\n' . print_r($void_response, 1));

			if ($void_response['Result'] == 'APPROVED') {
				$this->model_payment_bluepay_hosted->addTransaction($bluepay_hosted_order['bluepay_hosted_order_id'], 'void', 0.00);
				$this->model_payment_bluepay_hosted->updateVoidStatus($bluepay_hosted_order['bluepay_hosted_order_id'], 1);

				$json['msg'] = $this->data['text_void_ok'];
				$json['data'] = array();
				$json['data']['column_date_added'] = date("Y-m-d H:i:s");
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($void_response['MESSAGE']) && !empty($void_response['MESSAGE']) ? (string)$void_response['MESSAGE'] : 'Unable to void';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function release() {
		$this->data = $this->load->language('payment/bluepay_hosted');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($this->request->post['amount']) && $this->request->post['amount'] > 0) {
			$this->load->model('payment/bluepay_hosted');

			$bluepay_hosted_order = $this->model_payment_bluepay_hosted->getOrder($this->request->post['order_id']);

			$release_response = $this->model_payment_bluepay_hosted->release($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_bluepay_hosted->logger('Release result:\r\n' . print_r($release_response, 1));

			if ($release_response['Result'] == 'APPROVED') {
				$this->model_payment_bluepay_hosted->addTransaction($bluepay_hosted_order['bluepay_hosted_order_id'], 'sale', $this->request->post['amount']);

				$total_released = $this->model_payment_bluepay_hosted->getTotalReleased($bluepay_hosted_order['bluepay_hosted_order_id']);

				if ($total_released >= $bluepay_hosted_order['total']) {
					$this->model_payment_bluepay_hosted->updateReleaseStatus($bluepay_hosted_order['bluepay_hosted_order_id'], 1);
					$release_status = 1;
					$json['msg'] = $this->data['text_release_ok_order'];
				} else {
					$release_status = 0;
					$json['msg'] = $this->data['text_release_ok'];
				}

				$json['data'] = array();
				$json['data']['column_date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->request->post['amount'];
				$json['data']['release_status'] = $release_status;
				$json['data']['total'] = (float)$total_released;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($release_response['MESSAGE']) && !empty($release_response['MESSAGE']) ? (string)$release_response['MESSAGE'] : 'Unable to release';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = $this->data['error_data_missing'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function rebate() {
		$this->data = $this->load->language('payment/bluepay_hosted');
		$json = array();

		if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])) {
			$this->load->model('payment/bluepay_hosted');

			$bluepay_hosted_order = $this->model_payment_bluepay_hosted->getOrder($this->request->post['order_id']);

			$rebate_response = $this->model_payment_bluepay_hosted->rebate($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_bluepay_hosted->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

			if ($rebate_response['Result'] == 'APPROVED') {
				$this->model_payment_bluepay_hosted->addTransaction($bluepay_hosted_order['bluepay_hosted_order_id'], 'rebate', $this->request->post['amount'] * -1);

				$total_rebated = $this->model_payment_bluepay_hosted->getTotalRebated($bluepay_hosted_order['bluepay_hosted_order_id']);
				$total_released = $this->model_payment_bluepay_hosted->getTotalReleased($bluepay_hosted_order['bluepay_hosted_order_id']);

				if ($total_released <= 0 && $bluepay_hosted_order['release_status'] == 1) {
					$this->model_payment_bluepay_hosted->updateRebateStatus($bluepay_hosted_order['bluepay_hosted_order_id'], 1);
					$rebate_status = 1;
					$json['msg'] = $this->data['text_rebate_ok_order'];
				} else {
					$rebate_status = 0;
					$json['msg'] = $this->data['text_rebate_ok'];
				}

				$json['data'] = array();
				$json['data']['column_date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->request->post['amount'] * -1;
				$json['data']['total_released'] = (float)$total_released;
				$json['data']['total_rebated'] = (float)$total_rebated;
				$json['data']['rebate_status'] = $rebate_status;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($rebate_response['MESSAGE']) && !empty($rebate_response['MESSAGE']) ? (string)$rebate_response['MESSAGE'] : 'Unable to rebate';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/bluepay_hosted')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['bluepay_hosted_account_name']) {
			$this->error['account_name'] = $this->data['error_account_name'];
		}

		if (!$this->request->post['bluepay_hosted_account_id']) {
			$this->error['account_id'] = $this->data['error_account_id'];
		}

		if (!$this->request->post['bluepay_hosted_secret_key']) {
			$this->error['secret_key'] = $this->data['error_secret_key'];
		}

		return !$this->error;
	}

	public function callback() {
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($this->request->get));
	}
}