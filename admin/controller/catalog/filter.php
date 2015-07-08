<?php
class ControllerCatalogFilter extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data =  $this->load->language('catalog/filter');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/filter');

		$this->getList();
	}
	public function save() { // Create by Manish in place of add and edit
		$this->data = $this->load->language('catalog/filter');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/filter');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['filter_group_id'])){
				$this->model_catalog_filter->editFilter($this->request->get['filter_group_id'], $this->request->post);
			} else{
				$this->model_catalog_filter->addFilter($this->request->post);
			}
			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}
	public function delete() {
		$this->data = $this->load->language('catalog/filter');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/filter');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $filter_group_id) {
				$this->model_catalog_filter->deleteFilter($filter_group_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','fgd.name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->data['text_home'],
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->data['heading_title'],
			'href' => $this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		$this->data['save'] = $this->url->link('catalog/filter/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/filter/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['filters'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$filter_total = $this->model_catalog_filter->getTotalFilterGroups();

		$results = $this->model_catalog_filter->getFilterGroups($filter_data);

		foreach ($results as $result) {
			$this->data['filters'][] = array(
				'filter_group_id' => $result['filter_group_id'],
				'name'            => $result['name'],
				'sort_order'      => $result['sort_order'],
				'save'            => $this->url->link('catalog/filter/save', 'token=' . $this->session->data['token'] . '&filter_group_id=' . $result['filter_group_id'] . $url, 'SSL')
			);
		}
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
	
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['selected'] = $this->request->post('selected',array());
		
		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // For Sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
		
		$this->data['sort_name'] = $this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . '&sort=fgd.name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . '&sort=fg.sort_order' . $url, 'SSL');
		
		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $filter_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($filter_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($filter_total - $this->config->get('config_limit_admin'))) ? $filter_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $filter_total, ceil($filter_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/filter_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_group'] =  (isset($this->error['group'])?$this->error['group']:array());

		$this->data['error_filter'] =  (isset($this->error['filter'])?$this->error['filter']:array());
		
		$this->data['text_form'] = !isset($this->request->get['filter_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text' => $this->data['text_home'],
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$this->data['breadcrumbs'][] = array(
			'text' => $this->data['heading_title'],
			'href' => $this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . $url, 'SSL')
		);
		
		if (!isset($this->request->get['filter_group_id'])) {
			$this->data['action'] = $this->url->link('catalog/filter/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/filter/save', 'token=' . $this->session->data['token'] . '&filter_group_id=' . $this->request->get['filter_group_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/filter', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['filter_group_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$filter_group_info = $this->model_catalog_filter->getFilterGroup($this->request->get['filter_group_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['filter_group_id']) && !$this->error) {
			$this->data['filter_group_description'] = $this->model_catalog_filter->getFilterGroupDescriptions($this->request->get['filter_group_id']);
		} else {
			$this->data['filter_group_description'] = $this->request->post('filter_group_description',array());
		}
				
		if (!empty($filter_group_info) && !$this->error) {
			$this->data['sort_order'] = $filter_group_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order','');
		}

		if (isset($this->request->get['filter_group_id']) && !$this->error) {
			$this->data['filters'] = $this->model_catalog_filter->getFilterDescriptions($this->request->get['filter_group_id']);
		} else {
			$this->data['filters'] = $this->request->post('filter', array());
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/filter_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/filter')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
		
		foreach ($this->request->post['filter_group_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 64)) {
				$this->error['group'][$language_id] = $this->data['error_group'];
			}
		}

		if (isset($this->request->post['filter'])) {
			foreach ($this->request->post['filter'] as $filter_id => $filter) {
				foreach ($filter['filter_description'] as $language_id => $filter_description) {
					if ((utf8_strlen($filter_description['name']) < 1) || (utf8_strlen($filter_description['name']) > 64)) {
						$this->error['filter'][$filter_id][$language_id] = $this->data['error_name'];
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/filter')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/filter');

			$filter_this->data = array(
				'filter_name' => $this->request->get('filter_name', null),
				'start'       => 0,
				'limit'       => 5
			);

			$filters = $this->model_catalog_filter->getFilters($filter_this->data);

			foreach ($filters as $filter) {
				$json[] = array(
					'filter_id' => $filter['filter_id'],
					'name'      => strip_tags(html_entity_decode($filter['group'] . ' &gt; ' . $filter['name'], ENT_QUOTES, 'UTF-8'))
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