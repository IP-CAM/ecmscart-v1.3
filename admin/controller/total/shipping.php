<?php
class ControllerTotalShipping extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('total/shipping');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping', $this->request->post);

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
							$this->url->link('total/shipping', 'token=' . $this->session->data['token'], 'SSL')
						));		

		$this->data['action'] = $this->url->link('total/shipping', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['shipping_estimator'] = $this->request->post('shipping_estimator', $this->config->get('shipping_estimator'));
		
		$this->data['shipping_status'] = $this->request->post('shipping_status', $this->config->get('shipping_status'));
		
		$this->data['shipping_sort_order'] = $this->request->post('shipping_sort_order', $this->config->get('shipping_sort_order'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('total/shipping.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'total/shipping')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}