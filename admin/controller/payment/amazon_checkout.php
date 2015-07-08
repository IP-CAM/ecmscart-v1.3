<?php
class ControllerPaymentAmazonCheckout extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/amazon_checkout');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		$this->load->model('payment/amazon_checkout');

		$this->load->library('cba');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->request->post['amazon_checkout_access_key'] = $this->request->post['amazon_checkout_access_key'];
			$this->request->post['amazon_checkout_access_secret'] = $this->request->post['amazon_checkout_access_secret'];
			$this->request->post['amazon_checkout_merchant_id'] = $this->request->post['amazon_checkout_merchant_id'];

			if (!isset($this->request->post['amazon_checkout_allowed_ips'])) {
				$this->request->post['amazon_checkout_allowed_ips'] = array();
			}

			$this->model_setting_setting->editSetting('amazon_checkout', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$cba = new CBA($this->request->post['amazon_checkout_merchant_id'], $this->request->post['amazon_checkout_access_key'], $this->request->post['amazon_checkout_access_secret'], $this->request->post['amazon_checkout_marketplace']);

			$cba->setMode($this->request->post['amazon_checkout_mode']);

			$cba->scheduleReports();

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_merchant_id'] =  (isset($this->error['merchant_id'])?$this->error['merchant_id']:'');
		
		$this->data['error_access_key'] =  (isset($this->error['access_key'])?$this->error['access_key']:'');		
		
		$this->data['error_access_secret'] =  (isset($this->error['access_secret'])?$this->error['access_secret']:'');
				
		$this->data['error_currency'] =  (isset($this->error['currency'])?$this->error['currency']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/amazon_checkout', 'token=' . $this->session->data['token'], 'SSL')
						));
		
		$this->data['action'] = $this->url->link('payment/amazon_checkout', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token']);

		if ($this->config->get('amazon_checkout_merchant_id') && !$this->error) {
			$this->data['amazon_checkout_merchant_id'] = $this->config->get('amazon_checkout_merchant_id');
		} else {
			$this->data['amazon_checkout_merchant_id'] = $this->request->post('amazon_checkout_merchant_id','');
		}
		
		$this->data['amazon_checkout_access_key'] = $this->request->post('amazon_checkout_access_key',$this->config->get('amazon_checkout_access_key'));
		
		$this->data['amazon_checkout_access_secret'] = $this->request->post('amazon_checkout_access_secret',$this->config->get('amazon_checkout_access_secret'));
		
		$this->data['amazon_checkout_mode'] = $this->request->post('amazon_checkout_mode',$this->config->get('amazon_checkout_mode'));
		
		$this->data['amazon_checkout_marketplace'] = $this->request->post('amazon_checkout_marketplace',$this->config->get('amazon_checkout_marketplace'));
		
		$this->data['amazon_checkout_order_status_id'] = $this->request->post('amazon_checkout_order_status_id',$this->config->get('amazon_checkout_order_status_id'));
		
		$this->data['amazon_checkout_ready_status_id'] = $this->request->post('amazon_checkout_ready_status_id',$this->config->get('amazon_checkout_ready_status_id'));
		
		$this->data['amazon_checkout_canceled_status_id'] = $this->request->post('amazon_checkout_canceled_status_id',$this->config->get('amazon_checkout_canceled_status_id'));
		
		$this->data['amazon_checkout_shipped_status_id'] = $this->request->post('amazon_checkout_shipped_status_id',$this->config->get('amazon_checkout_shipped_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if ($this->config->get('amazon_checkout_cron_job_token') && !$this->error) {
			$this->data['amazon_checkout_cron_job_token'] = $this->config->get('amazon_checkout_cron_job_token');
		} else {
			$this->data['amazon_checkout_cron_job_token'] = $this->request->post('amazon_checkout_cron_job_token',sha1(uniqid(mt_rand(), 1)));
		}
		
		$this->data['cron_job_url'] = HTTPS_CATALOG . 'index.php?route=payment/amazon_checkout/cron&token=' . $this->data['amazon_checkout_cron_job_token'];
		
		$this->data['store'] = HTTPS_CATALOG;
		
		$this->data['cron_job_last_run'] = $this->config->get('amazon_checkout_cron_job_last_run');

		if ($this->config->get('amazon_checkout_allowed_ips') && !$this->error) {
			$this->data['amazon_checkout_ip_allowed'] = $this->config->get('amazon_checkout_ip_allowed');
		} else {
			$this->data['amazon_checkout_ip_allowed'] =$this->request->post('amazon_checkout_ip_allowed', array());
		}
		
		
		$this->data['amazon_checkout_geo_zone'] = $this->request->post('amazon_checkout_geo_zone',$this->config->get('amazon_checkout_geo_zone'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if ($this->config->get('amazon_checkout_total') && !$this->error) {
			$this->data['amazon_checkout_total'] = $this->config->get('amazon_checkout_total');
		} else {
			$this->data['amazon_checkout_total'] = $this->request->post('amazon_checkout_total', '0.00');
		}
		
		$this->data['amazon_checkout_status'] = $this->request->post('amazon_checkout_status',$this->config->get('amazon_checkout_status'));
		
		$this->data['amazon_checkout_sort_order'] = $this->request->post('amazon_checkout_sort_order',$this->config->get('amazon_checkout_sort_order'));
			
		if ($this->config->get('amazon_checkout_button_colour') && !$this->error) {
			$this->data['amazon_checkout_button_colour'] = $this->config->get('amazon_checkout_button_colour');
		} else {
			$this->data['amazon_checkout_button_colour'] = $this->request->post('amazon_checkout_button_colour', 'orange');
		}

		if ($this->config->get('amazon_checkout_button_size') && !$this->error) {
			$this->data['amazon_checkout_button_size'] = $this->config->get('amazon_checkout_button_size');
		} else {
			$this->data['amazon_checkout_button_size'] = $this->request->post('amazon_checkout_button_size', 'large');
		}

		if ($this->config->get('amazon_checkout_button_background') && !$this->error) {
			$this->data['amazon_checkout_button_background'] = $this->config->get('amazon_checkout_button_background');
		} else {
			$this->data['amazon_checkout_button_background'] = $this->request->post('amazon_checkout_button_background', '');
		}

		$this->data['button_colours'] = array(
			'orange' => $this->data['text_orange'],
			'tan'    => $this->data['text_tan']
		);

		$this->data['button_backgrounds'] = array(
			'white' => $this->data['text_white'],
			'light' => $this->data['text_light'],
			'dark'  => $this->data['text_dark'],
		);

		$this->data['button_sizes'] = array(
			'medium'  => $this->data['text_medium'],
			'large'   => $this->data['text_large'],
			'x-large' => $this->data['text_x_large'],
		);

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/amazon_checkout.tpl', $this->data));
	}

	public function install() {
		$this->load->model('payment/amazon_checkout');

		$this->load->model('setting/setting');

		$this->model_payment_amazon_checkout->install();
		
		$this->model_setting_setting->editSetting('amazon_checkout', $this->settings);
	}

	public function uninstall() {
		$this->load->model('payment/amazon_checkout');

		$this->model_payment_amazon_checkout->uninstall();
	}

	public function uploadOrderAdjustment() {
		$this->data = $this->load->language('payment/amazon_checkout');

		$json = array();

		if (!empty($this->request->files['file']['name'])) {
			$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->data['error_upload'];
			}
		} else {
			$json['error'] = $this->data['error_upload'];
		}

		if (!isset($json['error'])) {
			if (is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
				$flat = str_replace(",", "\t", file_get_contents($this->request->files['file']['tmp_name']));

				$this->load->library('cba');

				$cba = new CBA($this->config->get('amazon_checkout_merchant_id'), $this->config->get('amazon_checkout_access_key'), $this->config->get('amazon_checkout_access_secret'), $this->config->get('amazon_checkout_marketplace'));

				$response = $cba->orderAdjustment($flat);

				$response_xml = simplexml_load_string($response);
				$submission_id = (string)$response_xml->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;

				if (!empty($submission_id)) {
					$json['success'] = $this->data['text_upload_success'];
					$json['submission_id'] = $submission_id;
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addSubmission() {
		$this->load->model('payment/amazon_checkout');

		$this->model_payment_amazon_checkout->addReportSubmission($this->request->get['order_id'], $this->request->get['submission_id']);
	}

	public function orderAction() {
		$this->load->model('sale/order');
		$this->load->model('payment/amazon_checkout');
		$this->data = $this->load->language('sale/order');
		$this->data = array_merge($this->data, $this->load->language('payment/amazon_checkout'));

		$amazon_order_info = $this->model_payment_amazon_checkout->getOrder($this->request->get['order_id']);

		if ($amazon_order_info) {
			$order_products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

			$this->data['products'] = array();

			foreach ($order_products as $product) {
				$product_options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				$order_item_code = '';

				if (isset($amazon_order_info['products'][$product['order_product_id']])) {
					$order_item_code = $amazon_order_info['products'][$product['order_product_id']]['amazon_order_item_code'];
				}

				$this->data['products'][] = array(
					'amazon_order_item_code' => $order_item_code,
					'order_product_id'       => $product['order_product_id'],
					'product_id'             => $product['product_id'],
					'name'                   => $product['name'],
					'model'                  => $product['model'],
					'option'                 => $product_options,
					'quantity'               => $product['quantity'],
					'price'                  => $this->currency->format($product['price'], $order_info['currency_code'], $order_info['currency_value']),
					'total'                  => $this->currency->format($product['total'], $order_info['currency_code'], $order_info['currency_value']),
					'href'                   => $this->url->link('catalog/product/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $product['product_id'], 'SSL')
				);
			}

			$this->data['report_submissions'] = $this->model_payment_amazon_checkout->getReportSubmissions($this->request->get['order_id']);

			$this->data['text_download'] = sprintf($this->data['text_download'], 'https://sellercentral-europe.amazon.com/gp/transactions/uploadAdjustments.html');

			$this->data['amazon_order_id'] = $amazon_order_info['amazon_order_id'];

			$this->data['token'] = $this->session->data['token'];
			$this->data['order_id'] = $this->request->get['order_id'];

			return $this->load->view('payment/amazon_checkout_order.tpl', $this->data);
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/amazon_checkout')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['amazon_checkout_merchant_id']) {
			$this->error['merchant_id'] = $this->data['error_merchant_id'];
		}

		if (!$this->request->post['amazon_checkout_access_key']) {
			$this->error['access_key'] = $this->data['error_access_key'];
		}

		if (!$this->request->post['amazon_checkout_access_secret']) {
			$this->error['access_secret'] = $this->data['error_access_secret'];
		}

		switch ($this->request->post['amazon_checkout_marketplace']) {
			case 'uk':
				$currency_code = 'GBP';
				break;

			case 'de':
				$currency_code = 'EUR';
				break;
		}
		
		$this->load->model('localisation/currency');

		$currency_info = $this->model_localisation_currency->getCurrencyByCode($currency_code);

		if (empty($currency_info) || !$currency_info['status']) {
			$this->error['currency'] = sprintf($this->data['error_currency'], $currency_code);
		}

		return !$this->error;
	}
}