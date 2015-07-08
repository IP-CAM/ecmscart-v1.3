<?php
class ControllerSettingStore extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('setting/store');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/store');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('setting/store');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/store');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if($this->request->get['store_id']){
				$this->model_setting_store->editStore($this->request->get['store_id'], $this->request->post);
				
				$store_id = $this->request->get['store_id'];
			}else {
				$store_id = $this->model_setting_store->addStore($this->request->post);
			}
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('config', $this->request->post, $store_id);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('setting/store');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/store');

		$this->load->model('setting/setting');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $store_id) {
				$this->model_setting_store->deleteStore($store_id);

				$this->model_setting_setting->deleteSetting('config', $store_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('setting/store/save', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['delete'] = $this->url->link('setting/store/delete', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['stores'] = array();

		$this->data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name') . $this->data['text_default'],
			'url'      => HTTP_CATALOG,
			'save'     => $this->url->link('setting/setting', 'token=' . $this->session->data['token'], 'SSL')
		);

		$store_total = $this->model_setting_store->getTotalStores();

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$this->data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name'],
				'url'      => $result['url'],
				'save'     => $this->url->link('setting/store/save', 'token=' . $this->session->data['token'] . '&store_id=' . $result['store_id'], 'SSL')
			);
		}

		$this->data['error_warning'] =  isset($this->error['warning'])? $this->error['warning']: '';

		$this->data['success'] =  isset($this->session->data['success'])? $this->session->data['success']: '';
		
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);

		$this->data['selected'] =  $this->request->post('selected', array());

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('setting/store_list.tpl', $this->data));
	}

	public function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['store_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  isset($this->error['warning'])? $this->error['warning']: '';
		
		$this->data['error_url'] =  isset($this->error['url'])? $this->error['url']: '';
		
		$this->data['error_name'] =  isset($this->error['name'])? $this->error['name']: '';
		
		$this->data['error_owner'] =  isset($this->error['owner'])? $this->error['owner']: '';
		
		$this->data['error_address'] =  isset($this->error['address'])? $this->error['address']: '';
		
		$this->data['error_email'] =  isset($this->error['email'])? $this->error['email']: '';
		
		$this->data['error_telephone'] =  isset($this->error['telephone'])? $this->error['telephone']: '';
		
		$this->data['error_meta_title'] =  isset($this->error['meta_title'])? $this->error['meta_title']: '';
		
		$this->data['error_customer_group_display'] =  isset($this->error['customer_group_display'])? $this->error['customer_group_display']: '';

		$this->data['error_image_category'] =  isset($this->error['image_category'])? $this->error['image_category']: '';
		
		$this->data['error_image_thumb'] =  isset($this->error['image_thumb'])? $this->error['image_thumb']: '';
		
		$this->data['error_image_family'] =  isset($this->error['image_family'])? $this->error['image_family']: '';
		
		$this->data['error_thumb_family'] =  isset($this->error['thumb_family'])? $this->error['thumb_family']: '';
		
		$this->data['error_image_blog'] =  isset($this->error['image_blog'])? $this->error['image_blog']: '';
		
		$this->data['error_thumb_blog'] =  isset($this->error['thumb_blog'])? $this->error['thumb_blog']: '';
		
		$this->data['error_image_author'] =  isset($this->error['image_author'])? $this->error['image_author']: '';
		
		$this->data['error_image_popup'] =  isset($this->error['image_popup'])? $this->error['image_popup']: '';
		
		$this->data['error_image_product'] =  isset($this->error['image_product'])? $this->error['image_product']: '';
		
		$this->data['error_image_additional'] =  isset($this->error['image_additional'])? $this->error['image_additional']: '';
		
		$this->data['error_image_related'] =  isset($this->error['image_related'])? $this->error['image_related']: '';
		
		$this->data['error_image_compare'] =  isset($this->error['image_compare'])? $this->error['image_compare']: '';
		
		$this->data['error_image_wishlist'] =  isset($this->error['image_wishlist'])? $this->error['image_wishlist']: '';		

		$this->data['error_image_compare'] =  isset($this->error['image_compare'])? $this->error['image_compare']: '';
	
		$this->data['error_image_cart'] =  isset($this->error['image_cart'])? $this->error['image_cart']: '';
		
		$this->data['error_image_location'] =  isset($this->error['image_location'])? $this->error['image_location']: '';

		$this->data['error_product_limit'] =  isset($this->error['product_limit'])? $this->error['product_limit']: '';

		$this->data['error_product_description_length'] =  isset($this->error['product_description_length'])? $this->error['product_description_length']: '';
		
		$store_url = '';
		
		if (!isset($this->request->get['store_id'])) {
			$store_url = $this->url->link('setting/store/save', 'token=' . $this->session->data['token'], 'SSL');
		}else {
			$store_url = $this->url->link('setting/store/save', 'token=' . $this->session->data['token'] . '&store_id=' . $this->request->get['store_id'], 'SSL');
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_settings'],
							$store_url,
						));

		$this->data['success'] =  isset($this->session->data['success'])? $this->session->data['success']: '';
		
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);


		if (!isset($this->request->get['store_id'])) {
			$this->data['action'] = $this->url->link('setting/store/save', 'token=' . $this->session->data['token'], 'SSL');
		} else {
			$this->data['action'] = $this->url->link('setting/store/save', 'token=' . $this->session->data['token'] . '&store_id=' . $this->request->get['store_id'], 'SSL');
		}

		$this->data['cancel'] = $this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->get['store_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$this->load->model('setting/setting');

			$store_info = $this->model_setting_setting->getSetting('config', $this->request->get['store_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		if (isset($store_info['config_url']) && !$this->error) {
			$this->data['config_url'] = $store_info['config_url'];
		} else {
			$this->data['config_url'] = $this->request->post('config_url', '');
		}

		if (isset($store_info['config_ssl']) && !$this->error) {
			$this->data['config_ssl'] = $store_info['config_ssl'];
		} else {
			$this->data['config_ssl'] = $this->request->post('config_ssl', '');
		}

		if (isset($store_info['config_name']) && !$this->error) {
			$this->data['config_name'] = $store_info['config_name'];
		} else {
			$this->data['config_name'] = $this->request->post('config_name', '');
		}

		if (isset($store_info['config_owner']) && !$this->error) {
			$this->data['config_owner'] = $store_info['config_owner'];
		} else {
			$this->data['config_owner'] = $this->request->post('config_owner', '');
		}

		if (isset($store_info['config_address']) && !$this->error) {
			$this->data['config_address'] = $store_info['config_address'];
		} else {
			$this->data['config_address'] = $this->request->post('config_address', '');
		}

		if (isset($store_info['config_geocode']) && !$this->error) {
			$this->data['config_geocode'] = $store_info['config_geocode'];
		} else {
			$this->data['config_geocode'] = $this->request->post('config_geocode', '');
		}

		if (isset($store_info['config_email']) && !$this->error) {
			$this->data['config_email'] = $store_info['config_email'];
		} else {
			$this->data['config_email'] = $this->request->post('config_email', '');
		}

		if (isset($store_info['config_telephone']) && !$this->error) {
			$this->data['config_telephone'] = $store_info['config_telephone'];
		} else {
			$this->data['config_telephone'] = $this->request->post('config_telephone', '');
		}

		if (isset($store_info['config_fax']) && !$this->error) {
			$this->data['config_fax'] = $store_info['config_fax'];
		} else {
			$this->data['config_fax'] = $this->request->post('config_fax', '');
		}

		if (isset($store_info['config_image']) && !$this->error) {
			$this->data['config_image'] = $store_info['config_image'];
		} else {
			$this->data['config_image'] = $this->request->post('config_image', '');
		}

		$this->load->model('tool/image');

		if (isset($store_info['config_image']) && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($store_info['config_image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('config_image', ''), 100, 100);
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($store_info['config_open'])) {
			$this->data['config_open'] = $store_info['config_open'];
		} else {
			$this->data['config_open'] = $this->request->post('config_open', '');
		}

		if (isset($store_info['config_comment']) && !$this->error) {
			$this->data['config_comment'] = $store_info['config_comment'];
		} else {
			$this->data['config_comment'] = $this->request->post('config_comment', '');
		}

		$this->load->model('localisation/location');

		$this->data['locations'] = $this->model_localisation_location->getLocations();

		if ($this->config->get('config_location') && !$this->error) {
			$this->data['config_location'] = $this->config->get('config_location');
		} else {
			$this->data['config_location'] = $this->request->post('config_location', array());
		}

		if (isset($store_info['config_meta_title']) && !$this->error) {
			$this->data['config_meta_title'] = $store_info['config_meta_title'];
		} else {
			$this->data['config_meta_title'] = $this->request->post('config_meta_title', '');
		}

		if (isset($store_info['config_meta_description']) && !$this->error) {
			$this->data['config_meta_description'] = $store_info['config_meta_description'];
		} else {
			$this->data['config_meta_description'] = $this->request->post('config_meta_description', '');
		}

		if (isset($store_info['config_meta_keyword'])&& !$this->error) {
			$this->data['config_meta_keyword'] = $store_info['config_meta_keyword'];
		} else {
			$this->data['config_meta_keyword'] = $this->request->post('config_meta_keyword', '');
		}

		if (isset($store_info['config_layout_id']) && !$this->error) {
			$this->data['config_layout_id'] = $store_info['config_layout_id'];
		} else {
			$this->data['config_layout_id'] = $this->request->post('config_layout_id', '');
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		if (isset($store_info['config_template']) && !$this->error) {
			$this->data['config_template'] = $store_info['config_template'];
		} else {
			$this->data['config_template'] = $this->request->post('config_template', '');
		}

		$this->data['templates'] = array();

		$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);

		foreach ($directories as $directory) {
			$this->data['templates'][] = basename($directory);
		}

		if (isset($store_info['config_country_id']) && !$this->error) {
			$this->data['config_country_id'] = $store_info['config_country_id'];
		} else {
			$this->data['config_country_id'] =  $this->request->post('config_country_id', $this->config->get('config_country_id'));
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		if (isset($store_info['config_zone_id']) && !$this->error) {
			$this->data['config_zone_id'] = $store_info['config_zone_id'];
		} else {
			$this->data['config_zone_id'] = $this->request->post('config_zone_id', $this->config->get('config_zone_id'));
		}

		if (isset($store_info['config_language']) && !$this->error) {
			$this->data['config_language'] = $store_info['config_language'];
		} else {
			$this->data['config_language'] = $this->request->post('config_language', $this->config->get('config_language'));
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($store_info['config_currency']) && !$this->error) {
			$this->data['config_currency'] = $store_info['config_currency'];
		} else {
			$this->data['config_currency'] = $this->request->post('config_currency', $this->config->get('config_currency'));
		}

		$this->load->model('localisation/currency');

		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies();

		if (isset($store_info['config_product_limit']) && !$this->error) {
			$this->data['config_product_limit'] = $store_info['config_product_limit'];
		} else {
			$this->data['config_product_limit'] = $this->request->post('config_product_limit', '15');
		}

		if (isset($store_info['config_product_description_length']) && !$this->error) {
			$this->data['config_product_description_length'] = $store_info['config_product_description_length'];
		} else {
			$this->data['config_product_description_length'] = $this->request->post('config_product_description_length', '100');
		}

		if (isset($store_info['config_tax']) && !$this->error) {
			$this->data['config_tax'] = $store_info['config_tax'];
		} else {
			$this->data['config_tax'] = $this->request->post('config_product_description_length', '');
		}

		if (isset($store_info['config_tax_default']) && !$this->error) {
			$this->data['config_tax_default'] = $store_info['config_tax_default'];
		} else {
			$this->data['config_tax_default'] = $this->request->post('config_tax_default', '');
		}

		if (isset($store_info['config_tax_customer']) && !$this->error) {
			$this->data['config_tax_customer'] = $store_info['config_tax_customer'];
		} else {
			$this->data['config_tax_customer'] = $this->request->post('config_tax_customer', '');
		}

		if (isset($store_info['config_customer_group_id']) && !$this->error) {
			$this->data['config_customer_group_id'] = $store_info['config_customer_group_id'];
		} else {
			$this->data['config_customer_group_id'] = $this->request->post('config_customer_group_id', '');
		}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		if (isset($store_info['config_customer_group_display']) && !$this->error) {
			$this->data['config_customer_group_display'] = $store_info['config_customer_group_display'];
		} else {
			$this->data['config_customer_group_display'] = $this->request->post('config_customer_group_display', array());
		}

		if (isset($store_info['config_customer_price']) && !$this->error) {
			$this->data['config_customer_price'] = $store_info['config_customer_price'];
		} else {
			$this->data['config_customer_price'] = $this->request->post('config_customer_price', '');
		}

		if (isset($store_info['config_account_id']) && !$this->error) {
			$this->data['config_account_id'] = $store_info['config_account_id'];
		} else {
			$this->data['config_account_id'] = $this->request->post('config_account_id', '');
		}

		$this->load->model('catalog/information');

		$this->data['informations'] = $this->model_catalog_information->getInformations();

		if (isset($store_info['config_cart_weight']) && !$this->error) {
			$this->data['config_cart_weight'] = $store_info['config_cart_weight'];
		} else {
			$this->data['config_cart_weight'] = $this->request->post('config_cart_weight', '');
		}

		if (isset($store_info['config_checkout_guest']) && !$this->error) {
			$this->data['config_checkout_guest'] = $store_info['config_checkout_guest'];
		} else {
			$this->data['config_checkout_guest'] = $this->request->post('config_checkout_guest', '');
		}

		if (isset($store_info['config_checkout_id']) && !$this->error) {
			$this->data['config_checkout_id'] = $store_info['config_checkout_id'];
		} else {
			$this->data['config_checkout_id'] = $this->request->post('config_checkout_id', '');
		}

		if (isset($store_info['config_order_status_id']) && !$this->error) {
			$this->data['config_order_status_id'] = $store_info['config_order_status_id'];
		} else {
			$this->data['config_order_status_id'] = $this->request->post('config_order_status_id', '');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($store_info['config_stock_display']) && !$this->error) {
			$this->data['config_stock_display'] = $store_info['config_stock_display'];
		} else {
			$this->data['config_stock_display'] = $this->request->post('config_stock_display', '');
		}

		if (isset($store_info['config_stock_checkout']) && !$this->error) {
			$this->data['config_stock_checkout'] = $store_info['config_stock_checkout'];
		} else {
			$this->data['config_stock_checkout'] = $this->request->post('config_stock_checkout', '');
		}

		if (isset($store_info['config_logo']) && !$this->error) {
			$this->data['config_logo'] = $store_info['config_logo'];
		} else {
			$this->data['config_logo'] = $this->request->post('config_logo', '');
		}

		if (isset($store_info['config_logo']) && !$this->error) {
			$this->data['logo'] = $this->model_tool_image->resize($store_info['config_logo'], 100, 100);
		} else {
			$this->data['logo'] = $this->model_tool_image->resize($this->request->post('config_logo', ''), 100, 100);
		}
		
		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (isset($store_info['config_icon']) && !$this->error) {
			$this->data['config_icon'] = $store_info['config_icon'];
		} else {
			$this->data['config_icon'] = $this->request->post('config_icon', '');
		}

		if (isset($store_info['config_icon']) && !$this->error) {
			$this->data['icon'] = $this->model_tool_image->resize($store_info['config_icon'], 100, 100);
		} else {
			$this->data['icon'] = $this->model_tool_image->resize($this->request->post('config_icon', ''), 100, 100);
		}
		
		if (isset($store_info['config_image_category_width']) && !$this->error) {
			$this->data['config_image_category_width'] = $store_info['config_image_category_width'];
		} else {
			$this->data['config_image_category_width'] = $this->request->post('config_image_category_width', 80);
		}
		
		if (isset($store_info['config_image_category_height']) && !$this->error) {
			$this->data['config_image_category_height'] = $store_info['config_image_category_height'];
		} else {
			$this->data['config_image_category_height'] = $this->request->post('config_image_category_height', 80);
		}
		
		if (isset($store_info['config_image_family_height']) && !$this->error) {
			$this->data['config_image_family_height'] = $store_info['config_image_family_height'];
		} else {
			$this->data['config_image_family_height'] = $this->request->post('config_image_family_height', 200);
		}
		
		if (isset($store_info['config_image_family_width']) && !$this->error) {
			$this->data['config_image_family_width'] = $store_info['config_image_family_width'];
		} else {
			$this->data['config_image_family_width'] = $this->request->post('config_image_family_width', 200);
		}
		
		if (isset($store_info['config_thumb_family_height']) && !$this->error) {
			$this->data['config_thumb_family_height'] = $store_info['config_thumb_family_height'];
		} else {
			$this->data['config_thumb_family_height'] = $this->request->post('config_thumb_family_height', 80);
		}
		
		if (isset($store_info['config_thumb_family_width']) && !$this->error) {
			$this->data['config_thumb_family_width'] = $store_info['config_thumb_family_width'];
		} else {
			$this->data['config_thumb_family_width'] = $this->request->post('config_thumb_family_width', 80);
		}
		
		if (isset($store_info['config_image_blog_width']) && !$this->error) {
			$this->data['config_image_blog_width'] = $store_info['config_image_blog_width'];
		} else {
			$this->data['config_image_blog_width'] = $this->request->post('config_image_blog_width', 200);
		}
		
		if (isset($store_info['config_image_blog_height']) && !$this->error) {
			$this->data['config_image_blog_height'] = $store_info['config_image_blog_height'];
		} else {
			$this->data['config_image_blog_height'] = $this->request->post('config_image_blog_height', 200);
		}
		
		if (isset($store_info['config_thumb_blog_width']) && !$this->error) {
			$this->data['config_thumb_blog_width'] = $store_info['config_thumb_blog_width'];
		} else {
			$this->data['config_thumb_blog_width'] = $this->request->post('config_thumb_blog_width', 80);
		}
		
		if (isset($store_info['config_thumb_blog_height']) && !$this->error) {
			$this->data['config_thumb_blog_height'] = $store_info['config_thumb_blog_height'];
		} else {
			$this->data['config_thumb_blog_height'] = $this->request->post('config_thumb_blog_height', 80);
		}
		
		if (isset($store_info['config_image_author_height']) && !$this->error) {
			$this->data['config_image_author_height'] = $store_info['config_image_author_height'];
		} else {
			$this->data['config_image_author_height'] = $this->request->post('config_image_author_height', 47);
		}
		
		if (isset($store_info['config_image_author_width']) && !$this->error) {
			$this->data['config_image_author_width'] = $store_info['config_image_author_width'];
		} else {
			$this->data['config_image_author_width'] = $this->request->post('config_image_author_width', 47);
		}
		
		if (isset($store_info['config_image_thumb_width']) && !$this->error) {
			$this->data['config_image_thumb_width'] = $store_info['config_image_thumb_width'];
		} else {
			$this->data['config_image_thumb_width'] = $this->request->post('config_image_thumb_width', 228);
		}

		if (isset($store_info['config_image_thumb_height']) && !$this->error) {
			$this->data['config_image_thumb_height'] = $store_info['config_image_thumb_height'];
		} else {
			$this->data['config_image_thumb_height'] = $this->request->post('config_image_thumb_height', 228);
		}

		if (isset($store_info['config_image_popup_width']) && !$this->error) {
			$this->data['config_image_popup_width'] = $store_info['config_image_popup_width'];
		} else {
			$this->data['config_image_popup_width'] = $this->request->post('config_image_popup_width', 500);
		}

		if (isset($store_info['config_image_popup_height']) && !$this->error) {
			$this->data['config_image_popup_height'] = $store_info['config_image_popup_height'];
		} else {
			$this->data['config_image_popup_height'] = $this->request->post('config_image_popup_height', 500);
		}

		if (isset($store_info['config_image_product_width']) && !$this->error) {
			$this->data['config_image_product_width'] = $store_info['config_image_product_width'];
		} else {
			$this->data['config_image_product_width'] = $this->request->post('config_image_product_width', 80);
		}

		if (isset($store_info['config_image_product_height']) && !$this->error) {
			$this->data['config_image_product_height'] = $store_info['config_image_product_height'];
		} else {
			$this->data['config_image_product_height'] = $this->request->post('config_image_product_height', 80);
		}

		if (isset($store_info['config_image_additional_width']) && !$this->error) {
			$this->data['config_image_additional_width'] = $store_info['config_image_additional_width'];
		} else {
			$this->data['config_image_additional_width'] = $this->request->post('config_image_additional_width', 74);
		}

		if (isset($store_info['config_image_additional_height']) && !$this->error) {
			$this->data['config_image_additional_height'] = $store_info['config_image_additional_height'];
		} else {
			$this->data['config_image_additional_height'] = $this->request->post('config_image_additional_height', 74);
		}

		if (isset($store_info['config_image_related_width']) && !$this->error) {
			$this->data['config_image_related_width'] = $store_info['config_image_related_width'];
		} else {
			$this->data['config_image_related_width'] = $this->request->post('config_image_related_width', 80);
		}

		if (isset($store_info['config_image_related_height']) && !$this->error) {
			$this->data['config_image_related_height'] = $store_info['config_image_related_height'];
		} else {
			$this->data['config_image_related_height'] = $this->request->post('config_image_related_height', 80);
		}

		if (isset($store_info['config_image_compare_width']) && !$this->error) {
			$this->data['config_image_compare_width'] = $store_info['config_image_compare_width'];
		} else {
			$this->data['config_image_compare_width'] = $this->request->post('config_image_compare_width', 90);
		}

		if (isset($store_info['config_image_compare_height']) && !$this->error) {
			$this->data['config_image_compare_height'] = $store_info['config_image_compare_height'];
		} else {
			$this->data['config_image_compare_height'] = $this->request->post('config_image_compare_height', 90);
		}

		if (isset($store_info['config_image_wishlist_width']) && !$this->error) {
			$this->data['config_image_wishlist_width'] = $store_info['config_image_wishlist_width'];
		} else {
			$this->data['config_image_wishlist_width'] = $this->request->post('config_image_wishlist_width', 50);
		}

		if (isset($store_info['config_image_wishlist_height']) && !$this->error) {
			$this->data['config_image_wishlist_height'] = $store_info['config_image_wishlist_height'];
		} else {
			$this->data['config_image_wishlist_height'] = $this->request->post('config_image_wishlist_height', 50);
		}

		if (isset($store_info['config_image_cart_width']) && !$this->error) {
			$this->data['config_image_cart_width'] = $store_info['config_image_cart_width'];
		} else {
			$this->data['config_image_cart_width'] = $this->request->post('config_image_cart_width', 80);
		}

		if (isset($store_info['config_image_cart_height']) && !$this->error) {
			$this->data['config_image_cart_height'] = $store_info['config_image_cart_height'];
		} else {
			$this->data['config_image_cart_height'] = $this->request->post('config_image_cart_height', 80);
		}

		if (isset($store_info['config_image_location_width']) && !$this->error) {
			$this->data['config_image_location_width'] = $store_info['config_image_location_width'];
		} else {
			$this->data['config_image_location_width'] = $this->request->post('config_image_location_width', 240);
		}

		if (isset($store_info['config_image_location_height']) && !$this->error) {
			$this->data['config_image_location_height'] = $store_info['config_image_location_height'];
		} else {
			$this->data['config_image_location_height'] = $this->request->post('config_image_location_width', 180);
		}

		if (isset($store_info['config_secure']) && !$this->error) {
			$this->data['config_secure'] = $store_info['config_secure'];
		} else {
			$this->data['config_secure'] = $this->request->post('config_secure', '');
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('setting/store_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'setting/store')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['config_url']) {
			$this->error['url'] = $this->data['error_url'];
		}

		if (!$this->request->post['config_name']) {
			$this->error['name'] = $this->data['error_name'];
		}

		if ((utf8_strlen($this->request->post['config_owner']) < 3) || (utf8_strlen($this->request->post['config_owner']) > 64)) {
			$this->error['owner'] = $this->data['error_owner'];
		}

		if ((utf8_strlen($this->request->post['config_address']) < 3) || (utf8_strlen($this->request->post['config_address']) > 256)) {
			$this->error['address'] = $this->data['error_address'];
		}

		if ((utf8_strlen($this->request->post['config_email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['config_email'])) {
			$this->error['email'] = $this->data['error_email'];
		}

		if ((utf8_strlen($this->request->post['config_telephone']) < 3) || (utf8_strlen($this->request->post['config_telephone']) > 32)) {
			$this->error['telephone'] = $this->data['error_telephone'];
		}

		if (!$this->request->post['config_meta_title']) {
			$this->error['meta_title'] = $this->data['error_meta_title'];
		}

		if (!empty($this->request->post['config_customer_group_display']) && !in_array($this->request->post['config_customer_group_id'], $this->request->post['config_customer_group_display'])) {
			$this->error['customer_group_display'] = $this->data['error_customer_group_display'];
		}

		if (!$this->request->post['config_image_category_width'] || !$this->request->post['config_image_category_height']) {
			$this->error['image_category'] = $this->data['error_image_category'];
		}

		if (!$this->request->post['config_image_thumb_width'] || !$this->request->post['config_image_thumb_height']) {
			$this->error['image_thumb'] = $this->data['error_image_thumb'];
		}
		
		if (!$this->request->post['config_image_family_width'] || !$this->request->post['config_image_family_height']) {
			$this->error['image_family'] = $this->data['error_image_family'];
		}
		
		if (!$this->request->post['config_thumb_family_width'] || !$this->request->post['config_thumb_family_height']) {
			$this->error['thumb_family'] = $this->data['error_thumb_family'];
		}
		
		if (!$this->request->post['config_image_blog_width'] || !$this->request->post['config_image_blog_height']) {
			$this->error['image_blog'] = $this->data['error_image_blog'];
		}
		
		if (!$this->request->post['config_thumb_blog_width'] || !$this->request->post['config_thumb_blog_height']) {
			$this->error['thumb_blog'] = $this->data['error_thumb_blog'];
		}
		
		if (!$this->request->post['config_image_author_width'] || !$this->request->post['config_image_author_height']) {
			$this->error['image_author'] = $this->data['error_image_author'];
		}
		
		if (!$this->request->post['config_image_popup_width'] || !$this->request->post['config_image_popup_height']) {
			$this->error['image_popup'] = $this->data['error_image_popup'];
		}

		if (!$this->request->post['config_image_product_width'] || !$this->request->post['config_image_product_height']) {
			$this->error['image_product'] = $this->data['error_image_product'];
		}

		if (!$this->request->post['config_image_additional_width'] || !$this->request->post['config_image_additional_height']) {
			$this->error['image_additional'] = $this->data['error_image_additional'];
		}

		if (!$this->request->post['config_image_related_width'] || !$this->request->post['config_image_related_height']) {
			$this->error['image_related'] = $this->data['error_image_related'];
		}

		if (!$this->request->post['config_image_compare_width'] || !$this->request->post['config_image_compare_height']) {
			$this->error['image_compare'] = $this->data['error_image_compare'];
		}

		if (!$this->request->post['config_image_wishlist_width'] || !$this->request->post['config_image_wishlist_height']) {
			$this->error['image_wishlist'] = $this->data['error_image_wishlist'];
		}

		if (!$this->request->post['config_image_cart_width'] || !$this->request->post['config_image_cart_height']) {
			$this->error['image_cart'] = $this->data['error_image_cart'];
		}

		if (!$this->request->post['config_image_location_width'] || !$this->request->post['config_image_location_height']) {
			$this->error['image_location'] = $this->data['error_image_location'];
		}

		if (!$this->request->post['config_product_limit']) {
			$this->error['product_limit'] = $this->data['error_limit'];
		}

		if (!$this->request->post['config_product_description_length']) {
			$this->error['product_description_length'] = $this->data['error_limit'];
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->data['error_warning'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'setting/store')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('sale/order');

		foreach ($this->request->post['selected'] as $store_id) {
			if (!$store_id) {
				$this->error['warning'] = $this->data['error_default'];
			}

			$store_total = $this->model_sale_order->getTotalOrdersByStoreId($store_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->data['error_store'], $store_total);
			}
		}

		return !$this->error;
	}

	public function template() {
		if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}

		if (is_file(DIR_IMAGE . 'templates/' . basename($this->request->get['template']) . '.png')) {
			$this->response->setOutput($server . 'image/templates/' . basename($this->request->get['template']) . '.png');
		} else {
			$this->response->setOutput($server . 'image/no_image.jpg');
		}
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}