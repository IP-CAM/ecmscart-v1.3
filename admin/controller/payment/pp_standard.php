<?php
class ControllerPaymentPPStandard extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/pp_standard');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pp_standard', $this->request->post);

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
							 $this->url->link('payment/pp_standard', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('payment/pp_standard', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['pp_standard_email'] = $this->request->post('pp_standard_email',$this->config->get('pp_standard_email'));
		
		$this->data['pp_standard_test'] = $this->request->post('pp_standard_test',$this->config->get('pp_standard_test'));	

		$this->data['pp_standard_transaction'] = $this->request->post('pp_standard_transaction',$this->config->get('pp_standard_transaction'));
		
		$this->data['pp_standard_debug'] = $this->request->post('pp_standard_debug',$this->config->get('pp_standard_debug'));

		$this->data['pp_standard_total'] = $this->request->post('pp_standard_total',$this->config->get('pp_standard_total'));

		$this->data['pp_standard_canceled_reversal_status_id'] = $this->request->post('pp_standard_canceled_reversal_status_id',$this->config->get('pp_standard_canceled_reversal_status_id'));

		$this->data['pp_standard_completed_status_id'] = $this->request->post('pp_standard_completed_status_id',$this->config->get('pp_standard_completed_status_id'));

		$this->data['pp_standard_denied_status_id'] = $this->request->post('pp_standard_denied_status_id',$this->config->get('pp_standard_denied_status_id'));

		$this->data['pp_standard_expired_status_id'] = $this->request->post('pp_standard_expired_status_id',$this->config->get('pp_standard_expired_status_id'));

		$this->data['pp_standard_failed_status_id'] = $this->request->post('pp_standard_failed_status_id',$this->config->get('pp_standard_failed_status_id'));
		
		$this->data['pp_standard_pending_status_id'] = $this->request->post('pp_standard_pending_status_id',$this->config->get('pp_standard_pending_status_id'));

		$this->data['pp_standard_processed_status_id'] = $this->request->post('pp_standard_processed_status_id',$this->config->get('pp_standard_processed_status_id'));
		
		$this->data['pp_standard_refunded_status_id'] = $this->request->post('pp_standard_refunded_status_id',$this->config->get('pp_standard_refunded_status_id'));

		$this->data['pp_standard_reversed_status_id'] = $this->request->post('pp_standard_reversed_status_id',$this->config->get('pp_standard_reversed_status_id'));
		
		$this->data['pp_standard_voided_status_id'] = $this->request->post('pp_standard_voided_status_id',$this->config->get('pp_standard_voided_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['pp_standard_geo_zone_id'] = $this->request->post('pp_standard_geo_zone_id',$this->config->get('pp_standard_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['pp_standard_status'] = $this->request->post('pp_standard_status',$this->config->get('pp_standard_status'));

		$this->data['pp_standard_sort_order'] = $this->request->post('pp_standard_sort_order',$this->config->get('pp_standard_sort_order'));

		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/pp_standard.tpl', $this->data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/pp_standard')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['pp_standard_email']) {
			$this->error['email'] = $this->data['error_email'];
		}

		return !$this->error;
	}
}