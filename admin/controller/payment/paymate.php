<?php
class ControllerPaymentPayMate extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/paymate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paymate', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_username'] =  (isset($this->error['username'])?$this->error['username']:'');
		
		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/paymate', 'token=' . $this->session->data['token'], 'SSL')
						));	

		$this->data['action'] = $this->url->link('payment/paymate', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['paymate_username'] = $this->request->post('paymate_username', $this->config->get('paymate_username'));
		
		if ($this->config->get('paymate_password') && !$this->error) {
			$this->data['paymate_password'] = $this->config->get('paymate_password');
		} else {
			$this->data['paymate_password'] = $this->request->post('paymate_username', md5(mt_rand()));
		}
		
		$this->data['paymate_test'] = $this->request->post('paymate_test', $this->config->get('paymate_test'));
		
		$this->data['paymate_total'] = $this->request->post('paymate_total', $this->config->get('paymate_total'));

		$this->data['paymate_order_status_id'] = $this->request->post('paymate_order_status_id',$this->config->get('paymate_order_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['paymate_geo_zone_id'] = $this->request->post('paymate_geo_zone_id', $this->config->get('paymate_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['paymate_status'] = $this->request->post('paymate_status', $this->config->get('paymate_status'));
		$this->data['paymate_sort_order'] = $this->request->post('paymate_sort_order', $this->config->get('paymate_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/paymate.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paymate')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['paymate_username']) {
			$this->error['username'] = $this->data['error_username'];
		}

		if (!$this->request->post['paymate_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		return !$this->error;
	}
}
