<?php
class ControllerOpenbayEbay extends Controller {
	public function install() {
		$this->data = $this->load->language('openbay/ebay');
		$this->load->model('openbay/ebay');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');
		$this->load->model('extension/event');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/ebay_profile');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/ebay_profile');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/ebay_template');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/ebay_template');

		$this->model_openbay_ebay->install();
	}

	public function uninstall() {
		$this->load->model('openbay/ebay');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');
		$this->load->model('extension/event');

		$this->model_openbay_ebay->uninstall();
		$this->model_extension_extension->uninstall('openbay', $this->request->get['extension']);
		$this->model_setting_setting->deleteSetting($this->request->get['extension']);
	}

	public function index() {
		$this->data = $this->load->language('openbay/ebay');

		$this->document->setTitle($this->data['text_dashboard']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_dashboard'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['success'] = isset($this->session->data['success']) ? $this->session->data['success']: '';
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);

		$this->data['validation']               = $this->openbay->ebay->validate();
		$this->data['links_settings']           = $this->url->link('openbay/ebay/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_itemlink']           = $this->url->link('openbay/ebay/viewItemLinks', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_subscribe']          = $this->url->link('openbay/ebay/subscription', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_usage']          	= $this->url->link('openbay/ebay/viewUsage', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_itemimport']         = $this->url->link('openbay/ebay/viewItemImport', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_orderimport']        = $this->url->link('openbay/ebay/viewOrderImport', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_usage']              = $this->url->link('openbay/ebay/viewUsage', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_sync']               = $this->url->link('openbay/ebay/syncronise', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_linkmaintenance']    = $this->url->link('openbay/ebay/viewItemLinkMaintenance', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_summary']            = $this->url->link('openbay/ebay/summary', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_profile']            = $this->url->link('openbay/ebay_profile/profileAll', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['links_template']           = $this->url->link('openbay/ebay_template/listAll', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay.tpl', $this->data));
	}

	public function settings() {
		$this->data = $this->load->language('openbay/ebay_settings');

		$this->load->model('setting/setting');
		$this->load->model('openbay/ebay');
		$this->load->model('localisation/currency');
		$this->load->model('localisation/order_status');
		$this->load->model('sale/customer_group');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('ebay', $this->request->post);
			$this->session->data['success'] = $this->data['text_success'];
			$this->response->redirect($this->url->link('openbay/ebay&token=' . $this->session->data['token']));
		}

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/settings', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['action'] = $this->url->link('openbay/ebay/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['ebay_def_currency'] = $this->request->post('ebay_def_currency', $this->config->get('ebay_def_currency'));

		$this->data['currency_list'] = $this->model_localisation_currency->getCurrencies();

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning']: '';

		$this->data['ebay_status'] = $this->request->post('ebay_status', $this->config->get('ebay_status'));
		
		$this->data['ebay_token'] = $this->request->post('ebay_token', $this->config->get('ebay_token'));

		$this->data['ebay_secret'] = $this->request->post('ebay_secret', $this->config->get('ebay_secret'));

		$this->data['ebay_string1'] = $this->request->post('ebay_string1', $this->config->get('ebay_string1'));
		
		$this->data['ebay_string2'] = $this->request->post('ebay_string2', $this->config->get('ebay_string2'));
		
		$this->data['ebay_enditems'] = $this->request->post('ebay_enditems', $this->config->get('ebay_enditems'));

		$this->data['ebay_relistitems'] = $this->request->post('ebay_relistitems', $this->config->get('ebay_relistitems'));
		
		$this->data['ebay_disable_nostock'] = $this->request->post('ebay_disable_nostock', $this->config->get('ebay_disable_nostock'));
		
		$this->data['ebay_logging'] = $this->request->post('ebay_logging', $this->config->get('ebay_logging'));
		
		$this->data['ebay_created_hours'] = $this->request->post('ebay_created_hours', $this->config->get('ebay_created_hours'));

		$this->data['ebay_time_offset'] = $this->request->post('ebay_time_offset', $this->config->get('ebay_time_offset'));
		
		$this->data['ebay_update_notify'] = $this->request->post('ebay_update_notify', $this->config->get('ebay_update_notify'));
		
		$this->data['ebay_confirm_notify'] = $this->request->post('ebay_confirm_notify', $this->config->get('ebay_confirm_notify'));

		$this->data['ebay_confirmadmin_notify'] = $this->request->post('ebay_confirmadmin_notify', $this->config->get('ebay_confirmadmin_notify'));

		$this->data['ebay_email_brand_disable'] = $this->request->post('ebay_email_brand_disable', $this->config->get('ebay_email_brand_disable'));

		$this->data['ebay_itm_link'] = $this->request->post('ebay_itm_link', $this->config->get('ebay_itm_link'));
		
		$this->data['ebay_stock_allocate'] = $this->request->post('ebay_stock_allocate', $this->config->get('ebay_stock_allocate'));

		$this->data['ebay_create_date'] = $this->request->post('ebay_create_date', $this->config->get('ebay_create_date'));

		$this->data['durations'] = array(
			'Days_1' => $this->data['text_listing_1day'],
			'Days_3' => $this->data['text_listing_3day'],
			'Days_5' => $this->data['text_listing_5day'],
			'Days_7' => $this->data['text_listing_7day'],
			'Days_10' => $this->data['text_listing_10day'],
			'Days_30' => $this->data['text_listing_30day'],
			'GTC' => $this->data['text_listing_gtc']
		);

		$this->data['ebay_duration'] = $this->request->post('ebay_duration', $this->config->get('ebay_duration'));
		
		$this->data['ebay_measurement'] = $this->request->post('ebay_measurement', $this->config->get('ebay_measurement'));
		
		$this->data['ebay_default_addressformat'] = $this->request->post('ebay_default_addressformat', $this->config->get('ebay_default_addressformat'));

		$this->data['payment_options'] = $this->model_openbay_ebay->getPaymentTypes();
		
		$this->data['ebay_payment_types'] = $this->request->post('ebay_payment_types', $this->config->get('ebay_payment_types'));

		$this->data['ebay_payment_instruction'] = $this->request->post('ebay_payment_instruction', $this->config->get('ebay_payment_instruction'));

		$this->data['ebay_payment_paypal_address'] = $this->request->post('ebay_payment_paypal_address', $this->config->get('ebay_payment_paypal_address'));

		$this->data['ebay_payment_immediate'] = $this->request->post('ebay_payment_immediate', $this->config->get('ebay_payment_immediate'));

		$this->data['ebay_tax_listing'] = $this->request->post('ebay_tax_listing', $this->config->get('ebay_tax_listing'));
		
		$this->data['tax'] = $this->request->post('tax', $this->config->get('tax'));
		
		$this->data['ebay_import_unpaid'] = $this->request->post('ebay_import_unpaid', $this->config->get('ebay_import_unpaid'));
		
		$this->data['ebay_status_partial_refund_id'] = $this->request->post('ebay_status_partial_refund_id', $this->config->get('ebay_status_partial_refund_id'));
		
		$this->data['ebay_status_import_id'] = $this->request->post('ebay_status_import_id', $this->config->get('ebay_status_import_id'));
		
		$this->data['ebay_status_paid_id'] = $this->request->post('ebay_status_paid_id', $this->config->get('ebay_status_paid_id'));
		
		$this->data['ebay_status_shipped_id'] = $this->request->post('ebay_status_shipped_id', $this->config->get('ebay_status_shipped_id'));
		
		$this->data['ebay_status_cancelled_id'] = $this->request->post('ebay_status_cancelled_id', $this->config->get('ebay_status_cancelled_id'));

		$this->data['ebay_status_refunded_id'] = $this->request->post('ebay_status_refunded_id', $this->config->get('ebay_status_refunded_id'));

		$this->data['api_server']       = $this->openbay->ebay->getServer();
		$this->data['order_statuses']   = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['measurement_types'] = $this->openbay->ebay->getSetting('measurement_types');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_settings.tpl', $this->data));
	}

	public function updateSettings() {
		set_time_limit(0);

		$json = $this->openbay->ebay->updateSettings();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function updateCategories() {
		set_time_limit(0);

		$json = $this->openbay->ebay->updateCategories();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function updateStore() {
		set_time_limit(0);

		$json = $this->openbay->ebay->updateStore();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getCategories() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getCategory($this->request->get['parent']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getSuggestedCategories() {
		$this->load->model('openbay/ebay');

		$this->data = $this->load->language('openbay/ebay');

		$json = $this->model_openbay_ebay->getSuggestedCategories($this->request->get['qry']);

		if (empty($json['data'])) {
			$json['msg'] = $this->data['error_category_nosuggestions'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getShippingService() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getShippingService($this->request->get['loc'], $this->request->get['type']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getEbayCategorySpecifics() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getEbayCategorySpecifics($this->request->get['category']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getCategoryFeatures() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getCategoryFeatures($this->request->get['category']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function searchEbayCatalog() {
		$this->data = $this->load->language('openbay/ebay');
		$this->load->model('openbay/ebay_product');

		$response = $this->model_openbay_ebay_product->searchEbayCatalog($this->request->post['search'], $this->request->post['category_id']);

		if (isset($response['ack'])) {
			if ($response['ack'] == 'Success') {
				$json['error'] = false;
				$json['error_message'] = '';

				$json['results'] = (int)$response['productSearchResult']['paginationOutput']['totalEntries'];
				$json['page'] = (int)$response['productSearchResult']['paginationOutput']['pageNumber'];
				$json['page_total'] = (int)$response['productSearchResult']['paginationOutput']['totalPages'];
				$json['products'] = $response['productSearchResult']['products'];
			} else {
				$json['error'] = true;

				if (isset($response['errorMessage']['error']['message'])) {
					$json['error_message'] = $response['errorMessage']['error']['message'];
				} else {
					$json['error_message'] = $this->data['error_loading_catalog'];
				}
			}
		} else {
			$json['error'] = true;
			$json['error_message'] = $this->data['error_generic_fail'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function summary() {
		$this->data = $this->load->language('openbay/ebay_summary');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/summary', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['return'] = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['validation'] = $this->openbay->ebay->validate();
		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_summary.tpl', $this->data));
	}

	public function getSellerSummary() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getSellerSummary();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function verifyCreds() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->verifyCreds();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function viewItemImport() {
		$this->load->model('openbay/ebay_product');

		$this->data = $this->load->language('openbay/ebay_import');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/viewItemImport', 'token=' . $this->session->data['token'], 'SSL'),
						));
		
		$this->data['return'] = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['validation'] = $this->openbay->ebay->validate();
		$this->data['token'] = $this->session->data['token'];
		$this->data['maintenance'] = $this->config->get('config_maintenance');
		$this->data['image_import'] = $this->model_openbay_ebay_product->countImportImages();
		$this->data['image_import_link'] = $this->url->link('openbay/ebay/getImportImages', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_item_import.tpl', $this->data));
	}

	public function importItems() {
		$data = array(
			'adv' => $this->request->get['advanced'],
			'c' => 1,
			'd' => $this->request->get['desc'],
			'n' => $this->request->get['note'],
			'cat' => $this->request->get['categories'],
		);

		$this->openbay->ebay->callNoResponse('setup/getItemsMain/', $data);

		$json = array('msg' => 'ok');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function importOrdersManual() {
		$this->openbay->ebay->callNoResponse('order/getOrdersManual/');

		$json = array('msg' => 'ok');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getProductStock() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getProductStock($this->request->get['pid']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function setProductStock() {
		$this->load->model('openbay/ebay');
		$this->load->model('catalog/product');

		$product = $this->model_catalog_product->getProduct($this->request->get['product_id']);

		$json = array();

		if ($product['subtract'] == 1) {
			$this->openbay->ebay->productUpdateListen($this->request->get['product_id'], $product);

			$json['error'] = false;
			$json['msg'] = 'ok';
		} else {
			$this->data = $this->load->language('openbay/ebay_links');

			$json['error'] = true;
			$json['msg'] = $this->data['error_subtract_setting'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getPlans() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getPlans();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getMyPlan() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->getMyPlan();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function subscription() {
		$this->data = $this->load->language('openbay/ebay_subscription');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/subscription', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['return']       = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token']        = $this->session->data['token'];
		$this->data['obp_token']    = $this->config->get('ebay_token');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_subscription.tpl', $this->data));
	}

	public function viewUsage() {
		$this->data = $this->load->language('openbay/ebay_usage');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/view_usage', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['return']       = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token']        = $this->session->data['token'];
		$this->data['obp_token']    = $this->config->get('ebay_token');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_usage.tpl', $this->data));
	}

	public function getUsage() {
		$this->load->model('openbay/ebay');

		$json               = $this->model_openbay_ebay->getUsage();
		$json['html']       = base64_decode($json['html']);
		$json['lasterror']  = $this->openbay->ebay->lasterror;
		$json['lastmsg']    = $this->openbay->ebay->lastmsg;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function viewOrderImport() {
		$this->data = $this->load->language('openbay/ebay_orders');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/viewOrderImport', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['return']       = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['validation']   = $this->openbay->ebay->validate();
		$this->data['token']        = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_order_import.tpl', $this->data));
	}

	public function syncronise() {
		$this->data = $this->load->language('openbay/ebay_syncronise');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['breadcrumbs'] = array();

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/syncronise', 'token=' . $this->session->data['token'], 'SSL'),
						));
						
		$this->data['return']       = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['validation']   = $this->openbay->ebay->validate();
		$this->data['token']        = $this->session->data['token'];
		$this->data['error_validation']   = $this->data['error_validation'];
		
		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_syncronise.tpl', $this->data));
	}

	public function viewItemLinks() {
		$this->load->model('openbay/ebay');

		$this->data = $this->load->language('openbay/ebay_links');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

	// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/viewItemLinks', 'token=' . $this->session->data['token'], 'SSL'),
						));
		
		$this->data['return']       = $this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['edit_url']     = $this->url->link('openbay/ebay/edit', 'token=' . $this->session->data['token'] . '&product_id=', 'SSL');
		$this->data['validation']   = $this->openbay->ebay->validate();
		$this->data['token']        = $this->session->data['token'];

		$total_linked = $this->model_openbay_ebay->totalLinked();

		$linked_item_page = (int)$this->request->get('linked_item_page', 1);

		$linked_item_limit = (int)$this->request->get('linked_item_limit', 100);

		
		$pagination = new Pagination();
		$pagination->total = $total_linked;
		$pagination->page = $linked_item_page;
		$pagination->limit = 100;
		$pagination->text = $this->data['text_pagination'];
		$pagination->url = $this->url->link('openbay/ebay/viewItemLinks', 'token=' . $this->session->data['token'] . '&linked_item_page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['linked_items'] = $this->model_openbay_ebay->loadLinked($linked_item_limit, $linked_item_page);

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/ebay_item_link.tpl', $this->data));
	}

	public function saveItemLink() {
		$this->load->model('openbay/ebay');

		$json = $this->model_openbay_ebay->saveItemLink($this->request->get);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeItemLink() {
		$this->data = $this->load->language('openbay/ebay');

		$this->openbay->ebay->removeItemByProductId($this->request->get['product_id']);

		$json = array('error' => false, 'msg' => $this->data['item_link_removed']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function loadUnlinked(){
		set_time_limit(0);

		$this->load->model('openbay/ebay');

		$filter = array();

		if (isset($this->request->post['filter_variant']) && !empty($this->request->post['filter_variant'])) {
			$filter['variants'] = (int)$this->request->post['filter_variant'];
		}

		if (isset($this->request->post['filter_title']) && !empty($this->request->post['filter_title'])) {
			$filter['title'] = (string)$this->request->post['filter_title'];
		}

		if (isset($this->request->post['filter_qty_min']) && !empty($this->request->post['filter_qty_min'])) {
			$filter['qty_min'] = (int)$this->request->post['filter_qty_min'];
		}

		if (isset($this->request->post['filter_qty_max']) && !empty($this->request->post['filter_qty_max'])) {
			$filter['qty_max'] = (int)$this->request->post['filter_qty_max'];
		}

		$data = $this->model_openbay_ebay->loadUnlinked(200, $this->request->get['page'], $filter);

		if (!empty($data)) {
			$data['more_pages'] = 1;

			if ($data['next_page'] > $data['max_page']){
				$data['more_pages'] = 0;
			}

			$json['data'] = $data;
		} else {
			$json['data'] = null;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function loadLinkedStatus(){
		set_time_limit(0);

		$this->load->model('openbay/ebay');

		$json['data'] = '';
		if (isset($this->request->post['item_id']) && !empty($this->request->post['item_id'])){
			$data = $this->model_openbay_ebay->loadLinkedStatus($this->request->post['item_id']);

			if (!empty($data)) {
				$json['data'] = $data;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'openbay/ebay')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	private function checkConfig() {
		return true;
		if ($this->config->get('ebay_token') == '' || $this->config->get('ebay_secret') == '') {
			return false;
		} else {
			return true;
		}
	}

	public function edit() {
		if ($this->checkConfig() == true) {
			if (!empty($this->request->get['product_id'])) {
				$this->data = $this->load->language('openbay/ebay_edit');

				$this->load->model('catalog/product');
				$this->load->model('tool/image');
				$this->load->model('catalog/manufacturer');
				$this->load->model('openbay/ebay');
				$this->load->model('openbay/ebay_product');

				$this->document->setTitle($this->data['heading_title']);
				$this->document->addScript('view/javascript/openbay/js/faq.js');

				$this->data['action']       = $this->url->link('openbay/ebay/create', 'token=' . $this->session->data['token'], 'SSL');
				$this->data['cancel']       = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL');
				$this->data['view_link']    = $this->config->get('ebay_itm_link') . $this->openbay->ebay->getEbayItemId($this->request->get['product_id']);
				$this->data['token']        = $this->session->data['token'];
				$this->data['product_id']   = $this->request->get['product_id'];

				// Breadcrumb array with common function of Text and URL 
				$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/edit', 'token=' . $this->session->data['token'], 'SSL'),
						));

				$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

				$this->response->setOutput($this->load->view('openbay/ebay_edit.tpl', $this->data));
			} else {
				$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL'));
			}
		}
	}

	public function editLoad() {
		$this->load->model('catalog/product');
		$this->load->model('openbay/ebay_product');
		$this->load->model('tool/image');

		$item_id = $this->openbay->ebay->getEbayItemId($this->request->get['product_id']);

		if (!empty($item_id)) {
			$listings   = $this->openbay->ebay->getEbayListing($item_id);
			$stock      = $this->openbay->ebay->getProductStockLevel($this->request->get['product_id']);
			$reserve    = $this->openbay->ebay->getReserve($this->request->get['product_id'], $item_id);
			$options    = array();

			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

			if ($this->openbay->addonLoad('openstock') && $product_info['has_option'] == 1) {
				$this->load->model('openstock/openstock');
				$this->data['addon']['openstock'] = true;
				$product_info['options'] = $this->model_openstock_openstock->getProductOptionStocks($this->request->get['product_id']);
				$product_info['option_grp'] = $this->model_openbay_ebay_product->getProductOptions($this->request->get['product_id']);

				$t = array();
				$t_rel = array();

				foreach($product_info['option_grp'] as $grp) {
					$t_tmp = array();

					foreach($grp['product_option_value'] as $grp_node) {
						$t_tmp[$grp_node['option_value_id']] = $grp_node['name'];
						$t_rel[$grp_node['product_option_value_id']] = $grp['name'];
					}

					$t[] = array('name' => $grp['name'], 'child' => $t_tmp);
				}

				if (!isset($listings['variations']['Variation'][1])) {
					$listings['variations']['Variation'] = array($listings['variations']['Variation']);
				}

				foreach($product_info['options'] as $option) {
					$option['base64'] = base64_encode(serialize($option['opts']));
					$option_reserve = $this->openbay->ebay->getReserve($this->request->get['product_id'], $item_id, $option['var']);
					if ($option_reserve == false) {
						$option['reserve'] = 0;
					} else {
						$option['reserve']  = $this->openbay->ebay->getReserve($this->request->get['product_id'], $item_id, $option['var']);
					}

					$ebay_listing = '';

					foreach($listings['variations']['Variation'] as $listing) {

						$sku = (isset($listing['SKU']) ? $listing['SKU'] : '');

						if ($sku != '' && $sku == $option['var']) {
							$listing['StartPrice'] = number_format($listing['StartPrice'], 2, '.', '');
							$listing['Quantity'] = $listing['Quantity'] - $listing['SellingStatus']['QuantitySold'];

							$ebay_listing = $listing;
						}
					}

					$options[] = array('ebay' => $ebay_listing, 'local' => $option, 'var' => $option['var']);
				}

				//unset variants that dont appear on eBay
				$notlive = array();
				foreach($options as $k => $option) {
					if (empty($option['ebay'])) {
						$notlive[] = $options[$k];
						unset($options[$k]);
					}
				}

				$variant = array(
					'variant' => 1,
					'data' => array(
						'grp_info' => array(
							'optGroupArray' => base64_encode(serialize($t)),
							'optGroupRelArray' => base64_encode(serialize($t_rel)),
						),
						'options' => $options,
						'optionsinactive' => $notlive
					)
				);

			} else {
				$variant = array('variant' => 0, 'data' => '');
			}

			$this->data['product'] = $product_info;

			if ($reserve == false) {
				$reserve = 0;
			}

			$this->data = array(
				'listing'   => $listings,
				'stock'     => $stock,
				'reserve'   => $reserve,
				'variant'   => $variant
			);

			if (!empty($listings)) {
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode(array('error' => false, 'data' => $this->data)));
			} else {
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode(array('error' => true)));
			}
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(array('error' => true)));
		}
	}

	public function editSave() {
		if ($this->checkConfig() == true && $this->request->server['REQUEST_METHOD'] == 'POST') {

			$this->load->model('openbay/ebay');

			$json = $this->model_openbay_ebay->editSave($this->request->post);

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	public function create() {
	
		if ($this->checkConfig() == true) {
			if (!empty($this->request->get['product_id'])) {
				$this->data = $this->load->language('openbay/ebay_new');

				$this->load->model('catalog/product');
				
				$this->load->model('tool/image');
				$this->load->model('catalog/manufacturer');
				$this->load->model('openbay/ebay');
				$this->load->model('openbay/ebay_template');
				$this->load->model('openbay/ebay_product');
				$this->load->model('openbay/ebay_profile');

				$this->document->setTitle($this->data['heading_title']);
				$this->document->addScript('view/javascript/openbay/js/faq.js');

				$this->data['action']   = $this->url->link('openbay/ebay/create', 'token=' . $this->session->data['token'], 'SSL');
				$this->data['cancel']   = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL');
				$this->data['token']    = $this->session->data['token'];

				// Breadcrumb array with common function of Text and URL 
				$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/create', 'token=' . $this->session->data['token'], 'SSL'),
						));

				$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

				$setting = array();

				$setting['dispatch_times'] = $this->openbay->ebay->getSetting('dispatch_time_max');

				if (is_array($setting['dispatch_times'])) {
					ksort($setting['dispatch_times']);
				}

				$setting['countries'] = $this->openbay->ebay->getSetting('countries');

				if (is_array($setting['countries'])) {
					ksort($setting['countries']);
				}

				$setting['returns'] = $this->openbay->ebay->getSetting('returns');
				$setting['package_type'] = $this->openbay->ebay->getSetting('package_type');
				$setting['shipping_types'] = $this->openbay->ebay->getSetting('shipping_types');
				$setting['measurement_types'] = $this->openbay->ebay->getSetting('measurement_types');

				if (empty($setting['dispatch_times']) || empty($setting['countries']) || empty($setting['returns'])){
					$this->session->data['warning'] = $this->data['error_missing_settings'];
					$this->response->redirect($this->url->link('openbay/ebay/syncronise&token=' . $this->session->data['token'], 'SSL'));
				}

				$this->data['setting'] = $setting;

				if ($this->openbay->addonLoad('openstock') && $product_info['has_option'] == 1) {
					$this->load->model('openstock/openstock');
					$this->data['addon']['openstock'] = true;
					$product_info['options'] = $this->model_openstock_openstock->getProductOptionStocks($this->request->get['product_id']);
					$product_info['option_grp'] = $this->model_openbay_ebay_product->getProductOptions($this->request->get['product_id']);
				}

				// get the product tax rate from opencart
				if (isset($product_info['tax_class_id'])) {
					$product_info['defaults']['tax'] = $this->model_openbay_ebay_product->getTaxRate($product_info['tax_class_id']);
				} else {
					$product_info['defaults']['tax'] = 0.00;
				}

				//get the popular categories the user has used
				$product_info['popular_cats'] = $this->model_openbay_ebay->getPopularCategories();

				//get shipping profiles
				$product_info['profiles_shipping'] = $this->model_openbay_ebay_profile->getAll(0);
				//get default shipping profile
				$product_info['profiles_shipping_def'] = $this->model_openbay_ebay_profile->getDefault(0);

				//get returns profiles
				$product_info['profiles_returns'] = $this->model_openbay_ebay_profile->getAll(1);
				//get default returns profile
				$product_info['profiles_returns_def'] = $this->model_openbay_ebay_profile->getDefault(1);
				$this->data['data']['shipping_international_zones']     = $this->model_openbay_ebay->getShippingLocations();

				//get theme profiles
				$product_info['profiles_theme'] = $this->model_openbay_ebay_profile->getAll(2);
				//get default returns profile
				$product_info['profiles_theme_def'] = $this->model_openbay_ebay_profile->getDefault(2);

				//get generic profiles
				$product_info['profiles_generic'] = $this->model_openbay_ebay_profile->getAll(3);
				//get default generic profile
				$product_info['profiles_generic_def'] = $this->model_openbay_ebay_profile->getDefault(3);

				//product attributes - this is just a direct pass through used with the template tag
				$product_info['attributes'] = base64_encode(json_encode($this->model_openbay_ebay->getProductAttributes($this->request->get['product_id'])));

				//post edit link
				$product_info['edit_link'] = $this->url->link('openbay/ebay/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'], 'SSL');

				//images
				$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
				$product_info['product_images'] = array();

				if (!empty($product_info['image'])) {
					$img_info = getimagesize(DIR_IMAGE . $product_info['image']);

					$product_info['product_images'][] = array(
						'image' => $product_info['image'],
						'preview' => $this->model_tool_image->resize($product_info['image'], 100, 100),
						'full' => HTTPS_CATALOG . 'image/' . $product_info['image'],
						'width' => $img_info[0],
						'height' => $img_info[1],
					);
				}

				foreach ($product_images as $product_image) {
					if ($product_image['image'] && file_exists(DIR_IMAGE . $product_image['image'])) {
						$img_info = getimagesize(DIR_IMAGE . $product_image['image']);

						$product_info['product_images'][] = array(
							'image' => $product_image['image'],
							'preview' => $this->model_tool_image->resize($product_image['image'], 100, 100),
							'full' => HTTPS_CATALOG . 'image/' . $product_image['image'],
							'width' => $img_info[0],
							'height' => $img_info[1],
						);
					}
				}

				$product_info['manufacturers']                      = $this->model_catalog_manufacturer->getManufacturers();
				$product_info['payments']                           = $this->model_openbay_ebay->getPaymentTypes();
				$product_info['templates']                          = $this->model_openbay_ebay_template->getAll();
				$product_info['store_cats']                         = $this->model_openbay_ebay->getSellerStoreCategories();

				$product_info['defaults']['cod_surcharge'] = 0;

				foreach($product_info['payments'] as $payment) {
					if ($payment['ebay_name'] == 'COD') {
						$product_info['defaults']['cod_surcharge'] = 1;
					}
				}

				$product_info['defaults']['ebay_payment_types']     = $this->config->get('ebay_payment_types');
				$product_info['defaults']['paypal_address']         = $this->config->get('ebay_payment_paypal_address');
				$product_info['defaults']['payment_instruction']    = $this->config->get('ebay_payment_instruction');
				$product_info['defaults']['ebay_payment_immediate'] = $this->config->get('ebay_payment_immediate');

				$product_info['defaults']['gallery_height']         = '400';
				$product_info['defaults']['gallery_width']          = '400';
				$product_info['defaults']['thumb_height']           = '100';
				$product_info['defaults']['thumb_width']            = '100';

				$product_info['defaults']['ebay_measurement'] = $this->config->get('ebay_measurement');

				$product_info['defaults']['listing_duration'] = $this->config->get('ebay_duration');
				if ($product_info['defaults']['listing_duration'] == '') {
					$product_info['defaults']['listing_duration'] = 'Days_30';
				}

				if (isset($this->error['warning'])) {
					$this->data['error_warning'] = $this->error['warning'];
				} else {
					$this->data['error_warning'] = '';
				}

				if ($product_info['quantity'] < 1 && (!isset($product_info['has_option']) || $product_info['has_option'] == 0)) {
					$this->data['error_warning'] = $this->data['error_no_stock'];
				}

				$this->data['no_image'] = $this->model_tool_image->resize('no_image.png', 100, 100);

				$weight_parts = explode('.', $product_info['weight']);
				$product_info['weight_major'] = (int)$weight_parts[0];
				$product_info['weight_minor'] = (int)substr($weight_parts[1], 0, 3);

				$this->data['product'] = $product_info;

				$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

				$this->response->setOutput($this->load->view('openbay/ebay_new.tpl', $this->data));
			} else {
				$this->response->redirect($this->url->link('extension/openbay/items&token=' . $this->session->data['token']));
			}
		}
	}

	public function createBulk() {
		if ($this->checkConfig() == true) {
			if (!empty($this->request->post['selected'])) {
				$this->data = $this->load->language('openbay/ebay_newbulk');

				$this->load->model('catalog/product');
				$this->load->model('tool/image');
				$this->load->model('catalog/manufacturer');
				$this->load->model('openbay/ebay');
				$this->load->model('openbay/ebay_profile');

				// Breadcrumb array with common function of Text and URL 
				$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_ebay'],
							$this->url->link('openbay/ebay', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/ebay/createBulk', 'token=' . $this->session->data['token'], 'SSL'),
						));

				$this->data['error_warning'] = array();

				$this->data['cancel'] = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL');
				$this->data['image_directory'] = DIR_IMAGE;

				$active_list = $this->model_openbay_ebay->getLiveListingArray();

				$products = array();

				if ($this->openbay->addonLoad('openstock')) {
					$openstock = 1;
				} else {
					$openstock = 0;
				}

				foreach ($this->request->post['selected'] as $product_id) {
					if (!array_key_exists($product_id, $active_list)) {

						$prod = $this->model_catalog_product->getProduct($product_id);

						if ($openstock == 1 && isset($prod['has_option']) && $prod['has_option'] == 1) {
							$this->data['error_warning']['os'] = $this->data['text_error_variants'];
						} else {
							if ($prod['quantity'] > 0) {
								if ($prod['image'] && file_exists(DIR_IMAGE . $prod['image'])) {
									$prod['image'] = $this->model_tool_image->resize($prod['image'], 80, 80);
								} else {
									$prod['image'] = $this->model_tool_image->resize('no_image.png', 80, 80);
								}

								$products[] = $prod;
							} else {
								$this->data['error_warning']['stock'] = $this->data['text_error_stock'];
							}
						}
					} else {
						$this->data['error_warning']['exists'] = $this->data['text_exists'];
					}
				}

				$this->data['count'] = count($products);
				$this->data['token'] = $this->session->data['token'];
				$this->data['listing_link'] = $this->config->get('ebay_itm_link');

				$plan = $this->model_openbay_ebay->getMyPlan();

				if ($plan['plan']['listing_bulk'] == 1) {
					if ($this->data['count'] == 0) {
						$this->data['error_fail'][] = $this->data['text_error_no_product'];
					} else {
						if (($plan['plan']['listing_limit'] == 0) || (($plan['usage']['items'] + $this->data['count']) <= $plan['plan']['listing_limit'])) {
							if ($this->data['count'] > 5) {
								$this->data['error_warning']['count'] = sprintf($this->data['text_error_count'], $this->data['count']);
							}

							//load the settings from eBay
							$setting = array();

							$setting['dispatch_times'] = $this->openbay->ebay->getSetting('dispatch_time_max');
							if (is_array($setting['dispatch_times'])) {
								ksort($setting['dispatch_times']);
							}

							$setting['countries'] = $this->openbay->ebay->getSetting('countries');
							if (is_array($setting['countries'])) {
								ksort($setting['countries']);
							}

							$setting['returns'] = $this->openbay->ebay->getSetting('returns');

							if (empty($setting['dispatch_times']) || empty($setting['countries']) || empty($setting['returns'])){
								$this->session->data['warning'] = $this->data['error_missing_settings'];
								$this->response->redirect($this->url->link('openbay/ebay/syncronise&token=' . $this->session->data['token'], 'SSL'));
							}

							$this->data['setting'] = $setting;

							//get generic profiles
							$product_info['profiles_generic'] = $this->model_openbay_ebay_profile->getAll(3);
							//get default generic profile
							$product_info['profiles_generic_def'] = $this->model_openbay_ebay_profile->getDefault(3);
							if ($product_info['profiles_generic_def'] === false) {
								$this->data['error_fail'][] = $this->data['text_error_generic_profile'];
							}

							//get shipping profiles
							$product_info['profiles_shipping'] = $this->model_openbay_ebay_profile->getAll(0);
							//get default shipping profile
							$product_info['profiles_shipping_def'] = $this->model_openbay_ebay_profile->getDefault(0);
							//check it has a default profile
							if ($product_info['profiles_shipping_def'] === false) {
								$this->data['error_fail'][] = $this->data['text_error_ship_profile'];
							}

							//get returns profiles
							$product_info['profiles_returns'] = $this->model_openbay_ebay_profile->getAll(1);
							//get default returns profile
							$product_info['profiles_returns_def'] = $this->model_openbay_ebay_profile->getDefault(1);
							//check it has a default profile
							if ($product_info['profiles_returns_def'] === false) {
								$this->data['error_fail'][] = $this->data['text_error_return_profile'];
							}

							//get returns profiles
							$product_info['profiles_theme'] = $this->model_openbay_ebay_profile->getAll(2);
							//get default returns profile
							$product_info['profiles_theme_def'] = $this->model_openbay_ebay_profile->getDefault(2);
							//check it has a default profile
							if ($product_info['profiles_theme_def'] === false) {
								$this->data['error_fail'][] = $this->data['text_error_theme_profile'];
							}

							// get the product tax rate
							if (isset($product_info['tax_class_id'])) {
								$product_info['defaults']['tax'] = $this->model_openbay_ebay_product->getTaxRate($product_info['tax_class_id']);
							} else {
								$product_info['defaults']['tax'] = 0.00;
							}

							$this->data['products'] = $products;

							$product_info['manufacturers']  = $this->model_catalog_manufacturer->getManufacturers();
							$product_info['payments']       = $this->model_openbay_ebay->getPaymentTypes();
							$product_info['store_cats']     = $this->model_openbay_ebay->getSellerStoreCategories();

							$product_info['defaults']['ebay_template'] = $this->config->get('ebay_template');

							$product_info['defaults']['listing_duration'] = $this->config->get('ebay_duration');
							if ($product_info['defaults']['listing_duration'] == '') {
								$product_info['defaults']['listing_duration'] = 'Days_30';
							}

							$this->data['default'] = $product_info;
						} else {
							$this->data['error_fail']['plan'] = sprintf($this->data['text_item_limit'], $this->url->link('openbay/ebay/subscription', 'token=' . $this->session->data['token'], 'SSL'));
						}
					}
				} else {
					$this->data['error_fail']['plan'] = sprintf($this->data['text_bulk_plan_error'], $this->url->link('openbay/ebay/subscription', 'token=' . $this->session->data['token'], 'SSL'));
				}

				$this->document->setTitle($this->data['heading_title']);
				$this->document->addScript('view/javascript/openbay/js/faq.js');
				$this->document->addScript('view/javascript/openbay/js/openbay.js');

				$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

				$this->response->setOutput($this->load->view('openbay/ebay_new_bulk.tpl', $this->data));
			} else {
				$this->data = $this->load->language('openbay/ebay_newbulk');
				$this->session->data['warning'] = $this->data['text_error_no_selection'];
				$this->response->redirect($this->url->link('extension/openbay/items&token=' . $this->session->data['token']));
			}
		}
	}

	public function verify() {
		$this->load->model('openbay/ebay');
		$this->load->model('openbay/ebay_template');
		$this->load->model('catalog/product');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ($this->checkConfig() == true) {
				$this->model_openbay_ebay->logCategoryUsed($this->request->post['finalCat']);

				$item_id = $this->openbay->ebay->getEbayItemId($this->request->post['product_id']);

				if ($item_id == false) {
					$data = $this->request->post;

					if ($data['template'] != 'None') {
						$template = $this->model_openbay_ebay_template->get($this->data['template']);
						$data['template_html'] = (isset($template['html']) ? base64_encode($template['html']) : '');
					} else {
						$data['template_html'] = '';
					}

					// set shipping data
					$data['national'] = $data['data']['national'];
					$data['international'] = $data['data']['international'];
					unset($data['data']);

					if (!empty($data['img_tpl'])) {
						$tmp_gallery_array = array();
						$tmp_thumbnail_array = array();
						$this->load->model('tool/image');

						foreach ($data['img_tpl'] as $k => $v) {
							$tmp_gallery_array[$k] = $this->model_tool_image->resize($v, $data['gallery_width'], $data['gallery_height']);
							$tmp_thumbnail_array[$k] = $this->model_tool_image->resize($v, $data['thumb_width'], $data['thumb_height']);
						}

						$data['img_tpl'] = $tmp_gallery_array;
						$data['img_tpl_thumb'] = $tmp_thumbnail_array;
					}

					$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$data['product_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

					$data['product_info'] = $query->row;

					if (!empty($data['product_info']['sku'])){
						$data['sku'] = $data['product_info']['sku'];
					}

					$json = $this->model_openbay_ebay->ebayVerifyAddItem($data, $this->request->get['options']);

					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode($json));
				} else {
					$this->response->addHeader('Content-Type: application/json');
					$this->response->setOutput(json_encode(array('error' => true, 'msg' => 'This item is already listed in your eBay account', 'item' => $item_id)));
				}
			}
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items&token=' . $this->session->data['token']));
		}
	}

	public function verifyBulk() {
		$this->load->model('openbay/ebay_profile');
		$this->load->model('openbay/ebay');
		$this->load->model('openbay/ebay_template');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ($this->checkConfig() == true) {
				$post = $this->request->post;
				$data = array();

				//load all of the listing defaults and assign to correct variable names
				$profile_shipping           = $this->model_openbay_ebay_profile->get($post['shipping_profile']);
				$profile_return             = $this->model_openbay_ebay_profile->get($post['return_profile']);
				$profile_template           = $this->model_openbay_ebay_profile->get($post['theme_profile']);
				$profile_generic            = $this->model_openbay_ebay_profile->get($post['generic_profile']);

				$payments                   = $this->model_openbay_ebay->getPaymentTypes();
				$payments_accepted          = $this->config->get('ebay_payment_types');
				$product_info               = $this->model_catalog_product->getProduct($post['product_id']);

				// set shipping data
				$data['national'] = $profile_shipping['data']['national'];
				$data['international'] = $profile_shipping['data']['international'];

				$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$post['product_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

				$data['product_info'] 		= $query->row;

				$data['description']        = $product_info['description'];
				$data['name']               = $post['title'];
				$data['sub_name']           = '';
				$data['bestoffer']          = 0;
				$data['finalCat']           = $post['finalCat'];
				$data['price'][0]           = $post['price'];
				$data['qty'][0]             = (int)$post['qty'];
				$data['product_id']         = (int)$post['product_id'];

				$data['feat']           	= $post['feat'];
				$data['featother']          = $post['featother'];

				if (!empty($product_info['sku'])){
					$data['sku'] = $product_info['sku'];
				}

				$data['auction_duration']   = $post['duration'];
				$data['condition']          = (isset($post['condition']) && $post['condition'] != 0 ? $post['condition'] : '');
				$data['auction_type']       = 'FixedPriceItem';
				$data['catalog_epid']       = (isset($post['catalog_epid']) && $post['catalog_epid'] != 0 ? $post['catalog_epid'] : '');

				$data['ebay_payment_immediate']  = $this->config->get('ebay_payment_immediate');
				$data['paypal_email']       = $this->config->get('ebay_payment_paypal_address');
				$data['payment_instruction']= $this->config->get('ebay_payment_instruction');

				if (isset($profile_return['data']['returns_accepted'])) {
					$data['returns_accepted'] = $profile_return['data']['returns_accepted'];
				}
				if (isset($profile_return['data']['returns_policy'])) {
					$data['return_policy'] = $profile_return['data']['returns_policy'];
				}
				if (isset($profile_return['data']['returns_option'])) {
					$data['returns_option'] = $profile_return['data']['returns_option'];
				}
				if (isset($profile_return['data']['returns_within'])) {
					$data['returns_within'] = $profile_return['data']['returns_within'];
				}
				if (isset($profile_return['data']['returns_shipping'])) {
					$data['returns_shipping'] = $profile_return['data']['returns_shipping'];
				}
				if (isset($profile_return['data']['returns_restocking_fee'])) {
					$data['returns_restocking_fee'] = $profile_return['data']['returns_restocking_fee'];
				}

				$data['location']           = $profile_shipping['data']['location'];
				$data['postcode']           = $profile_shipping['data']['postcode'];
				$data['dispatch_time']      = $profile_shipping['data']['dispatch_time'];

				if (isset($profile_shipping['data']['country'])) {
					$data['country'] = $profile_shipping['data']['country'];
				}

				$data['get_it_fast']        = (isset($profile_shipping['data']['get_it_fast']) ? $profile_shipping['data']['get_it_fast'] : 0);

				if (isset($profile_template['data']['ebay_template_id'])) {
					$template = $this->model_openbay_ebay_template->get($profile_template['data']['ebay_template_id']);
					$data['template_html'] = (isset($template['html']) ? base64_encode($template['html']) : '');
					$data['template'] = $profile_template['data']['ebay_template_id'];
				} else {
					$data['template_html'] = '';
					$data['template'] = '';
				}

				$data['gallery_plus']       = $profile_template['data']['ebay_gallery_plus'];
				$data['gallery_super']      = $profile_template['data']['ebay_supersize'];

				$data['private_listing']    = $profile_generic['data']['private_listing'];

				//product attributes - this is just a direct pass through used with the template tag
				$data['attributes'] = base64_encode(json_encode($this->model_openbay_ebay->getProductAttributes($post['product_id'])));

				$data['payments'] = array();
				foreach($payments as $payment) {
					if ($payments_accepted[$payment['ebay_name']] == 1) {
						$data['payments'][$payment['ebay_name']] = 1;
					}
				}

				$data['main_image'] = 0;
				$data['img'] = array();

				$product_images = $this->model_catalog_product->getProductImages($post['product_id']);

				$product_info['product_images'] = array();

				if (!empty($product_info['image'])) {
					$data['img'][] = $product_info['image'];
				}

				if (isset($profile_template['data']['ebay_img_ebay']) && $profile_template['data']['ebay_img_ebay'] == 1) {
					foreach ($product_images as $product_image) {
						if ($product_image['image'] && file_exists(DIR_IMAGE . $product_image['image'])) {
							$data['img'][] =  $product_image['image'];
						}
					}
				}

				if (isset($profile_template['data']['ebay_img_template']) && $profile_template['data']['ebay_img_template'] == 1) {
					$tmp_gallery_array = array();
					$tmp_thumbnail_array = array();

					//if the user has not set the exclude default image, add it to the array for theme images.
					$key_offset = 0;
					if (!isset($profile_template['data']['default_img_exclude']) || $profile_template['data']['default_img_exclude'] != 1) {
						$tmp_gallery_array[0] = $this->model_tool_image->resize($product_info['image'], $profile_template['data']['ebay_gallery_width'], $profile_template['data']['ebay_gallery_height']);
						$tmp_thumbnail_array[0] = $this->model_tool_image->resize($product_info['image'], $profile_template['data']['ebay_thumb_width'], $profile_template['data']['ebay_thumb_height']);
						$key_offset = 1;
					}

					//loop through the product images and add them.
					foreach ($product_images as $k => $v) {
						$tmp_gallery_array[$k+$key_offset] = $this->model_tool_image->resize($v['image'], $profile_template['data']['ebay_gallery_width'], $profile_template['data']['ebay_gallery_height']);
						$tmp_thumbnail_array[$k+$key_offset] = $this->model_tool_image->resize($v['image'], $profile_template['data']['ebay_thumb_width'], $profile_template['data']['ebay_thumb_height']);
					}

					$data['img_tpl']        = $tmp_gallery_array;
					$data['img_tpl_thumb']  = $tmp_thumbnail_array;
				}

				$data = array_merge($data, $profile_shipping['data']);

				$verify_response = $this->model_openbay_ebay->ebayVerifyAddItem($data, 'no');

				$json = array(
					'errors'    => $verify_response['data']['Errors'],
					'fees'      => $verify_response['data']['Fees'],
					'itemid'    => (string)$verify_response['data']['ItemID'],
					'preview'   => (string)$verify_response['data']['link'],
					'i'         => $this->request->get['i'],
					'ack'       => (string)$verify_response['data']['Ack'],
				);

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			}
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items&token=' . $this->session->data['token']));
		}
	}

	public function listItem() {
		$this->load->model('openbay/ebay');
		$this->load->model('openbay/ebay_template');
		$this->load->model('catalog/product');

		if ($this->checkConfig() == true && $this->request->server['REQUEST_METHOD'] == 'POST') {
			$data = $this->request->post;

			if ($data['template'] != 'None') {
				$template = $this->model_openbay_ebay_template->get($this->data['template']);
				$data['template_html'] = (isset($template['html']) ? base64_encode($template['html']) : '');
			} else {
				$data['template_html'] = '';
			}

			// set shipping data
			$data['national'] = $data['data']['national'];
			$data['international'] = $data['data']['international'];
			unset($data['data']);

			if (!empty($this->data['img_tpl'])) {
				$tmp_gallery_array = array();
				$tmp_thumbnail_array = array();
				$this->load->model('tool/image');

				foreach ($this->data['img_tpl'] as $k => $v) {
					$tmp_gallery_array[$k] = $this->model_tool_image->resize($v, $data['gallery_width'], $data['gallery_height']);
					$tmp_thumbnail_array[$k] = $this->model_tool_image->resize($v, $data['thumb_width'], $data['thumb_height']);
				}

				$data['img_tpl'] = $tmp_gallery_array;
				$data['img_tpl_thumb'] = $tmp_thumbnail_array;
			}

			$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$data['product_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

			$data['product_info'] = $query->row;

			if (!empty($data['product_info']['sku'])){
				$data['sku'] = $data['product_info']['sku'];
			}

			$json = $this->model_openbay_ebay->ebayAddItem($data, $this->request->get['options']);

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items&token=' . $this->session->data['token']));
		}
	}

	public function listItemBulk() {
		$this->load->model('openbay/ebay_profile');
		$this->load->model('openbay/ebay');
		$this->load->model('openbay/ebay_template');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ($this->checkConfig() == true) {
				$post = $this->request->post;
				$data = array();

				//load all of the listing defaults and assign to correct variable names
				$profile_shipping           = $this->model_openbay_ebay_profile->get($post['shipping_profile']);
				$profile_return             = $this->model_openbay_ebay_profile->get($post['return_profile']);
				$profile_template           = $this->model_openbay_ebay_profile->get($post['theme_profile']);
				$profile_generic            = $this->model_openbay_ebay_profile->get($post['generic_profile']);
				$payments                   = $this->model_openbay_ebay->getPaymentTypes();
				$payments_accepted           = $this->config->get('ebay_payment_types');
				$product_info               = $this->model_catalog_product->getProduct($post['product_id']);

				// set shipping data
				$data['national'] = $profile_shipping['data']['national'];
				$data['international'] = $profile_shipping['data']['international'];

				$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$post['product_id'] . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

				$data['product_info']       = $query->row;

				$data['description']        = $product_info['description'];
				$data['name']               = $post['title'];
				$data['sub_name']           = '';
				$data['bestoffer']          = 0;
				$data['finalCat']           = $post['finalCat'];
				$data['price'][0]           = $post['price'];
				$data['qty'][0]             = $post['qty'];
				$data['product_id']         = $post['product_id'];

				$data['feat']           	= $post['feat'];
				$data['featother']          = $post['featother'];

				if (!empty($product_info['sku'])){
					$data['sku'] = $product_info['sku'];
				}

				$data['auction_duration']   = $post['duration'];
				$data['condition']          = (isset($post['condition']) && $post['condition'] != 0 ? $post['condition'] : '');
				$data['auction_type']       = 'FixedPriceItem';
				$data['catalog_epid']       = (isset($post['catalog_epid']) && $post['catalog_epid'] != 0 ? $post['catalog_epid'] : '');

				$data['ebay_payment_immediate']  = $this->config->get('ebay_payment_immediate');
				$data['paypal_email']       = $this->config->get('ebay_payment_paypal_address');
				$data['payment_instruction']= $this->config->get('ebay_payment_instruction');

				if (isset($profile_return['data']['returns_accepted'])) {
					$data['returns_accepted'] = $profile_return['data']['returns_accepted'];
				}
				if (isset($profile_return['data']['returns_policy'])) {
					$data['return_policy'] = $profile_return['data']['returns_policy'];
				}
				if (isset($profile_return['data']['returns_option'])) {
					$data['returns_option'] = $profile_return['data']['returns_option'];
				}
				if (isset($profile_return['data']['returns_within'])) {
					$data['returns_within'] = $profile_return['data']['returns_within'];
				}
				if (isset($profile_return['data']['returns_shipping'])) {
					$data['returns_shipping'] = $profile_return['data']['returns_shipping'];
				}
				if (isset($profile_return['data']['returns_restocking_fee'])) {
					$data['returns_restocking_fee'] = $profile_return['data']['returns_restocking_fee'];
				}

				$data['location']           = $profile_shipping['data']['location'];
				$data['postcode']           = $profile_shipping['data']['postcode'];
				$data['dispatch_time']      = $profile_shipping['data']['dispatch_time'];

				if (isset($profile_shipping['data']['country'])) {
					$data['country'] = $profile_shipping['data']['country'];
				}

				$data['get_it_fast']        = (isset($profile_shipping['data']['get_it_fast']) ? $profile_shipping['data']['get_it_fast'] : 0);

				if (isset($profile_template['data']['ebay_template_id'])) {
					$template = $this->model_openbay_ebay_template->get($profile_template['data']['ebay_template_id']);
					$data['template_html'] = (isset($template['html']) ? base64_encode($template['html']) : '');
					$data['template'] = $profile_template['data']['ebay_template_id'];
				} else {
					$data['template_html'] = '';
					$data['template'] = '';
				}

				$data['gallery_plus']       = $profile_template['data']['ebay_gallery_plus'];
				$data['gallery_super']      = $profile_template['data']['ebay_supersize'];

				$data['private_listing']    = $profile_generic['data']['private_listing'];

				//product attributes - this is just a direct pass through used with the template tag
				$data['attributes'] = base64_encode(json_encode($this->model_openbay_ebay->getProductAttributes($post['product_id'])));

				$data['payments'] = array();
				foreach($payments as $payment) {
					if ($payments_accepted[$payment['ebay_name']] == 1) {
						$data['payments'][$payment['ebay_name']] = 1;
					}
				}

				$data['main_image'] = 0;
				$data['img'] = array();

				$product_images = $this->model_catalog_product->getProductImages($post['product_id']);

				$product_info['product_images'] = array();

				if (!empty($product_info['image'])) {
					$data['img'][] = $product_info['image'];
				}

				if (isset($profile_template['data']['ebay_img_ebay']) && $profile_template['data']['ebay_img_ebay'] == 1) {
					foreach ($product_images as $product_image) {
						if ($product_image['image'] && file_exists(DIR_IMAGE . $product_image['image'])) {
							$data['img'][] =  $product_image['image'];
						}
					}
				}

				if (isset($profile_template['data']['ebay_img_template']) && $profile_template['data']['ebay_img_template'] == 1) {
					$tmp_gallery_array = array();
					$tmp_thumbnail_array = array();

					//if the user has not set the exclude default image, add it to the array for theme images.
					$key_offset = 0;
					if (!isset($profile_template['data']['default_img_exclude']) || $profile_template['data']['default_img_exclude'] != 1) {
						$tmp_gallery_array[0] = $this->model_tool_image->resize($product_info['image'], $profile_template['data']['ebay_gallery_width'], $profile_template['data']['ebay_gallery_height']);
						$tmp_thumbnail_array[0] = $this->model_tool_image->resize($product_info['image'], $profile_template['data']['ebay_thumb_width'], $profile_template['data']['ebay_thumb_height']);
						$key_offset = 1;
					}

					//loop through the product images and add them.
					foreach ($product_images as $k => $v) {
						$tmp_gallery_array[$k+$key_offset] = $this->model_tool_image->resize($v['image'], $profile_template['data']['ebay_gallery_width'], $profile_template['data']['ebay_gallery_height']);
						$tmp_thumbnail_array[$k+$key_offset] = $this->model_tool_image->resize($v['image'], $profile_template['data']['ebay_thumb_width'], $profile_template['data']['ebay_thumb_height']);
					}

					$data['img_tpl']        = $tmp_gallery_array;
					$data['img_tpl_thumb']  = $tmp_thumbnail_array;
				}

				$data = array_merge($data, $profile_shipping['data']);

				$verify_response = $this->model_openbay_ebay->ebayAddItem($data, 'no');

				$json = array(
					'errors'    => $verify_response['data']['Errors'],
					'fees'      => $verify_response['data']['Fees'],
					'itemid'    => (string)$verify_response['data']['ItemID'],
					'preview'   => (string)$verify_response['data']['link'],
					'i'         => $this->request->get['i'],
					'ack'       => (string)$verify_response['data']['Ack'],
				);

				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			}
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items&token=' . $this->session->data['token']));
		}
	}

	public function getImportImages() {
		set_time_limit(0);
		$this->openbay->ebay->getImages();

		$json = array('error' => false, 'msg' => 'OK');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function repairLinks() {
		$this->load->model('openbay/ebay_product');

		$this->model_openbay_ebay_product->repairLinks();

		$json = array('msg' => 'Links repaired');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function deleteAllLocks() {
		$this->openbay->ebay->log('deleteAllLocks() - Deleting all locks');
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ebay_order_lock`");

		$json = array('msg' => 'Locks deleted');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function endItem() {
		$json = $this->openbay->ebay->endItem($this->request->get['id']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}