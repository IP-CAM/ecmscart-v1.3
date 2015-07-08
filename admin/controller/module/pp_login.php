<?php
class ControllerModulePPLogin extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->language->load('module/pp_login');

		$this->load->model('setting/setting');

		$this->document->setTitle($this->data['heading_title']);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pp_login', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] = (isset($this->error['warning']))? $this->error['warning']: '';
		
		$this->data['error_client_id'] = (isset($this->error['client_id']))? $this->error['client_id']: '';
		
		$this->data['error_secret'] = (isset($this->error['secret']))? $this->error['secret']: '';
		
	// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_module'],	// Text to display link
							$this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('module/pp_login', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL describe above
						));	

		$this->data['action'] = $this->url->link('module/pp_login', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['pp_login_client_id'] = $this->request->post('pp_login_client_id', $this->config->get('pp_login_client_id'));

		$this->data['pp_login_secret'] = $this->request->post('pp_login_secret', $this->config->get('pp_login_secret'));
		
		$this->data['pp_login_sandbox'] = $this->request->post('pp_login_sandbox', $this->config->get('pp_login_sandbox'));
		
		$this->data['pp_login_debug'] = $this->request->post('pp_login_debug', $this->config->get('pp_login_debug'));

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		$this->data['pp_login_customer_group_id'] = $this->request->post('pp_login_customer_group_id', $this->config->get('pp_login_customer_group_id'));

		if ($this->config->get('pp_login_button_colour')) {
			$this->data['pp_login_button_colour'] = $this->config->get('pp_login_button_colour');
		} else {
			$this->data['pp_login_button_colour'] = $this->request->post('pp_login_button_colour', 'blue');
		}

		$this->data['pp_login_seamless'] = $this->request->post('pp_login_seamless', $this->config->get('pp_login_seamless'));

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->data['locales'] = array();

		$this->data['locales'][] = array(
			'value' => 'en-gb',
			'text' => 'English (Great Britain)'
		);

		$this->data['locales'][] = array(
			'value' => 'zh-cn',
			'text' => 'Chinese (People\'s Republic of China)'
		);

		$this->data['locales'][] = array(
			'value' => 'zh-hk',
			'text' => 'Chinese (Hong Kong)',
		);

		$this->data['locales'][] = array(
			'value' => 'zh-tw',
			'text' => 'Chinese (Taiwan)'
		);

		$this->data['locales'][] = array(
			'value' => 'zh-xc',
			'text' => 'Chinese (US)'
		);

		$this->data['locales'][] = array(
			'value' => 'da-dk',
			'text' => 'Danish'
		);

		$this->data['locales'][] = array(
			'value' => 'nl-nl',
			'text' => 'Dutch'
		);

		$this->data['locales'][] = array(
			'value' => 'en-au',
			'text' => 'English (Australia)'
		);

		$this->data['locales'][] = array(
			'value' => 'en-us',
			'text' => 'English (US)',
		);

		$this->data['locales'][] = array(
			'value' => 'fr-fr',
			'text' => 'French'
		);

		$this->data['locales'][] = array(
			'value' => 'fr-ca',
			'text' => 'French (Canada)'
		);

		$this->data['locales'][] = array(
			'value' => 'fr-xc',
			'text' => 'French (international)'
		);

		$this->data['locales'][] = array(
			'value' => 'de-de',
			'text' => 'German'
		);

		$this->data['locales'][] = array(
			'value' => 'he-il',
			'text' => 'Hebrew (Israel)'
		);

		$this->data['locales'][] = array(
			'value' => 'id-id',
			'text' => 'Indonesian'
		);

		$this->data['locales'][] = array(
			'value' => 'it-il',
			'text' => 'Italian'
		);

		$this->data['locales'][] = array(
			'value' => 'ja-jp' ,
			'text' => 'Japanese'
		);

		$this->data['locales'][] = array(
			'value' => 'no-no',
			'text' => 'Norwegian'
		);

		$this->data['locales'][] = array(
			'value' => 'pl-pl',
			'text' => 'Polish');

		$this->data['locales'][] = array(
			'value' => 'pt-pt',
			'text' => 'Portuguese'
		);

		$this->data['locales'][] = array(
			'value' => 'pt-br',
			'text' => 'Portuguese (Brazil)'
		);

		$this->data['locales'][] = array(
			'value' => 'ru-ru',
			'text' => 'Russian'
		);

		$this->data['locales'][] = array(
			'value' => 'es-es',
			'text'  => 'Spanish'
		);

		$this->data['locales'][] = array(
			'value' => 'es-xc',
			'text'  => 'Spanish (Mexico)'
		);

		$this->data['locales'][] = array(
			'value' => 'sv-se',
			'text'  => 'Swedish'
		);

		$this->data['locales'][] = array(
			'value' => 'th-th',
			'text'  => 'Thai'
		);

		$this->data['locales'][] = array(
			'value' => 'tr-tr',
			'text'  => 'Turkish'
		);

		$this->data['pp_login_locale'] = $this->request->post('pp_login_locale', $this->config->get('pp_login_locale'));

		$this->data['return_url'] = HTTPS_CATALOG . 'index.php?route=module/pp_login/login';

		$this->data['pp_login_status'] = $this->request->post('pp_login_status', $this->config->get('pp_login_status'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/pp_login.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/pp_login')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['pp_login_client_id']) {
			$this->error['client_id'] = $this->data['error_client_id'];
		}

		if (!$this->request->post['pp_login_secret']) {
			$this->error['secret'] = $this->data['error_secret'];
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('extension/event');

		$this->model_extension_event->addEvent('pp_login', 'post.customer.logout', 'module/pp_login/logout');
	}

	public function uninstall() {
		$this->load->model('extension/event');

		$this->model_extension_event->deleteEvent('pp_login');
	}
}