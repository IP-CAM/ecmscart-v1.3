<?php
class ControllerPaymentPPPro extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/pp_pro');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pp_pro', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_username'] =  (isset($this->error['username'])?$this->error['username']:'');
		
		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');
		
		$this->data['error_signature'] =  (isset($this->error['signature'])?$this->error['signature']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/pp_pro', 'token=' . $this->session->data['token'], 'SSL')
						));	
					
		$this->data['action'] = $this->url->link('payment/pp_pro', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['pp_pro_username'] = $this->request->post('pp_pro_username',$this->config->get('pp_pro_username'));
		
		$this->data['pp_pro_password'] = $this->request->post('pp_pro_password',$this->config->get('pp_pro_password'));
		
		$this->data['pp_pro_signature'] = $this->request->post('pp_pro_signature',$this->config->get('pp_pro_signature'));	

		$this->data['pp_pro_test'] = $this->request->post('pp_pro_test',$this->config->get('pp_pro_test'));	

		$this->data['pp_pro_transaction'] = $this->request->post('pp_pro_transaction',$this->config->get('pp_pro_transaction'));

		$this->data['pp_pro_total'] = $this->request->post('pp_pro_total',$this->config->get('pp_pro_total'));

		$this->data['pp_pro_order_status_id'] = $this->request->post('pp_pro_order_status_id',$this->config->get('pp_pro_order_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['pp_pro_geo_zone_id'] = $this->request->post('pp_pro_geo_zone_id',$this->config->get('pp_pro_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['pp_pro_status'] = $this->request->post('pp_pro_status',$this->config->get('pp_pro_status'));

		$this->data['pp_pro_sort_order'] = $this->request->post('pp_pro_sort_order',$this->config->get('pp_pro_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/pp_pro.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/pp_pro')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['pp_pro_username']) {
			$this->error['username'] = $this->data['error_username'];
		}

		if (!$this->request->post['pp_pro_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		if (!$this->request->post['pp_pro_signature']) {
			$this->error['signature'] = $this->data['error_signature'];
		}

		return !$this->error;
	}
}