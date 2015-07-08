<?php
class ControllerCatalogCategory extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('catalog/category');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/category');

		$this->getList();
	}

	public function save() { // Create by Manish in place of add and edit
		$this->data = $this->load->language('catalog/category');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/category');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			
			if(isset($this->request->get['category_id'])){
				$this->model_catalog_category->editCategory($this->request->get['category_id'], $this->request->post);
			} else{
				$this->model_catalog_category->addCategory($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/category', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('catalog/category');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/category');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $category_id) {
				$this->model_catalog_category->deleteCategory($category_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/category', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function repair() {
		$this->data = $this->load->language('catalog/category');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/category');

		if ($this->validateRepair()) {
			$this->model_catalog_category->repairCategories();

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('catalog/category', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'name');
		
		$order = $this->request->get('order', 'ASC');
		
		$page = $this->request->get('page', 1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/category', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('catalog/category/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/category/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['repair'] = $this->url->link('catalog/category/repair', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['categories'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$category_total = $this->model_catalog_category->getTotalCategories();

		$results = $this->model_catalog_category->getCategories($filter_data);

		foreach ($results as $result) {
			$this->data['categories'][] = array(
				'category_id' => $result['category_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'save'        => $this->url->link('catalog/category/save', 'token=' . $this->session->data['token'] . '&category_id=' . $result['category_id'] . $url, 'SSL'),
				'delete'      => $this->url->link('catalog/category/delete', 'token=' . $this->session->data['token'] . '&category_id=' . $result['category_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());
		
		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // for sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
		
		$this->data['sort_name'] = $this->url->link('catalog/category', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/category', 'token=' . $this->session->data['token'] . '&sort=sort_order' . $url, 'SSL');
		
		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $category_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/category', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($category_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($category_total - $this->config->get('config_limit_admin'))) ? $category_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $category_total, ceil($category_total / $this->config->get('config_limit_admin')));

		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/category_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['category_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning'] : '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name'] : array());

		$this->data['error_meta_title'] =  (isset($this->error['meta_title'])? $this->error['meta_title'] : array());
		
		$this->data['error_keyword'] =  isset($this->error['keyword']) ? $this->error['keyword']: array();
		
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/category', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['category_id'])) {
			$this->data['action'] = $this->url->link('catalog/category/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/category/save', 'token=' . $this->session->data['token'] . '&category_id=' . $this->request->get['category_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/category', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$category_info = $this->model_catalog_category->getCategory($this->request->get['category_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['category_id']) && !$this->error) {
			$this->data['category_description'] = $this->model_catalog_category->getCategoryDescriptions($this->request->get['category_id']);
		} else {
			$this->data['category_description'] =  $this->request->post('category_description', array());
		}
		
		if (isset($this->request->get['category_id']) && !$this->error) {
			$this->data['category_keyword'] = $this->model_catalog_category->getCategoryKeyword($this->request->get['category_id']);
		} else {
			$this->data['category_keyword'] = $this->request->post('category_keyword', array());
		}
		
		if (!empty($category_info) && !$this->error) {
			$this->data['path'] = $category_info['path'];
		} else {
			$this->data['path'] =  $this->request->post('path', '');
		}

		if (!empty($category_info) && !$this->error) {
			$this->data['parent_id'] = $category_info['parent_id'];
		} else {
			$this->data['parent_id'] = $this->request->post('parent_id',0);
		}

		$this->load->model('catalog/filter');

		if (isset($this->request->get['category_id']) && !$this->error) {
			$filters = $this->model_catalog_category->getCategoryFilters($this->request->get['category_id']);
		} else {
			$filters = $this->request->post('category_filter',array());
		}

		$this->data['category_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$this->data['category_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->get['category_id']) && !$this->error) {
			$this->data['category_store'] = $this->model_catalog_category->getCategoryStores($this->request->get['category_id']);
		} else {
			$this->data['category_store'] = $this->request->post('category_store',array(0));
		}

		if (!empty($category_info) && !$this->error) {
			$this->data['image'] = $category_info['image'];
		} else {
			$this->data['image'] =  $this->request->post('image','');
		}

		$this->load->model('tool/image');

		if (!empty($category_info) && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($category_info['image'], 100, 100);
		} else {
			$this->data['thumb'] =  $this->model_tool_image->resize($this->request->post('image',''), 100, 100);
		}
		
		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (!empty($category_info) && !$this->error) {
			$this->data['top'] = $category_info['top'];
		} else {
			$this->data['top'] = $this->request->post('top',0);
		}

		if (!empty($category_info) && !$this->error) {
			$this->data['column'] = $category_info['column'];
		} else {
			$this->data['column'] = $this->request->post('column',1);
		}

		if (!empty($category_info) && !$this->error) {
			$this->data['sort_order'] = $category_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order',0);
		}

		if (!empty($category_info) && !$this->error) {
			$this->data['status'] = $category_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status',true);
		}

		if (isset($this->request->get['category_id']) && !$this->error) {
			$this->data['category_layout'] = $this->model_catalog_category->getCategoryLayouts($this->request->get['category_id']);
		} else {
			$this->data['category_layout'] = $this->request->post('category_layout',array());
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/category_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['category_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->data['error_meta_title'];
			}
		}
		
		foreach ($this->request->post['category_keyword'] as $language_id => $value) {	
			if (utf8_strlen($value['keyword']) > 0) {
				$this->load->model('catalog/url_alias');

				$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($value['keyword']);
	
				if ($url_alias_info && isset($this->request->get['category_id']) && $url_alias_info['query'] != 'category_id=' . $this->request->get['category_id']) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}

				if ($url_alias_info && !isset($this->request->get['category_id'])) {
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
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'catalog/category')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$filter_data = array(
				'filter_name' => $this->request->get('filter_name', null),
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_catalog_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
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