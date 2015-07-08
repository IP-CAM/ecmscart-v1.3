<?php
class ControllerPaymentNOCHEX extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/nochex');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('nochex', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');

		$this->data['error_merchant'] =  (isset($this->error['merchant'])?$this->error['merchant']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/nochex', 'token=' . $this->session->data['token'], 'SSL')
						));	

		$this->data['action'] = $this->url->link('payment/nochex', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['nochex_email'] = $this->request->post('nochex_email',$this->config->get('nochex_email'));
		
		$this->data['nochex_account'] = $this->request->post('nochex_account',$this->config->get('nochex_account'));	
		
		$this->data['nochex_merchant'] = $this->request->post('nochex_merchant',$this->config->get('nochex_merchant'));	
		
		$this->data['nochex_merchant'] = $this->request->post('nochex_merchant',$this->config->get('nochex_merchant'));	
		
		$this->data['nochex_template'] = $this->request->post('nochex_template',$this->config->get('nochex_template'));	
		
		$this->data['nochex_test'] = $this->request->post('nochex_test',$this->config->get('nochex_test'));	
		
		$this->data['nochex_total'] = $this->request->post('nochex_total',$this->config->get('nochex_total'));

		$this->data['nochex_order_status_id'] = $this->request->post('nochex_order_status_id',$this->config->get('nochex_order_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['nochex_geo_zone_id'] = $this->request->post('nochex_geo_zone_id',$this->config->get('nochex_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['nochex_status'] = $this->request->post('nochex_status',$this->config->get('nochex_status'));

		$this->data['nochex_sort_order'] = $this->request->post('nochex_sort_order',$this->config->get('nochex_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/nochex.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/nochex')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['nochex_email']) {
			$this->error['email'] = $this->data['error_email'];
		}

		if (!$this->request->post['nochex_merchant']) {
			$this->error['merchant'] = $this->data['error_merchant'];
		}

		return !$this->error;
	}
}