<?php
class ControllerOpenbayAmazon extends Controller {
	public function install() {
		$this->load->model('openbay/amazon');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');
		$this->load->model('extension/event');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/amazon_listing');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/amazon_listing');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'openbay/amazon_product');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'openbay/amazon_product');

		$this->model_openbay_amazon->install(); 
	}

	public function uninstall() {  
		$this->load->model('openbay/amazon');
		$this->load->model('setting/setting');
		$this->load->model('extension/extension');
		$this->load->model('extension/event');

		$this->model_openbay_amazon->uninstall();
		$this->model_extension_extension->uninstall('openbay', $this->request->get['extension']);
		$this->model_setting_setting->deleteSetting($this->request->get['extension']);
	}

	public function index() {
		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->load->model('openbay/amazon');
		$this->load->model('sale/customer_group');

		$this->data = $this->load->language('openbay/amazon');

		$this->document->setTitle($this->data['text_dashboard']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_dashboard'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL')
						));

		$this->data['success'] = isset($this->session->data['success']) ? $this->session->data['success']: '';
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);

		$this->data['validation'] = $this->openbay->amazon->validate();
		$this->data['link_settings'] = $this->url->link('openbay/amazon/settings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['link_subscription'] = $this->url->link('openbay/amazon/subscription', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['link_item_link'] = $this->url->link('openbay/amazon/itemLinks', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['link_stock_updates'] = $this->url->link('openbay/amazon/stockUpdates', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['link_saved_listings'] = $this->url->link('openbay/amazon/savedListings', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['link_bulk_listing'] = $this->url->link('openbay/amazon/bulkListProducts', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['link_bulk_linking'] = $this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon.tpl', $this->data));
	}

	public function stockUpdates() {
		$this->data = $this->load->language('openbay/amazon_stockupdates');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_dashboard'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/amazon/stockUpdates', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['link_overview'] = $this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL');

		$request_arg = array();

		if (isset($this->request->get['filter_date_start'])) {
			$request_arg['date_start'] = date("Y-m-d", strtotime($this->request->get['filter_date_start']));
		} else {
			$request_arg['date_start'] = date("Y-m-d");
		}

		if (isset($this->request->get['filter_date_end'])) {
			$request_arg['date_end'] = date("Y-m-d", strtotime($this->request->get['filter_date_end']));
		} else {
			$request_arg['date_end'] = date("Y-m-d");
		}

		$this->data['date_start'] = $request_arg['date_start'];
		$this->data['date_end'] = $request_arg['date_end'];

		$xml = $this->openbay->amazon->getStockUpdatesStatus($request_arg);
		$xml_object = simplexml_load_string($xml);
		
		$this->data['table_data'] = array();

		if ($xml_object !== false) {
			$table_data = array();

			foreach($xml_object->update as $update_node) {
				$row = array('date_requested' => (string)$update_node->date_requested,
					'date_updated' => (string)$update_node->date_updated,
					'status' => (string)$update_node->status,
					);
				$this->data_items = array();
				foreach($update_node->data->product as $product_node) {
					$this->data_items[] = array('sku' => (string)$product_node->sku,
						'stock' => (int)$product_node->stock
						);
				}
				$row['data'] = $this->data_items;
				$table_data[(int)$update_node->ref] = $row;
			}

			$this->data['table_data'] = $table_data;

		} else {
			$this->data['error'] = 'Could not connect to OpenBay PRO API . ';
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon_stock_updates.tpl', $this->data));

	}

	public function subscription() {
		$this->data = $this->load->language('openbay/amazon_subscription');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_dashboard'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/amazon/subscription', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['link_overview'] = $this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL');

		$response = simplexml_load_string($this->openbay->amazon->call('plans/getPlans'));

		$this->data['plans'] = array();

		if ($response) {
			foreach ($response->Plan as $plan) {
				$this->data['plans'][] = array(
					'title' => (string)$plan->Title,
					'description' => (string)$plan->Description,
					'order_frequency' => (string)$plan->OrderFrequency,
					'product_listings' => (string)$plan->ProductListings,
					'bulk_listing' => (string)$plan->BulkListing,
					'price' => (string)$plan->Price,
				);
			}
		}

		$response = simplexml_load_string($this->openbay->amazon->call('plans/getUsersPlans'));

		$plan = false;

		if ($response) {
			$plan = array(
				'merchant_id' => (string)$response->MerchantId,
				'user_status' => (string)$response->UserStatus,
				'title' => (string)$response->Title,
				'description' => (string)$response->Description,
				'price' => (string)$response->Price,
				'order_frequency' => (string)$response->OrderFrequency,
				'product_listings' => (string)$response->ProductListings,
				'listings_remain' => (string)$response->ListingsRemain,
				'listings_reserved' => (string)$response->ListingsReserved,
				'bulk_listing' => (string)$response->BulkListing,
			);
		}

		$this->data['user_plan'] = $plan;
		$this->data['link_change_plan'] = $this->openbay->amazon->getServer() . 'account/changePlan/?token=' . $this->config->get('openbay_amazon_token');
		$this->data['link_change_seller'] = $this->openbay->amazon->getServer() . 'account/changeSellerId/?token=' . $this->config->get('openbay_amazon_token');
		$this->data['link_register'] = 'https://account.openbaypro.com/amazon/apiRegister/';

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon_subscription.tpl', $this->data));
	}

	public function settings() {
		$this->data = $this->load->language('openbay/amazon_settings');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->load->model('openbay/amazon');
		$this->load->model('sale/customer_group');

		$settings = $this->model_setting_setting->getSetting('openbay_amazon');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->post['openbay_amazon_orders_marketplace_ids'])) {
				$this->request->post['openbay_amazon_orders_marketplace_ids'] = array();
			}

			$settings = array_merge($settings, $this->request->post);
			$this->model_setting_setting->editSetting('openbay_amazon', $settings);

			$this->config->set('openbay_amazon_token', $this->request->post['openbay_amazon_token']);
			$this->config->set('openbay_amazon_enc_string1', $this->request->post['openbay_amazon_enc_string1']);
			$this->config->set('openbay_amazon_enc_string2', $this->request->post['openbay_amazon_enc_string2']);

			$this->model_openbay_amazon->scheduleOrders($settings);

			$this->session->data['success'] = $this->data['text_settings_updated'];
			$this->response->redirect($this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'));
			return;
		}

		$this->data['cancel'] = $this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_amazon'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/amazon/settings', 'token=' . $this->session->data['token'], 'SSL'),
							
						));

		$this->data['marketplace_ids']	= (isset($settings['openbay_amazon_orders_marketplace_ids'])) ? (array)$settings['openbay_amazon_orders_marketplace_ids'] : array();
		$this->data['default_listing_marketplace_ids']  = (isset($settings['openbay_amazon_default_listing_marketplace_ids'])) ? (array)$settings['openbay_amazon_default_listing_marketplace_ids'] : array();

		$this->data['marketplaces'] = array(
			array('name' => $this->data['text_de'], 'id' => 'A1PA6795UKMFR9', 'code' => 'de'),
			array('name' => $this->data['text_fr'], 'id' => 'A13V1IB3VIYZZH', 'code' => 'fr'),
			array('name' => $this->data['text_it'], 'id' => 'APJ6JRA9NG5V4', 'code' => 'it'),
			array('name' => $this->data['text_es'], 'id' => 'A1RKKUPIHCS9HS', 'code' => 'es'),
			array('name' => $this->data['text_uk'], 'id' => 'A1F83G8C2ARO7P', 'code' => 'uk'),
		);

		$this->data['conditions'] = array(
			'New' => $this->data['text_new'],
			'UsedLikeNew' => $this->data['text_used_like_new'],
			'UsedVeryGood' => $this->data['text_used_very_good'],
			'UsedGood' => $this->data['text_used_good'],
			'UsedAcceptable' => $this->data['text_used_acceptable'],
			'CollectibleLikeNew' => $this->data['text_collectible_like_new'],
			'CollectibleVeryGood' => $this->data['text_collectible_very_good'],
			'CollectibleGood' => $this->data['text_collectible_good'],
			'CollectibleAcceptable' => $this->data['text_collectible_acceptable'],
			'Refurbished' => $this->data['text_refurbished'],
		);

		$this->data['openbay_amazon_status'] = isset($settings['openbay_amazon_status']) ? $settings['openbay_amazon_status'] : '';
		$this->data['openbay_amazon_token'] = isset($settings['openbay_amazon_token']) ? $settings['openbay_amazon_token'] : '';
		$this->data['openbay_amazon_enc_string1'] = isset($settings['openbay_amazon_enc_string1']) ? $settings['openbay_amazon_enc_string1'] : '';
		$this->data['openbay_amazon_enc_string2'] = isset($settings['openbay_amazon_enc_string2']) ? $settings['openbay_amazon_enc_string2'] : '';
		$this->data['openbay_amazon_listing_tax_added'] = isset($settings['openbay_amazon_listing_tax_added']) ? $settings['openbay_amazon_listing_tax_added'] : '0.00';
		$this->data['openbay_amazon_order_tax'] = isset($settings['openbay_amazon_order_tax']) ? $settings['openbay_amazon_order_tax'] : '00';
		$this->data['openbay_amazon_default_listing_marketplace'] = isset($settings['openbay_amazon_default_listing_marketplace']) ? $settings['openbay_amazon_default_listing_marketplace'] : '';
		$this->data['openbay_amazon_listing_default_condition'] = isset($settings['openbay_amazon_listing_default_condition']) ? $settings['openbay_amazon_listing_default_condition'] : '';

		$this->data['carriers'] = $this->openbay->amazon->getCarriers();
		
		$this->data['openbay_amazon_default_carrier'] = isset($settings['openbay_amazon_default_carrier']) ? $settings['openbay_amazon_default_carrier'] : '';

		$unshipped_status_id = isset($settings['openbay_amazon_order_status_unshipped']) ? $settings['openbay_amazon_order_status_unshipped'] : '';
		
		$partially_shipped_status_id = isset($settings['openbay_amazon_order_status_partially_shipped']) ? $settings['openbay_amazon_order_status_partially_shipped'] : '';
		
		$shipped_status_id = isset($settings['openbay_amazon_order_status_shipped']) ? $settings['openbay_amazon_order_status_shipped'] : '';
		
		$canceled_status_id = isset($settings['openbay_amazon_order_status_canceled']) ? $settings['openbay_amazon_order_status_canceled'] : '';

		$amazon_order_statuses = array(
			'unshipped' => array('name' => $this->data['text_unshipped'], 'order_status_id' => $unshipped_status_id),
			'partially_shipped' => array('name' => $this->data['text_partially_shipped'], 'order_status_id' => $partially_shipped_status_id),
			'shipped' => array('name' => $this->data['text_shipped'], 'order_status_id' => $shipped_status_id),
			'canceled' => array('name' => $this->data['text_canceled'], 'order_status_id' => $canceled_status_id),
		);

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
		$this->data['openbay_amazon_order_customer_group'] = isset($settings['openbay_amazon_order_customer_group']) ? $settings['openbay_amazon_order_customer_group'] : '';

		$this->data['amazon_order_statuses'] = $amazon_order_statuses;
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['subscription_url'] = $this->url->link('openbay/amazon/subscription', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['itemLinks_url'] = $this->url->link('openbay/amazon_product/linkItems', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['openbay_amazon_notify_admin'] = isset($settings['openbay_amazon_notify_admin']) ? $settings['openbay_amazon_notify_admin'] : '';

		$ping_info = simplexml_load_string($this->openbay->amazon->call('ping/info'));

		$api_status = false;
		$api_auth = false;
		if ($ping_info) {
			$api_status = ((string)$ping_info->Api_status == 'ok') ? true : false;
			$api_auth = ((string)$ping_info->Auth == 'true') ? true : false;
		}

		$this->data['API_status'] = $api_status;
		$this->data['API_auth'] = $api_auth;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon_settings.tpl', $this->data));
	}

	public function itemLinks() {
		$this->data = $this->load->language('openbay/amazon_links');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_amazon'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/amazon/itemlinks', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['token'] = $this->session->data['token'];

		$this->data['cancel'] = $this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['add_item_link_ajax'] = $this->url->link('openbay/amazon/addItemLinkAjax', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['remove_item_link_ajax'] = $this->url->link('openbay/amazon/removeItemLinkAjax', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['get_item_links_ajax'] = $this->url->link('openbay/amazon/getItemLinksAjax', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['get_unlinked_items_ajax'] = $this->url->link('openbay/amazon/getUnlinkedItemsAjax', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['get_openstock_options_ajax'] = $this->url->link('openbay/amazon/getOpenstockOptionsAjax', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon_item_links.tpl', $this->data));
	}

	public function savedListings() {
		$this->data = $this->load->language('openbay/amazon_listingsaved');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['link_overview'] = $this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_amazon'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/amazon/savedListings', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['token'] = $this->session->data['token'];
		$this->load->model('openbay/amazon');
		$saved_products = $this->model_openbay_amazon->getSavedProducts();

		$this->data['saved_products'] = array();

		foreach($saved_products as $saved_product) {
			$this->data['saved_products'][] = array(
				'product_id' => $saved_product['product_id'],
				'product_name' => $saved_product['product_name'],
				'product_model' => $saved_product['product_model'],
				'product_sku' => $saved_product['product_sku'],
				'amazon_sku' => $saved_product['amazon_sku'],
				'var' => $saved_product['var'],
				'edit_link' => $this->url->link('openbay/amazon_product', 'token=' . $this->session->data['token'] . '&product_id=' . $saved_product['product_id'] . '&var=' . $saved_product['var'], 'SSL'),
			);
		}

		$this->data['deleteSavedAjax'] = $this->url->link('openbay/amazon/deleteSavedAjax', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['uploadSavedAjax'] = $this->url->link('openbay/amazon_product/uploadSavedAjax', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon_saved_listings.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'openbay/amazon')) {
			$this->error = $this->data['error_permission'];
		}

		if (empty($this->error)) {
			return true;
		}

		return false;
	}

	public function getOpenstockOptionsAjax() {
		$json = array();
		if ($this->openbay->addonLoad('openstock') && isset($this->request->get['product_id'])) {
			$this->load->model('openstock/openstock');
			$this->load->model('tool/image');
			$json = $this->model_openstock_openstock->getProductOptionStocks($this->request->get['product_id']);
		}
		if (empty($json)) {
			$json = false;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addItemLinkAjax() {
		if (isset($this->request->get['product_id']) && isset($this->request->get['amazon_sku'])) {
			$this->load->model('openbay/amazon');

			$amazon_sku = $this->request->get['amazon_sku'];
			$product_id = $this->request->get['product_id'];
			$var = isset($this->request->get['var']) ? $this->request->get['var'] : '';

			$this->model_openbay_amazon->linkProduct($amazon_sku, $product_id, $var);
			$logger = new Log('amazon_stocks.log');
			$logger->write('addItemLink() called for product id: ' . $product_id . ', amazon sku: ' . $amazon_sku . ', var: ' . $var);

			if ($var != '' && $this->openbay->addonLoad('openstock')) {
				$logger->write('Using openStock');
				$this->load->model('tool/image');
				$this->load->model('openstock/openstock');
				$option_stocks = $this->model_openstock_openstock->getProductOptionStocks($product_id);
				$quantity_data = array();
				foreach($option_stocks as $option_stock) {
					if (isset($option_stock['var']) && $option_stock['var'] == $var) {
						$quantity_data[$amazon_sku] = $option_stock['stock'];
						break;
					}
				}
				if (!empty($quantity_data)) {
					$logger->write('Updating quantities with data: ' . print_r($quantity_data, true));
					$this->openbay->amazon->updateQuantities($quantity_data);
				} else {
					$logger->write('No quantity data will be posted . ');
				}
			} else {
				$this->openbay->amazon->putStockUpdateBulk(array($product_id));
			}

			$json = json_encode('ok');
		} else {
			$json = json_encode('error');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function removeItemLinkAjax() {
		if (isset($this->request->get['amazon_sku'])) {
			$this->load->model('openbay/amazon');

			$amazon_sku = $this->request->get['amazon_sku'];

			$this->model_openbay_amazon->removeProductLink($amazon_sku);

			$json = json_encode('ok');
		} else {
			$json = json_encode('error');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function getItemLinksAjax() {
		$this->load->model('openbay/amazon');
		$this->load->model('catalog/product');

		$json = json_encode($this->model_openbay_amazon->getProductLinks());

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function getUnlinkedItemsAjax() {
		$this->load->model('openbay/amazon');
		$this->load->model('catalog/product');

		$json = json_encode($this->model_openbay_amazon->getUnlinkedProducts());

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput($json);
	}

	public function deleteSavedAjax() {
		if (!isset($this->request->get['product_id']) || !isset($this->request->get['var'])) {
			return;
		}

		$this->load->model('openbay/amazon');
		$this->model_openbay_amazon->deleteSaved($this->request->get['product_id'], $this->request->get['var']);
	}

	public function doBulkList() {
		$this->data = $this->load->language('amazon/listing');

		if (empty($this->request->post['products'])) {
			$json = array(
				'message' => $this->data['error_not_searched'],
			);
		} else {
			$this->load->model('openbay/amazon_listing');

			$delete_search_results = array();

			$bulk_list_products = array();

			foreach ($this->request->post['products'] as $product_id => $asin) {
				$delete_search_results[] = $product_id;

				if (!empty($asin) && in_array($product_id, $this->request->post['product_ids'])) {
					$bulk_list_products[$product_id] = $asin;
				}
			}

			$status = false;

			if ($bulk_list_products) {
				$this->data = array();

				$this->data['products'] = $bulk_list_products;
				$this->data['marketplace'] = $this->request->post['marketplace'];

				if (!empty($this->request->post['start_selling'])) {
					$this->data['start_selling'] = $this->request->post['start_selling'];
				}

				if (!empty($this->request->post['condition']) && !empty($this->request->post['condition_note'])) {
					$this->data['condition'] = $this->request->post['condition'];
					$this->data['condition_note'] = $this->request->post['condition_note'];
				}

				$status = $this->model_openbay_amazon_listing->doBulkListing($this->data);

				if ($status) {
					$message = $this->data['text_products_sent'];

					if ($delete_search_results) {
						$this->model_openbay_amazon_listing->deleteSearchResults($this->request->post['marketplace'], $delete_search_results);
					}
				} else {
					$message = $this->data['error_sending_products'];
				}
			} else {
				$message = $this->data['error_no_products_selected'];
			}

			$json = array(
				'status' => $status,
				'message' => $message,
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function doBulkSearch() {
		$this->load->model('catalog/product');
		$this->load->model('openbay/amazon_listing');
		$this->data = $this->load->language('openbay/amazon_bulk_listing');

		$json = array();
		$search_data = array();

		if (!empty($this->request->post['product_ids'])) {
			foreach ($this->request->post['product_ids'] as $product_id) {
				$product = $this->model_catalog_product->getProduct($product_id);

				if (empty($product['sku'])) {
					$json[$product_id] = array(
						'error' => $this->data['error_product_sku']
					);
				}

				$key = '';

				$id_types = array('isbn', 'upc', 'ean', 'jan', 'sku');

				foreach ($id_types as $id_type) {
					if (!empty($product[$id_type])) {
						$key = $id_type;
						break;
					}
				}

				if (!$key) {
					$json[$product_id] = array(
						'error' => $this->data['error_searchable_fields']
					);
				}

				if (!isset($json[$product_id])) {
					$search_data[$key][] = array(
						'product_id' => $product['product_id'],
						'value' => trim($product[$id_type]),
						'marketplace' => $this->request->post['marketplace'],
					);

					$json[$product_id] = array(
						'success' => $this->data['text_searching']
					);
				}
			}
		}

		if ($search_data) {
			$this->model_openbay_amazon_listing->doBulkSearch($search_data);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function bulkListProducts() {
		$this->load->model('openbay/amazon');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$this->data = $this->load->language('openbay/amazon_bulk_listing');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_amazon'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/amazon/bulkListProducts', 'token=' . $this->session->data['token'], 'SSL'),
							
						));

		$ping_info = simplexml_load_string($this->openbay->amazon->call('ping/info'));

		$bulk_listing_status = false;
		if ($ping_info) {
			$bulk_listing_status = ((string)$ping_info->BulkListing == 'true') ? true : false;
		}

		if (!empty($this->request->get['filter_marketplace'])) {
			$filter_marketplace = $this->request->get['filter_marketplace'];
		} else {
			$filter_marketplace = $this->config->get('openbay_amazon_default_listing_marketplace');
		}

		$this->data['filter_marketplace'] = $filter_marketplace;

		$this->data['bulk_listing_status'] = $bulk_listing_status;

		$this->data['link_overview'] = $this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];

		if ($bulk_listing_status) {
			$this->data['link_search'] = $this->url->link('openbay/amazon/doBulkSearch', 'token=' . $this->session->data['token'], 'SSL');

			$this->data['default_condition'] = $this->config->get('openbay_amazon_listing_default_condition');
			$this->data['conditions'] = array(
				'New' => $this->data['text_new'],
				'UsedLikeNew' => $this->data['text_used_like_new'],
				'UsedVeryGood' => $this->data['text_used_very_good'],
				'UsedGood' => $this->data['text_used_good'],
				'UsedAcceptable' => $this->data['text_used_acceptable'],
				'CollectibleLikeNew' => $this->data['text_collectible_like_new'],
				'CollectibleVeryGood' => $this->data['text_collectible_very_good'],
				'CollectibleGood' => $this->data['text_collectible_good'],
				'CollectibleAcceptable' => $this->data['text_collectible_acceptable'],
				'Refurbished' => $this->data['text_refurbished'],
			);

			$this->data['marketplaces'] = array(
				array('name' => $this->data['text_de'], 'code' => 'de'),
				array('name' => $this->data['text_fr'], 'code' => 'fr'),
				array('name' => $this->data['text_it'], 'code' => 'it'),
				array('name' => $this->data['text_es'], 'code' => 'es'),
				array('name' => $this->data['text_uk'], 'code' => 'uk'),
			);

			$page = (!empty($this->request->get['page'])) ? $this->request->get['page']: 1;
			
			$filter = array();

			$filter['filter_marketplace'] = $filter_marketplace;
			$filter['start'] = ($page - 1) * $this->config->get('config_limit_admin');
			$filter['limit'] = $this->config->get('config_limit_admin');

			$results = $this->model_openbay_amazon->getProductSearch($filter);
			$product_total = $this->model_openbay_amazon->getProductSearchTotal($filter);

			$this->data['products'] = array();

			foreach ($results as $result) {
				$product = $this->model_catalog_product->getProduct($result['product_id']);

				if ($product['image'] && file_exists(DIR_IMAGE . $product['image'])) {
					$image = $this->model_tool_image->resize($product['image'], 40, 40);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 40, 40);
				}

				if ($result['status'] == 'searching') {
					$search_status = $this->data['text_searching'];
				} else if ($result['status'] == 'finished') {
					$search_status = $this->data['text_finished'];
				} else {
					$search_status = '-';
				}

				$href = $this->url->link('catalog/product/save', 'token=' . $this->session->data['token'] . '&product_id=' . $product['product_id'], 'SSL');

				$search_results = array();

				if ($result['data']) {
					foreach ($result['data'] as $search_result) {
						$link = $this->model_openbay_amazon->getAsinLink($search_result['asin'], $result['marketplace']);

						$search_results[] = array(
							'title' => $search_result['title'],
							'asin' => $search_result['asin'],
							'href' => $link,
						);
					}
				}

				$this->data['products'][] = array(
					'product_id' => $product['product_id'],
					'href' => $href,
					'name' => $product['name'],
					'model' => $product['model'],
					'image' => $image,
					'matches' => $result['matches'],
					'search_status' => $search_status,
					'search_results' => $search_results,
				);
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_limit_admin');
			$pagination->text = $this->data['text_pagination'];
			$pagination->url = $this->url->link('openbay/amazon/bulkListProducts', 'token=' . $this->session->data['token'] . '&page={page}&filter_marketplace=' . $filter_marketplace, 'SSL');

			$this->data['pagination'] = $pagination->render();
			$this->data['results'] = sprintf($this->data['text_pagination'], ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon_bulk_listing.tpl', $this->data));
	}

	public function bulkLinking() {
		$this->load->model('openbay/amazon');

		$this->data = $this->load->language('openbay/amazon_bulk_linking');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_amazon'],
							$this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'], 'SSL'),
						));


		$ping_info = simplexml_load_string($this->openbay->amazon->call('ping/info'));

		$bulk_linking_status = false;
		if ($ping_info) {
			$bulk_linking_status = ((string)$ping_info->BulkLinking == 'true') ? true : false;
		}

		$this->data['bulk_linking_status'] = $bulk_linking_status;

		$marketplace_code = (isset($this->request->get['marketplace']))? $this->request->get['marketplace']: 'uk';
	
		$total_linked = $this->model_openbay_amazon->getTotalUnlinkedItemsFromReport($marketplace_code);

		$linked_item_page = (isset($this->request->get['linked_item_page']))? (int)$this->request->get['linked_item_page']: 1;
		
		$linked_item_limit = (isset($this->request->get['linked_item_limit']))? (int)$this->request->get['linked_item_limit']: 25;
		
		$marketplaces = array(
			'uk' => array(
				'name' => $this->data['text_uk'],
				'code' => 'uk',
				'href_load_listings' => $this->url->link('openbay/amazon/loadListingReport', 'token=' . $this->session->data['token'] . '&marketplace=uk', 'SSL'),
				'link' => $this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'] . '&marketplace=uk', 'SSL'),
			),
			'de' => array(
				'name' => $this->data['text_de'],
				'code' => 'de',
				'href_load_listings' => $this->url->link('openbay/amazon/loadListingReport', 'token=' . $this->session->data['token'] . '&marketplace=de', 'SSL'),
				'link' => $this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'] . '&marketplace=de', 'SSL'),
			),
			'fr' => array(
				'name' => $this->data['text_fr'],
				'code' => 'fr',
				'href_load_listings' => $this->url->link('openbay/amazon/loadListingReport', 'token=' . $this->session->data['token'] . '&marketplace=fr', 'SSL'),
				'link' => $this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'] . '&marketplace=fr', 'SSL'),
			),
			'it' => array(
				'name' => $this->data['text_it'],
				'code' => 'it',
				'href_load_listings' => $this->url->link('openbay/amazon/loadListingReport', 'token=' . $this->session->data['token'] . '&marketplace=it', 'SSL'),
				'link' => $this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'] . '&marketplace=it', 'SSL'),
			),
			'es' => array(
				'name' => $this->data['text_es'],
				'code' => 'es',
				'href_load_listings' => $this->url->link('openbay/amazon/loadListingReport', 'token=' . $this->session->data['token'] . '&marketplace=es', 'SSL'),
				'link' => $this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'] . '&marketplace=es', 'SSL'),
			),
		);

		$pagination = new Pagination();
		$pagination->total = $total_linked;
		$pagination->page = $linked_item_page;
		$pagination->limit = $linked_item_limit;
		$pagination->text = $this->data['text_pagination'];
		$pagination->url = $this->url->link('openbay/amazon/bulklinking', 'token=' . $this->session->data['token'] . '&linked_item_page={page}&marketplace=' . $marketplace_code, 'SSL');

		$this->data['pagination'] = $pagination->render();

		$results = $this->model_openbay_amazon->getUnlinkedItemsFromReport($marketplace_code, $linked_item_limit, $linked_item_page);

		$products = array();

		foreach ($results as $result) {
			$products[] = array(
				'asin' => $result['asin'],
				'href_amazon' => $this->model_openbay_amazon->getAsinLink($result['asin'], $marketplace_code),
				'amazon_sku' => $result['amazon_sku'],
				'amazon_quantity' => $result['amazon_quantity'],
				'amazon_price' => $result['amazon_price'],
				'name' => $result['name'],
				'sku' => $result['sku'],
				'quantity' => $result['quantity'],
				'combination' => $result['combination'],
				'product_id' => $result['product_id'],
				'var' => $result['var'],
				'href_product' => $this->url->link('catalog/product/save', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'], 'SSL'),
			);
		}

		$this->data['unlinked_products'] = $products;

		$this->data['marketplaces'] = $marketplaces;
		$this->data['marketplace_code'] = $marketplace_code;

		$this->data['marketplaces_processing'] = array();
		if (is_array($this->config->get('openbay_amazon_processing_listing_reports'))) {
			$this->data['marketplaces_processing'] = $this->config->get('openbay_amazon_processing_listing_reports');
		}

		$this->data['href_return'] = $this->url->link('openbay/amazon', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['href_do_bulk_linking'] = $this->url->link('openbay/amazon/dobulklinking', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazon_bulk_linking.tpl', $this->data));
	}

	public function loadListingReport() {
		$this->load->model('openbay/amazon');
		$this->load->model('setting/setting');
		$this->data = $this->load->language('openbay/amazon_bulk_linking');

		$marketplace = $this->request->get['marketplace'];

		$this->model_openbay_amazon->deleteListingReports($marketplace);

		$request_data = array(
			'marketplace' => $marketplace,
			'response_url' => HTTPS_CATALOG . 'index.php?route=openbay/amazon/listingreport',
		);

		$response = $this->openbay->amazon->call('report/listing', $request_data);
		$response = json_decode($response, 1);

		$json = array();
		$json['status'] = $response['status'];

		if ($json['status']) {
			$json['message'] = $this->data['text_report_requested'];

			$settings = $this->model_setting_setting->getSetting('openbay_amazon');
			$settings['openbay_amazon_processing_listing_reports'][] = $marketplace;

			$this->model_setting_setting->editSetting('openbay_amazon', $settings);
		} else {
			$json['message'] = $this->data['text_report_request_failed'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function doBulkLinking() {
		$this->load->model('openbay/amazon');

		$links = array();
		$amazon_skus = array();

		if (!empty($this->request->post['link'])) {
			foreach ($this->request->post['link'] as $link) {
				if (!empty($link['product_id'])) {
					$links[] = $link;
					$amazon_skus[] = $link['amazon_sku'];
				}
			}
		}

		if (!empty($links)) {
			foreach ($links as $link) {
				$this->model_openbay_amazon->linkProduct($link['amazon_sku'], $link['product_id'], $link['var']);
			}

			//$this->model_openbay_amazon->updateAmazonSkusQuantities($amazon_skus);
		}
	}
}