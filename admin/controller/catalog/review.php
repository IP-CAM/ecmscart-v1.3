<?php
class ControllerCatalogReview extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'filter_product' => 'encode', // for using these functions urlencode(html_entity_decode($this->get[$item], ENT_QUOTES, 'UTF-8'));
				'filter_author' => 'encode',
				'filter_date_added',
				'filter_status',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('catalog/review');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/review');

		$this->getList();
	}

	public function save() { // Create by Manish in place of add and edit
		$this->data = $this->load->language('catalog/review');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/review');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['review_id'])){
				$this->model_catalog_review->editReview($this->request->get['review_id'], $this->request->post);
			} else{
				$this->model_catalog_review->addReview($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('catalog/review');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/review');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $review_id) {
				$this->model_catalog_review->deleteReview($review_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_product = $this->request->get('filter_product', null);
		
		$filter_author = $this->request->get('filter_author', null);
		
		$filter_date_added = $this->request->get('filter_date_added', null);
		
		$filter_status = $this->request->get('filter_status', null);
		
		$sort = $this->request->get('sort','r.date_added');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->data['save'] = $this->url->link('catalog/review/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/review/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['reviews'] = array();

		$filter_data = array(
			'filter_product'    => $filter_product,
			'filter_author'     => $filter_author,
			'filter_status'     => $filter_status,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$review_total = $this->model_catalog_review->getTotalReviews($filter_data);

		$results = $this->model_catalog_review->getReviews($filter_data);

		foreach ($results as $result) {
			$this->data['reviews'][] = array(
				'review_id'  => $result['review_id'],
				'name'       => $result['name'],
				'author'     => $result['author'],
				'rating'     => $result['rating'],
				'status'     => ($result['status']) ? $this->data['text_enabled'] : $this->data['text_disabled'],
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'save'       => $this->url->link('catalog/review/save', 'token=' . $this->session->data['token'] . '&review_id=' . $result['review_id'] . $url, 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected', array());
		
		// Sorting and Filter Function for paging
		$url_data = array(
				'filter_product' => 'encode', 
				'filter_author' => 'encode',
				'filter_date_added',
				'filter_status',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // For sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_product'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_author'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.author' . $url, 'SSL');
		$this->data['sort_rating'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.rating' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . '&sort=r.date_added' . $url, 'SSL');	
		
		// Sorting and Filter Function for paging
		$url_data = array(
				'filter_product' => 'encode', 
				'filter_author' => 'encode',
				'filter_date_added',
				'filter_status',
				'sort',
				'order', // paging not required
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($review_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($review_total - $this->config->get('config_limit_admin'))) ? $review_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $review_total, ceil($review_total / $this->config->get('config_limit_admin')));

		$this->data['filter_product'] = $filter_product;
		$this->data['filter_author'] = $filter_author;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/review_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['review_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['error_warning'] =  (isset($this->error['warning']) ? $this->error['warning'] : '');
		
		$this->data['error_product'] =  (isset($this->error['product']) ? $this->error['product']: '');
		
		$this->data['error_author'] =  (isset($this->error['author']) ? $this->error['author']: '');
		
		$this->data['error_text'] =  (isset($this->error['text']) ? $this->error['text']: '');
		
		$this->data['error_rating'] =  (isset($this->error['rating']) ? $this->error['rating']: '');

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['review_id'])) {
			$this->data['action'] = $this->url->link('catalog/review/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/review/save', 'token=' . $this->session->data['token'] . '&review_id=' . $this->request->get['review_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/review', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['review_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$review_info = $this->model_catalog_review->getReview($this->request->get['review_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('catalog/product');

		if (!empty($review_info) && !$this->error) {
			$this->data['product_id'] = $review_info['product_id'];
		} else {
			$this->data['product_id'] = $this->request->post('product_id', '');
		}

		if (!empty($review_info) && !$this->error) {
			$this->data['product'] = $review_info['product'];
		} else {
			$this->data['product'] = $this->request->post('product', '');
		}

		if (!empty($review_info) && !$this->error) {
			$this->data['author'] = $review_info['author'];
		} else {
			$this->data['author'] = $this->request->post('author', '');
		}

		if (!empty($review_info) && !$this->error) {
			$this->data['text'] = $review_info['text'];
		} else {
			$this->data['text'] = $this->request->post('text', '');
		}

		if (!empty($review_info) && !$this->error) {
			$this->data['rating'] = $review_info['rating'];
		} else {
			$this->data['rating'] = $this->request->post('rating', '');
		}

		if (!empty($review_info) && !$this->error) {
			$this->data['status'] = $review_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', '');
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/review_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/review')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['product_id']) {
			$this->error['product'] = $this->data['error_product'];
		}

		if ((utf8_strlen($this->request->post['author']) < 3) || (utf8_strlen($this->request->post['author']) > 64)) {
			$this->error['author'] = $this->data['error_author'];
		}

		if (utf8_strlen($this->request->post['text']) < 1) {
			$this->error['text'] = $this->data['error_text'];
		}

		if (!isset($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
			$this->error['rating'] = $this->data['error_rating'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/review')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}