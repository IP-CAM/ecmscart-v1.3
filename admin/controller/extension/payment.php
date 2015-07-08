<?php
class ControllerExtensionPayment extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('extension/payment');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		$this->getList();
	}

	public function install() {
		$this->data = $this->load->language('extension/payment');

		$this->load->model('extension/extension');

		if ($this->validate()) {
			$this->model_extension_extension->install('payment', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'payment/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'payment/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('payment/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();
	}

	public function uninstall() {
		$this->data = $this->load->language('extension/payment');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		if ($this->validate()) {
			$this->model_extension_extension->uninstall('payment', $this->request->get['extension']);

			$this->load->model('setting/setting');

			$this->model_setting_setting->deleteSetting($this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('payment/' . $this->request->get['extension'] . '/uninstall');
			
			$this->load->model('user/user_group');
			
			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'payment/' . $this->request->get['extension']);
			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'payment/' . $this->request->get['extension']);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();
	}

	public function getList() {
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
						));
		
		
		$this->data['error_warning'] = (isset($this->error['warning'])) ?  $this->error['warning']: '';

		$this->data['success'] = (isset($this->session->data['success']))?  $this->session->data['success']: '';
	
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);

		$this->load->model('extension/extension');

		$extensions = $this->model_extension_extension->getInstalled('payment');

		foreach ($extensions as $key => $value) {
			if (!file_exists(DIR_APPLICATION . 'controller/payment/' . $value . '.php')) {
				$this->model_extension_extension->uninstall('payment', $value);

				unset($extensions[$key]);
			}
		}

		$this->data['extensions'] = array();

		$files = glob(DIR_APPLICATION . 'controller/payment/*.php');
		
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				$this->data2 = $this->load->language('payment/' . $extension);

				$text_link = isset($this->data2['text_' . $extension])? $this->data2['text_' . $extension]: '';

				if ($text_link != 'text_' . $extension) {
					$link = isset($this->data2['text_' . $extension])? $this->data2['text_' . $extension]: '';
				} else {
					$link = '';
				}

				$this->data['extensions'][] = array(
					'name'       => $this->data2['heading_title'],
					'link'       => $link,
					'status'     => $this->config->get($extension . '_status') ? $this->data2['text_enabled'] : $this->data2['text_disabled'],
					'sort_order' => $this->config->get($extension . '_sort_order'),
					'install'   => $this->url->link('extension/payment/install', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
					'uninstall' => $this->url->link('extension/payment/uninstall', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('payment/' . $extension . '', 'token=' . $this->session->data['token'], 'SSL')
				);
			}
		}
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}