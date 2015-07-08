<?php
class ControllerOpenbayEtsy extends Controller {
	public function install() {
		$this->load->language('openbay/etsy');
		$this->load->model('openbay/etsy');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');
		$this->load->model('extension/event');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/etsy_product');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/etsy_product');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/etsy_shipping');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/etsy_shipping');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/etsy_shop');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/etsy_shop');

		$this->model_openbay_etsy->install();
	}

	public function uninstall() {
		$this->load->model('openbay/etsy');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');
		$this->load->model('extension/event');

		$this->model_openbay_etsy->uninstall();
		$this->model_extension_extension->uninstall('openbay', $this->request->get['extension']);
		$this->model_setting_setting->deleteSetting($this->request->get['extension']);
	}

	public function index() {
		$this->data = $this->load->language('openbay/etsy');

		$this->document->setTitle($this->data['text_dashboard']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_etsy'],
							$this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['success'] = isset($this->session->data['success']) ? $this->session->data['success']: '';

		if (isset($this->session->data['success']))			
			unset($this->session->data['success']);

		$this->data['validation'] 	= $this->openbay->etsy->validate();
		$this->data['links_settings'] = $this->url->link('openbay/etsy/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_products'] = $this->url->link('openbay/etsy_product/links', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_listings'] = $this->url->link('openbay/etsy_product/listings', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy.tpl', $this->data));
	}

	public function settings() {
		$this->data = $this->load->language('openbay/etsy_settings');

		$this->load->model('setting/setting');
		$this->load->model('openbay/etsy');
		$this->load->model('localisation/order_status');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('etsy', $this->request->post);
			$this->session->data['success'] = $this->data['text_success'];
			$this->response->redirect($this->url->link('openbay/etsy/index&token=' . $this->session->data['token']));
		}

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_etsy'],
							$this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/etsy/settings', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['action'] = $this->url->link('openbay/etsy/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] = isset($this->session->data['warning']) ? $this->session->data['warning']: '';
		
		$this->data['etsy_status'] = $this->request->post('etsy_status', $this->config->get('etsy_status'));
		
		$this->data['etsy_token'] = $this->request->post('etsy_token', $this->config->get('etsy_token'));
		
		$this->data['etsy_enc1'] = $this->request->post('etsy_enc1', $this->config->get('etsy_enc1'));
		
		$this->data['etsy_enc2'] = $this->request->post('etsy_enc2', $this->config->get('etsy_enc2'));
		
		$this->data['etsy_address_format'] = $this->request->post('etsy_address_format', $this->config->get('etsy_address_format'));
		
		$this->data['etsy_order_status_new'] = $this->request->post('etsy_order_status_new', $this->config->get('etsy_order_status_new'));
		
		$this->data['etsy_order_status_paid'] = $this->request->post('etsy_order_status_paid', $this->config->get('etsy_order_status_paid'));
		
		$this->data['etsy_order_status_shipped'] = $this->request->post('etsy_order_status_shipped', $this->config->get('etsy_order_status_shipped'));
		
		$this->data['etsy_token'] = $this->request->post('etsy_token', $this->config->get('etsy_token'));

		$this->data['api_server'] = $this->openbay->etsy->getServer();
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['account_info'] = $this->model_openbay_etsy->verifyAccount();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy_settings.tpl', $this->data));
	}

	public function settingsUpdate() {
		$this->openbay->etsy->settingsUpdate();

		$response = array('header_code' => 200);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function getOrders() {
		$response = $this->openbay->etsy->call('order/get/all', 'GET');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'openbay/etsy')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}