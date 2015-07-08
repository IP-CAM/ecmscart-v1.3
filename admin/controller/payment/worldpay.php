<?php
class ControllerPaymentWorldPay extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/worldpay');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('worldpay', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_merchant'] =  (isset($this->error['merchant'])?$this->error['merchant']:'');

		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/worldpay', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('payment/worldpay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['worldpay_merchant'] = $this->request->post('worldpay_merchant',$this->config->get('worldpay_merchant'));
		
		$this->data['worldpay_password'] = $this->request->post('worldpay_password',$this->config->get('worldpay_password'));	
		
		$this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/worldpay/callback';
		
		$this->data['worldpay_test'] = $this->request->post('worldpay_test',$this->config->get('worldpay_test'));

		$this->data['worldpay_total'] = $this->request->post('worldpay_total',$this->config->get('worldpay_total'));
		
		$this->data['worldpay_order_status_id'] = $this->request->post('worldpay_order_status_id',$this->config->get('worldpay_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['worldpay_geo_zone_id'] = $this->request->post('worldpay_geo_zone_id',$this->config->get('worldpay_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['worldpay_status'] = $this->request->post('worldpay_status',$this->config->get('worldpay_status'));
		
		$this->data['worldpay_sort_order'] = $this->request->post('worldpay_sort_order',$this->config->get('worldpay_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/worldpay.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/worldpay')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['worldpay_merchant']) {
			$this->error['merchant'] = $this->data['error_merchant'];
		}

		if (!$this->request->post['worldpay_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		return !$this->error;
	}
}