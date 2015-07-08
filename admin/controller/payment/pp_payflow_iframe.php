<?php
class ControllerPaymentPPPayflowIframe extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/pp_payflow_iframe');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pp_payflow_iframe', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_vendor'] =  (isset($this->error['vendor'])?$this->error['vendor']:'');
		
		$this->data['error_user'] =  (isset($this->error['user'])?$this->error['user']:'');
		
		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');
		
		$this->data['error_partner'] =  (isset($this->error['partner'])?$this->error['partner']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/pp_payflow', 'token=' . $this->session->data['token'], 'SSL')
						));
		
		$this->data['action'] = $this->url->link('payment/pp_payflow_iframe', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['pp_payflow_iframe_vendor'] = $this->request->post('pp_payflow_iframe_vendor',$this->config->get('pp_payflow_iframe_vendor'));
		
		$this->data['pp_payflow_iframe_user'] = $this->request->post('pp_payflow_iframe_user',$this->config->get('pp_payflow_iframe_user'));	

		$this->data['pp_payflow_iframe_password'] = $this->request->post('pp_payflow_iframe_password',$this->config->get('pp_payflow_iframe_password'));	

		$this->data['pp_payflow_iframe_partner'] = $this->request->post('pp_payflow_iframe_partner',$this->config->get('pp_payflow_iframe_partner'));

		$this->data['pp_payflow_iframe_transaction_method'] = $this->request->post('pp_payflow_iframe_transaction_method',$this->config->get('pp_payflow_iframe_transaction_method'));

		$this->data['pp_payflow_iframe_test'] = $this->request->post('pp_payflow_iframe_test',$this->config->get('pp_payflow_iframe_test'));

		$this->data['pp_payflow_iframe_total'] = $this->request->post('pp_payflow_iframe_total',$this->config->get('pp_payflow_iframe_total'));

		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['pp_payflow_iframe_order_status_id'] = $this->request->post('pp_payflow_iframe_order_status_id',$this->config->get('pp_payflow_iframe_order_status_id'));

		$this->data['pp_payflow_iframe_geo_zone_id'] = $this->request->post('pp_payflow_iframe_geo_zone_id',$this->config->get('pp_payflow_iframe_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['pp_payflow_iframe_status'] = $this->request->post('pp_payflow_iframe_status',$this->config->get('pp_payflow_iframe_status'));
		
		$this->data['pp_payflow_iframe_sort_order'] = $this->request->post('pp_payflow_iframe_sort_order',$this->config->get('pp_payflow_iframe_sort_order'));

		$this->data['pp_payflow_iframe_checkout_method'] = $this->request->post('pp_payflow_iframe_checkout_method',$this->config->get('pp_payflow_iframe_checkout_method'));

		$this->data['pp_payflow_iframe_debug'] = $this->request->post('pp_payflow_iframe_debug',$this->config->get('pp_payflow_iframe_debug'));

		$this->data['post_url'] = HTTPS_CATALOG . 'index.php?route=payment/pp_payflow_iframe/paymentipn';
		$this->data['cancel_url'] = HTTPS_CATALOG . 'index.php?route=payment/pp_payflow_iframe/paymentcancel';
		$this->data['error_url'] = HTTPS_CATALOG . 'index.php?route=payment/pp_payflow_iframe/paymenterror';
		$this->data['return_url'] = HTTPS_CATALOG . 'index.php?route=payment/pp_payflow_iframe/paymentreturn';
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/pp_payflow_iframe.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/pp_payflow_iframe');
		
		$this->model_payment_pp_payflow_iframe->install();
	}

	public function uninstall() {
		$this->load->model('payment/pp_payflow_iframe');
		
		$this->model_payment_pp_payflow_iframe->uninstall();
	}

	public function refund() {
		$this->load->model('payment/pp_payflow_iframe');
		$this->load->model('sale/order');
		$this->data = $this->load->language('payment/pp_payflow_iframe');

		$transaction = $this->model_payment_pp_payflow_iframe->getTransaction($this->request->get['transaction_reference']);

		if ($transaction) {
			$this->document->setTitle($this->data['heading_refund']);
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/pp_payflow_iframe', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_refund'],
							$this->url->link('payment/pp_payflow_iframe/refund', 'transaction_reference=' . $this->request->get['transaction_reference'] . '&token=' . $this->session->data['token'], 'SSL')
						));

			$this->data['transaction_reference'] = $transaction['transaction_reference'];
			$this->data['transaction_amount'] = number_format($transaction['amount'], 2);
			$this->data['cancel'] = $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $transaction['order_id'], 'SSL');

			$this->data['token'] = $this->session->data['token'];
			
			$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('payment/pp_payflow_iframe_refund.tpl', $this->data));
		} else {
			return $this->forward('error/not_found');
		}
	}

	public function doRefund() {
		$this->load->model('payment/pp_payflow_iframe');
		$this->data = $this->load->language('payment/pp_payflow_iframe');
		$json = array();

		if (isset($this->request->post['transaction_reference']) && isset($this->request->post['amount'])) {

			$transaction = $this->model_payment_pp_payflow_iframe->getTransaction($this->request->post['transaction_reference']);

			if ($transaction) {
				$call_data = array(
					'TRXTYPE' => 'C',
					'TENDER'  => 'C',
					'ORIGID'  => $transaction['transaction_reference'],
					'AMT'     => $this->request->post['amount'],
				);

				$result = $this->model_payment_pp_payflow_iframe->call($call_data);

				if ($result['RESULT'] == 0) {
					$json['success'] = $this->data['text_refund_issued'];

					$this->data = array(
						'order_id' => $transaction['order_id'],
						'type' => 'C',
						'transaction_reference' => $result['PNREF'],
						'amount' => $this->request->post['amount'],
					);

					$this->model_payment_pp_payflow_iframe->addTransaction($this->data);
				} else {
					$json['error'] = $result['RESPMSG'];
				}
			} else {
				$json['error'] = $this->data['error_missing_order'];
			}
		} else {
			$json['error'] = $this->data['error_missing_data'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function capture() {
		$this->load->model('payment/pp_payflow_iframe');
		$this->load->model('sale/order');
		$this->data = $this->load->language('payment/pp_payflow_iframe');

		if (isset($this->request->post['order_id']) && isset($this->request->post['amount']) && isset($this->request->post['complete'])) {
			$order_id = $this->request->post['order_id'];
			$paypal_order = $this->model_payment_pp_payflow_iframe->getOrder($order_id);
			$paypal_transactions = $this->model_payment_pp_payflow_iframe->getTransactions($order_id);
			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($paypal_order && $order_info) {
				if ($this->request->post['complete'] == 1) {
					$complete = 'Y';
				} else {
					$complete = 'N';
				}

				$call_data = array(
					'TRXTYPE'         => 'D',
					'TENDER'          => 'C',
					'ORIGID'          => $paypal_order['transaction_reference'],
					'AMT'             => $this->request->post['amount'],
					'CAPTURECOMPLETE' => $complete
				);

				$result = $this->model_payment_pp_payflow_iframe->call($call_data);

				if ($result['RESULT'] == 0) {

					$this->data = array(
						'order_id'              => $order_id,
						'type'                  => 'D',
						'transaction_reference' => $result['PNREF'],
						'amount'                => $this->request->post['amount']
					);

					$this->model_payment_pp_payflow_iframe->addTransaction($this->data);
					$this->model_payment_pp_payflow_iframe->updateOrderStatus($order_id, $this->request->post['complete']);

					$actions = array();

					$actions[] = array(
						'title' => $this->data['text_capture'],
						'href' => $this->url->link('payment/pp_payflow_iframe/refund', 'transaction_reference=' . $result['PNREF'] . '&token=' . $this->session->data['token']),
					);

					$json['success'] = array(
						'transaction_type' => $this->data['text_capture'],
						'transaction_reference' => $result['PNREF'],
						'time' => date('Y-m-d H:i:s'),
						'amount' => number_format($this->request->post['amount'], 2),
						'actions' => $actions,
					);
				} else {
					$json['error'] = $result['RESPMSG'];
				}
			} else {
				$json['error'] = $this->data['error_missing_order'];
			}
		} else {
			$json['error'] = $this->data['error_missing_data'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function void() {
		$this->load->model('payment/pp_payflow_iframe');
		$this->data = $this->load->language('payment/pp_payflow_iframe');

		if (isset($this->request->post['order_id']) && $this->request->post['order_id'] != '') {
			$order_id = $this->request->post['order_id'];
			$paypal_order = $this->model_payment_pp_payflow_iframe->getOrder($order_id);

			if ($paypal_order) {
				$call_data = array(
					'TRXTYPE' => 'V',
					'TENDER' => 'C',
					'ORIGID' => $paypal_order['transaction_reference'],
				);

				$result = $this->model_payment_pp_payflow_iframe->call($call_data);

				if ($result['RESULT'] == 0) {
					$json['success'] = $this->data['text_void_success'];
					$this->model_payment_pp_payflow_iframe->updateOrderStatus($order_id, 1);

					$this->data = array(
						'order_id' => $order_id,
						'type' => 'V',
						'transaction_reference' => $result['PNREF'],
						'amount' => '',
					);

					$this->model_payment_pp_payflow_iframe->addTransaction($this->data);
					$this->model_payment_pp_payflow_iframe->updateOrderStatus($order_id, 1);

					$json['success'] = array(
						'transaction_type' => $this->data['text_void'],
						'transaction_reference' => $result['PNREF'],
						'time' => date('Y-m-d H:i:s'),
						'amount' => '0.00',
					);
				} else {
					$json['error'] = $result['RESPMSG'];
				}
			} else {
				$json['error'] = $this->data['error_missing_order'];
			}
		} else {
			$json['error'] = $this->data['error_missing_data'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function orderAction() {
		$this->load->model('payment/pp_payflow_iframe');
		$this->data = $this->load->language('payment/pp_payflow_iframe');

		$order_id = $this->request->get['order_id'];

		$paypal_order = $this->model_payment_pp_payflow_iframe->getOrder($order_id);

		if ($paypal_order) {
			
			$this->data['complete'] = $paypal_order['complete'];
			$this->data['order_id'] = $this->request->get['order_id'];
			$this->data['token'] = $this->request->get['token'];

			$this->data['transactions'] = array();

			$transactions = $this->model_payment_pp_payflow_iframe->getTransactions($order_id);

			foreach ($transactions as $transaction) {
				$actions = array();

				switch ($transaction['transaction_type']) {
					case 'V':
						$transaction_type = $this->data['text_void'];
						break;
					case 'S':
						$transaction_type = $this->data['text_sale'];

						$actions[] = array(
							'title' => $this->data['text_refund'],
							'href' => $this->url->link('payment/pp_payflow_iframe/refund', 'transaction_reference=' . $transaction['transaction_reference'] . '&token=' . $this->session->data['token']),
						);
						break;
					case 'D':
						$transaction_type = $this->data['text_capture'];

						$actions[] = array(
							'title' => $this->data['text_refund'],
							'href' => $this->url->link('payment/pp_payflow_iframe/refund', 'transaction_reference=' . $transaction['transaction_reference'] . '&token=' . $this->session->data['token']),
						);
						break;
					case 'A':
						$transaction_type = $this->data['text_authorise'];
						break;

					case 'C':
						$transaction_type = $this->data['text_refund'];#
						break;

					default:
						$transaction_type = '';
						break;
				}

				$this->data['transactions'][] = array(
					'transaction_reference' => $transaction['transaction_reference'],
					'transaction_type'      => $transaction_type,
					'time'                  => $transaction['time'],
					'amount'                => $transaction['amount'],
					'actions'               => $actions
				);
			}

			return $this->load->view('payment/pp_payflow_iframe_order.tpl', $this->data);
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/pp_payflow_iframe')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['pp_payflow_iframe_vendor']) {
			$this->error['vendor'] = $this->data['error_vendor'];
		}

		if (!$this->request->post['pp_payflow_iframe_user']) {
			$this->error['user'] = $this->data['error_user'];
		}

		if (!$this->request->post['pp_payflow_iframe_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		if (!$this->request->post['pp_payflow_iframe_partner']) {
			$this->error['partner'] = $this->data['error_partner'];
		}

		return !$this->error;
	}
}