<?php
class ControllerExtensionTotal extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('extension/total');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		$this->getList();
	}

	public function install() {
		$this->data = $this->load->language('extension/total');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		if ($this->validate()) {
			$this->model_extension_extension->install('total', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'total/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'total/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('total/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();
	}

	public function uninstall() {
		$this->data = $this->load->language('extension/total');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		if ($this->validate()) {
			$this->model_extension_extension->uninstall('total', $this->request->get['extension']);

			$this->load->model('setting/setting');

			$this->model_setting_setting->deleteSetting($this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('total/' . $this->request->get['extension'] . '/uninstall');
			
			$this->load->model('user/user_group');

			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'total/' . $this->request->get['extension']);
			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'total/' . $this->request->get['extension']);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();
	}

	public function getList() {
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							
						));
		
		$this->data['error_warning'] = (isset($this->error['warning'])) ?  $this->error['warning']: '';

		$this->data['success'] = (isset($this->session->data['success']))?  $this->session->data['success']: '';
	
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);

		$this->load->model('extension/extension');

		$extensions = $this->model_extension_extension->getInstalled('total');

		foreach ($extensions as $key => $value) {
			if (!file_exists(DIR_APPLICATION . 'controller/total/' . $value . '.php')) {
				$this->model_extension_extension->uninstall('total', $value);

				unset($extensions[$key]);
			}
		}

		$this->data['extensions'] = array();

		$files = glob(DIR_APPLICATION . 'controller/total/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->data2 = $this->load->language('total/' . $extension);

				$this->data['extensions'][] = array(
					'name'       => $this->data2['heading_title'],
					'status'     => $this->config->get($extension . '_status') ? $this->data2['text_enabled'] : $this->data2['text_disabled'],
					'sort_order' => $this->config->get($extension . '_sort_order'),
					'install'   => $this->url->link('extension/total/install', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
					'uninstall' => $this->url->link('extension/total/uninstall', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('total/' . $extension . '', 'token=' . $this->session->data['token'], 'SSL')
				);
			}
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/total.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}