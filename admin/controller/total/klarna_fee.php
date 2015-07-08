<?php
class ControllerTotalKlarnaFee extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('total/klarna_fee');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$status = false;

			foreach ($this->request->post['klarna_fee'] as $klarna_account) {
				if ($klarna_account['status']) {
					$status = true;

					break;
				}
			}

			$this->model_setting_setting->editSetting('klarna_fee', array_merge($this->request->post, array('klarna_fee_status' => $status)));

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
							$this->url->link('total/klarna_fee', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['action'] = $this->url->link('total/klarna_fee', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['countries'] = array();

		$this->data['countries'][] = array(
			'name' => $this->data['text_germany'],
			'code' => 'DEU'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_netherlands'],
			'code' => 'NLD'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_denmark'],
			'code' => 'DNK'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_sweden'],
			'code' => 'SWE'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_norway'],
			'code' => 'NOR'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_finland'],
			'code' => 'FIN'
		);
		
		$this->data['klarna_fee'] = $this->request->post('klarna_fee', $this->config->get('klarna_fee'));
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('total/klarna_fee.tpl', $this->data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'total/klarna_fee')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}