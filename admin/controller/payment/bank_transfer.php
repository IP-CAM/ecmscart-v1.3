<?php
class ControllerPaymentBankTransfer extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/bank_transfer');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('bank_transfer', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if (isset($this->error['bank' . $language['language_id']])) {
				$this->data['error_bank' . $language['language_id']] = $this->error['bank' . $language['language_id']];
			} else {
				$this->data['error_bank' . $language['language_id']] = '';
			}
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/bank_transfer', 'token=' . $this->session->data['token'], 'SSL')
						));
		
		$this->data['action'] = $this->url->link('payment/bank_transfer', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/language');

		foreach ($languages as $language) {
			if (isset($this->request->post['bank_transfer_bank' . $language['language_id']])) {
				$this->data['bank_transfer_bank' . $language['language_id']] = $this->request->post['bank_transfer_bank' . $language['language_id']];
			} else {
				$this->data['bank_transfer_bank' . $language['language_id']] = $this->config->get('bank_transfer_bank' . $language['language_id']);
			}
		}

		$this->data['languages'] = $languages;

		$this->data['bank_transfer_total'] = $this->request->post('bank_transfer_total',$this->config->get('bank_transfer_total'));
		
		$this->data['bank_transfer_order_status_id'] = $this->request->post('bank_transfer_order_status_id', $this->config->get('bank_transfer_order_status_id'));
		
		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['bank_transfer_geo_zone_id'] = $this->request->post('bank_transfer_geo_zone_id', $this->config->get('bank_transfer_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['bank_transfer_status'] = $this->request->post('bank_transfer_status', $this->config->get('bank_transfer_status'));

		$this->data['bank_transfer_sort_order'] = $this->request->post('bank_transfer_sort_order', $this->config->get('bank_transfer_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/bank_transfer.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/bank_transfer')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if (empty($this->request->post['bank_transfer_bank' . $language['language_id']])) {
				$this->error['bank' .  $language['language_id']] = $this->data['error_bank'];
			}
		}

		return !$this->error;
	}
}