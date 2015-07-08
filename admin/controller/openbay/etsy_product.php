<?php
class ControllerOpenbayEtsyProduct extends Controller {
	private $error;

	public function create() {
		$this->data = $this->load->language('openbay/etsy_create');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['action']   = $this->url->link('openbay/etsy_product/create', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel']   = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token']    = $this->session->data['token'];

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_etsy'],
							$this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/etsy_product/create', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

		$this->load->model('tool/image');

		if (!empty($product_info) && !$this->error) {
			$product_info['image_url'] = $this->model_tool_image->resize($product_info['image'], 800, 800);
			$product_info['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
		} else {
			$product_info['image_url'] = '';
			$product_info['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		// Images
		if (isset($this->request->get['product_id'])) {
			$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
		} else {
			$product_images = array();
		}

		$this->data['product_images'] = array();

		foreach ($product_images as $product_image) {
			$product_info['product_images'][] = array(
				'image_url'  => $this->model_tool_image->resize($product_image['image'], 800, 800),
				'thumb'      => $this->model_tool_image->resize($product_image['image'], 100, 100),
				'sort_order' => $product_image['sort_order']
			);
		}

		$this->data['product'] = $product_info;
		$this->data['product']['description_raw'] = trim(strip_tags(html_entity_decode($this->data['product']['description'], ENT_QUOTES, 'UTF-8')));

		$setting = array();

		$setting['who_made'] = $this->openbay->etsy->getSetting('who_made');
		if (is_array($setting['who_made'])) {
			ksort($setting['who_made']);
		}

		$setting['when_made'] = $this->openbay->etsy->getSetting('when_made');
		if (is_array($setting['when_made'])) {
			ksort($setting['when_made']);
		}

		$setting['recipient'] = $this->openbay->etsy->getSetting('recipient');
		if (is_array($setting['recipient'])) {
			ksort($setting['recipient']);
		}

		$setting['occasion'] = $this->openbay->etsy->getSetting('occasion');
		if (is_array($setting['occasion'])) {
			ksort($setting['occasion']);
		}

		$setting['top_categories'] = $this->openbay->etsy->getSetting('top_categories');
		if (is_array($setting['top_categories'])) {
			ksort($setting['top_categories']);
		}

		$setting['state'] = array('active', 'draft');

		$this->data['setting'] = $setting;

		if ($product_info['quantity'] > 999) {
			$this->error['warning'] = sprintf($this->data['error_stock_max'], $product_info['quantity']);
		}
		
		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy_create.tpl', $this->data));
	}

	public function createSubmit() {
		$this->data = $this->load->language('openbay/etsy_create');
		$this->load->model('openbay/etsy_product');

		$this->data = array_merge($this->data, $this->request->post);

		// validation
		if (!isset($this->data['title']) || empty($this->data['title']) || strlen($this->data['title']) > 255) {
			$this->error['title'] = (strlen($this->data['title']) > 255) ? $this->data['error_title_length'] : $this->data['error_title_missing'];
		}
		
		if (!isset($this->data['description']) || empty($this->data['description'])) 
			$this->error['title'] = $this->data['error_desc_missing'];

		if (!isset($this->data['price']) || empty($this->data['price'])) 
			$this->error['price'] = $this->data['error_price_missing'];

		if (!isset($data['category_id']) || empty($data['category_id']) || $data['category_id'] == 0) 
			$this->error['category_id'] = $this->language->get('error_category');

		if (isset($data['tags']) && count($data['tags']) > 13) 
			$this->error['tags'] = $this->language->get('error_tags');
	
		if (isset($data['materials']) && count($data['materials']) > 13)
			$this->error['materials'] = $this->language->get('error_materials');
		
		if (isset($this->data['style_1']) && !empty($this->data['style_1'])) {
			if (preg_match('/[^\p{L}\p{Nd}\p{Zs}]/u', $this->data['style_1']) == 1) {
				$this->error['style_1'] = $this->data['error_style_1_tag'];
			}
		}

		if (isset($this->data['style_2']) && !empty($this->data['style_2'])) {
			if (preg_match('/[^\p{L}\p{Nd}\p{Zs}]/u', $this->data['style_2']) == 1) {
				$this->error['style_2'] = $this->data['error_style_2_tag'];
			}
		}

		if ($this->data['quantity'] > 999) 
			$this->error['quantity'] = sprintf($this->data['error_stock_max'], $this->data['quantity']);

		if (count($this->data['product_image']) > 4) 
			$this->error['images'] = sprintf($this->data['error_image_max'], count($this->data['product_image'])+1);

		if (!$this->error) {
			// process the request
			$response = $this->openbay->etsy->call('product/listing/create', 'POST', $this->data);

			$this->response->addHeader('Content-Type: application/json');

			if (isset($response['data']['results'][0]['listing_id'])) {
				$this->model_openbay_etsy_product->addLink($this->data['product_id'], $response['data']['results'][0]['listing_id'], 1);
			}

			if (isset($response['data']['error'])) {
				$this->response->setOutput(json_encode($response['data']));
			} else {
				$this->response->setOutput(json_encode($response['data']['results'][0]));
			}
		} else {
			$this->response->setOutput(json_encode(array('error' => $this->error)));
		}
	}

	public function edit() {
		$this->data = $this->load->language('openbay/etsy_edit');
		$this->load->model('openbay/etsy_product');
		$this->load->model('tool/image');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['action']   = $this->url->link('openbay/etsy_product/editSubmit', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel']   = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['token']    = $this->session->data['token'];

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_etsy'],
							$this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/etsy_product/edit', 'token=' . $this->session->data['token']. '&product_id=' . $this->request->get['product_id'], 'SSL'),
						));
		

		$links = $this->openbay->etsy->getLinks($this->request->get['product_id'], 1, 1);

		$this->data['listing'] = $this->openbay->etsy->getEtsyItem($links[0]['etsy_item_id']);

		$this->data['etsy_item_id'] = $links[0]['etsy_item_id'];
		$this->data['product_id'] = $this->request->get['product_id'];

		$setting['state'] = array('active', 'inactive', 'draft');

		$this->data['setting'] = $setting;

		if ($this->data['listing']['state'] == 'edit') 
			$this->data['listing']['state'] = 'inactive';
		

		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy_edit.tpl', $this->data));
	}

	public function editSubmit() {
		$this->load->language('openbay/etsy_edit');
		$this->load->model('openbay/etsy_product');

		$this->data = $this->request->post;

		// validation
		if (!isset($this->data['title']) || empty($this->data['title']) || strlen($this->data['title']) > 255) {
			$this->error['title'] = (strlen($this->data['title']) > 255) ? $this->data['error_title_length'] : $this->data['error_title_missing'];
		}

		if (!isset($this->data['description']) || empty($this->data['description'])) 
			$this->error['title'] = $this->data['error_desc_missing'];

		if (!isset($this->data['price']) || empty($this->data['price'])) 
			$this->error['price'] = $this->data['error_price_missing'];

		if (!isset($this->data['state']) || empty($this->data['state'])) 
			$this->error['state'] = $this->data['error_state_missing'];

		if (!$this->error) {
			// process the request
			$response = $this->openbay->etsy->call('product/listing/' . $this->data['etsy_item_id'] . '/update', 'POST', $this->data);

			$this->response->addHeader('Content-Type: application/json');

			if (isset($response['data']['error'])) {
				$this->response->setOutput(json_encode($response['data']));
			} else {
				$this->response->setOutput(json_encode($response['data']['results'][0]));
			}
		} else {
			$this->response->setOutput(json_encode(array('error' => $this->error)));
		}
	}

	public function addImage() {
		$this->data = $this->load->language('openbay/etsy_create');

		$this->data = array_merge($this->data, $this->request->post);

		if (!isset($this->data['image']) || empty($this->data['image']))
			$this->error['image'] = $this->data['error_no_img_url'];

		if (!isset($this->data['listing_id']) || empty($this->data['listing_id'])) 
			$this->error['listing_id'] = $this->data['error_no_listing_id'];
			
		if (!$this->error) {
			$response = $this->openbay->etsy->call('product/listing/' . (int)$this->data['listing_id'] . '/image', 'POST', $this->data);

			$this->response->addHeader('Content-Type: application/json');

			if (isset($response['data']['error'])) {
				$this->response->setOutput(json_encode($response['data']));
			} else {
				$this->response->setOutput(json_encode($response['data']['results'][0]));
			}
		}
	}

	public function getCategory() {
		$this->data = $this->request->post;

		$categories = $this->openbay->etsy->call('product/category/getCategory?tag=' . $this->data['tag'], 'GET');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($categories));
	}

	public function getSubCategory() {
		$this->data = $this->request->post;

		$categories = $this->openbay->etsy->call('product/category/findAllTopCategoryChildren?tag=' . $this->data['tag'], 'GET');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($categories));
	}

