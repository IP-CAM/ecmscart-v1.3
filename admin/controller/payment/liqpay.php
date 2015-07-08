<?php
class ControllerPaymentLiqPay extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/liqpay');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('liqpay', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_merchant'] =  (isset($this->error['merchant'])?$this->error['merchant']:'');

		$this->data['error_signature'] =  (isset($this->error['signature'])?$this->error['signature']:'');

		$this->data['error_type'] =  (isset($this->error['type'])?$this->error['type']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/liqpay', 'token=' . $this->session->data['token'], 'SSL')
						));	
		
		$this->data['action'] = $this->url->link('payment/liqpay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['liqpay_merchant'] = $this->request->post('liqpay_merchant',$this->config->get('liqpay_merchant'));
		
		$this->data['liqpay_signature'] = $this->request->post('liqpay_signature',$this->config->get('liqpay_signature'));
			
		$this->data['liqpay_type'] = $this->request->post('liqpay_type',$this->config->get('liqpay_type'));

		$this->data['liqpay_total'] = $this->request->post('liqpay_total',$this->config->get('liqpay_total'));

		$this->data['liqpay_order_status_id'] = $this->request->post('liqpay_order_status_id',$this->config->get('liqpay_order_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['liqpay_geo_zone_id'] = $this->request->post('liqpay_geo_zone_id',$this->config->get('liqpay_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['liqpay_status'] = $this->request->post('liqpay_status',$this->config->get('liqpay_status'));

		$this->data['liqpay_sort_order'] = $this->request->post('liqpay_sort_order',$this->config->get('liqpay_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/liqpay.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/liqpay')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['liqpay_merchant']) {
			$this->error['merchant'] = $this->data['error_merchant'];
		}

		if (!$this->request->post['liqpay_signature']) {
			$this->error['signature'] = $this->data['error_signature'];
		}

		return !$this->error;
	}
}