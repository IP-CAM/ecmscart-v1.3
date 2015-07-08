<?php
class ControllerExtensionOpenbay extends Controller {
	private $error = array();
	// sorting and filter array orderList of openbay
	private $url_data = array(
				'filter_order_id',
				'filter_customer', 
				'filter_order_status_id',
				'filter_date_added',
				'filter_channel',
				'sort',
				'order',
				'page',
			);
	// sorting and filter array orderList of openbay items
	private  $url_data2 = array(
				'filter_name' => 'encode', // for using these functions urlencode(html_entity_decode($this->get[$item], ENT_QUOTES, 'UTF-8'));
				'filter_model' => 'encode', 
				'filter_price',
				'filter_price_to',
				'filter_quantity',
				'filter_quantity_to',
				'filter_status',
				'filter_sku',
				'filter_desc',
				'filter_category',
				'filter_manufacturer',
				'filter_marketplace',
				'sort',
				'order',
				'page',
			);
			
	public function install() {
		$this->data = $this->load->language('extension/openbay');

		$this->load->model('extension/extension');

		if (!$this->user->hasPermission('modify', 'extension/openbay')) {
			$this->session->data['error'] = $this->data['error_permission'];

			$this->response->redirect($this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'));
		} else {
			
			$this->model_extension_extension->install('openbay', $this->request->get['extension']);
			
			$this->session->data['success'] = $this->data['text_install_success'];

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/' . $this->request->get['extension']);
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/' . $this->request->get['extension']);

			require_once(DIR_APPLICATION . 'controller/openbay/' . $this->request->get['extension'] . '.php');

			$class = 'ControllerOpenbay' . str_replace('_', '', $this->request->get['extension']);
			$class = new $class($this->registry);

			if (method_exists($class, 'install')) {
				$class->install();
			}
			$this->response->redirect($this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	public function uninstall() {
		$this->data = $this->load->language('extension/openbay');

		$this->load->model('extension/extension');

		if (!$this->user->hasPermission('modify', 'extension/openbay')) {
			$this->session->data['error'] = $this->data['error_permission'];

			$this->response->redirect($this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'));
		} else {
			$this->session->data['success'] = $this->data['text_uninstall_success'];

			require_once(DIR_APPLICATION . 'controller/openbay/' . $this->request->get['extension'] . '.php');

			$this->load->model('extension/extension');
			$this->load->model('setting/setting');

			$this->model_extension_extension->uninstall('openbay', $this->request->get['extension']);
			$this->model_setting_setting->deleteSetting($this->request->get['extension']);
			
			$this->load->model('user/user_group');

			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'openbay/' . $this->request->get['extension']);
			$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'openbay/' . $this->request->get['extension']);

			$class = 'ControllerOpenbay' . str_replace('_', '', $this->request->get['extension']);
			$class = new $class($this->registry);

			if (method_exists($class, 'uninstall')) {
				$class->uninstall();
			}

			$this->response->redirect($this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	public function index() {
		$this->load->model('openbay/openbay');
		$this->load->model('extension/extension');
		$this->load->model('setting/setting');
		$this->load->model('openbay/version');

		$this->data = $this->load->language('extension/openbay');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL')	// Link URL
						));

		$this->data['manage_link'] = $this->url->link('extension/openbay/manage', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['product_link'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['order_link'] = $this->url->link('extension/openbay/orderlist', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['success'] = (isset($this->session->data['success'])) ? $this->session->data['success']: '';

		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$this->data['error'] = (isset($this->session->data['error'])) ? $this->session->data['error']: $this->model_openbay_openbay->requirementTest();
				
		if (isset($this->session->data['error'])) {
			unset($this->session->data['error']);
		}

		$extensions = $this->model_extension_extension->getInstalled('openbay');

		foreach ($extensions as $key => $value) {
			if (!file_exists(DIR_APPLICATION . 'controller/openbay/' . $value . '.php')) {
				$this->model_extension_extension->uninstall('openbay', $value);
				unset($extensions[$key]);
			}
		}

		$this->data['extensions'] = array();

		$markets = array('ebay', 'etsy', 'amazon', 'amazonus');

		foreach ($markets as $market) {
			$extension = basename($market, '.php');

			$this->data2 = $this->load->language('openbay/' . $extension); // for inner modules of the openbay like ebay, amazon

			$this->data['extensions'][] = array(
				'name' => $this->data2['heading_title'],
				'edit' => $this->url->link('openbay/' . $extension . '', 'token=' . $this->session->data['token'], 'SSL'),
				'status' => ($this->config->get('openbay_' . $extension . '_status') || $this->config->get($extension . '_status')) ? $this->data2['text_enabled'] : $this->data2['text_disabled'],
				'install' => $this->url->link('extension/openbay/install', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
				'uninstall' => $this->url->link('extension/openbay/uninstall', 'token=' . $this->session->data['token'] . '&extension=' . $extension, 'SSL'),
				'installed' => in_array($extension, $extensions),
				'code' => $extension
			);
		}

		$settings = $this->model_setting_setting->getSetting('openbay');

		if (isset($settings['openbay_version'])) {
			$this->data['openbay_version'] = $settings['openbay_version'];
		} else {
			$this->data['openbay_version'] = $this->model_openbay_version->version();
			$settings['openbay_version'] = $this->model_openbay_version->version();
			$this->model_setting_setting->editSetting('openbay', $settings);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/openbay.tpl', $this->data));
	}

	public function manage() {
		$this->load->model('setting/setting');

		$this->data = $this->load->language('extension/openbay');

		$this->document->setTitle($this->data['text_manage']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_manage'],
							$this->url->link('extension/openbay/manage', 'token=' . $this->session->data['token'], 'SSL'),
						));

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_setting_setting->editSetting('openbay', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'));
		}

		if (isset($this->request->post['openbay_version'])) {
			$this->data['openbay_version'] = $this->request->post['openbay_version'];
		} else {
			$settings = $this->model_setting_setting->getSetting('openbay');

			if (isset($settings['openbay_version'])) {
				$this->data['openbay_version'] = $settings['openbay_version'];
			} else {
				$this->load->model('openbay/version');
				$settings['openbay_version'] = $this->model_openbay_version->version();
				$this->data['openbay_version'] = $this->model_openbay_version->version();
				$this->model_setting_setting->editSetting('openbay', $settings);
			}
		}

		$this->data['openbay_ftp_username'] = $this->request->post('openbay_ftp_username', $this->config->get('openbay_ftp_username'));
		
		$this->data['openbay_ftp_pw'] = $this->request->post('openbay_ftp_pw', $this->config->get('openbay_ftp_pw'));

		$this->data['openbay_ftp_rootpath'] = $this->request->post('openbay_ftp_rootpath', $this->config->get('openbay_ftp_rootpath'));

		$this->data['openbay_ftp_pasv'] = $this->request->post('openbay_ftp_pasv', $this->config->get('openbay_ftp_pasv'));

		$this->data['openbay_ftp_beta'] = $this->request->post('openbay_ftp_beta', $this->config->get('openbay_ftp_beta'));

		$this->data['openbay_ftp_server'] = $_SERVER["SERVER_ADDR"];
		
		$this->data['openbay_ftp_beta'] = $this->request->post('openbay_ftp_server', $this->config->get('openbay_ftp_server'));
		
		if ($this->config->get('openbay_admin_directory')) {
			$this->data['openbay_admin_directory'] =  $this->config->get('openbay_admin_directory');
		} else {
			$this->data['openbay_admin_directory'] = $this->request->post('openbay_admin_directory', 'admin');
	
		}
		
		$this->data['openbay_language'] = $this->request->post('openbay_language', $this->config->get('openbay_language'));

		$this->data['languages'] = array(
			'en_GB' => 'English',
			'de_DE' => 'German',
			'es_ES' => 'Spanish',
			'fr_FR' => 'French',
			'it_IT' => 'Italian',
			'nl_NL' => 'Dutch',
			'zh_HK' => 'Simplified Chinese'
		);

		$this->data['text_version'] = $this->config->get('openbay_version');
		$this->data['openbay_menu'] = $this->config->get('openbay_menu');

		$this->data['action'] = $this->url->link('extension/openbay/manage', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/openbay_manage.tpl', $this->data));
	}

	public function updateTest() {
		$this->load->model('openbay/openbay');

		$json = $this->model_openbay_openbay->updateTest();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function update() {
		$this->load->model('openbay/openbay');

		$json = $this->model_openbay_openbay->update();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function updateV2() {
		$this->load->model('openbay/openbay');
		$this->data = $this->load->language('extension/openbay');

		// set base var
		$web_root = preg_replace('/system\/$/', '', DIR_SYSTEM);

		$stage = $this->request->get('stage', 'check_server');
		
		$beta = (!isset($this->request->get['beta']) || $this->request->get['beta'] == 0) ? 0 : 1;
		
		switch ($stage) {
			case 'check_server': // step 1
				$response = $this->model_openbay_openbay->updateV2Test();

				sleep(1);
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($response));
				break;
			case 'check_version': // step 2
				$response = $this->model_openbay_openbay->updateV2CheckVersion($beta);

				sleep(1);
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($response));
				break;
			case 'download': // step 3
				$response = $this->model_openbay_openbay->updateV2Download($beta);

				sleep(1);
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($response));
				break;
			case 'extract': // step 4
				$response = $this->model_openbay_openbay->updateV2Extract();

				sleep(1);
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($response));
				break;
			case 'remove': // step 5 - remove any files no longer needed
				$response = $this->model_openbay_openbay->updateV2Remove();

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($response));
				break;
			case 'run_patch': // step 6 - run any db updates or other patch files
				if ($this->config->get('ebay_status') == 1) {
					$this->load->model('openbay/ebay');
					$this->model_openbay_ebay->patch(false);
				}

				if ($this->config->get('amazon_status') == 1) {
					$this->load->model('openbay/amazon');
					$this->model_openbay_amazon->patch(false);
				}

				if ($this->config->get('amazonus_status') == 1) {
					$this->load->model('openbay/amazonus');
					$this->model_openbay_amazonus->patch(false);
				}

				if ($this->config->get('etsy_status') == 1) {
					$this->load->model('openbay/etsy');
					$this->model_openbay_etsy->patch(false);
				}

				$response = array('error' => 0, 'response' => '', 'percent_complete' => 90, 'status_message' => 'Running patch files');

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($response));
				break;
			case 'update_version': // step 7 - update the version number
				$this->load->model('setting/setting');

				$response = $this->model_openbay_openbay->updateV2UpdateVersion($beta);

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($response));
				break;
			default;
		}
	}

	public function patch() {
		$this->load->model('openbay/ebay');
		$this->load->model('openbay/amazon');
		$this->load->model('openbay/amazonus');
		$this->load->model('extension/extension');
		$this->load->model('setting/setting');
		$this->load->model('user/user_group');
		$this->load->model('openbay/version');

		$this->model_openbay_ebay->patch();
		$this->model_openbay_amazon->patch();
		$this->model_openbay_amazonus->patch();

		$openbay = $this->model_setting_setting->getSetting('openbay');
		$openbay['openbay_version'] = (int)$this->model_openbay_version->version();
		$openbay['openbay_menu'] = 1;
		$this->model_setting_setting->editSetting('openbay', $openbay);

		$installed_modules = $this->model_extension_extension->getInstalled('module');

		if (!in_array('openbay', $installed_modules)) {
			$this->model_extension_extension->install('feed', 'openbay');
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'feed/openbay');
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'feed/openbay');
		}

		sleep(1);

		$json = array('msg' => 'ok');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function notifications() {
		$this->load->model('openbay/openbay');

		$json = $this->model_openbay_openbay->getNotifications();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function version() {
		$this->load->model('openbay/openbay');

		$json = $this->model_openbay_openbay->version();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function faq() {
		$this->load->model('openbay/openbay');

		$this->data = $this->load->language('extension/openbay');

		$this->data = $this->model_openbay_openbay->faqGet($this->request->get['qry_route']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($this->data));
	}

	public function faqDismiss() {
		$this->load->model('openbay/openbay');

		$json = $this->model_openbay_openbay->faqDismiss($this->request->get['qry_route']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function faqClear() {
		$this->load->model('openbay/openbay');
		$this->model_openbay_openbay->faqClear();

		$json = array('msg' => 'ok');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getOrderInfo() {
		$this->data = array();

		$this->data = array_merge($this->data, $this->load->language('extension/openbay'));

		if ($this->config->get('ebay_status') == 1) {
			if ($this->openbay->ebay->isEbayOrder($this->request->get['order_id']) !== false) {
				if ($this->config->get('ebay_status_shipped_id') == $this->request->get['status_id']) {
					$this->data['carriers'] = $this->openbay->ebay->getCarriers();
					$this->data['order_info'] = $this->openbay->ebay->getOrder($this->request->get['order_id']);
					$this->response->setOutput($this->load->view('openbay/ebay_ajax_shippinginfo.tpl', $this->data));
				}
			}
		}

		if ($this->config->get('openbay_amazon_status') == 1) {
			$this->data['order_info'] = $this->openbay->amazon->getOrder($this->request->get['order_id']);

			if ($this->data['order_info']) {
				if ($this->request->get['status_id'] == $this->config->get('openbay_amazon_order_status_shipped')) {
					$this->data['couriers'] = $this->openbay->amazon->getCarriers();
					$this->data['courier_default'] = $this->config->get('openbay_amazon_default_carrier');
					$this->response->setOutput($this->load->view('openbay/amazon_ajax_shippinginfo.tpl', $this->data));
				}
			}
		}

		if ($this->config->get('openbay_amazonus_status') == 1) {
			$this->data['order_info'] = $this->openbay->amazonus->getOrder($this->request->get['order_id']);

			if ($this->data['order_info']) {
				if ($this->request->get['status_id'] == $this->config->get('openbay_amazonus_order_status_shipped')) {
					$this->data['couriers'] = $this->openbay->amazonus->getCarriers();
					$this->data['courier_default'] = $this->config->get('openbay_amazon_default_carrier');
					$this->response->setOutput($this->load->view('openbay/amazonus_ajax_shippinginfo.tpl', $this->data));
				}
			}
		}

		if ($this->config->get('etsy_status') == 1) {
			$this->data['order_info'] = $this->openbay->etsy->orderFind($this->request->get['order_id']);

			if ($this->data['order_info']) {
				if ($this->request->get['status_id'] == $this->config->get('etsy_order_status_shipped')) {

				}
			}
		}
	}

	public function addOrderInfo() {
		if ($this->config->get('ebay_status') == 1 && $this->openbay->ebay->isEbayOrder($this->request->get['order_id']) !== false) {
			if ($this->config->get('ebay_status_shipped_id') == $this->request->get['status_id']) {
				$this->openbay->ebay->orderStatusListen($this->request->get['order_id'], $this->request->get['status_id'], array('tracking_no' => $this->request->post['tracking_no'], 'carrier_id' => $this->request->post['carrier_id']));
			}else{
				$this->openbay->ebay->orderStatusListen($this->request->get['order_id'], $this->request->get['status_id']);
			}
		}

		if ($this->config->get('openbay_amazon_status') == 1 && $this->openbay->amazon->getOrder($this->request->get['order_id']) !== false) {
			if ($this->config->get('openbay_amazon_order_status_shipped') == $this->request->get['status_id']) {
				if (!empty($this->request->post['courier_other'])) {
					$this->openbay->amazon->updateOrder($this->request->get['order_id'], 'shipped', $this->request->post['courier_other'], false, $this->request->post['tracking_no']);
				} else {
					$this->openbay->amazon->updateOrder($this->request->get['order_id'], 'shipped', $this->request->post['courier_id'], true, $this->request->post['tracking_no']);
				}
			}

			if ($this->config->get('openbay_amazon_order_status_canceled') == $this->request->get['status_id']) {
				$this->openbay->amazon->updateOrder($this->request->get['order_id'], 'canceled');
			}
		}

		if ($this->config->get('openbay_amazonus_status') == 1 && $this->openbay->amazonus->getOrder($this->request->get['order_id']) !== false) {
			if ($this->config->get('openbay_amazonus_order_status_shipped') == $this->request->get['status_id']) {
				if (!empty($this->request->post['courier_other'])) {
					$this->openbay->amazonus->updateOrder($this->request->get['order_id'], 'shipped', $this->request->post['courier_other'], false, $this->request->post['tracking_no']);
				} else {
					$this->openbay->amazonus->updateOrder($this->request->get['order_id'], 'shipped', $this->request->post['courier_id'], true, $this->request->post['tracking_no']);
				}
			}
			if ($this->config->get('openbay_amazonus_order_status_canceled') == $this->request->get['status_id']) {
				$this->openbay->amazonus->updateOrder($this->request->get['order_id'], 'canceled');
			}
		}

		if ($this->config->get('etsy_status') == 1) {
			$linked_order = $this->openbay->etsy->orderFind($this->request->get['order_id']);

			if ($linked_order != false) {
				if ($this->config->get('etsy_order_status_paid') == $this->request->get['status_id']) {
					$response = $this->openbay->etsy->orderUpdatePaid($linked_order['receipt_id'], "true");
				}

				if ($this->config->get('etsy_order_status_shipped') == $this->request->get['status_id']) {
					$response = $this->openbay->etsy->orderUpdateShipped($linked_order['receipt_id'], "true");
				}
			}
		}
	}

	public function orderList() {
		$this->data = $this->load->language('sale/order');
		$this->load->model('openbay/order');

		$this->data = $this->load->language('openbay/openbay_order');
		$this->document->setTitle($this->data['heading_title']);
		
		$filter_order_id = $this->request->get('filter_order_id', null);
		
		$filter_customer = $this->request->get('filter_customer', null);
		
		$filter_order_status_id = $this->request->get('filter_order_status_id', null);

		$filter_date_added = $this->request->get('filter_date_added', null);
		
		$filter_channel = $this->request->get('filter_channel', null);
		
		$sort = $this->request->get('sort','o.order_id');
		
		$order = $this->request->get('order','DESC');
		
		$page = $this->request->get('page',1);
		
		// filter and sorting function for openbay orderList
		$url = $this->request->getUrl($this->url_data);
	
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('extension/openbay/manage', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['orders'] = array();

		$filter = array(
			'filter_order_id'        => $filter_order_id,
			'filter_customer'	     => $filter_customer,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_date_added'      => $filter_date_added,
			'filter_channel'         => $filter_channel,
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$order_total = $this->model_openbay_order->getTotalOrders($filter);
		$results = $this->model_openbay_order->getOrders($filter);

		foreach ($results as $result) {
			$channel = $this->data['text_' . $result['channel']];

			$this->data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'status'        => $result['status'],
				'date_added'    => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'selected'      => isset($this->request->post['selected']) && in_array($result['order_id'], $this->request->post['selected']),
				'view'          => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'] . $url, 'SSL'),
				'channel'       => $channel,
			);
		}

		$this->data['channels'] = array();

		$this->data['channels'][] = array(
			'module' => 'web',
			'title' => $this->data['text_web'],
		);

		if ($this->config->get('ebay_status')) {
			$this->data['channels'][] = array(
				'module' => 'ebay',
				'title' => $this->data['text_ebay'],
			);
		}

		if ($this->config->get('openbay_amazon_status')) {
			$this->data['channels'][] = array(
				'module' => 'amazon',
				'title' => $this->data['text_amazon'],
			);
		}

		if ($this->config->get('openbay_amazonus_status')) {
			$this->data['channels'][] = array(
				'module' => 'amazonus',
				'title' => $this->data['text_amazonus'],
			);
		}

		if ($this->config->get('etsy_status')) {
			$this->data['channels'][] = array(
				'module' => 'etsy',
				'title' => $this->data['text_etsy'],
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] = (isset($this->session->data['error'])) ? $this->session->data['error'] : '';
		
		if (isset($this->session->data['error'])) 
			unset($this->session->data['error']);
		
		$this->data['success'] = (isset($this->session->data['success'])) ? $this->session->data['success'] : '';

		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);
		
		// for filter and chaning sorting in the url.
		$url_data = array(
				'filter_order_id',
				'filter_customer', 
				'filter_order_status_id',
				'filter_date_added',
				'filter_channel',
			);
			
		// filter and sorting function for openbay orderList
		$url = $this->request->getUrl($url_data);

		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_order'] = $this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'] . '&sort=o.order_id' . $url, 'SSL');
		$this->data['sort_customer'] = $this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'] . '&sort=customer' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'] . '&sort=o.date_added' . $url, 'SSL');
		$this->data['sort_channel'] = $this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'] . '&sort=channel' . $url, 'SSL');
		$this->data['link_update'] = $this->url->link('extension/openbay/orderListUpdate', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['cancel'] = $this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL');

		// sorting and filter array orderList of openbay
		$url_data = array(
				'filter_order_id',
				'filter_customer', 
				'filter_order_status_id',
				'filter_date_added',
				'filter_channel',
				'sort',
				'order',
			);
			
		// filter and sorting function for openbay orderList pagination
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->data['text_pagination'];
		$pagination->url = $this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$this->data['filter_order_id'] = $filter_order_id;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_order_status_id'] = $filter_order_status_id;
		$this->data['filter_date_added'] = $filter_date_added;
		$this->data['filter_channel'] = $filter_channel;

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/openbay_orderlist.tpl', $this->data));
	}

	public function orderListUpdate() {
		$this->data = $this->load->language('openbay/openbay_order');
		$this->document->setTitle($this->data['heading_title']);

		if (!isset($this->request->post['selected']) || empty($this->request->post['selected'])) {
			$this->session->data['error'] = $this->data['text_no_orders'];
			$this->response->redirect($this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'], 'SSL'));
		} else {
			$this->load->model('openbay/order');
			$this->load->language('sale/order');


			$this->data['link_complete'] = $this->url->link('extension/openbay/orderListComplete', 'token=' . $this->session->data['token'], 'SSL');

			$this->data['market_options'] = array();

			if ($this->config->get('ebay_status') == 1) {
				$this->data['market_options']['ebay']['carriers'] = $this->openbay->ebay->getCarriers();
			}

			if ($this->config->get('openbay_amazon_status') == 1) {
				$this->data['market_options']['amazon']['carriers'] = $this->openbay->amazon->getCarriers();
				$this->data['market_options']['amazon']['default_carrier'] = $this->config->get('openbay_amazon_default_carrier');
			}

			if ($this->config->get('openbay_amazonus_status') == 1) {
				$this->data['market_options']['amazonus']['carriers'] = $this->openbay->amazonus->getCarriers();
			}

			$this->load->model('localisation/order_status');
			$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
			$this->data['status_mapped'] = array();

			foreach($this->data['order_statuses'] as $status) {
				$this->data['status_mapped'][$status['order_status_id']] = $status['name'];
			}

			$orders = array();

			foreach($this->request->post['selected'] as $order_id) {
				$order = $this->model_openbay_order->getOrder($order_id);

				if ($order['order_status_id'] != $this->request->post['change_order_status_id']) {
					$order['channel'] = $this->data['text_' . $order['channel']];
					$orders[] = $order;
				}
			}

			if (empty($orders)) {
				$this->session->data['error'] = $this->data['text_no_orders'];
				$this->response->redirect($this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'], 'SSL'));
			}else{
				$this->data['orders'] = $orders;
			}

			// Breadcrumb array with common function of Text and URL 
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('extension/openbay/manage', 'token=' . $this->session->data['token'], 'SSL'),
						));
						
			$this->data['change_order_status_id'] = $this->request->post['change_order_status_id'];
			$this->data['ebay_status_shipped_id'] = $this->config->get('ebay_status_shipped_id');
			$this->data['openbay_amazon_order_status_shipped'] = $this->config->get('openbay_amazon_order_status_shipped');
			$this->data['openbay_amazonus_order_status_shipped'] = $this->config->get('openbay_amazonus_order_status_shipped');

			$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

			// filter and sorting function for openbay orderList
			$url = $this->request->getUrl($this->url_data);

			$this->data['cancel'] = $this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'] . $url, 'SSL');
			$this->data['button_cancel'] = $this->data['button_cancel'];

			$this->response->setOutput($this->load->view('openbay/openbay_orderlist_confirm.tpl', $this->data));
		}
	}

	public function orderListComplete() {
		$this->load->model('sale/order');
		$this->load->model('openbay/openbay');
		$this->load->model('localisation/order_status');

		$this->data = $this->load->language('openbay/openbay_order');

		$order_statuses = $this->model_localisation_order_status->getOrderStatuses();
		$status_mapped = array();

		foreach($order_statuses as $status) {
			$status_mapped[$status['order_status_id']] = $status['name'];
		}

		//Amazon EU
		if ($this->config->get('openbay_amazon_status') == 1) {
			$orders = array();

			foreach ($this->request->post['order_id'] as $order_id) {
				if ($this->request->post['channel'][$order_id] == 'Amazon EU') {

					if ($this->config->get('openbay_amazon_order_status_shipped') == $this->request->post['order_status_id']) {
						$carrier = '';

						if (isset($this->request->post['carrier_other'][$order_id]) && !empty($this->request->post['carrier_other'][$order_id])) {
							$carrier_from_list = false;
							$carrier = $this->request->post['carrier_other'][$order_id];
						} else {
							$carrier_from_list = true;
							$carrier = $this->request->post['carrier'][$order_id];
						}

						$orders[] = array(
							'order_id' => $order_id,
							'status' => 'shipped',
							'carrier' => $carrier,
							'carrier_from_list' => $carrier_from_list,
							'tracking' => $this->request->post['tracking'][$order_id],
						);
					}

					if ($this->config->get('openbay_amazon_order_status_canceled') == $this->request->post['order_status_id']) {
						$orders[] = array(
							'order_id' => $order_id,
							'status' => 'canceled',
						);
					}
				}
			}

			if ($orders) {
				$this->openbay->amazon->bulkUpdateOrders($orders);
			}
		}

		//Amazon US
		if ($this->config->get('openbay_amazonus_status') == 1) {
			$orders = array();

			foreach ($this->request->post['order_id'] as $order_id) {
				if ($this->request->post['channel'][$order_id] == 'Amazon US') {

					if ($this->config->get('openbay_amazonus_order_status_shipped') == $this->request->post['order_status_id']) {
						$carrier = '';

						if (isset($this->request->post['carrier_other'][$order_id]) && !empty($this->request->post['carrier_other'][$order_id])) {
							$carrier_from_list = false;
							$carrier = $this->request->post['carrier_other'][$order_id];
						} else {
							$carrier_from_list = true;
							$carrier = $this->request->post['carrier'][$order_id];
						}

						$orders[] = array(
							'order_id' => $order_id,
							'status' => 'shipped',
							'carrier' => $carrier,
							'carrier_from_list' => $carrier_from_list,
							'tracking' => $this->request->post['tracking'][$order_id],
						);
					}

					if ($this->config->get('openbay_amazonus_order_status_canceled') == $this->request->post['order_status_id']) {
						$orders[] = array(
							'order_id' => $order_id,
							'status' => 'canceled',
						);
					}
				}
			}

			if ($orders) {
				$this->openbay->amazonus->bulkUpdateOrders($orders);
			}
		}

		$i = 0;
		foreach($this->request->post['order_id'] as $order_id) {
			if ($this->config->get('ebay_status') == 1 && $this->request->post['channel'][$order_id] == 'eBay') {
				if ($this->config->get('ebay_status_shipped_id') == $this->request->post['order_status_id']) {
					$this->openbay->ebay->orderStatusListen($order_id, $this->request->post['order_status_id'], array('tracking_no' => $this->request->post['tracking'][$order_id], 'carrier_id' => $this->request->post['carrier'][$order_id]));
				}else{
					$this->openbay->ebay->orderStatusListen($this->request->get['order_id'], $this->request->get['status_id']);
				}
			}

			if ($this->config->get('etsy_status') == 1 && $this->request->post['channel'][$order_id] == 'Etsy') {
				$linked_order = $this->openbay->etsy->orderFind($order_id);

				if ($linked_order != false) {
					if ($this->config->get('etsy_order_status_paid') == $this->request->post['order_status_id']) {
						$response = $this->openbay->etsy->orderUpdatePaid($linked_order['receipt_id'], "true");
					}

					if ($this->config->get('etsy_order_status_shipped') == $this->request->post['order_status_id']) {
						$response = $this->openbay->etsy->orderUpdateShipped($linked_order['receipt_id'], "true");
					}
				}
			}

			$this->data = array(
				'append' => 0,
				'notify' => $this->request->post['notify'][$order_id],
				'order_status_id' => $this->request->post['order_status_id'],
				'comment' => $this->request->post['comments'][$order_id],
			);

			$this->model_openbay_openbay->addOrderHistory($order_id, $this->data);
			$i++;
		}

		$this->session->data['success'] = sprintf($this->data['text_confirmed'], $i, $status_mapped[$this->request->post['order_status_id']]);

		$this->response->redirect($this->url->link('extension/openbay/orderList', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function items() {
		$this->document->addScript('view/javascript/openbay/js/openbay.js');
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data = array();

		$this->data = array_merge($this->data, $this->load->language('catalog/product'));
		$this->data = array_merge($this->data, $this->load->language('openbay/openbay_itemlist'));

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		$this->load->model('openbay/openbay');
		$this->load->model('tool/image');

		if ($this->openbay->addonLoad('openstock')) {
			$this->load->model('openstock/openstock');
			$openstock_installed = true;
		} else {
			$openstock_installed = false;
		}

		$this->data['category_list'] = $this->model_catalog_category->getCategories(array());
		$this->data['manufacturer_list'] = $this->model_catalog_manufacturer->getManufacturers(array());
		
		$filter_name = $this->request->get('filter_name', null);
		
		$filter_model = $this->request->get('filter_model', null);
		
		$filter_price = $this->request->get('filter_price', null);
		
		$filter_price_to = $this->request->get('filter_price_to', null);

		$filter_quantity = $this->request->get('filter_quantity', null);
		
		$filter_quantity_to = $this->request->get('filter_quantity_to', null);
		
		$filter_status = $this->request->get('filter_status', null);
		
		$filter_sku = $this->request->get('filter_sku', null);
		
		$filter_desc = $this->request->get('filter_desc', null);
		
		$filter_category = $this->request->get('filter_category', null);
		
		$filter_manufacturer = $this->request->get('filter_manufacturer', null);
		
		$filter_marketplace = $this->request->get('filter_marketplace', null);
		
		$sort = $this->request->get('sort','pd.name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// URL function
		$url = $this->request->getUrl($this->url_data2);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
						$this->data['text_home'],	// Text to display link
						$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
						$this->data['text_openbay'],	// Text to display link
						$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
						$this->data['heading_title'],
						$this->url->link('extension/openbay/items', 'token=' . $this->session->data['token']. $url, 'SSL'),
					));
		
		$this->data['link_amazon_eu_bulk'] = ($this->config->get('openbay_amazon_status')) ? $this->url->link('openbay/amazon/bulkListProducts', 'token=' . $this->session->data['token'] . $url, 'SSL'): '';
		
		$this->data['link_amazon_us_bulk'] = ($this->config->get('openbay_amazonus_status')) ? $this->url->link('openbay/amazonus/bulkListProducts', 'token=' . $this->session->data['token'] . $url, 'SSL'): '';
		
		$this->data['link_ebay_bulk'] = ($this->config->get('ebay_status') == '1') ? $this->url->link('openbay/openbay/createBulk', 'token=' . $this->session->data['token'], 'SSL'): '';

		$this->data['products'] = array();

		$filter_market_id = '';
		$filter_market_name = '';

		$ebay_status = array(
			0 => 'ebay_inactive',
			1 => 'ebay_active',
		);

		if (in_array($filter_marketplace, $ebay_status)) {
			$filter_market_name = 'ebay';
			$filter_market_id = array_search($filter_marketplace, $ebay_status);
		}

		$amazon_status = array(
			0 => 'amazon_unlisted',
			1 => 'amazon_saved',
			2 => 'amazon_uploaded',
			3 => 'amazon_ok',
			4 => 'amazon_error',
			5 => 'amazon_linked',
			6 => 'amazon_not_linked',
		);

		if (in_array($filter_marketplace, $amazon_status)) {
			$filter_market_name = 'amazon';
			$filter_market_id = array_search($filter_marketplace, $amazon_status);
		}

		$amazonus_status = array(
			0 => 'amazonus_unlisted',
			1 => 'amazonus_saved',
			2 => 'amazonus_uploaded',
			3 => 'amazonus_ok',
			4 => 'amazonus_error',
			5 => 'amazonus_linked',
			6 => 'amazonus_not_linked',
		);

		if (in_array($filter_marketplace, $amazonus_status)) {
			$filter_market_name = 'amazonus';
			$filter_market_id = array_search($filter_marketplace, $amazonus_status);
		}

		$filter = array(
			'filter_name'	        => $filter_name,
			'filter_model'	        => $filter_model,
			'filter_price'	        => $filter_price,
			'filter_price_to'	    => $filter_price_to,
			'filter_quantity'       => $filter_quantity,
			'filter_quantity_to'    => $filter_quantity_to,
			'filter_status'         => $filter_status,
			'filter_sku'            => $filter_sku,
			'filter_desc'           => $filter_desc,
			'filter_category'       => $filter_category,
			'filter_manufacturer'   => $filter_manufacturer,
			'filter_market_name'    => $filter_market_name,
			'filter_market_id'      => $filter_market_id,
			'sort'                  => $sort,
			'order'                 => $order,
			'start'                 => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                 => $this->config->get('config_limit_admin')
		);

		if ($this->config->get('ebay_status') != '1' && $filter['filter_market_name'] == 'ebay') {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		if ($this->config->get('openbay_amazon_status') != '1' && $filter['filter_market_name'] == 'amazon') {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		if ($this->config->get('openbay_amazonus_status') != '1' && $filter['filter_market_name'] == 'amazonus') {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		if ($this->config->get('etsy_status') != '1' && $filter['filter_market_name'] == 'etsy') {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->data['marketplace_statuses'] = array(
			'ebay' => $this->config->get('ebay_status'),
			'amazon' => $this->config->get('openbay_amazon_status'),
			'amazonus' => $this->config->get('openbay_amazonus_status'),
			'etsy' => $this->config->get('etsy_status'),
		);

		$product_total = $this->model_openbay_openbay->getTotalProducts($filter);

		$results = $this->model_openbay_openbay->getProducts($filter);

		foreach ($results as $result) {
			$edit = $this->url->link('catalog/product/save', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL');
			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || $product_special['date_start'] < date('Y-m-d')) && ($product_special['date_end'] == '0000-00-00' || $product_special['date_end'] > date('Y-m-d'))) {
					$special = $product_special['price'];

					break;
				}
			}

			/**
			 * Button status key:
			 * 0 = Inactive / no link to market
			 * 1 = Active
			 * 2 = Error
			 * 3 = Pending
			 */

			$markets = array();

			if ($this->config->get('ebay_status') == '1') {
				$this->load->model('openbay/ebay');

				$active_list = $this->model_openbay_ebay->getLiveListingArray();

				if (!array_key_exists($result['product_id'], $active_list)) {
					$markets[] = array(
						'name'      => $this->data['text_ebay'],
						'text'      => $this->data['button_add'],
						'href'      => $this->url->link('openbay/ebay/create', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 0
					);
				} else {
					$markets[] = array(
						'name'      => $this->data['text_ebay'],
						'text'      => $this->data['button_edit'],
						'href'      => $this->url->link('openbay/ebay/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 1
					);
				}
			}

			if ($this->config->get('openbay_amazon_status') == '1') {
				$this->load->model('openbay/amazon');
				$amazon_status = $this->model_openbay_amazon->getProductStatus($result['product_id']);

				if ($amazon_status == 'processing') {
					$markets[] = array(
						'name'      => $this->data['text_amazon'],
						'text'      => $this->data['text_processing'],
						'href'      => '',
						'status'	=> 3
					);
				} else if ($amazon_status == 'linked' || $amazon_status == 'ok' || $amazon_status == 'saved') {
					$markets[] = array(
						'name'      => $this->data['text_amazon'],
						'text'      => $this->data['button_edit'],
						'href'      => $this->url->link('openbay/amazon_listing/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 1
					);
				} else if ($amazon_status == 'error_quick' || $amazon_status == 'error_advanced' || $amazon_status == 'error_few') {
					$markets[] = array(
						'name'      => $this->data['text_amazon'],
						'text'      => $this->data['button_error_fix'],
						'href'      => $this->url->link('openbay/amazon_listing/create', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 2
					);
				} else {
					$markets[] = array(
						'name'      => $this->data['text_amazon'],
						'text'      => $this->data['button_add'],
						'href'      => $this->url->link('openbay/amazon_listing/create', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 0
					);
				}
			}

			if ($this->config->get('openbay_amazonus_status') == '1') {
				$this->load->model('openbay/amazonus');
				$amazonus_status = $this->model_openbay_amazonus->getProductStatus($result['product_id']);

				if ($amazonus_status == 'processing') {
					$markets[] = array(
						'name'      => $this->data['text_amazonus'],
						'text'      => $this->data['text_processing'],
						'href'      => '',
						'status'	=> 3
					);
				} else if ($amazonus_status == 'linked' || $amazonus_status == 'ok' || $amazonus_status == 'saved') {
					$markets[] = array(
						'name'      => $this->data['text_amazonus'],
						'text'      => $this->data['button_edit'],
						'href'      => $this->url->link('openbay/amazonus_listing/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 1
					);
				} else if ($amazonus_status == 'error_quick' || $amazonus_status == 'error_advanced' || $amazonus_status == 'error_few') {
					$markets[] = array(
						'name'      => $this->data['text_amazonus'],
						'text'      => $this->data['button_error_fix'],
						'href'      => $this->url->link('openbay/amazonus_listing/create', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 2
					);
				} else {
					$markets[] = array(
						'name'      => $this->data['text_amazonus'],
						'text'      => $this->data['button_add'],
						'href'      => $this->url->link('openbay/amazonus_listing/create', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 0
					);
				}
			}

			if ($this->config->get('etsy_status') == '1') {
				$this->load->model('openbay/etsy_product');

				$status = $this->model_openbay_etsy_product->getStatus($result['product_id']);

				if ($status == 0) {
					$markets[] = array(
						'name'      => $this->data['text_etsy'],
						'text'      => $this->data['button_add'],
						'href'      => $this->url->link('openbay/etsy_product/create', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 0
					);
				} else {
					$markets[] = array(
						'name'      => $this->data['text_etsy'],
						'text'      => $this->data['button_edit'],
						'href'      => $this->url->link('openbay/etsy_product/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL'),
						'status'	=> 1
					);
				}
			}

			if (!isset($result['has_option'])) {
				$result['has_option'] = 0;
			}

			$this->data['products'][] = array(
				'markets'   => $markets,
				'product_id' => $result['product_id'],
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $result['price'],
				'special'    => $special,
				'image'      => $this->model_tool_image->resize($result['image'], 40, 40),
				'quantity'   => $result['quantity'],
				'status'     => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'selected'   => isset($this->request->post['selected']) && in_array($result['product_id'], $this->request->post['selected']),
				'edit'       => $edit,
				'has_option' => $openstock_installed ? $result['has_option'] : 0,
				'vCount'     => $openstock_installed ? $this->model_openstock_openstock->countVariation($result['product_id']) : '',
				'vsCount'    => $openstock_installed ? $this->model_openstock_openstock->countVariationStock($result['product_id']) : '',
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] =(isset($this->error['warning'])) ? $this->error['warning']: '';
		
		if (isset($this->session->data['warning'])) {
			$this->data['error_warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		} 
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		 $url_data2 = array(
				'filter_name' => 'encode', // for using these functions urlencode(html_entity_decode($this->get[$item], ENT_QUOTES, 'UTF-8'));
				'filter_model' => 'encode', 
				'filter_price',
				'filter_price_to',
				'filter_quantity',
				'filter_quantity_to',
				'filter_status',
				'filter_sku',
				'filter_desc',
				'filter_category',
				'filter_manufacturer',
				'filter_marketplace',
			);

		$url = $this->request->getUrl($url_data2);
	
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_model'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . '&sort=p.model' . $url, 'SSL');
		$this->data['sort_price'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . '&sort=p.price' . $url, 'SSL');
		$this->data['sort_quantity'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . '&sort=p.quantity' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . '&sort=p.status' . $url, 'SSL');
		$this->data['sort_order'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . '&sort=p.sort_order' . $url, 'SSL');
 		
		$url_data2 = array(
				'filter_name' => 'encode', // for using these functions urlencode(html_entity_decode($this->get[$item], ENT_QUOTES, 'UTF-8'));
				'filter_model' => 'encode', 
				'filter_price',
				'filter_price_to',
				'filter_quantity',
				'filter_quantity_to',
				'filter_status',
				'filter_sku',
				'filter_desc',
				'filter_category',
				'filter_manufacturer',
				'filter_marketplace',
				'sort',
				'order',
			);

		$url = $this->request->getUrl($url_data2);
	
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->data['text_pagination'];
		$pagination->url = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_model'] = $filter_model;
		$this->data['filter_price'] = $filter_price;
		$this->data['filter_price_to'] = $filter_price_to;
		$this->data['filter_quantity'] = $filter_quantity;
		$this->data['filter_quantity_to'] = $filter_quantity_to;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_sku'] = $filter_sku;
		$this->data['filter_desc'] = $filter_desc;
		$this->data['filter_category'] = $filter_category;
		$this->data['filter_manufacturer'] = $filter_manufacturer;
		$this->data['filter_marketplace'] = $filter_marketplace;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['ebay_status'] = $this->config->get('ebay_status');
		$this->data['token'] = $this->request->get['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/openbay_itemlist.tpl', $this->data));
	}

	public function eventDeleteProduct($product_id) {
		foreach ($this->openbay->installed_markets as $market) {
			if ($this->config->get($market . '_status') == 1) {
				$this->openbay->{$market}->deleteProduct($product_id);
			}
		}
	}

	public function eventEditProduct() {
		foreach ($this->openbay->installed_markets as $market) {
			if ($this->config->get($market . '_status') == 1) {
				$this->openbay->{$market}->productUpdateListen($this->request->get['product_id'], $this->request->post);
			}
		}
	}

	public function purge() {
		/**
		 * This is a function that is very dangerous
		 * Only developers should use this if you need to!!
		 * You need this code: **135** (includes stars)
		 *
		 * ACTIONS HERE CANNOT BE UNDONE WITHOUT A BACKUP
		 *
		 * !! IMPORTANT !!
		 * This section will by default comment out the database delete actions
		 * If you want to use them, uncomment.
		 * When you are finished, ensure you comment them back out!
		 */

		$this->log->write('User is trying to wipe system data');

		if ($this->request->post['pass'] != '**135**') {
			$this->log->write('User failed password validation');
			$json = array('msg' => 'Password wrong, check the source code for the password! This is so you know what this feature does.');
		} else {
			$this->log->write('User passed validation');
			$this->db->query("TRUNCATE `" . DB_PREFIX . "order`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "order_history`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "order_option`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "order_product`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "order_total`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "customer`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "customer_activity`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "customer_ban_ip`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "customer_transaction`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "address`");

			/*
			if ($this->config->get('ebay_status') == 1) {
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_category`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_category_history`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_image_import`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_listing`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_listing_pending`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_order`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_order_lock`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_payment_method`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_profile`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_setting_option`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_shipping`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_shipping_location`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_shipping_location_exclude`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_stock_reserve`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_template`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_transaction`");
			}
			*/
			/*
			if ($this->config->get('etsy_status') == 1) {
				$this->db->query("TRUNCATE `" . DB_PREFIX . "etsy_listing`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "etsy_order`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "etsy_order_lock`");
				$this->db->query("TRUNCATE `" . DB_PREFIX . "etsy_setting_option`");
			}
			*/
			/*
			$this->db->query("TRUNCATE `" . DB_PREFIX . "manufacturer`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "manufacturer_to_store`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "attribute`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "attribute_description`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "attribute_group`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "attribute_group_description`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "ebay_listing`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "category`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "category_description`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "category_to_store`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product_to_store`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product_description`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product_attribute`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product_option`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product_option_value`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product_image`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "product_to_category`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "option`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "option_description`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "option_value`");
			$this->db->query("TRUNCATE `" . DB_PREFIX . "option_value_description`");

			if ($this->openbay->addonLoad('openstock')) {
				$this->db->query("TRUNCATE `" . DB_PREFIX . "product_option_relation`");
			}
			*/
			$this->log->write('Data cleared');
			$json = array('msg' => 'Data cleared');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}