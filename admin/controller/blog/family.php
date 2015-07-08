<?php
class ControllerBlogFamily extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);
			
	public function index() {
		$this->data = $this->load->language('blog/family');
		
		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/family');
		
		$this->getList();
	}


	public function save() {
		$this->data = $this->load->language('blog/family');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/family');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['family_id'])){
				$this->model_blog_family->editFamily($this->request->get['family_id'], $this->request->post);
			}else{
				$this->model_blog_family->addFamily($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];

		// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('blog/family', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('blog/family');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/family');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $family_id) {
				$this->model_blog_family->deleteFamily($family_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('blog/family', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function repair() {
		$this->data = $this->load->language('blog/family');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/family');

		if ($this->validateRepair()) {
			$this->model_blog_family->repairFamilies();

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('blog/family', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('blog/family', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

	
		$this->data['save'] = $this->url->link('blog/family/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('blog/family/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['repair'] = $this->url->link('blog/family/repair', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['families'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$family_total = $this->model_blog_family->getTotalFamilies();

		$results = $this->model_blog_family->getFamilies($filter_data);

		foreach ($results as $result) {
			$this->data['families'][] = array(
				'family_id' => $result['family_id'],
				'name'        => $result['name'],
				'sort_order'  => $result['sort_order'],
				'save'        => $this->url->link('blog/family/save', 'token=' . $this->session->data['token'] . '&family_id=' . $result['family_id'] . $url, 'SSL'),
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['selected'] =  $this->request->post('selected', array());

		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('blog/family', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('blog/family', 'token=' . $this->session->data['token'] . '&sort=sort_order' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $family_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/family', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($family_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($family_total - $this->config->get('config_limit_admin'))) ? $family_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $family_total, ceil($family_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/family_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['family_id']) ? $this->data['text_add'] : $this->data['text_edit'];
	
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_name'] =  isset($this->error['name']) ? $this->error['name']: '';

		$this->data['error_meta_title'] =  isset($this->error['meta_title']) ? $this->error['meta_title']: array();
		
		$this->data['error_keyword'] =  isset($this->error['keyword']) ? $this->error['keyword']: array();
		
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);
		
			// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('blog/family', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		
		if (!isset($this->request->get['family_id'])) {
			$this->data['action'] = $this->url->link('blog/family/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('blog/family/save', 'token=' . $this->session->data['token'] . '&family_id=' . $this->request->get['family_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('blog/family', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['family_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$family_info = $this->model_blog_family->getFamily($this->request->get['family_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['family_id']) && !$this->error) {
			$this->data['family_description'] = $this->model_blog_family->getFamilyDescriptions($this->request->get['family_id']);
		} else {
			$this->data['family_description'] = $this->request->post('family_description', array());
		}
		
		if (isset($this->request->get['family_id']) && !$this->error) {
			$this->data['family_keyword'] = $this->model_blog_family->getFamilyKeyword($this->request->get['family_id']);
		} else {
			$this->data['family_keyword'] = $this->request->post('family_keyword', array());
		}

		if (!empty($family_info) && !$this->error) {
			$this->data['path'] = $family_info['path'];
		} else {
			$this->data['path'] = $this->request->post('path', '');
		}

		if (!empty($family_info) && !$this->error) {
			$this->data['parent_id'] = $family_info['parent_id'];
		} else {
			$this->data['parent_id'] = $this->request->post('parent_id', 0);
		}

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->get['family_id']) && !$this->error) {
			$this->data['family_store'] = $this->model_blog_family->getFamilyStores($this->request->get['family_id']);
		} else {
			$this->data['family_store'] = $this->request->post('family_store', array(0));
		}

		if (!empty($family_info) && !$this->error) {
			$this->data['image'] = $family_info['image'];
		} else {
			$this->data['image'] = $this->request->post('image', '');
		}

		$this->load->model('tool/image');
		
		if (!empty($family_info) && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($family_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('image', ''), 100, 100);
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (!empty($family_info) && !$this->error) {
			$this->data['sort_order'] = $family_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', 0);
		}

		if (!empty($family_info) && !$this->error) {
			$this->data['status'] = $family_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', true);
		}

		if (isset($this->request->get['family_id']) && !$this->error) {
			$this->data['family_layout'] = $this->model_blog_family->getFamilyLayouts($this->request->get['family_id']);
		} else {
			$this->data['family_layout'] = $this->request->post('family_layout', array());
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/family_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/family')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['family_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->data['error_meta_title'];
			}
		}
		
		foreach ($this->request->post['family_keyword'] as $language_id => $value) {	
			if (utf8_strlen($value['keyword']) > 0) {
				$this->load->model('catalog/url_alias');

				$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($value['keyword']);
	
				if ($url_alias_info && isset($this->request->get['family_id']) && $url_alias_info['query'] != 'family_id=' . $this->request->get['family_id']) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}

				if ($url_alias_info && !isset($this->request->get['family_id'])) {
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
		if (!$this->user->hasPermission('modify', 'blog/family')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	protected function validateRepair() {
		if (!$this->user->hasPermission('modify', 'blog/family')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('blog/family');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_blog_family->getFamilies($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'family_id' => $result['family_id'],
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