<?php
class ControllerPaymentWebPaymentSoftware extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/web_payment_software');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('web_payment_software', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_login'] =  (isset($this->error['login'])?$this->error['login']:'');

		$this->data['error_key'] =  (isset($this->error['key'])?$this->error['key']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/web_payment_software', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/web_payment_software&token=' . $this->session->data['token'];

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];

		$this->data['web_payment_software_merchant_name'] = $this->request->post('web_payment_software_merchant_name',$this->config->get('web_payment_software_merchant_name'));
		
		$this->data['web_payment_software_merchant_key'] = $this->request->post('web_payment_software_merchant_key',$this->config->get('web_payment_software_merchant_key'));	
		
		$this->data['web_payment_software_mode'] = $this->request->post('web_payment_software_mode',$this->config->get('web_payment_software_mode'));
		
		$this->data['web_payment_software_method'] = $this->request->post('web_payment_software_method',$this->config->get('web_payment_software_method'));
		
		$this->data['web_payment_software_order_status_id'] = $this->request->post('web_payment_software_order_status_id',$this->config->get('web_payment_software_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['web_payment_software_geo_zone_id'] = $this->request->post('web_payment_software_geo_zone_id',$this->config->get('web_payment_software_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['web_payment_software_status'] = $this->request->post('web_payment_software_status',$this->config->get('web_payment_software_status'));

		$this->data['web_payment_software_total'] = $this->request->post('web_payment_software_total',$this->config->get('web_payment_software_total'));

		$this->data['web_payment_software_sort_order'] = $this->request->post('web_payment_software_sort_order',$this->config->get('web_payment_software_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/web_payment_software.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/web_payment_software')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['web_payment_software_merchant_name']) {
			$this->error['login'] = $this->data['error_login'];
		}

		if (!$this->request->post['web_payment_software_merchant_key']) {
			$this->error['key'] = $this->data['error_key'];
		}

		return !$this->error;
	}
}