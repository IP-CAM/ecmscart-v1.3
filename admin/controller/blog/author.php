<?php
class ControllerBlogAuthor extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('blog/author');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/author');
	
		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('blog/author');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/author');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['author_id'])){
				$this->model_blog_author->editAuthor($this->request->get['author_id'], $this->request->post);
			}else{
				$this->model_blog_author->addAuthor($this->request->post);
			}
			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('blog/author', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('blog/author');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/author');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $author_id) {
				$this->model_blog_author->deleteAuthor($author_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('blog/author', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
							$this->url->link('blog/author', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('blog/author/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('blog/author/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['authors'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$author_total = $this->model_blog_author->getTotalAuthors();

		$results = $this->model_blog_author->getAuthors($filter_data);


		foreach ($results as $result) {
			$this->data['authors'][] = array(
				'author_id'      => $result['author_id'],
				'name'           => $result['name'],
				'date_added'     => $result['date_added'],
				'sort_order'     => $result['sort_order'],
				'save'           => $this->url->link('blog/author/save', 'token=' . $this->session->data['token'] . '&author_id=' . $result['author_id'] . $url, 'SSL')
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

		$this->data['sort_name'] = $this->url->link('blog/author', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('blog/author', 'token=' . $this->session->data['token'] . '&sort=a.sort_order' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('blog/author', 'token=' . $this->session->data['token'] . '&sort=a.date_added' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $author_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/author', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($author_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($author_total - $this->config->get('config_limit_admin'))) ? $author_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $author_total, ceil($author_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
	
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('blog/author_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['author_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_name'] =  isset($this->error['name']) ? $this->error['name']: '';
		
		$this->data['error_description'] =  isset($this->error['description']) ? $this->error['description']: array();

		$this->data['error_meta_title'] =  isset($this->error['meta_title']) ? $this->error['meta_title']: array();
		
		$this->data['error_keyword'] =  isset($this->error['keyword']) ? $this->error['keyword']: array();
		
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('blog/author', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['author_id'])) {
			$this->data['action'] = $this->url->link('blog/author/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('blog/author/save', 'token=' . $this->session->data['token'] . '&author_id=' . $this->request->get['author_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('blog/author', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['author_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$author_info = $this->model_blog_author->getAuthor($this->request->get['author_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['author_id']) && !$this->error) {
			$this->data['author_description'] = $this->model_blog_author->getAuthorDescriptions($this->request->get['author_id']);
		} else {
			$this->data['author_description'] = $this->request->post('author_description', array());
		}
		
		if (isset($this->request->get['author_id']) && !$this->error) {
			$this->data['author_keyword'] = $this->model_blog_author->getAuthorKeyword($this->request->get['author_id']);
		} else {
			$this->data['author_keyword'] = $this->request->post('author_keyword', array());
		}

		if (!empty($author_info) && !$this->error) {
			$this->data['name'] = $author_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}
			
		$this->data['date_modified'] = (!empty($author_info)) ? $author_info['date_modified'] : '';
		
		if (!empty($author_info) && !$this->error) {
			$this->data['status'] = $author_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', true);
		}

		if (!empty($author_info) && !$this->error) {
			$this->data['sort_order'] = $author_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', '');
		}
		
		if (!empty($author_info) && !$this->error) {
			$this->data['image'] = $author_info['image'];
		} else {
			$this->data['image'] = $this->request->post('image', '');
		}

		$this->load->model('tool/image');

		if (!empty($author_info) && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($author_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('image', ''), 100, 100);
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('blog/author_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/author')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->data['error_name'];
		}
			
		foreach ($this->request->post['author_description'] as $language_id => $value) {
			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->data['error_description'];
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->data['error_meta_title'];
			}
		}

		foreach ($this->request->post['author_keyword'] as $language_id => $value) {	
			if (utf8_strlen($value['keyword']) > 0) {
				$this->load->model('catalog/url_alias');

				$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($value['keyword']);
	
				if ($url_alias_info && isset($this->request->get['author_id']) && $url_alias_info['query'] != 'author_id=' . $this->request->get['author_id']) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}

				if ($url_alias_info && !isset($this->request->get['author_id'])) {
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
		if (!$this->user->hasPermission('modify', 'blog/author')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
		return !$this->error;
	}
	
	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('blog/author');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 5
			);

			$results = $this->model_blog_author->getAuthors($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'author_id' => $result['author_id'],
					'name'            => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
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