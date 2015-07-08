<?php
class ControllerPaymentSagepayUS extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/sagepay_us');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('sagepay_us', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_merchant_id'] =  (isset($this->error['merchant_id'])?$this->error['merchant_id']:'');

		$this->data['error_merchant_key'] =  (isset($this->error['merchant_key'])?$this->error['merchant_key']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/sagepay_us', 'token=' . $this->session->data['token'], 'SSL')
					));

		$this->data['action'] = $this->url->link('payment/sagepay_us', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['sagepay_us_merchant_id'] = $this->request->post('sagepay_us_merchant_id',$this->config->get('sagepay_us_merchant_id'));
		
		$this->data['sagepay_us_merchant_key'] = $this->request->post('sagepay_us_merchant_key',$this->config->get('sagepay_us_merchant_key'));	
		
		$this->data['sagepay_us_total'] = $this->request->post('sagepay_us_total',$this->config->get('sagepay_us_total'));
		
		$this->data['sagepay_us_order_status_id'] = $this->request->post('sagepay_us_order_status_id',$this->config->get('sagepay_us_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['sagepay_us_geo_zone_id'] = $this->request->post('sagepay_us_geo_zone_id',$this->config->get('sagepay_us_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['sagepay_us_sort_order'] = $this->request->post('sagepay_us_sort_order',$this->config->get('sagepay_us_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/sagepay_us.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/sagepay_us')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['sagepay_us_merchant_id']) {
			$this->error['merchant_id'] = $this->data['error_merchant_id'];
		}

		if (!$this->request->post['sagepay_us_merchant_key']) {
			$this->error['merchant_key'] = $this->data['error_merchant_key'];
		}

		return !$this->error;
	}
}