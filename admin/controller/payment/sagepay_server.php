<?php
class ControllerPaymentSagepayServer extends Controller {
	private $error = array();

	public function index() {

		$this->data = $this->load->language('payment/sagepay_server');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('sagepay_server', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_vendor'] =  (isset($this->error['vendor'])?$this->error['vendor']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/sagepay_server', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('payment/sagepay_server', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['sagepay_server_vendor'] = $this->request->post('sagepay_server_vendor',$this->config->get('sagepay_server_vendor'));
		
		$this->data['sagepay_server_password'] = $this->request->post('sagepay_server_password',$this->config->get('sagepay_server_password'));	
		
		$this->data['sagepay_server_test'] = $this->request->post('sagepay_server_test',$this->config->get('sagepay_server_test'));
		
		$this->data['sagepay_server_transaction'] = $this->request->post('sagepay_server_transaction',$this->config->get('sagepay_server_transaction'));
		
		$this->data['sagepay_server_total'] = $this->request->post('sagepay_server_total',$this->config->get('sagepay_server_total'));
		
		$this->data['sagepay_server_card'] = $this->request->post('sagepay_server_card',$this->config->get('sagepay_server_card'));
		
		$this->data['sagepay_server_order_status_id'] = $this->request->post('sagepay_server_order_status_id',$this->config->get('sagepay_server_order_status_id'));
		
		if ($this->config->get('sagepay_server_cron_job_token')  && !$this->error) {
			$this->data['sagepay_server_cron_job_token'] = $this->config->get('sagepay_server_cron_job_token');
		} else {
			$this->data['sagepay_server_cron_job_token'] = $this->request->post('sagepay_server_cron_job_token',sha1(uniqid(mt_rand(), 1)));
		}

		$this->data['sagepay_server_cron_job_url'] = HTTPS_CATALOG . 'index.php?route=payment/sagepay_server/cron&token=' . $this->data['sagepay_server_cron_job_token'];
		
		$this->data['sagepay_server_last_cron_job_run'] = $this->config->get('sagepay_server_last_cron_job_run')?  $this->config->get('sagepay_server_last_cron_job_run'):'';
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['sagepay_server_geo_zone_id'] = $this->request->post('sagepay_server_geo_zone_id',$this->config->get('sagepay_server_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['sagepay_server_status'] = $this->request->post('sagepay_server_status',$this->config->get('sagepay_server_status'));

		$this->data['sagepay_server_debug'] = $this->request->post('sagepay_server_debug',$this->config->get('sagepay_server_debug'));

		$this->data['sagepay_server_sort_order'] = $this->request->post('sagepay_server_sort_order',$this->config->get('sagepay_server_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/sagepay_server.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/sagepay_server');
		$this->model_payment_sagepay_server->install();
	}

	public function uninstall() {
		$this->load->model('payment/sagepay_server');
		$this->model_payment_sagepay_server->uninstall();
	}

	public function orderAction() {

		if ($this->config->get('sagepay_server_status')) {
			$this->load->model('payment/sagepay_server');

			$sagepay_server_order = $this->model_payment_sagepay_server->getOrder($this->request->get['order_id']);

			if (!empty($sagepay_server_order)) {
				$this->data = $this->load->language('payment/sagepay_server');

				$sagepay_server_order['total_released'] = $this->model_payment_sagepay_server->getTotalReleased($sagepay_server_order['sagepay_server_order_id']);

				$sagepay_server_order['total_formatted'] = $this->currency->format($sagepay_server_order['total'], $sagepay_server_order['currency_code'], false, false);
				$sagepay_server_order['total_released_formatted'] = $this->currency->format($sagepay_server_order['total_released'], $sagepay_server_order['currency_code'], false, false);

				$this->data['sagepay_server_order'] = $sagepay_server_order;

				$this->data['auto_settle'] = $sagepay_server_order['settle_type'];

				$this->data['order_id'] = $this->request->get['order_id'];
				
				$this->data['token'] = $this->request->get['token'];

				return $this->load->view('payment/sagepay_server_order.tpl', $this->data);
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/sagepay_server');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$this->load->model('payment/sagepay_server');

			$sagepay_server_order = $this->model_payment_sagepay_server->getOrder($this->request->post['order_id']);

			$void_response = $this->model_payment_sagepay_server->void($this->request->post['order_id']);

			$this->model_payment_sagepay_server->logger('Void result:\r\n' . print_r($void_response, 1));

			if ($void_response['Status'] == 'OK') {
				$this->model_payment_sagepay_server->addTransaction($sagepay_server_order['sagepay_server_order_id'], 'void', 0.00);
				$this->model_payment_sagepay_server->updateVoidStatus($sagepay_server_order['sagepay_server_order_id'], 1);

				$json['msg'] = $this->data['text_void_ok'];

				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($void_response['StatuesDetail']) && !empty($void_response['StatuesDetail']) ? (string)$void_response['StatuesDetail'] : 'Unable to void';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function release() {
		$this->data = $this->load->language('payment/sagepay_server');
		$json = array();

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '' && isset($this->request->post['amount']) && $this->request->post['amount'] > 0) {
			$this->load->model('payment/sagepay_server');

			$sagepay_server_order = $this->model_payment_sagepay_server->getOrder($this->request->post['order_id']);

			$release_response = $this->model_payment_sagepay_server->release($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_sagepay_server->logger('Release result:\r\n' . print_r($release_response, 1));

			if ($release_response['Status'] == 'OK') {
				$this->model_payment_sagepay_server->addTransaction($sagepay_server_order['sagepay_server_order_id'], 'payment', $this->request->post['amount']);

				$total_released = $this->model_payment_sagepay_server->getTotalReleased($sagepay_server_order['sagepay_server_order_id']);

				if ($total_released >= $sagepay_server_order['total'] || $sagepay_server_order['settle_type'] == 0) {
					$this->model_payment_sagepay_server->updateReleaseStatus($sagepay_server_order['sagepay_server_order_id'], 1);
					$release_status = 1;
					$json['msg'] = $this->data['text_release_ok_order'];
				} else {
					$release_status = 0;
					$json['msg'] = $this->data['text_release_ok'];
				}

				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->request->post['amount'];
				$json['data']['release_status'] = $release_status;
				$json['data']['total'] = (float)$total_released;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($release_response['StatusDetail']) && !empty($release_response['StatusDetail']) ? (string)$release_response['StatusDetail'] : 'Unable to release';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = $this->data['error_data_missing'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function rebate() {
		$this->data = $this->load->language('payment/sagepay_server');
		$json = array();

		if (isset($this->request->post['order_id']) && !empty($this->request->post['order_id'])) {
			$this->load->model('payment/sagepay_server');

			$sagepay_server_order = $this->model_payment_sagepay_server->getOrder($this->request->post['order_id']);

			$rebate_response = $this->model_payment_sagepay_server->rebate($this->request->post['order_id'], $this->request->post['amount']);

			$this->model_payment_sagepay_server->logger('Rebate result:\r\n' . print_r($rebate_response, 1));

			if ($rebate_response['Status'] == 'OK') {
				$this->model_payment_sagepay_server->addTransaction($sagepay_server_order['sagepay_server_order_id'], 'rebate', $this->request->post['amount'] * -1);

				$total_rebated = $this->model_payment_sagepay_server->getTotalRebated($sagepay_server_order['sagepay_server_order_id']);
				$total_released = $this->model_payment_sagepay_server->getTotalReleased($sagepay_server_order['sagepay_server_order_id']);

				if ($total_released <= 0 && $sagepay_server_order['release_status'] == 1) {
					$this->model_payment_sagepay_server->updateRebateStatus($sagepay_server_order['sagepay_server_order_id'], 1);
					$rebate_status = 1;
					$json['msg'] = $this->data['text_rebate_ok_order'];
				} else {
					$rebate_status = 0;
					$json['msg'] = $this->data['text_rebate_ok'];
				}

				$json['data'] = array();
				$json['data']['date_added'] = date("Y-m-d H:i:s");
				$json['data']['amount'] = $this->request->post['amount'] * -1;
				$json['data']['total_released'] = (float)$total_released;
				$json['data']['total_rebated'] = (float)$total_rebated;
				$json['data']['rebate_status'] = $rebate_status;
				$json['error'] = false;
			} else {
				$json['error'] = true;
				$json['msg'] = isset($rebate_response['StatusDetail']) && !empty($rebate_response['StatusDetail']) ? (string)$rebate_response['StatusDetail'] : 'Unable to rebate';
			}
		} else {
			$json['error'] = true;
			$json['msg'] = 'Missing data';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/sagepay_server')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['sagepay_server_vendor']) {
			$this->error['vendor'] = $this->data['error_vendor'];
		}

		return !$this->error;
	}
}