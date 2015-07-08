<?php 
class ControllerPaymentPayu extends Controller {
	private $error = array(); 

	public function index() {
		$this->data = $this->load->language('payment/payu');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payu', $this->request->post);				
			
			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

 		$this->data['error_merchant'] =  (isset($this->error['merchant'])?$this->error['merchant']:'');
		
		$this->data['error_salt'] =  (isset($this->error['salt'])?$this->error['salt']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/payu', 'token=' . $this->session->data['token'], 'SSL')
						));
      
		$this->data['action'] = $this->url->link('payment/payu', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['payu_merchant'] = $this->request->post('payu_merchant',$this->config->get('payu_merchant'));
		
		$this->data['payu_salt'] = $this->request->post('payu_salt',$this->config->get('payu_salt'));
		
		$this->data['payu_test'] = $this->request->post('payu_test',$this->config->get('payu_test'));
		
		$this->data['payu_total'] = $this->request->post('payu_total',$this->config->get('payu_total'));
		
		$this->data['payu_order_status_id'] = $this->request->post('payu_order_status_id',$this->config->get('payu_order_status_id'));
		 
		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['payu_geo_zone_id'] = $this->request->post('payu_geo_zone_id',$this->config->get('payu_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['payu_status'] = $this->request->post('payu_status',$this->config->get('payu_status'));
		
		$this->data['payu_sort_order'] = $this->request->post('payu_sort_order',$this->config->get('payu_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['footer'] = $this->load->controller('common/footer');

				
		$this->response->setOutput($this->load->view('payment/payu.tpl', $this->data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/payu')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
		
		if (!$this->request->post['payu_merchant']) {
			$this->error['merchant'] = $this->data['error_merchant'];
		}
			
		if (!$this->request->post['payu_salt']) {
			$this->error['salt'] = $this->data['error_salt'];
		}
		
				
		return !$this->error;
	}
}
