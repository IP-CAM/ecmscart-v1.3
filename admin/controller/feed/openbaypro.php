<?php
class ControllerFeedOpenbaypro extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('feed/openbaypro');

		$this->document->setTitle($this->data['heading_title']);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_module'],
							$this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],	// Text to display link
							$this->url->link('feed/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
						));

		$this->data['cancel'] = $this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('feed/openbaypro.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/openbaypro')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function install() {
		$this->data = $this->load->model('setting/setting');
		$this->load->model('extension/event');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/openbay');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/openbay');

		$settings = $this->model_setting_setting->getSetting('openbaypro');
		$settings['openbaypro_menu'] = 1;
		$settings['openbaypro_status'] = 1;
		$this->model_setting_setting->editSetting('openbaypro', $settings);

		// register the event triggers
		$this->model_extension_event->addEvent('openbay', 'post.product.delete', 'extension/openbay/eventDeleteProduct');
		$this->model_extension_event->addEvent('openbay', 'post.product.edit', 'extension/openbay/eventEditProduct');
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->load->model('extension/event');
		
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/openbay');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/openbay');
		
		$settings = $this->model_setting_setting->getSetting('openbaypro');
		$settings['openbaypro_menu'] = 0;
		$settings['openbaypro_status'] = 0;
		$this->model_setting_setting->editSetting('openbaypro', $settings);

		// delete the event triggers
		$this->model_extension_event->deleteEvent('openbay');
	}
}