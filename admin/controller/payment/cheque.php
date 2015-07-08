<?php
class ControllerPaymentCheque extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/cheque');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('cheque', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_payable'] =  (isset($this->error['payable'])?$this->error['payable']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/cheque', 'token=' . $this->session->data['token'], 'SSL')
						));
	
		$this->data['action'] = $this->url->link('payment/cheque', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cheque_payable'] = $this->request->post('cheque_payable',$this->config->get('cheque_payable'));
		
		$this->data['cheque_total'] = $this->request->post('cheque_total',$this->config->get('cheque_total'));
			
		$this->data['cheque_order_status_id'] = $this->request->post('cheque_order_status_id',$this->config->get('cheque_order_status_id'));

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['cheque_geo_zone_id'] = $this->request->post('cheque_geo_zone_id',$this->config->get('cheque_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['cheque_status'] = $this->request->post('cheque_status',$this->config->get('cheque_status'));

		$this->data['cheque_sort_order'] = $this->request->post('cheque_sort_order',$this->config->get('cheque_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/cheque.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/cheque')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['cheque_payable']) {
			$this->error['payable'] = $this->data['error_payable'];
		}

		return !$this->error;
	}
}