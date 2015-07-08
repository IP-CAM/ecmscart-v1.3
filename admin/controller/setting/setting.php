<?php
class ControllerSettingSetting extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('setting/setting');
		
		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('config', $this->request->post);

			if ($this->config->get('config_currency_auto')) {
				$this->load->model('localisation/currency');

				$this->model_localisation_currency->refresh();
			}
			$this->session->data['RF'] = '';	// Changes by manish for file manager language
			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  isset($this->error['warning'])? $this->error['warning']: '';
		
		$this->data['error_name'] =  isset($this->error['name'])? $this->error['name']: '';
		
		$this->data['error_owner'] =  isset($this->error['owner'])? $this->error['owner']: '';
		
		$this->data['error_address'] =  isset($this->error['address'])? $this->error['address']: '';
		
		$this->data['error_email'] =  isset($this->error['email'])? $this->error['email']: '';
		
		$this->data['error_telephone'] =  isset($this->error['telephone'])? $this->error['telephone']: '';
		
		$this->data['error_meta_title'] =  isset($this->error['meta_title'])? $this->error['meta_title']: '';

		$this->data['error_country'] =  isset($this->error['country'])? $this->error['country']: '';
		
		$this->data['error_zone'] =  isset($this->error['zone'])? $this->error['zone']: '';
		
		$this->data['error_customer_group_display'] =  isset($this->error['customer_group_display'])? $this->error['customer_group_display']: '';
		
		$this->data['error_login_attempts'] =  isset($this->error['login_attempts'])? $this->error['login_attempts']: '';
		
		$this->data['error_voucher_min'] =  isset($this->error['voucher_min'])? $this->error['voucher_min']: '';
		
		$this->data['error_voucher_max'] =  isset($this->error['voucher_max'])? $this->error['voucher_max']: '';
		
		$this->data['error_processing_status'] =  isset($this->error['processing_status'])? $this->error['processing_status']: '';
		
		$this->data['error_complete_status'] =  isset($this->error['complete_status'])? $this->error['complete_status']: '';
		
		$this->data['error_ftp_hostname'] =  isset($this->error['ftp_hostname'])? $this->error['ftp_hostname']: '';
		
		$this->data['error_ftp_port'] =  isset($this->error['ftp_port'])? $this->error['ftp_port']: '';
		
		$this->data['error_ftp_username'] =  isset($this->error['ftp_username'])? $this->error['ftp_username']: '';
		
		$this->data['error_ftp_password'] =  isset($this->error['ftp_password'])? $this->error['ftp_password']: '';
		
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
		
		$this->data['error_error_filename'] =  isset($this->error['error_error_filename'])? $this->error['error_error_filename']: '';

		$this->data['error_product_limit'] =  isset($this->error['product_limit'])? $this->error['product_limit']: '';

		$this->data['error_product_description_length'] =  isset($this->error['product_description_length'])? $this->error['product_description_length']: '';
		
		$this->data['error_limit_admin'] =  isset($this->error['limit_admin'])? $this->error['limit_admin']: '';
		
		$this->data['error_encryption'] =  isset($this->error['encryption'])? $this->error['encryption']: '';
		
	
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
								$this->data['text_home'],	// Text to display link
								$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
								$this->data['text_stores'],	// Text to display link
								$this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
								$this->data['heading_title'],
								$this->url->link('setting/setting', 'token=' . $this->session->data['token'], 'SSL'),
							));

		$this->data['success'] =  isset($this->session->data['success'])? $this->session->data['success']: '';
		
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);


		$this->data['action'] = $this->url->link('setting/setting', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('setting/store', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['token'] = $this->session->data['token'];

		$this->data['config_name'] = $this->request->post('config_name', $this->config->get('config_name'));

		$this->data['config_owner'] = $this->request->post('config_owner', $this->config->get('config_owner'));
		
		$this->data['config_address'] = $this->request->post('config_address', $this->config->get('config_address'));
		
		$this->data['config_geocode'] = $this->request->post('config_geocode', $this->config->get('config_geocode'));
		
		$this->data['config_email'] = $this->request->post('config_email', $this->config->get('config_email'));
		
		$this->data['config_telephone'] = $this->request->post('config_telephone', $this->config->get('config_telephone'));
		
		$this->data['config_fax'] = $this->request->post('config_fax', $this->config->get('config_fax'));
		
		$this->data['config_image'] = $this->request->post('config_image', $this->config->get('config_image'));
		
		$this->load->model('tool/image');

		if ($this->config->get('config_image') && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($this->config->get('config_image'), 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('config_image',''), 100, 100);
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		$this->data['config_open'] = $this->request->post('config_open', $this->config->get('config_open'));
		
		$this->data['config_comment'] = $this->request->post('config_comment', $this->config->get('config_comment'));
		
		$this->load->model('localisation/location');

		$this->data['locations'] = $this->model_localisation_location->getLocations();

		if ($this->config->get('config_location') & $this->error) {
			$this->data['config_location'] = $this->config->get('config_location');
		} else {
			$this->data['config_location'] = $this->request->post('config_location', array());
		}

		$this->data['config_meta_title'] = $this->request->post('config_meta_title', $this->config->get('config_meta_title'));
		
		$this->data['config_meta_description'] = $this->request->post('config_meta_description', $this->config->get('config_meta_description'));
		
		$this->data['config_meta_keyword'] = $this->request->post('config_meta_keyword', $this->config->get('config_meta_keyword'));
		
		$this->data['config_layout_id'] = $this->request->post('config_layout_id', $this->config->get('config_layout_id'));
		
		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();
		
		$this->data['config_template'] = $this->request->post('config_template', $this->config->get('config_template'));

		$this->data['templates'] = array();

		$directories = glob(DIR_CATALOG . 'view/theme/*', GLOB_ONLYDIR);

		foreach ($directories as $directory) {
			$this->data['templates'][] = basename($directory);
		}

		$this->data['config_country_id'] = $this->request->post('config_country_id', $this->config->get('config_country_id'));
		
		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->data['config_zone_id'] = $this->request->post('config_zone_id', $this->config->get('config_zone_id'));
		
		$this->data['config_language'] = $this->request->post('config_language', $this->config->get('config_language'));

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		///changes by manish for file manager language
		$this->data['config_filemanager_language'] = $this->request->post('config_filemanager_language', $this->config->get('config_filemanager_language'));
		
		$this->data['filemanagerLanguages'] = $this->fmlanguages;

		$this->data['config_admin_language'] = $this->request->post('config_admin_language', $this->config->get('config_admin_language'));
		
		$this->data['config_currency'] = $this->request->post('config_currency', $this->config->get('config_currency'));
		
		$this->data['config_currency_auto'] = $this->request->post('config_currency_auto', $this->config->get('config_currency_auto'));

		$this->load->model('localisation/currency');

		$this->data['currencies'] = $this->model_localisation_currency->getCurrencies();
		
		$this->data['config_length_class_id'] = $this->request->post('config_length_class_id', $this->config->get('config_length_class_id'));

		$this->load->model('localisation/length_class');

		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		$this->data['config_weight_class_id'] = $this->request->post('config_weight_class_id', $this->config->get('config_weight_class_id'));
		
		$this->load->model('localisation/weight_class');

		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
		
		$this->data['config_product_limit'] = $this->request->post('config_product_limit', $this->config->get('config_product_limit'));
		
		$this->data['config_product_description_length'] = $this->request->post('config_product_description_length', $this->config->get('config_product_description_length'));
		
		$this->data['config_limit_admin'] = $this->request->post('config_limit_admin', $this->config->get('config_limit_admin'));
		
		$this->data['config_product_count'] = $this->request->post('config_product_count', $this->config->get('config_product_count'));
		
		$this->data['config_blog_comment_status'] = $this->request->post('config_blog_comment_status', $this->config->get('config_blog_comment_status'));
		
		$this->data['config_blog_comment_guest'] = $this->request->post('config_blog_comment_guest', $this->config->get('config_blog_comment_guest'));
		
		$this->data['config_blog_comment_mail'] = $this->request->post('config_blog_comment_mail', $this->config->get('config_blog_comment_mail'));
		
		$this->data['config_blog_footer'] = $this->request->post('config_comment_mail', $this->config->get('config_blog_footer'));		
		
		$this->data['config_review_status'] = $this->request->post('config_review_status', $this->config->get('config_review_status'));
		
		$this->data['config_review_guest'] = $this->request->post('config_review_guest', $this->config->get('config_review_guest'));
		
		$this->data['config_review_mail'] = $this->request->post('config_review_mail', $this->config->get('config_review_mail'));
		
		$this->data['config_voucher_min'] = $this->request->post('config_voucher_min', $this->config->get('config_voucher_min'));
		
		$this->data['config_voucher_max'] = $this->request->post('config_voucher_max', $this->config->get('config_voucher_max'));
		
		$this->data['config_tax'] = $this->request->post('config_tax', $this->config->get('config_tax'));
		
		$this->data['config_tax_default'] = $this->request->post('config_tax_default', $this->config->get('config_tax_default'));
		
		$this->data['config_tax_customer'] = $this->request->post('config_tax_customer', $this->config->get('config_tax_customer'));
		
		$this->data['config_customer_online'] = $this->request->post('config_customer_online', $this->config->get('config_customer_online'));
		
		$this->data['config_customer_group_id'] = $this->request->post('config_customer_group_id', $this->config->get('config_customer_group_id'));
		

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		if ($this->config->get('config_customer_group_display') && !$this->error) {
			$this->data['config_customer_group_display'] = $this->config->get('config_customer_group_display');
		} else {
			$this->data['config_customer_group_display'] = $this->request->post('config_customer_group_display', array());
		}

		$this->data['config_customer_price'] = $this->request->post('config_customer_price', $this->config->get('config_customer_price'));
		
		$this->data['config_login_attempts'] = $this->request->post('config_login_attempts', $this->config->get('config_login_attempts'));
		
		if ($this->config->has('config_login_attempts') && !$this->error) {
			$this->data['config_login_attempts'] = $this->config->get('config_login_attempts');
		} else {
			$this->data['config_login_attempts'] = $this->request->post('config_login_attempts', 5);
		}
		
		$this->data['config_account_id'] = $this->request->post('config_account_id', $this->config->get('config_account_id'));

		$this->load->model('catalog/information');

		$this->data['informations'] = $this->model_catalog_information->getInformations();
	
		$this->data['config_account_mail'] = $this->request->post('config_account_mail', $this->config->get('config_account_mail'));
		
		$this->data['config_api_id'] = $this->request->post('config_api_id', $this->config->get('config_api_id'));
		
		$this->load->model('user/api');

		$this->data['apis'] = $this->model_user_api->getApis();
		
		$this->data['config_cart_weight'] = $this->request->post('config_cart_weight', $this->config->get('config_cart_weight'));
		
		$this->data['config_checkout_guest'] = $this->request->post('config_checkout_guest', $this->config->get('config_checkout_guest'));
		
		$this->data['config_checkout_id'] = $this->request->post('config_checkout_id', $this->config->get('config_checkout_id'));
		
		if ($this->config->get('config_invoice_prefix') && !$this->error) {
			$this->data['config_invoice_prefix'] = $this->config->get('config_invoice_prefix');
		} else {
			$this->data['config_invoice_prefix'] = $this->request->post('config_invoice_prefix', 'INV-' . date('Y') . '-00');
		}

		$this->data['config_order_status_id'] = $this->request->post('config_order_status_id', $this->config->get('config_order_status_id'));

		if ($this->config->get('config_processing_status') && !$this->error) {
			$this->data['config_processing_status'] = $this->config->get('config_processing_status');
		} else {
			$this->data['config_processing_status'] = $this->request->post('config_processing_status', array());
		}

		if ($this->config->get('config_complete_status') && !$this->error) {
			$this->data['config_complete_status'] = $this->config->get('config_complete_status');
		} else {
			$this->data['config_complete_status'] = $this->request->post('config_complete_status', array());
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$this->data['config_order_mail'] = $this->request->post('config_order_mail', $this->config->get('config_order_mail'));
		
		$this->data['config_stock_display'] = $this->request->post('config_stock_display', $this->config->get('config_stock_display'));

		$this->data['config_stock_warning'] = $this->request->post('config_stock_warning', $this->config->get('config_stock_warning'));
		
		$this->data['config_stock_checkout'] = $this->request->post('config_stock_checkout', $this->config->get('config_stock_checkout'));
		
		$this->data['config_stock_display'] = $this->request->post('config_stock_display', $this->config->get('config_stock_display'));

		if ($this->config->has('config_affiliate_commission') && !$this->error) {
			$this->data['config_affiliate_approval'] = $this->config->get('config_affiliate_approval');
		} else {
			$this->data['config_affiliate_approval'] = $this->request->post('config_affiliate_approval', '');
		}

		if ($this->config->has('config_affiliate_auto') && !$this->error) {
			$this->data['config_affiliate_auto'] = $this->config->get('config_affiliate_auto');
		} else {
			$this->data['config_affiliate_auto'] = $this->request->post('config_affiliate_auto', '');
		}

		if ($this->config->has('config_affiliate_commission') && !$this->error) {
			$this->data['config_affiliate_commission'] = $this->config->get('config_affiliate_commission');
		} else {
			$this->data['config_affiliate_commission'] = $this->request->post('config_affiliate_commission', '5.00');
		}

		if ($this->config->has('config_affiliate_mail') && !$this->error) {
			$this->data['config_affiliate_mail'] = $this->config->get('config_affiliate_mail');
		} else {
			$this->data['config_affiliate_mail'] = $this->request->post('config_affiliate_mail', '');
		}
	
		$this->data['config_affiliate_id'] = $this->request->post('config_affiliate_id', $this->config->get('config_affiliate_id'));
		
		$this->data['config_return_id'] = $this->request->post('config_return_id', $this->config->get('config_return_id'));
		
		$this->data['config_return_status_id'] = $this->request->post('config_return_status_id', $this->config->get('config_return_status_id'));
		
		$this->load->model('localisation/return_status');

		$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

		$this->data['config_logo'] = $this->request->post('config_logo', $this->config->get('config_logo'));

		if ($this->config->get('config_logo') && !$this->error) {
			$this->data['logo'] = $this->model_tool_image->resize($this->config->get('config_logo'), 100, 100);
		} else {
			$this->data['logo'] = $this->model_tool_image->resize($this->request->post('config_logo', ''), 100, 100);
		}

		$this->data['config_icon'] = $this->request->post('config_icon', $this->config->get('config_icon'));
		
		if ($this->config->get('config_icon') && !$this->error) {
			$this->data['icon'] = $this->model_tool_image->resize($this->config->get('config_icon'), 100, 100);
		} else {
			$this->data['icon'] = $this->model_tool_image->resize($this->request->post('config_icon', ''), 100, 100);
		}

		$this->data['config_image_category_width'] = $this->request->post('config_image_category_width', $this->config->get('config_image_category_width'));
		
		$this->data['config_image_category_height'] = $this->request->post('config_image_category_height', $this->config->get('config_image_category_height'));
		
		$this->data['config_image_family_height'] = $this->request->post('config_image_family_height', $this->config->get('config_image_family_height'));
		
		$this->data['config_image_family_width'] = $this->request->post('config_image_family_width', $this->config->get('config_image_family_width'));
		
		$this->data['config_thumb_family_height'] = $this->request->post('config_thumb_family_height', $this->config->get('config_thumb_family_height'));
		
		$this->data['config_thumb_family_width'] = $this->request->post('config_thumb_family_width', $this->config->get('config_thumb_family_width'));		
		
		$this->data['config_image_blog_width'] = $this->request->post('config_image_blog_width', $this->config->get('config_image_blog_width'));
		
		$this->data['config_image_blog_height'] = $this->request->post('config_image_blog_height', $this->config->get('config_image_blog_height'));
		
		$this->data['config_thumb_blog_width'] = $this->request->post('config_thumb_blog_width', $this->config->get('config_thumb_blog_width'));
		
		$this->data['config_thumb_blog_height'] = $this->request->post('config_thumb_blog_height', $this->config->get('config_thumb_blog_height'));
		
		$this->data['config_image_author_height'] = $this->request->post('config_image_author_height', $this->config->get('config_image_author_height'));
		
		$this->data['config_image_author_width'] = $this->request->post('config_image_author_width', $this->config->get('config_image_author_width'));
		
		$this->data['config_image_thumb_width'] = $this->request->post('config_image_thumb_width', $this->config->get('config_image_thumb_width'));
		
		$this->data['config_image_thumb_height'] = $this->request->post('config_image_thumb_height', $this->config->get('config_image_thumb_height'));
		
		$this->data['config_image_popup_width'] = $this->request->post('config_image_popup_width', $this->config->get('config_image_popup_width'));
		
		$this->data['config_image_popup_height'] = $this->request->post('config_image_popup_height', $this->config->get('config_image_popup_height'));
		
		$this->data['config_image_product_width'] = $this->request->post('config_image_product_width', $this->config->get('config_image_product_width'));
		
		$this->data['config_image_product_height'] = $this->request->post('config_image_product_height', $this->config->get('config_image_product_height'));
		
		$this->data['config_image_additional_width'] = $this->request->post('config_image_additional_width', $this->config->get('config_image_additional_width'));
		
		$this->data['config_image_additional_height'] = $this->request->post('config_image_additional_height', $this->config->get('config_image_additional_height'));
		
		$this->data['config_image_related_width'] = $this->request->post('config_image_related_width', $this->config->get('config_image_related_width'));
		
		$this->data['config_image_related_height'] = $this->request->post('config_image_related_height', $this->config->get('config_image_related_height'));
		
		$this->data['config_image_compare_width'] = $this->request->post('config_image_compare_width', $this->config->get('config_image_compare_width'));
		
		$this->data['config_image_compare_height'] = $this->request->post('config_image_compare_height', $this->config->get('config_image_compare_height'));
		
		$this->data['config_image_wishlist_width'] = $this->request->post('config_image_wishlist_width', $this->config->get('config_image_wishlist_width'));
		
		$this->data['config_image_wishlist_height'] = $this->request->post('config_image_wishlist_height', $this->config->get('config_image_wishlist_height'));
		$this->data['config_image_cart_width'] = $this->request->post('config_image_cart_width', $this->config->get('config_image_cart_width'));
		
		$this->data['config_image_cart_height'] = $this->request->post('config_image_cart_height', $this->config->get('config_image_cart_height'));
		
		$this->data['config_image_location_width'] = $this->request->post('config_image_location_width', $this->config->get('config_image_location_width'));
		
		$this->data['config_image_location_height'] = $this->request->post('config_image_location_height', $this->config->get('config_image_location_height'));
		if ($this->config->get('config_ftp_hostname') && !$this->error) {
			$this->data['config_ftp_hostname'] = $this->config->get('config_ftp_hostname');
		} else {
			$this->data['config_ftp_hostname'] = $this->request->post('config_ftp_hostname', str_replace('www.', '', $this->request->server['HTTP_HOST']));
		}

		if ($this->config->get('config_ftp_port') && !$this->error) {
			$this->data['config_ftp_port'] = $this->config->get('config_ftp_port');
		} else {
			$this->data['config_ftp_port'] = $this->request->post('config_ftp_port', 21);
		}

		$this->data['config_ftp_username'] = $this->request->post('config_ftp_username', $this->config->get('config_ftp_username'));
		
		$this->data['config_ftp_password'] = $this->request->post('config_ftp_password', $this->config->get('config_ftp_password'));
		
		$this->data['config_ftp_root'] = $this->request->post('config_ftp_root', $this->config->get('config_ftp_root'));
		
		$this->data['config_ftp_status'] = $this->request->post('config_ftp_status', $this->config->get('config_ftp_status'));
		
		$config_mail = array();
		$this->data['config_mail_protocol'] = '';
		$this->data['config_mail_parameter'] = '';
		$this->data['config_smtp_hostname'] = '';
		$this->data['config_smtp_username'] = '';
		$this->data['config_smtp_password'] = '';
		$this->data['config_smtp_port'] = 25;
		$this->data['config_smtp_timeout'] = 5;

		$config_mail = $this->request->post('config_mail', $this->config->get('config_mail'));

		$this->data['config_mail_protocol'] = $config_mail['protocol'];
		$this->data['config_mail_parameter'] = $config_mail['parameter'];
		$this->data['config_smtp_hostname'] = $config_mail['smtp_hostname'];
		$this->data['config_smtp_username'] = $config_mail['smtp_username'];
		$this->data['config_smtp_password'] = $config_mail['smtp_password'];
		$this->data['config_smtp_port'] = $config_mail['smtp_port'];
		$this->data['config_smtp_timeout'] = $config_mail['smtp_timeout'];
		
		$this->data['config_mail_alert'] = $this->request->post('config_mail_alert', $this->config->get('config_mail_alert'));	
	
		$this->data['config_fraud_detection'] = $this->request->post('config_fraud_detection', $this->config->get('config_fraud_detection'));
		
		$this->data['config_fraud_key'] = $this->request->post('config_fraud_key', $this->config->get('config_fraud_key'));
		
		$this->data['config_fraud_score'] = $this->request->post('config_fraud_score', $this->config->get('config_fraud_score'));
		
		$this->data['config_fraud_status_id'] = $this->request->post('config_fraud_status_id', $this->config->get('config_fraud_status_id'));
		
		$this->data['config_secure'] = $this->request->post('config_secure', $this->config->get('config_secure'));
		
		$this->data['config_shared'] = $this->request->post('config_shared', $this->config->get('config_shared'));
		
		$this->data['config_robots'] = $this->request->post('config_robots', $this->config->get('config_robots'));
		
		$this->data['config_seo_url'] = $this->request->post('config_seo_url', $this->config->get('config_seo_url'));
		
		if ($this->config->get('config_file_max_size') && !$this->error) {
			$this->data['config_file_max_size'] = $this->config->get('config_file_max_size');
		} else {
			$this->data['config_file_max_size'] = $this->request->post('config_file_max_size', 300000);
		}
		
		$this->data['config_file_ext_allowed'] = $this->request->post('config_file_ext_allowed', $this->config->get('config_file_ext_allowed'));	
		
		$this->data['config_file_mime_allowed'] = $this->request->post('config_file_mime_allowed', $this->config->get('config_file_mime_allowed'));
		
		$this->data['config_maintenance'] = $this->request->post('config_maintenance', $this->config->get('config_maintenance'));
		
		$this->data['config_password'] = $this->request->post('config_password', $this->config->get('config_password'));
		
		$this->data['config_encryption'] = $this->request->post('config_encryption', $this->config->get('config_encryption'));
		
		$this->data['config_compression'] = $this->request->post('config_compression', $this->config->get('config_compression'));
		
		$this->data['config_error_display'] = $this->request->post('config_error_display', $this->config->get('config_error_display'));
		
		$this->data['config_error_log'] = $this->request->post('config_error_log', $this->config->get('config_error_log'));
		
		$this->data['config_error_filename'] = $this->request->post('config_error_filename', $this->config->get('config_error_filename'));
		
		$this->data['config_google_analytics'] = $this->request->post('config_google_analytics', $this->config->get('config_google_analytics'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('setting/setting.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'setting/setting')) {
			$this->error['warning'] = $this->data['error_permission'];
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
		
		if ($this->request->post['config_login_attempts'] < 1) {
			$this->error['login_attempts'] = $this->data['error_login_attempts'];
		}
		
		if (!$this->request->post['config_voucher_min']) {
			$this->error['voucher_min'] = $this->data['error_voucher_min'];
		}

		if (!$this->request->post['config_voucher_max']) {
			$this->error['voucher_max'] = $this->data['error_voucher_max'];
		}

		if (!isset($this->request->post['config_processing_status'])) {
			$this->error['processing_status'] = $this->data['error_processing_status'];
		}

		if (!isset($this->request->post['config_complete_status'])) {
			$this->error['complete_status'] = $this->data['error_complete_status'];
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

		if ($this->request->post['config_ftp_status']) {
			if (!$this->request->post['config_ftp_hostname']) {
				$this->error['ftp_hostname'] = $this->data['error_ftp_hostname'];
			}

			if (!$this->request->post['config_ftp_port']) {
				$this->error['ftp_port'] = $this->data['error_ftp_port'];
			}

			if (!$this->request->post['config_ftp_username']) {
				$this->error['ftp_username'] = $this->data['error_ftp_username'];
			}

			if (!$this->request->post['config_ftp_password']) {
				$this->error['ftp_password'] = $this->data['error_ftp_password'];
			}
		}

		if (!$this->request->post['config_error_filename']) {
			$this->error['error_error_filename'] = $this->data['error_error_filename'];
		}

		if (!$this->request->post['config_product_limit']) {
			$this->error['product_limit'] = $this->data['error_limit'];
		}

		if (!$this->request->post['config_product_description_length']) {
			$this->error['product_description_length'] = $this->data['error_limit'];
		}

		if (!$this->request->post['config_limit_admin']) {
			$this->error['limit_admin'] = $this->data['error_limit'];
		}

		if ((utf8_strlen($this->request->post['config_encryption']) < 3) || (utf8_strlen($this->request->post['config_encryption']) > 32)) {
			$this->error['encryption'] = $this->data['error_encryption'];
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->data['error_warning'];
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
			$this->response->setOutput($server . 'image/no_image.png');
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