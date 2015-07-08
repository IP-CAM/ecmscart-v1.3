<?php
class ControllerPaymentPPPayflow extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/pp_payflow');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pp_payflow', $this->request->post);

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
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/pp_payflow', 'token=' . $this->session->data['token'], 'SSL')
						));	
		
		$this->data['action'] = $this->url->link('payment/pp_payflow', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['pp_payflow_vendor'] = $this->request->post('pp_payflow_vendor',$this->config->get('pp_payflow_vendor'));
		
		$this->data['pp_payflow_user'] = $this->request->post('pp_payflow_user',$this->config->get('pp_payflow_user'));	
		
		$this->data['pp_payflow_password'] = $this->request->post('pp_payflow_password',$this->config->get('pp_payflow_password'));	
		
		$this->data['pp_payflow_partner'] = $this->request->post('pp_payflow_partner',$this->config->get('pp_payflow_partner'));	

		$this->data['pp_payflow_test'] = $this->request->post('pp_payflow_test',$this->config->get('pp_payflow_test'));

		$this->data['pp_payflow_transaction'] = $this->request->post('pp_payflow_transaction',$this->config->get('pp_payflow_transaction'));
		
		$this->data['pp_payflow_total'] = $this->request->post('pp_payflow_total',$this->config->get('pp_payflow_total'));

		$this->data['pp_payflow_order_status_id'] = $this->request->post('pp_payflow_order_status_id',$this->config->get('pp_payflow_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['pp_payflow_geo_zone_id'] = $this->request->post('pp_payflow_geo_zone_id',$this->config->get('pp_payflow_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['pp_payflow_status'] = $this->request->post('pp_payflow_status',$this->config->get('pp_payflow_status'));

		$this->data['pp_payflow_sort_order'] = $this->request->post('pp_payflow_sort_order',$this->config->get('pp_payflow_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/pp_payflow.tpl', $this->data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/pp_payflow')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['pp_payflow_vendor']) {
			$this->error['vendor'] = $this->data['error_vendor'];
		}

		if (!$this->request->post['pp_payflow_user']) {
			$this->error['user'] = $this->data['error_user'];
		}

		if (!$this->request->post['pp_payflow_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		if (!$this->request->post['pp_payflow_partner']) {
			$this->error['partner'] = $this->data['error_partner'];
		}

		return !$this->error;
	}
}