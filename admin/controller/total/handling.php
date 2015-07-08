<?php
class ControllerTotalHandling extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('total/handling');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('handling', $this->request->post);

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
							$this->url->link('total/handling', 'token=' . $this->session->data['token'], 'SSL')
						));
		
		$this->data['action'] = $this->url->link('total/handling', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['handling_total'] = $this->request->post('handling_total', $this->config->get('handling_total'));
		
		$this->data['handling_fee'] = $this->request->post('handling_fee', $this->config->get('handling_fee'));
		
		$this->data['handling_tax_class_id'] = $this->request->post('handling_tax_class_id', $this->config->get('handling_tax_class_id'));

		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		$this->data['handling_status'] = $this->request->post('handling_status', $this->config->get('handling_status'));
		
		$this->data['handling_sort_order'] = $this->request->post('handling_sort_order', $this->config->get('handling_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('total/handling.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'total/handling')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}