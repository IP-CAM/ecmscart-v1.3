<?php
class ControllerOpenbayAmazonusListing extends Controller{
	// sorting and filter array
	private $url_data = array(
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
				'sort',
				'order',
				'page',
			);
			
	public function create() {
		$this->data = $this->load->language('openbay/amazonus_listing');
		$this->load->model('openbay/amazonus_listing');
		$this->load->model('openbay/amazonus');
		$this->load->model('catalog/product');
		$this->load->model('localisation/country');

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		if ($this->request->post) {
			$result = $this->model_openbay_amazonus_listing->simpleListing($this->request->post);

			if ($result['status'] === 1) {
				$this->session->data['success'] = $this->data['text_product_sent'];
				$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL'));
			} else {
				$this->session->data['error'] = sprintf($this->data['text_product_not_sent'], $result['message']);
				$this->response->redirect($this->url->link('openbay/amazonus_listing/create', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->post['product_id'] . $url, 'SSL'));
			}
		}

		if (isset($this->request->get['product_id'])) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
			if (empty($product_info)) {
				$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
			$listing_status = $this->model_openbay_amazonus->getProductStatus($this->request->get['product_id']);
			if ($listing_status === 'processing' || $listing_status === 'ok') {
				$this->response->redirect($this->url->link('openbay/amazonus_listing/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $url, 'SSL'));
			} else if ($listing_status === 'error_advanced' || $listing_status === 'saved' || $listing_status === 'error_few') {
				$this->response->redirect($this->url->link('openbay/amazonus_product', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $url, 'SSL'));
			}
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->data['error_warning'] = isset($this->session->data['error']) ? $this->session->data['error']: '';

		if (isset($this->session->data['error']))			
			unset($this->session->data['error']);

		$this->data['url_return']  = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['url_search']  = $this->url->link('openbay/amazonus_listing/search', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['url_advanced']  = $this->url->link('openbay/amazonus_product', 'token=' . $this->session->data['token'] . '&product_id=' . $this->request->get['product_id'] . $url, 'SSL');

		$this->data['form_action'] = $this->url->link('openbay/amazonus_listing/create', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['sku'] = trim($product_info['sku']);

		if ($this->config->get('openbay_amazonus_listing_tax_added')) {
				$this->data['price'] = $product_info['price'] * (1 + $this->config->get('openbay_amazonus_listing_tax_added') / 100);
			} else {
				$this->data['price'] = $product_info['price'];
			}

		$this->data['listing_errors'] = array();

		if ($listing_status == 'error_quick') {
				$this->data['listing_errors'] = $this->model_openbay_amazonus->getProductErrors($product_info['product_id'], 3);
			}

		$this->data['price'] = number_format($this->data['price'], 2, '.', '');
		$this->data['quantity'] = $product_info['quantity'];

		$this->data['product_id'] = $product_info['product_id'];

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

		$this->data['default_condition'] = $this->config->get('openbay_amazonus_listing_default_condition');

		$this->data['token'] = $this->session->data['token'];

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_amazon'],
							$this->url->link('openbay/amazonus', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
						 	$this->url->link('openbay/amazonus_listing/create', 'token=' . $this->session->data['token'] . $url, 'SSL'),
						));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazonus_listing.tpl', $this->data));
	}

	public function edit() {
		$this->load->model('openbay/amazonus_listing');
		$this->load->model('openbay/amazonus');
		$this->data = $this->load->language('openbay/amazonus_listing');

		$this->document->setTitle($this->data['text_edit_heading']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_openbay'],	// Text to display link
							$this->url->link('extension/openbay', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['text_amazonus'],
							$this->url->link('openbay/amazonus', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],
						 	$this->url->link('openbay/amazonus_listing/edit', 'token=' . $this->session->data['token'] . '&product_id=' . $product_id . $url, 'SSL')
						));

		$status = $this->model_openbay_amazonus->getProductStatus($product_id);

		if ($status === false) {
			$this->response->redirect($this->url->link('openbay/amazonus_listing/create', 'token=' . $this->session->data['token'] . '&product_id=' . $product_id . $url, 'SSL'));
			return;
		}

		$this->data['product_links'] = $this->model_openbay_amazonus->getProductLinks($product_id);
		$this->data['url_return']  = $this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL');
		if ($status == 'ok' || $status == 'linked') {
			$this->data['url_create_new']  = $this->url->link('openbay/amazonus_listing/createNew', 'token=' . $this->session->data['token'] . '&product_id=' . $product_id . $url, 'SSL');
			$this->data['url_delete_links']  = $this->url->link('openbay/amazonus_listing/deleteLinks', 'token=' . $this->session->data['token'] . '&product_id=' . $product_id . $url, 'SSL');
		}
		
		$this->data['has_saved_listings'] = ($status == 'saved')? true: false;

		$this->data['url_saved_listings']  = $this->url->link('openbay/amazonus/savedListings', 'token=' . $this->session->data['token'] . '&product_id=' . $product_id, 'SSL');

		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('openbay/amazonus_listing_edit.tpl', $this->data));
	}

	public function createNew() {
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
		$this->load->model('openbay/amazonus');
		$this->model_openbay_amazonus->deleteProduct($product_id);
		$this->response->redirect($this->url->link('openbay/amazonus_listing/create', 'token=' . $this->session->data['token'] . '&product_id=' . $product_id . $url, 'SSL'));
	}

	public function deleteLinks() {
		$this->data = $this->load->language('openbay/amazonus_listing');
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
		} else {
			$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->load->model('openbay/amazonus');

		$links = $this->model_openbay_amazonus->getProductLinks($product_id);
		foreach ($links as $link) {
			$this->model_openbay_amazonus->removeProductLink($link['amazonus_sku']);
		}

		$this->model_openbay_amazonus->deleteProduct($product_id);
		$this->session->data['success'] = $this->data['text_links_removed'];

		$this->response->redirect($this->url->link('extension/openbay/items', 'token=' . $this->session->data['token'] . $url, 'SSL'));
	}

	public function search() {
		$this->load->model('openbay/amazonus_listing');
		$this->data = $this->load->language('openbay/amazonus_listing');

		$error = '';

		if (empty($this->request->post['search_string'])) {
			$error = $this->data['error_text_missing'];
		}

		if ($error) {
			$response = array(
				'data' => '',
				'error' => $error,
			);
		} else {
			$response = array(
				'data' => $this->model_openbay_amazonus_listing->search($this->request->post['search_string']),
				'error' => '',
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function bestPrice() {
		$this->load->model('openbay/amazonus_listing');
		$this->data = $this->load->language('openbay/amazonus_listing');

		$error = '';

		if (empty($this->request->post['asin'])) {
			$error = $this->data['error_missing_asin'];
		}

		if (empty($this->request->post['condition'])) {
			$error = $this->data['error_condition_missing'];
		}

		if ($error) {
			$response = array(
				'data' => '',
				'error' => $error,
			);
		} else {
			$best_price = $this->model_openbay_amazonus_listing->getBestPrice($this->request->post['asin'], $this->request->post['condition']);

			if ($best_price) {
				$response = array(
					'data' => $best_price,
					'error' => '',
				);
			} else {
				$response = array(
					'data' => '',
					'error' => $this->data['error_amazonus_price'],
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function getProductByAsin() {
		$this->load->model('openbay/amazonus_listing');

		$data = $this->model_openbay_amazonus_listing->getProductByAsin($this->request->post['asin']);

		$response = array(
			'title' => (string)$data['ItemAttributes']['Title'],
			'img' => (!isset($data['ItemAttributes']['SmallImage']['URL']) ? '' : $data['ItemAttributes']['SmallImage']['URL'])
		);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function getBrowseNodes() {
		$this->load->model('openbay/amazonus_listing');

		$data = array(
			'node' => (isset($this->request->post['node']) ? $this->request->post['node'] : ''),
		);

		$response = $this->model_openbay_amazonus_listing->getBrowseNodes($data);

		$this->response->setOutput($response);
	}
}