	public function getSubSubCategory() {
		$this->data = $this->request->post;

		$categories = $this->openbay->etsy->call('product/category/findAllSubCategoryChildren?sub_tag=' . $this->data['sub_tag'], 'GET');

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($categories));
	}

	public function addLink() {
		$this->data = $this->load->language('openbay/etsy_links');
		$this->load->model('openbay/etsy_product');
		$this->load->model('catalog/product');

		$this->data = array_merge($this->data, $this->request->post);

		if (!isset($this->data['product_id'])) {
			echo json_encode(array('error' => $this->data['error_product_id']));
			die();
		}

		if (!isset($this->data['etsy_id'])) {
			echo json_encode(array('error' => $this->data['error_etsy_id']));
			die();
		}

		$links = $this->openbay->etsy->getLinks($this->data['product_id'], 1);

		if ($links != false) {
			echo json_encode(array('error' => $this->data['error_link_exists']));
			die();
		}

		$product = $this->model_catalog_product->getProduct($this->data['product_id']);

		if (!$product) {
			echo json_encode(array('error' => $this->data['error_product']));
			die();
		}

		if ($product['quantity'] <= 0) {
			echo json_encode(array('error' => $this->data['error_stock']));
			die();
		}

		// check the etsy item exists
		$get_response = $this->openbay->etsy->getEtsyItem($this->data['etsy_id']);

		if (isset($get_response['data']['error'])) {
			echo json_encode(array('error' => $this->data['error_etsy'] . $get_response['data']['error']));
			die();
		} else {
			if ((int)$get_response['quantity'] != (int)$product['quantity']) {
				// if the stock is different than the item being linked update the etsy stock level
				$update_response = $this->openbay->etsy->updateListingStock($this->data['etsy_id'], $product['quantity'], $get_response['state']);

				if (isset($update_response['data']['error'])) {
					echo json_encode(array('error' => $this->data['error_etsy'] . $update_response['data']['error']));
					die();
				}
			}
		}

		$this->model_openbay_etsy_product->addLink($this->data['product_id'], $this->data['etsy_id'], 1);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('error' => false)));
	}

	public function deleteLink() {
		$this->data = $this->load->language('openbay/etsy_links');

		$this->data = array_merge($this->data, $this->request->post);

		if (!isset($this->data['etsy_link_id'])) {
			echo json_encode(array('error' => $this->data['error_link_id']));
			die();
		}

		$this->openbay->etsy->deleteLink($this->data['etsy_link_id']);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array('error' => false)));
	}

	public function links() {
		$this->load->model('openbay/etsy_product');

		$this->data = $this->load->language('openbay/etsy_links');

		$this->data['cancel']   = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'], 'SSL');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_etsy'],
							$this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/etsy_product/itemLinks', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$this->data['return']       = $this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL');
		//$this->data['edit_url']     = $this->url->link('openbay/ebay/edit', 'token=' . $this->session->data['token'] . '&product_id=', 'SSL');
		//$this->data['validation']   = $this->openbay->ebay->validate();
		$this->data['token']        = $this->session->data['token'];

		$total_linked = $this->model_openbay_etsy_product->totalLinked();

		if (isset($this->request->get['page'])){
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])){
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 100;
		}

		$pagination = new Pagination();
		$pagination->total = $total_linked;
		$pagination->page = $page;
		$pagination->limit = 100;
		$pagination->text = $this->data['text_pagination'];
		$pagination->url = $this->url->link('openbay/etsy/itemLinks', 'token=' . $this->session->data['token'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['items'] = $this->model_openbay_etsy_product->loadLinked($limit, $page);

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy_links.tpl', $this->data));
	}

	public function listings() {
		$this->data = $this->load->language('openbay/etsy_listings');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->load->model('openbay/etsy_product');

		$this->data['token'] = $this->session->data['token'];

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_etsy'],
							$this->url->link('openbay/etsy', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
							$this->url->link('openbay/etsy_product/itemLinks', 'token=' . $this->session->data['token'], 'SSL'),
						));

		$filter = array();

		$filter['status'] = $this->request->get('status', 'active');
		
		$filter['status'] = $this->request->get('page', 1);

		$filter['limit'] = $this->request->get('limit', 25);

		$filter['limit'] = $this->request->get('limit', 25);
		
		if (isset($this->request->get['keywords'])) 
			$filter['keywords'] = $this->request->get['keywords'];

		$this->data['filter'] = $filter;

		$listing_response = $this->openbay->etsy->call('product/getListings?' . http_build_query($filter), 'GET');
		unset($filter['page']);

		if (isset($listing_response['data']['error'])) {
			$this->data['listings'] = array();
			$this->data['pagination'] = '';
			$this->data['results'] = '';
			$this->error['warning'] = $this->data['error_etsy'] . $listing_response['data']['error'];
		}else {
			$listings = array();

			foreach($listing_response['data']['results'] as $listing) {
				$product_link = $this->openbay->etsy->getLinkedProduct($listing['listing_id']);

				$actions = array();

				if ($filter['status'] == 'inactive') {
					$actions[] = 'activate_item';
				}

				if ($filter['status'] == 'active') {
					$actions[] = 'end_item';
					$actions[] = 'deactivate_item';
				}

				if ($filter['status'] == 'active' && empty($product_link)) {
					$actions[] = 'add_link';
				}

				if (!empty($product_link)) {
					$actions[] = 'delete_link';
				}

				if ($product_link != false) {
					$listings[] = array('link' => $product_link, 'listing' => $listing, 'actions' => $actions);
				} else {
					$listings[] = array('link' => '', 'listing' => $listing, 'actions' => $actions);
				}
			}

			$this->data['listings'] = $listings;

			$pagination = new Pagination();
			$pagination->total = $listing_response['data']['count'];
			$pagination->page = $listing_response['data']['pagination']['effective_page'];
			$pagination->limit = $listing_response['data']['pagination']['effective_limit'];
			$pagination->url = $this->url->link('openbay/etsy_product/listings', 'token=' . $this->session->data['token'] . '&page={page}&' . http_build_query($filter), 'SSL');

			$this->data['pagination'] = $pagination->render();
			$this->data['results'] = sprintf($this->data['text_pagination'], ($listing_response['data']['count']) ? (($listing_response['data']['pagination']['effective_page'] - 1) * $listing_response['data']['pagination']['effective_limit']) + 1 : 0, ((($listing_response['data']['pagination']['effective_page'] - 1) * $listing_response['data']['pagination']['effective_limit']) > ($listing_response['data']['count'] - $listing_response['data']['pagination']['effective_limit'])) ? $listing_response['data']['count'] : ((($listing_response['data']['pagination']['effective_page'] - 1) * $listing_response['data']['pagination']['effective_limit']) + $listing_response['data']['pagination']['effective_limit']), $listing_response['data']['count'], ceil($listing_response['data']['count'] / $listing_response['data']['pagination']['effective_limit']));
		}

		$this->data['success'] = '';

		if (isset($this->request->get['item_ended'])) 
			$this->data['success'] = $this->data['text_item_ended'];

		if (isset($this->request->get['item_activated'])) 
			$this->data['success'] = $this->data['text_item_activated'];
		
		if (isset($this->request->get['item_deactivated'])) 
			$this->data['success'] = $this->data['text_item_deactivated'];
		
		if (isset($this->request->get['link_added'])) 
			$this->data['success'] = $this->data['text_link_added'];
		
		if (isset($this->request->get['link_deleted'])) 
			$this->data['success'] = $this->data['text_link_deleted'];

		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/etsy_listings.tpl', $this->data));
	}

	public function endListing() {
		$this->data = $this->load->language('openbay/etsy_links');

		$this->data = array_merge($this->data, $this->request->post);

		if (!isset($this->data['etsy_item_id'])) {
			echo json_encode(array('error' => $this->data['error_etsy_id']));
			die();
		}

		$response = $this->openbay->etsy->call('product/listing/' . (int)$this->data['etsy_item_id'] . '/delete', 'POST', array());

		if (isset($response['data']['error'])) {
			echo json_encode(array('error' => $this->data['error_etsy'] . $response['data']['error']));
			die();
		} else {
			$linked_item = $this->openbay->etsy->getLinkedProduct($this->data['etsy_item_id']);

			if ($linked_item != false) {
				$this->openbay->etsy->deleteLink($linked_item['etsy_listing_id']);
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(array('error' => false)));
		}
	}

	public function deactivateListing() {
		$this->data = $this->load->language('openbay/etsy_links');

		$this->data = array_merge($this->data, $this->request->post);

		if (!isset($this->data['etsy_item_id'])) {
			echo json_encode(array('error' => $this->data['error_etsy_id']));
			die();
		}

		$response = $this->openbay->etsy->call('product/listing/' . (int)$this->data['etsy_item_id'] . '/inactive', 'POST', array());

		if (isset($response['data']['error'])) {
			echo json_encode(array('error' => $this->data['error_etsy'] . $response['data']['error']));
			die();
		} else {
			$linked_item = $this->openbay->etsy->getLinkedProduct($this->data['etsy_item_id']);

			if ($linked_item != false) {
				$this->openbay->etsy->deleteLink($linked_item['etsy_listing_id']);
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(array('error' => false)));
		}
	}

	public function activateListing() {
		$this->data = $this->load->language('openbay/etsy_links');

		$this->data = array_merge($this->data, $this->request->post);

		$this->response->addHeader('Content-Type: application/json');

		if (!isset($this->data['etsy_item_id'])) {
			echo json_encode(array('error' => $this->data['error_etsy_id']));
			die();
		}

		$response = $this->openbay->etsy->call('product/listing/' . (int)$this->data['etsy_item_id'] . '/active', 'POST', array());

		if (isset($response['data']['error'])) {
			echo json_encode(array('error' => $this->data['error_etsy'] . $response['data']['error']));
			die();
		} else {
			$this->response->setOutput(json_encode(array('error' => false)));
		}
	}
}