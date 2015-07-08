<?php
class ControllerPaymentFirstdata extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/firstdata');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('firstdata', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['notify_url'] = HTTPS_CATALOG . 'index.php?route=payment/firstdata/notify';

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_merchant_id'] =  (isset($this->error['error_merchant_id'])?$this->error['error_merchant_id']:'');

		$this->data['error_secret'] =  (isset($this->error['error_secret'])?$this->error['error_secret']:'');

		$this->data['error_live_url'] =  (isset($this->error['error_live_url'])?$this->error['error_live_url']:'');
		$this->data['error_demo_url'] =  (isset($this->error['error_demo_url'])?$this->error['error_demo_url']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/firstdata', 'token=' . $this->session->data['token'], 'SSL')
						));
		
		$this->data['action'] = $this->url->link('payment/firstdata', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['firstdata_merchant_id'] = $this->request->post('firstdata_merchant_id',$this->config->get('firstdata_merchant_id'));
		
		$this->data['firstdata_secret'] = $this->request->post('firstdata_secret',$this->config->get('firstdata_secret'));	
		
		$this->data['firstdata_live_demo'] = $this->request->post('firstdata_live_demo',$this->config->get('firstdata_live_demo'));
		
		$this->data['firstdata_geo_zone_id'] = $this->request->post('firstdata_geo_zone_id',$this->config->get('firstdata_geo_zone_id'));
		
		$this->data['firstdata_total'] = $this->request->post('firstdata_total',$this->config->get('firstdata_total'));
		
		$this->data['firstdata_sort_order'] = $this->request->post('firstdata_sort_order',$this->config->get('firstdata_sort_order'));
		
		$this->data['firstdata_status'] = $this->request->post('firstdata_status',$this->config->get('firstdata_status'));

		$this->data['firstdata_debug'] = $this->request->post('firstdata_debug',$this->config->get('firstdata_debug'));

		if ($this->config->get('firstdata_auto_settle') != '' && !$this->error) {
			$this->data['firstdata_auto_settle'] = $this->config->get('firstdata_auto_settle');
		} else {
			$this->data['firstdata_auto_settle'] = $this->request->post('firstdata_auto_settle',1);
		}

		$this->data['firstdata_order_status_success_settled_id'] = $this->request->post('firstdata_order_status_success_settled_id',$this->config->get('firstdata_order_status_success_settled_id'));
		
		$this->data['firstdata_order_status_success_unsettled_id'] = $this->request->post('firstdata_order_status_success_unsettled_id',$this->config->get('firstdata_order_status_success_unsettled_id'));
		
		$this->data['firstdata_order_status_decline_id'] = $this->request->post('firstdata_order_status_decline_id',$this->config->get('firstdata_order_status_decline_id'));
		
		$this->data['firstdata_order_status_void_id'] = $this->request->post('firstdata_order_status_void_id',$this->config->get('firstdata_order_status_void_id'));
		
		$this->data['firstdata_live_url'] = $this->request->post('firstdata_live_url',$this->config->get('firstdata_live_url'));
		
		if (empty($this->data['firstdata_live_url'])) 
			$this->data['firstdata_live_url'] = 'https://ipg-online.com/connect/gateway/processing';
		
		$this->data['firstdata_demo_url'] = $this->request->post('firstdata_demo_url',$this->config->get('firstdata_demo_url'));
		
		$this->data['firstdata_card_storage'] = $this->request->post('firstdata_card_storage',$this->config->get('firstdata_card_storage'));
		
		if (empty($this->data['firstdata_demo_url'])) 
			$this->data['firstdata_demo_url'] = 'https://test.ipg-online.com/connect/gateway/processing';
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/firstdata.tpl', $this->data));
	}

	public function orderAction() {
		if ($this->config->get('firstdata_status')) {
			$this->load->model('payment/firstdata');

			$firstdata_order = $this->model_payment_firstdata->getOrder($this->request->get['order_id']);

			if (!empty($firstdata_order)) {
				$this->data = $this->load->language('payment/firstdata');

				$firstdata_order['total_captured'] = $this->model_payment_firstdata->getTotalCaptured($firstdata_order['firstdata_order_id']);
				$firstdata_order['total_formatted'] = $this->currency->format($firstdata_order['total'], $firstdata_order['currency_code'], 1, true);
				$firstdata_order['total_captured_formatted'] = $this->currency->format($firstdata_order['total_captured'], $firstdata_order['currency_code'], 1, true);

				$this->data['firstdata_order'] = $firstdata_order;
				$this->data['merchant_id'] = $this->config->get('firstdata_merchant_id');
				$this->data['currency'] = $this->model_payment_firstdata->mapCurrency($firstdata_order['currency_code']);
				$this->data['amount'] = number_format($firstdata_order['total'], 2);

				$this->data['request_timestamp'] = date("Y:m:d-H:i:s");

				$this->data['hash'] = sha1(bin2hex($this->data['merchant_id'] . $this->data['request_timestamp'] . $this->data['amount'] . $this->data['currency'] . $this->config->get('firstdata_secret')));

				$this->data['void_url'] = $this->url->link('payment/firstdata/void', 'token=' . $this->session->data['token'], 'SSL');
				$this->data['capture_url'] = $this->url->link('payment/firstdata/capture', 'token=' . $this->session->data['token'], 'SSL');
				$this->data['notify_url'] = HTTPS_CATALOG . 'index.php?route=payment/firstdata/notify';

				if ($this->config->get('firstdata_live_demo') == 1) {
					$this->data['action_url'] = $this->config->get('firstdata_live_url');
				} else {
					$this->data['action_url'] = $this->config->get('firstdata_demo_url');
				}
				
		$this->data['void_success'] = isset($this->session->data['void_success'])? $this->session->data['void_success']: '';
		
		if (isset($this->session->data['void_success']))  // To unset success session variable.
			unset($this->session->data['void_success']);
			
		$this->data['void_error'] = isset($this->session->data['void_error'])? $this->session->data['void_error']: '';
		
		if (isset($this->session->data['void_error']))  // To unset success session variable.
			unset($this->session->data['void_error']);	
				
		$this->data['capture_success'] = isset($this->session->data['capture_success'])? $this->session->data['capture_success']: '';
		
		if (isset($this->session->data['capture_success']))  // To unset success session variable.
			unset($this->session->data['capture_success']);		

		$this->data['capture_error'] = isset($this->session->data['capture_error'])? $this->session->data['capture_error']: '';
		
		if (isset($this->session->data['capture_error']))  // To unset success session variable.
			unset($this->session->data['capture_error']);		

				$this->data['order_id'] = $this->request->get['order_id'];
				$this->data['token'] = $this->request->get['token'];

				return $this->load->view('payment/firstdata_order.tpl', $this->data);
			}
		}
	}

	public function void() {
		$this->data = $this->load->language('payment/firstdata');

		if ($this->request->post['status'] == 'FAILED') {
			if (isset($this->request->post['fail_reason'])) {
				$this->session->data['void_error'] = $this->request->post['fail_reason'];
			} else {
				$this->session->data['void_error'] = $this->data['error_void_error'];
			}
		}

		if ($this->request->post['status'] == 'DECLINED') 
			$this->session->data['void_success'] = $this->data['success_void'];
		
		$this->response->redirect($this->url->link('sale/order/info', 'order_id=' . $this->request->post['order_id'] . '&token=' . $this->session->data['token'], 'SSL'));
	}

	public function capture() {
		$this->data = $this->load->language('payment/firstdata');

		if ($this->request->post['status'] == 'FAILED') {
			if (isset($this->request->post['fail_reason'])) {
				$this->session->data['capture_error'] = $this->request->post['fail_reason'];
			} else {
				$this->session->data['capture_error'] = $this->data['error_capture_error'];
			}
		}

		if ($this->request->post['status'] == 'APPROVED') 
			$this->session->data['capture_success'] = $this->data['success_capture'];

		$this->response->redirect($this->url->link('sale/order/info', 'order_id=' . $this->request->post['order_id'] . '&token=' . $this->session->data['token'], 'SSL'));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/firstdata')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['firstdata_merchant_id']) {
			$this->error['error_merchant_id'] = $this->data['error_merchant_id'];
		}

		if (!$this->request->post['firstdata_secret']) {
			$this->error['error_secret'] = $this->data['error_secret'];
		}

		if (!$this->request->post['firstdata_live_url']) {
			$this->error['error_live_url'] = $this->data['error_live_url'];
		}

		if (!$this->request->post['firstdata_demo_url']) {
			$this->error['error_demo_url'] = $this->data['error_demo_url'];
		}

		return !$this->error;
	}
}