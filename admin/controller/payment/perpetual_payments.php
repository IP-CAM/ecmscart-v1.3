<?php
class ControllerPaymentPerpetualPayments extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/perpetual_payments');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('perpetual_payments', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');

		$this->data['error_auth_id'] =  (isset($this->error['auth_id'])? $this->error['auth_id']: '');

		$this->data['error_auth_pass'] =  (isset($this->error['auth_pass'])? $this->error['auth_pass']: '');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/perpetual_payments', 'token=' . $this->session->data['token'], 'SSL')
						));	

		$this->data['action'] = $this->url->link('payment/perpetual_payments', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['perpetual_payments_auth_id'] = $this->request->post('perpetual_payments_auth_id', $this->config->get('perpetual_payments_auth_id'));
		
		$this->data['perpetual_payments_auth_pass'] = $this->request->post('perpetual_payments_auth_pass', $this->config->get('perpetual_payments_auth_pass'));	
		
		$this->data['perpetual_payments_test'] = $this->request->post('perpetual_payments_test', $this->config->get('perpetual_payments_test'));
		
		$this->data['perpetual_payments_total'] = $this->request->post('perpetual_payments_total', $this->config->get('perpetual_payments_total'));
		
		$this->data['perpetual_payments_order_status_id'] = $this->request->post('perpetual_payments_order_status_id', $this->config->get('perpetual_payments_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['perpetual_payments_geo_zone_id'] = $this->request->post('perpetual_payments_geo_zone_id', $this->config->get('perpetual_payments_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['perpetual_payments_status'] = $this->request->post('perpetual_payments_status', $this->config->get('perpetual_payments_status'));
		
		$this->data['perpetual_payments_sort_order'] = $this->request->post('perpetual_payments_sort_order', $this->config->get('perpetual_payments_sort_order'));

		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/perpetual_payments.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/perpetual_payments')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['perpetual_payments_auth_id']) {
			$this->error['auth_id'] = $this->data['error_auth_id'];
		}

		if (!$this->request->post['perpetual_payments_auth_pass']) {
			$this->error['auth_pass'] = $this->data['error_auth_pass'];
		}

		return !$this->error;
	}
}