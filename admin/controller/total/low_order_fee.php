<?php
class ControllerTotalLowOrderFee extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('total/low_order_fee');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('low_order_fee', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  isset($this->error['warning'])?$this->error['warning']: '';

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_total'],	// Text to display link
							$this->url->link('extension/total', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('total/low_order_fee', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('total/low_order_fee', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['low_order_fee_total'] = $this->request->post('low_order_fee_total', $this->config->get('low_order_fee_total'));
		
		$this->data['low_order_fee_fee'] = $this->request->post('low_order_fee_fee', $this->config->get('low_order_fee_fee'));
		
		$this->data['low_order_fee_tax_class_id'] = $this->request->post('low_order_fee_tax_class_id', $this->config->get('low_order_fee_tax_class_id'));
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		$this->data['low_order_fee_status'] = $this->request->post('low_order_fee_status', $this->config->get('low_order_fee_status'));

		$this->data['low_order_fee_sort_order'] = $this->request->post('low_order_fee_sort_order', $this->config->get('low_order_fee_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('total/low_order_fee.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'total/low_order_fee')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}