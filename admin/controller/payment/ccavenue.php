<?php
class ControllerPaymentccavenue extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/ccavenue');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ccavenue', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

 		$this->data['error_Merchant_Id'] =  (isset($this->error['Merchant_Id'])?$this->error['Merchant_Id']:'');
		
		$this->data['error_access_code'] =  (isset($this->error['access_code'])?$this->error['access_code']:'');

 		$this->data['error_total'] =  (isset($this->error['total'])?$this->error['total']:'');
		
		$this->data['error_workingkey'] =  (isset($this->error['workingkey'])?$this->error['workingkey']:'');
		
		$this->data['error_action'] =  (isset($this->error['action'])?$this->error['action']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/ccavenue', 'token=' . $this->session->data['token'], 'SSL')
						));
		
		
		$this->data['action'] = $this->url->link('payment/ccavenue', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['ccavenue_Merchant_Id'] = $this->request->post('ccavenue_Merchant_Id',$this->config->get('ccavenue_Merchant_Id'));
		
		$this->data['ccavenue_total'] = $this->request->post('ccavenue_total',$this->config->get('ccavenue_total'));
			
		$this->data['ccavenue_action'] = $this->request->post('ccavenue_action',$this->config->get('ccavenue_action'));
		
		$this->data['ccavenue_access_code'] = $this->request->post('ccavenue_access_code',$this->config->get('ccavenue_access_code'));
	
		$this->data['ccavenue_workingkey'] = $this->request->post('ccavenue_workingkey',$this->config->get('ccavenue_workingkey'));
		
		$this->data['ccavenue_completed_status_id'] = $this->request->post('ccavenue_completed_status_id',$this->config->get('ccavenue_completed_status_id'));
		 
		$this->data['ccavenue_failed_status_id'] = $this->request->post('ccavenue_failed_status_id',$this->config->get('ccavenue_failed_status_id'));
		
		$this->data['ccavenue_pending_status_id'] = $this->request->post('ccavenue_pending_status_id',$this->config->get('ccavenue_pending_status_id'));
		
		$this->data['ccavenue_voided_status_id'] = $this->request->post('ccavenue_voided_status_id',$this->config->get('ccavenue_voided_status_id'));	
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['ccavenue_geo_zone_id'] = $this->request->post('ccavenue_geo_zone_id',$this->config->get('ccavenue_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['ccavenue_status'] = $this->request->post('ccavenue_status',$this->config->get('ccavenue_status'));

		$this->data['ccavenue_sort_order'] = $this->request->post('ccavenue_sort_order',$this->config->get('ccavenue_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/ccavenue.tpl', $this->data));

		
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/ccavenue')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!isset($this->request->post['ccavenue_Merchant_Id'])) {
			$this->error['Merchant_Id'] = $this->data['error_Merchant_Id'];
		}
		if (!isset($this->request->post['ccavenue_Merchant_Id'])) {
			$this->error['Merchant_Id'] = $this->data['error_Merchant_Id'];
		}
		if (!isset($this->request->post['ccavenue_total'])) {
			$this->error['total'] = $this->data['error_total'];
		}
		if (!isset($this->request->post['ccavenue_action'])) {
			$this->error['action'] = $this->data['error_action'];
		}
		if (!isset($this->request->post['ccavenue_access_code'])) {
			$this->error['access_code'] = $this->data['error_access_code'];
		}
		if (!isset($this->request->post['ccavenue_workingkey'])) {
			$this->error['workingkey'] = $this->data['error_workingkey'];
		}

		return !$this->error;
	}
}
