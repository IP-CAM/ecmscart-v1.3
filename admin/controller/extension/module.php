<?php
class ControllerExtensionModule extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('extension/module');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->load->model('extension/extension');
		
		$this->load->model('extension/module');

		$this->getList();
	}
	
	public function install() {
		$this->data = $this->load->language('extension/module');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		$this->load->model('extension/module');

		if ($this->validate()) {
			$this->model_extension_extension->install('module', $this->request->get['extension']);

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'module/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'module/' . $this->request->get['extension']);

			// Call install method if it exsits
			$this->load->controller('module/' . $this->request->get['extension'] . '/install');

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();	
	}
	
	public function uninstall() {
		$this->data = $this->load->language('extension/module');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		$this->load->model('extension/module');

		if ($this->validate()) {
			$this->model_extension_extension->uninstall('module', $this->request->get['extension']);

			$this->model_extension_module->deleteModulesByCode($this->request->get['extension']);

			$this->load->model('setting/setting');

			$this->model_setting_setting->deleteSetting($this->request->get['extension']);

			// Call uninstall method if it exsits
			$this->load->controller('module/' . $this->request->get['extension'] . '/uninstall');
			
			$this->load->model('user/user_group');

			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'module/' . $this->request->get['extension']);
			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'module/' . $this->request->get['extension']);


			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}	
		
		$this->getList();
	}
	
	public function delete() {
		$this->data = $this->load->language('extension/module');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('extension/extension');

		$this->load->model('extension/module');

		if (isset($this->request->get['module_id']) && $this->validateDelete()) {
			$this->model_extension_module->deleteModule($this->request->get['module_id']);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();		
	}
	
	public function getList() {
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							
						));
		
		$this->data['text_layout'] = sprintf($this->data['text_layout'], $this->url->link('design/layout', 'token=' . $this->session->data['token'], 'SSL'));
		
		$this->data['error_warning'] = (isset($this->error['warning'])) ?  $this->error['warning']: '';

		$this->data['success'] = (isset($this->session->data['success']))?  $this->session->data['success']: '';
		
		$this->data['selected'] = (isset($this->request->post['selected'])) ? (array)$this->request->post['selected']: array();
	
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);
			
		$this->data['delete'] = $this->url->link('extension/module/delete', 'token=' . $this->session->data['token'], 'SSL');

		$extensions = $this->model_extension_extension->getInstalled('module');

		foreach ($extensions as $key => $value) {
			if (!file_exists(DIR_APPLICATION . 'controller/module/' . $value . '.php')) {
				$this->model_extension_extension->uninstall('module', $value);

				unset($extensions[$key]);
				
				$this->model_extension_module->deleteModulesByCode($value);
			}
		}	

		$this->data['extensions'] = array();

		$files = glob(DIR_APPLICATION . 'controller/module/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->data2 = $this->load->language('module/' . $extension);

				$module_data = array();
				
				$modules = $this->model_extension_module->getModulesByCode($extension);
				
				foreach ($modules as $module) {
					$module_data[] = array(
						'module_id' => $module['module_id'],
						'name'      => $this->data2['heading_title'] . ' &gt; ' . $module['name'],
						'edit'      => $this->url->link('module/' . $extension, 'token=' . $this->session->data['token'] . '&module_id=' . $module['module_id'], 'SSL'),
						'delete'    => $this->url->link('extension/module/delete', 'token=' . $this->session->data['token'] . '&module_id=' . $module['module_id'], 'SSL')
					);
				}

				$this->data['extensions'][] = array(
					'name'      => $this->data2['heading_title'],
					'module'    => $module_data,
					'install'   => $this->url->link('extension/module/install', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
					'uninstall' => $this->url->link('extension/module/uninstall', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
					'installed' => in_array($extension, $extensions),
					'edit'      => $this->url->link('module/' . $extension, 'token=' . $this->session->data['token'], 'SSL')
				);
			}
		}
				
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module.tpl', $this->data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
			
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/module')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}	
}