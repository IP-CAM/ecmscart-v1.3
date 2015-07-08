<?php
class ControllerPaymentAuthorizenetAim extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/authorizenet_aim');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('authorizenet_aim', $this->request->post);

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
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/authorizenet_aim', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('payment/authorizenet_aim', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['authorizenet_aim_login'] = $this->request->post('authorizenet_aim_login',$this->config->get('authorizenet_aim_login'));
		
		$this->data['authorizenet_aim_key'] = $this->request->post('authorizenet_aim_key',$this->config->get('authorizenet_aim_key'));
		
		$this->data['authorizenet_aim_hash'] = $this->request->post('authorizenet_aim_hash',$this->config->get('authorizenet_aim_hash'));
		
		$this->data['authorizenet_aim_server'] = $this->request->post('authorizenet_aim_server',$this->config->get('authorizenet_aim_server'));
		
		$this->data['authorizenet_aim_mode'] = $this->request->post('authorizenet_aim_mode',$this->config->get('authorizenet_aim_mode'));
		
		$this->data['authorizenet_aim_method'] = $this->request->post('authorizenet_aim_method',$this->config->get('authorizenet_aim_method'));
		
		$this->data['authorizenet_aim_total'] = $this->request->post('authorizenet_aim_total',$this->config->get('authorizenet_aim_total'));
		
		$this->data['authorizenet_aim_order_status_id'] = $this->request->post('authorizenet_aim_order_status_id',$this->config->get('authorizenet_aim_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['authorizenet_aim_geo_zone_id'] = $this->request->post('authorizenet_aim_geo_zone_id',$this->config->get('authorizenet_aim_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['authorizenet_aim_status'] = $this->request->post('authorizenet_aim_status',$this->config->get('authorizenet_aim_status'));
		
		$this->data['authorizenet_aim_sort_order'] = $this->request->post('authorizenet_aim_sort_order',$this->config->get('authorizenet_aim_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/authorizenet_aim.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/authorizenet_aim')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['authorizenet_aim_login']) {
			$this->error['login'] = $this->data['error_login'];
		}

		if (!$this->request->post['authorizenet_aim_key']) {
			$this->error['key'] = $this->data['error_key'];
		}

		return !$this->error;
	}
}