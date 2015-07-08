<?php
class ControllerCatalogProduct extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'filter_name' => 'encode', // for using these functions urlencode(html_entity_decode($this->get[$item], ENT_QUOTES, 'UTF-8'));
				'filter_model' => 'encode',
				'filter_price',
				'filter_quantity',
				'filter_status',
				'sort',
				'order',
				'page',
			);
			
	public function index() {
		$this->data = $this->load->language('catalog/product');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/product');

		$this->getList();
	}

	public function save() { // Create by Manish in place of add and edit
		$this->data = $this->load->language('catalog/product');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/product');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['product_id'])){
				$this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post);
			} else{
				$this->model_catalog_product->addProduct($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('catalog/product');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->deleteProduct($product_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function copy() {
		$this->data = $this->load->language('catalog/product');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->copyProduct($product_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
	
			$url = $this->request->getUrl($this->url_data);
		
			$this->response->redirect($this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_name = $this->request->get('filter_name', null);
		///first argument is post variable of the tpl file and second argument is default value of the post field in the above function.
		
		$filter_model = $this->request->get('filter_model', null);
		
		$filter_price = $this->request->get('filter_price', null);

		$filter_quantity = $this->request->get('filter_quantity', null);
		
		$filter_status = $this->request->get('filter_status', null);
		
		$sort = $this->request->get('sort','pd.name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);

		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('catalog/product/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['copy'] = $this->url->link('catalog/product/copy', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/product/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['products'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

		$results = $this->model_catalog_product->getProducts($filter_data);	

		foreach ($results as $result) {
			
			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $product_special['price'];

					break;
				}
			}

			$this->data['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $this->model_tool_image->resize($result['image'], 40, 40),
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $result['price'],
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'status'     => ($result['status']) ? $this->data['text_enabled'] : $this->data['text_disabled'],
				'save'       => $this->url->link('catalog/product/save', 'token=' . $this->session->data['token'] . '&product_id=' . $result['product_id'] . $url, 'SSL')
			);
		}
		
		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['selected'] =  $this->request->post('selected', array());
		
		// Sorting and Filter Function for filter variable again
		$url_data = array(
				'filter_name' => 'encode', 
				'filter_model' => 'encode',
				'filter_price',
				'filter_quantity',
				'filter_status',
			);
		
		$url = $this->request->getUrl($url_data);
	
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
		
		$this->data['sort_name'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_model'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.model' . $url, 'SSL');
		$this->data['sort_price'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.price' . $url, 'SSL');
		$this->data['sort_quantity'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.quantity' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.status' . $url, 'SSL');
		$this->data['sort_order'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . '&sort=p.sort_order' . $url, 'SSL');
		
		// Sorting and Filter Function for paging
		$url_data = array(
				'filter_name' => 'encode', 
				'filter_model' => 'encode',
				'filter_price',
				'filter_quantity',
				'filter_status',
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		
		$this->data['results'] = sprintf($this->data['text_pagination'], ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_model'] = $filter_model;
		$this->data['filter_price'] = $filter_price;
		$this->data['filter_quantity'] = $filter_quantity;
		$this->data['filter_status'] = $filter_status;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/product_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['product_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']:array());
		
		$this->data['error_meta_title'] =  (isset($this->error['meta_title'])?$this->error['meta_title']:array());
		
		$this->data['error_model'] =  (isset($this->error['model'])?$this->error['model']:'');

		$this->data['error_date_available'] =  (isset($this->error['date_available'])?$this->error['date_available']:'');

		$this->data['error_keyword'] =  (isset($this->error['keyword'])?$this->error['keyword']:'');
		
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['product_id'])) {
			$this->data['action'] = $this->url->link('catalog/product/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/product/save', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/product', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['product_id']) && !$this->error) {
			$this->data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
		} else {
			$this->data['product_description'] = $this->request->post('product_description', array()); 
		}
		
		if (isset($this->request->get['product_id']) && !$this->error) {
			$this->data['product_keyword'] = $this->model_catalog_product->getProductKeyword($this->request->get['product_id']);
		} else {
			$this->data['product_keyword'] = $this->request->post('product_keyword', array());
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['image'] = $product_info['image'];
		} else {
			$this->data['image'] = $this->request->post('image', '');
		}

		$this->load->model('tool/image');

		if (!empty($product_info) && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('image', ''), 100, 100); 
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (!empty($product_info) && !$this->error) {
			$this->data['model'] = $product_info['model'];
		} else {
			$this->data['model'] = $this->request->post('model', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['sku'] = $product_info['sku'];
		} else {
			$this->data['sku'] = $this->request->post('sku', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['upc'] = $product_info['upc'];
		} else {
			$this->data['upc'] = $this->request->post('upc', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['ean'] = $product_info['ean'];
		} else {
			$this->data['ean'] = $this->request->post('ean', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['jan'] = $product_info['jan'];
		} else {
			$this->data['jan'] = $this->request->post('jan', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['isbn'] = $product_info['isbn'];
		} else {
			$this->data['isbn'] = $this->request->post('isbn', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['mpn'] = $product_info['mpn'];
		} else {
			$this->data['mpn'] = $this->request->post('mpn', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['location'] = $product_info['location'];
		} else {
			$this->data['location'] = $this->request->post('location', '');
		}

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->get['product_id']) && !$this->error) {
			$this->data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
		} else {
			$this->data['product_store'] = $this->request->post('product_store',  array(0));
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['shipping'] = $product_info['shipping'];
		} else {
			$this->data['shipping'] = $this->request->post('shipping', 1);
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['price'] = $product_info['price'];
		} else {
			$this->data['price'] = $this->request->post('price', '');
		}

		$this->load->model('catalog/recurring');

		$this->data['recurrings'] = $this->model_catalog_recurring->getRecurrings();

		if (!empty($product_info) && !$this->error) {
			$this->data['product_recurrings'] = $this->model_catalog_product->getRecurrings($product_info['product_id']);
		} else {
			$this->data['product_recurrings'] = $this->request->post('product_recurrings',  array());
		}

		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (!empty($product_info) && !$this->error) {
			$this->data['tax_class_id'] = $product_info['tax_class_id'];
		} else {
			$this->data['tax_class_id'] = $this->request->post('tax_class_id', 0);
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['date_available'] = ($product_info['date_available'] != '0000-00-00') ? $product_info['date_available'] : '';
		} else {
			$this->data['date_available'] = $this->request->post('date_available', date('Y-m-d'));
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['quantity'] = $product_info['quantity'];
		} else {
			$this->data['quantity'] = $this->request->post('quantity', 1);
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['minimum'] = $product_info['minimum'];
		} else {
			$this->data['minimum'] = $this->request->post('minimum', 1);
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['subtract'] = $product_info['subtract'];
		} else {
			$this->data['subtract'] = $this->request->post('subtract', 1);
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['sort_order'] = $product_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', 1);
		}

		$this->load->model('localisation/stock_status');

		$this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

		if (!empty($product_info) && !$this->error) {
			$this->data['stock_status_id'] = $product_info['stock_status_id'];
		} else {
			$this->data['stock_status_id'] = $this->request->post('stock_status_id', 0);
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['status'] = $product_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', true);
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['weight'] = $product_info['weight'];
		} else {
			$this->data['weight'] = $this->request->post('weight', '');
		}

		$this->load->model('localisation/weight_class');

		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		if (!empty($product_info) && !$this->error) {
			$this->data['weight_class_id'] = $product_info['weight_class_id'];
		} else {
			$this->data['weight_class_id'] = $this->request->post('weight_class_id', $this->config->get('config_weight_class_id'));
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['length'] = $product_info['length'];
		} else {
			$this->data['length'] = $this->request->post('length', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['width'] = $product_info['width'];
		} else {
			$this->data['width'] = $this->request->post('width', '');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['height'] = $product_info['height'];
		} else {
			$this->data['height'] = $this->request->post('height', '');
		}

		$this->load->model('localisation/length_class');

		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		if (!empty($product_info) && !$this->error) {
			$this->data['length_class_id'] = $product_info['length_class_id'];
		} else {
			$this->data['length_class_id'] = $this->request->post('length_class_id', $this->config->get('config_length_class_id'));
		}

		$this->load->model('catalog/manufacturer');

		if (!empty($product_info) && !$this->error) {
			$this->data['manufacturer_id'] = $product_info['manufacturer_id'];
		} else {
			$this->data['manufacturer_id'] = $this->request->post('manufacturer_id', 0);
		}

		if (!empty($product_info) && !$this->error) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);
			$this->data['manufacturer'] = ($manufacturer_info) ? $manufacturer_info['name'] : '';
		} else {
			$this->data['manufacturer'] = $this->request->post('manufacturer', '');
		}

		// Categories
		$this->load->model('catalog/category');

		if (isset($this->request->get['product_id'])) {
			$categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
		} else {
			$categories = $this->request->post('product_category', array());
		}

		$this->data['product_categories'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$this->data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		// Filters
		$this->load->model('catalog/filter');

		if (isset($this->request->get['product_id'])) {
			$filters = $this->model_catalog_product->getProductFilters($this->request->get['product_id']);
		} else {
			$filters = $this->request->post('product_filter', array());
		}

		$this->data['product_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$this->data['product_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		// Attributes
		$this->load->model('catalog/attribute');

		if (isset($this->request->get['product_id'])) {
			$product_attributes = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
		} else {
			$product_attributes = $this->request->post('product_attribute', array());
		}

		$this->data['product_attributes'] = array();

		foreach ($product_attributes as $product_attribute) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

			if ($attribute_info) {
				$this->data['product_attributes'][] = array(
					'attribute_id'                  => $product_attribute['attribute_id'],
					'name'                          => $attribute_info['name'],
					'product_attribute_description' => $product_attribute['product_attribute_description']
				);
			}
		}

		// Options
		$this->load->model('catalog/option');

		if (isset($this->request->get['product_id'])) {
			$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
		} else {
			$product_options = $this->request->post('product_option', array());
		}

		$this->data['product_options'] = array();

		foreach ($product_options as $product_option) {
			$product_option_value_data = array();

			if (isset($product_option['product_option_value'])) {
				foreach ($product_option['product_option_value'] as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'points'                  => $product_option_value['points'],
						'points_prefix'           => $product_option_value['points_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']
					);
				}
			}

			$this->data['product_options'][] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => isset($product_option['value']) ? $product_option['value'] : '',
				'required'             => $product_option['required']
			);
		}

		$this->data['option_values'] = array();

		foreach ($this->data['product_options'] as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				if (!isset($this->data['option_values'][$product_option['option_id']])) {
					$this->data['option_values'][$product_option['option_id']] = $this->model_catalog_option->getOptionValues($product_option['option_id']);
				}
			}
		}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		if (isset($this->request->get['product_id'])) {
			$product_discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
		} else {
			$product_discounts = $this->request->post('product_discount', array());
		}

		$this->data['product_discounts'] = array();

		foreach ($product_discounts as $product_discount) {
			$this->data['product_discounts'][] = array(
				'customer_group_id' => $product_discount['customer_group_id'],
				'quantity'          => $product_discount['quantity'],
				'priority'          => $product_discount['priority'],
				'price'             => $product_discount['price'],
				'date_start'        => ($product_discount['date_start'] != '0000-00-00') ? $product_discount['date_start'] : '',
				'date_end'          => ($product_discount['date_end'] != '0000-00-00') ? $product_discount['date_end'] : ''
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_specials = $this->model_catalog_product->getProductSpecials($this->request->get['product_id']);
		} else {
			$product_specials = $this->request->post('product_special', array());
		}

		$this->data['product_specials'] = array();

		foreach ($product_specials as $product_special) {
			$this->data['product_specials'][] = array(
				'customer_group_id' => $product_special['customer_group_id'],
				'priority'          => $product_special['priority'],
				'price'             => $product_special['price'],
				'date_start'        => ($product_special['date_start'] != '0000-00-00') ? $product_special['date_start'] : '',
				'date_end'          => ($product_special['date_end'] != '0000-00-00') ? $product_special['date_end'] :  ''
			);
		}

		// Images
		if (isset($this->request->get['product_id'])) {
			$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
		} else {
			$product_images = $this->request->post('product_image', array());
		}

		$this->data['product_images'] = array();

		foreach ($product_images as $product_image) {
			$this->data['product_images'][] = array(
				'image'      => $product_image['image'],
				'thumb'      => $this->model_tool_image->resize($product_image['image'], 100, 100),
				'sort_order' => $product_image['sort_order']
			);
		}

		// Downloads
		$this->load->model('catalog/download');

		if (isset($this->request->get['product_id'])) {
			$product_downloads = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
		} else {
			$product_downloads = $this->request->post('product_download', array());
		}

		$this->data['product_downloads'] = array();

		foreach ($product_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);

			if ($download_info) {
				$this->data['product_downloads'][] = array(
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				);
			}
		}

		if (isset($this->request->get['product_id'])) {
			$products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
		} else {
			$products = $this->request->post('product_related', array());
		}

		$this->data['product_relateds'] = array();

		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);

			if ($related_info) {
				$this->data['product_relateds'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (!empty($product_info)) {
			$this->data['points'] = $product_info['points'];
		} else {
			$this->data['points'] = $this->request->post('points', '');
		}

		if (isset($this->request->get['product_id'])) {
			$this->data['product_reward'] = $this->model_catalog_product->getProductRewards($this->request->get['product_id']);
		} else {
			$this->data['product_reward'] = $this->request->post('product_reward', array());
		}

		if (isset($this->request->get['product_id'])) {
			$this->data['product_layout'] = $this->model_catalog_product->getProductLayouts($this->request->get['product_id']);
		} else {
			$this->data['product_layout'] = $this->request->post('product_layout', array());
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/product_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['product_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->data['error_meta_title'];
			}
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->data['error_model'];
		}
		
		foreach ($this->request->post['product_keyword'] as $language_id => $value) {	
			if (utf8_strlen($value['keyword']) > 0) {
				$this->load->model('catalog/url_alias');

				$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($value['keyword']);
	
				if ($url_alias_info && isset($this->request->get['product_id']) && $url_alias_info['query'] != 'product_id=' . $this->request->get['product_id']) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}

				if ($url_alias_info && !isset($this->request->get['product_id'])) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}			
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->data['error_warning'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

			$filter_data = array(
				'filter_name'  => $this->request->get('filter_name', null),
				'filter_model' => $this->request->get('filter_model', null),
				'start'        => 0,
				'limit'        => $this->request->get('limit', 5)
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}