<?php
class ControllerCatalogAttribute extends Controller {
	private $error = array();
	
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);
	public function index() {
		$this->data = $this->load->language('catalog/attribute');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/attribute');

		$this->getList();
	}

	public function save() { // Create by Manish in place of add and edit
		$this->data = $this->load->language('catalog/attribute');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/attribute');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['attribute_id'])){
				$this->model_catalog_attribute->editAttribute($this->request->get['attribute_id'], $this->request->post);
			} else{
				$this->model_catalog_attribute->addAttribute($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}	

	public function delete() {
		$this->data = $this->load->language('catalog/attribute');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/attribute');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $attribute_id) {
				$this->model_catalog_attribute->deleteAttribute($attribute_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','ad.name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('catalog/attribute/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/attribute/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['attributes'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$attribute_total = $this->model_catalog_attribute->getTotalAttributes();

		$results = $this->model_catalog_attribute->getAttributes($filter_data);

		foreach ($results as $result) {
			$this->data['attributes'][] = array(
				'attribute_id'    => $result['attribute_id'],
				'name'            => $result['name'],
				'attribute_group' => $result['attribute_group'],
				'sort_order'      => $result['sort_order'],
				'save'            => $this->url->link('catalog/attribute/save', 'token=' . $this->session->data['token'] . '&attribute_id=' . $result['attribute_id'] . $url, 'SSL')
			);
		}
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());

		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ;
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
		
		$this->data['sort_name'] = $this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . '&sort=ad.name' . $url, 'SSL');
		$this->data['sort_attribute_group'] = $this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . '&sort=attribute_group' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . '&sort=a.sort_order' . $url, 'SSL');
		
		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
	
		$pagination = new Pagination();
		$pagination->total = $attribute_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($attribute_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($attribute_total - $this->config->get('config_limit_admin'))) ? $attribute_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $attribute_total, ceil($attribute_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/attribute_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['attribute_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']:array());
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['attribute_id'])) {
			$this->data['action'] = $this->url->link('catalog/attribute/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/attribute/save', 'token=' . $this->session->data['token'] . '&attribute_id=' . $this->request->get['attribute_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/attribute', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['attribute_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($this->request->get['attribute_id']);
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->get['attribute_id']) && !$this->error) {
			$this->data['attribute_description'] = $this->model_catalog_attribute->getAttributeDescriptions($this->request->get['attribute_id']);
		} else {
			$this->data['attribute_description'] = $this->request->post('attribute_description', array());
		}
		
		if (!empty($attribute_info) && !$this->error) {
			$this->data['attribute_group_id'] = $attribute_info['attribute_group_id'];
		} else {
			$this->data['attribute_group_id'] = $this->request->post('attribute_group_id', '');
		}

		$this->load->model('catalog/attribute_group');

		$this->data['attribute_groups'] = $this->model_catalog_attribute_group->getAttributeGroups();

		if (!empty($attribute_info) && !$this->error) {
			$this->data['sort_order'] = $attribute_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order','');
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/attribute_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/attribute')) {
			$this->error['warning'] = $this->data['error_permission'];
			$this->valid_check = false;
		}

		foreach ($this->request->post['attribute_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 64)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
				$this->valid_check = false;
			}
		}
		
		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/attribute')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $attribute_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByAttributeId($attribute_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->data['error_product'], $product_total);
			}
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/attribute');

			$filter_data = array(
				'filter_name' => $this->request->get('filter_name', null),
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_attribute->getAttributes($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'attribute_id'    => $result['attribute_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'attribute_group' => $result['attribute_group']
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}