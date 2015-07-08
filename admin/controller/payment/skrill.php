<?php
class ControllerPaymentSkrill extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/skrill');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('skrill', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/skrill', 'token=' . $this->session->data['token'], 'SSL')
					));

		$this->data['action'] = $this->url->link('payment/skrill', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['skrill_email'] = $this->request->post('skrill_email',$this->config->get('skrill_email'));
		
		$this->data['skrill_secret'] = $this->request->post('skrill_secret',$this->config->get('skrill_secret'));
		
		$this->data['skrill_total'] = $this->request->post('skrill_total',$this->config->get('skrill_total'));
		
		$this->data['skrill_order_status_id'] = $this->request->post('skrill_order_status_id',$this->config->get('skrill_order_status_id'));

		$this->data['skrill_pending_status_id'] = $this->request->post('skrill_pending_status_id',$this->config->get('skrill_pending_status_id'));

		$this->data['skrill_canceled_status_id'] = $this->request->post('skrill_canceled_status_id',$this->config->get('skrill_canceled_status_id'));

		$this->data['skrill_failed_status_id'] = $this->request->post('skrill_failed_status_id',$this->config->get('skrill_failed_status_id'));

		$this->data['skrill_chargeback_status_id'] = $this->request->post('skrill_chargeback_status_id',$this->config->get('skrill_chargeback_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['skrill_geo_zone_id'] = $this->request->post('skrill_geo_zone_id',$this->config->get('skrill_geo_zone_id'));

		 $this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['skrill_status'] = $this->request->post('skrill_status',$this->config->get('skrill_status'));

		$this->data['skrill_sort_order'] = $this->request->post('skrill_sort_order',$this->config->get('skrill_sort_order'));

		$this->data['skrill_rid'] = $this->request->post('skrill_rid',$this->config->get('skrill_rid'));

		$this->data['skrill_custnote'] = $this->request->post('skrill_custnote',$this->config->get('skrill_custnote'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/skrill.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/skrill')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['skrill_email']) {
			$this->error['email'] = $this->data['error_email'];
		}

		return !$this->error;
	}
}