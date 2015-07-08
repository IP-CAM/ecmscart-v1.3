<?php
class ControllerPaymentAuthorizeNetSim extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/authorizenet_sim');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('authorizenet_sim', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_merchant'] =  (isset($this->error['merchant'])?$this->error['merchant']:'');

		$this->data['error_key'] =  (isset($this->error['key'])?$this->error['key']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/authorizenet_sim', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('payment/authorizenet_sim', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['authorizenet_sim_merchant'] = $this->request->post('authorizenet_sim_merchant',$this->config->get('authorizenet_sim_merchant'));
		
		$this->data['authorizenet_sim_key'] = $this->request->post('authorizenet_sim_key',$this->config->get('authorizenet_sim_key'));
		
		$this->data['authorizenet_sim_test'] = $this->request->post('authorizenet_sim_test',$this->config->get('authorizenet_sim_test'));
		
		$this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/authorizenet_sim/callback';

		$this->data['authorizenet_sim_md5'] = $this->request->post('authorizenet_sim_md5',$this->config->get('authorizenet_sim_md5'));
		
		$this->data['authorizenet_sim_total'] = $this->request->post('authorizenet_sim_total',$this->config->get('authorizenet_sim_total'));
		
		$this->data['authorizenet_sim_order_status_id'] = $this->request->post('authorizenet_sim_order_status_id',$this->config->get('authorizenet_sim_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['authorizenet_sim_geo_zone_id'] = $this->request->post('authorizenet_sim_geo_zone_id',$this->config->get('authorizenet_sim_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['authorizenet_sim_status'] = $this->request->post('authorizenet_sim_status',$this->config->get('authorizenet_sim_status'));
	
		$this->data['authorizenet_sim_sort_order'] = $this->request->post('authorizenet_sim_sort_order',$this->config->get('authorizenet_sim_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/authorizenet_sim.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/authorizenet_sim')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['authorizenet_sim_merchant']) {
			$this->error['merchant'] = $this->data['error_merchant'];
		}

		if (!$this->request->post['authorizenet_sim_key']) {
			$this->error['key'] = $this->data['error_key'];
		}

		return !$this->error;
	}
}