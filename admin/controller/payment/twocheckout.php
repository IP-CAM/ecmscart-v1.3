<?php
class ControllerPaymentTwoCheckout extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/twocheckout');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('twocheckout', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_account'] =  (isset($this->error['account'])?$this->error['account']:'');

		$this->data['error_secret'] =  (isset($this->error['secret'])?$this->error['secret']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/twocheckout', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('payment/twocheckout', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['twocheckout_account'] = $this->request->post('twocheckout_account',$this->config->get('twocheckout_account'));
		
		$this->data['twocheckout_secret'] = $this->request->post('twocheckout_secret',$this->config->get('twocheckout_secret'));

		$this->data['twocheckout_display'] = $this->request->post('twocheckout_display',$this->config->get('twocheckout_display'));

		$this->data['twocheckout_test'] = $this->request->post('twocheckout_test',$this->config->get('twocheckout_test'));

		$this->data['twocheckout_total'] = $this->request->post('twocheckout_total',$this->config->get('twocheckout_total'));

		$this->data['twocheckout_order_status_id'] = $this->request->post('twocheckout_order_status_id',$this->config->get('twocheckout_order_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['twocheckout_geo_zone_id'] = $this->request->post('twocheckout_geo_zone_id',$this->config->get('twocheckout_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['twocheckout_status'] = $this->request->post('twocheckout_status',$this->config->get('twocheckout_status'));

		$this->data['twocheckout_sort_order'] = $this->request->post('twocheckout_sort_order',$this->config->get('twocheckout_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/twocheckout.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/twocheckout')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['twocheckout_account']) {
			$this->error['account'] = $this->data['error_account'];
		}

		if (!$this->request->post['twocheckout_secret']) {
			$this->error['secret'] = $this->data['error_secret'];
		}

		return !$this->error;
	}
}