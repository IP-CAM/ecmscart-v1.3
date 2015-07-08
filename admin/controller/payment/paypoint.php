<?php
class ControllerPaymentPayPoint extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/paypoint');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paypoint', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_merchant'] =  (isset($this->error['merchant'])?$this->error['merchant']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/paypoint', 'token=' . $this->session->data['token'], 'SSL')
						));	

		$this->data['action'] = $this->url->link('payment/paypoint', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		
		$this->data['paypoint_merchant'] = $this->request->post('paypoint_merchant', $this->config->get('paypoint_merchant'));
		
		$this->data['paypoint_password'] = $this->request->post('paypoint_password', $this->config->get('paypoint_password'));	
		
		$this->data['paypoint_test'] = $this->request->post('paypoint_test', $this->config->get('paypoint_test'));	
		
		$this->data['paypoint_total'] = $this->request->post('paypoint_total', $this->config->get('paypoint_total'));
		
		$this->data['paypoint_order_status_id'] = $this->request->post('paypoint_order_status_id', $this->config->get('paypoint_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['paypoint_geo_zone_id'] = $this->request->post('paypoint_geo_zone_id', $this->config->get('paypoint_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['paypoint_status'] = $this->request->post('paypoint_status', $this->config->get('paypoint_status'));

		$this->data['paypoint_sort_order'] = $this->request->post('paypoint_sort_order', $this->config->get('paypoint_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/paypoint.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paypoint')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['paypoint_merchant']) {
			$this->error['merchant'] = $this->data['error_merchant'];
		}

		return !$this->error;
	}
}