<?php
class ControllerBlogBlog extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);
			
	public function index() {
		$this->data = $this->load->language('blog/blog');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/blog');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('blog/blog');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/blog');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['blog_id'])){
				$this->model_blog_blog->editBlog($this->request->get['blog_id'], $this->request->post);
			}else{
				$this->model_blog_blog->addBlog($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('blog/blog', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('blog/blog');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/blog');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $blog_id) {
				$this->model_blog_blog->deleteBlog($blog_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('blog/blog', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','bd.title');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
			
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('blog/blog', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

			
		$this->data['save'] = $this->url->link('blog/blog/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('blog/blog/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['blogs'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$blog_total = $this->model_blog_blog->getTotalBlogs();

		$results = $this->model_blog_blog->getBlogs($filter_data);

		foreach ($results as $result) {
			$this->data['blogs'][] = array(
				'blog_id' 	 => $result['blog_id'],
				'title'          => $result['title'],
				'date_added'     => $result['date_added'],
				'author'     	 => $result['author'],
				'sort_order'     => $result['sort_order'],
				'save'           => $this->url->link('blog/blog/save', 'token=' . $this->session->data['token'] . '&blog_id=' . $result['blog_id'] . $url, 'SSL')
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


		$this->data['sort_title'] = $this->url->link('blog/blog', 'token=' . $this->session->data['token'] . '&sort=bd.title' . $url, 'SSL');
		$this->data['sort_author'] = $this->url->link('blog/blog', 'token=' . $this->session->data['token'] . '&sort=author' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('blog/blog', 'token=' . $this->session->data['token'] . '&sort=b.sort_order' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('blog/blog', 'token=' . $this->session->data['token'] . '&sort=b.date_added' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $blog_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/blog', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($blog_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($blog_total - $this->config->get('config_limit_admin'))) ? $blog_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $blog_total, ceil($blog_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
	

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/blog_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['blog_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_title'] =  isset($this->error['title']) ? $this->error['title']: array();
		
		$this->data['error_author'] =  isset($this->error['author']) ? $this->error['author']: '';
		
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
							$this->url->link('blog/blog', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['blog_id'])) {
			$this->data['action'] = $this->url->link('blog/blog/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('blog/blog/save', 'token=' . $this->session->data['token'] . '&blog_id=' . $this->request->get['blog_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('blog/blog', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['blog_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$blog_info = $this->model_blog_blog->getBlog($this->request->get['blog_id']);
		}
		
		$this->data['blog_id'] = isset($this->request->get['blog_id']) ? $this->request->get['blog_id'] : '';
		
		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['blog_id']) && !$this->error) {
			$this->data['blog_description'] = $this->model_blog_blog->getBlogDescriptions($this->request->get['blog_id']);
		} else {
			$this->data['blog_description'] = $this->request->post('blog_description', array());
		}
		
		if (isset($this->request->get['blog_id']) && !$this->error) {
			$this->data['blog_keyword'] = $this->model_blog_blog->getBlogKeyword($this->request->get['blog_id']);
		} else {
			$this->data['blog_keyword'] = $this->request->post('blog_keyword', array());
		}

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->get['blog_id']) && !$this->error) {
			$this->data['blog_store'] = $this->model_blog_blog->getBlogStores($this->request->get['blog_id']);
		} else {
			$this->data['blog_store'] = $this->request->post('blog_store', array(0));
		}
		
		if (isset($this->request->get['blog_id']) && !$this->error) {
			$blogs = $this->model_blog_blog->getBlogRelated($this->request->get['blog_id']);
		} else {
			$blogs = $this->request->post('blog_related', array());
		}

		$this->data['blog_relateds'] = array();

		foreach ($blogs as $blog_id) {
			$related_info = $this->model_blog_blog->getBlogTitle($blog_id);

			if ($related_info) {
				$this->data['blog_relateds'][] = array(
					'blog_id' 	=> $blog_id,
					'title'     => $related_info['title']
				);
			}
		}			

		if (isset($this->request->get['blog_id']) && !$this->error) {
			$families = $this->model_blog_blog->getBlogFamily($this->request->get['blog_id']);
		} else {
			$families = $this->request->post('blog_family', array());
		}

		$this->data['blog_families'] = array();
				
		$this->load->model('blog/family');
		foreach ($families as $family_id) {
			$family_info = $this->model_blog_family->getFamilyName($family_id);

			if ($family_info) {
				$this->data['blog_families'][] = array(
					'family_id' 	=> $family_info['family_id'],
					'name'       	=> $family_info['name']
				);
			}
		}	

		$this->load->model('blog/author');

		if (!empty($blog_info) && !$this->error) {
			$this->data['author_id'] = $blog_info['author_id'];
		} else {
			$this->data['author_id'] = $this->request->post('author_id', 0);
		}

		if (!empty($blog_info) && !$this->error) {
			$author_info = $this->model_blog_author->getAuthor($blog_info['author_id']);

			if ($author_info) {
				$this->data['author'] = $author_info['name'];
			} else {
				$this->data['author'] = '';
			}
		} else {
			$this->data['author'] = $this->request->post('author', '');
		}
		
		$this->data['date_modified'] = !empty($blog_info) ? $blog_info['date_modified'] : '';
		
		if (!empty($blog_info) && !$this->error) {
			$this->data['comments'] = $blog_info['comments'];
		} else {
			$this->data['comments'] = $this->request->post('comments', 0);
		}

		if (!empty($blog_info)) {
			$this->data['status'] = $blog_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', true);
		}

		if (!empty($blog_info) && !$this->error) {
			$this->data['sort_order'] = $blog_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', '');
		}		
		
		$this->load->model('tool/image');
		
		if (!empty($blog_info) && !$this->error) {
			$this->data['image'] = $blog_info['image'];
		} else {
			$this->data['image'] = $this->request->post('image', '');
		}
		
		if (!empty($blog_info) && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($blog_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('image', ''), 100, 100);
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		
		if (isset($this->request->get['blog_id']) && !$this->error) {
			$this->data['blog_layout'] = $this->model_blog_blog->getBlogLayouts($this->request->get['blog_id']);
		} else {
			$this->data['blog_layout'] = $this->request->post('blog_layout', array());
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/blog_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/blog')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
		
		if (!$this->request->post['author_id']) {
				$this->error['author'] = $this->data['error_author'];
			}

		foreach ($this->request->post['blog_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->data['error_title'];
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->data['error_description'];
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->data['error_meta_title'];
			}
		}

		foreach ($this->request->post['blog_keyword'] as $language_id => $value) {	
			if (utf8_strlen($value['keyword']) > 0) {
				$this->load->model('catalog/url_alias');

				$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($value['keyword']);
	
				if ($url_alias_info && isset($this->request->get['blog_id']) && $url_alias_info['query'] != 'blog_id=' . $this->request->get['blog_id']) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}

				if ($url_alias_info && !isset($this->request->get['blog_id'])) {
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
		if (!$this->user->hasPermission('modify', 'blog/blog')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
		return !$this->error;
	}
	
	public function autocomplete() {
		$json = array();
			
		if (isset($this->request->get['filter_name'])) {
			$this->load->model('blog/blog');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'title',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 5
			);
			

			$results = $this->model_blog_blog->getBlogs($filter_data, $this->request->get('blog_id', 0));

			foreach ($results as $result) {
				$json[] = array(
					'blog_id' => $result['blog_id'],
					'title'            => strip_tags(html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['title'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}