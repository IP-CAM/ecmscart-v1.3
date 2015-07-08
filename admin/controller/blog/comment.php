<?php
class ControllerBlogComment extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'filter_blog' => 'encode',
				'filter_commenter' => 'encode',
				'filter_status',
				'filter_date_added',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('blog/comment');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/comment');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('blog/comment');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/comment');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['comment_id'])){
				$this->model_blog_comment->editComment($this->request->get['comment_id'], $this->request->post);
			}else{
				$this->model_blog_author->addComment($this->request->post);
			}
			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('blog/comment');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('blog/comment');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $comment_id) {
				$this->model_blog_comment->deleteComment($comment_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_blog = $this->request->get('filter_blog', null);
		///first argument is post variable of the tpl file and second argument is default value of the post field in the above function.
		
		$filter_commenter = $this->request->get('filter_commenter', null);

		$filter_date_added = $this->request->get('filter_date_added', null);
		
		$filter_status = $this->request->get('filter_status', null);
		
		$sort = $this->request->get('sort','c.date_added');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		
		$this->data['save'] = $this->url->link('blog/comment/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('blog/comment/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['comments'] = array();

		$filter_data = array(
			'filter_blog'    => $filter_blog,
			'filter_commenter'     => $filter_commenter,
			'filter_status'     => $filter_status,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$comment_total = $this->model_blog_comment->getTotalComments($filter_data);

		$results = $this->model_blog_comment->getComments($filter_data);

		foreach ($results as $result) {
			$this->data['comments'][] = array(
				'comment_id'  => $result['comment_id'],
				'title'       => $result['title'],
				'commenter'     => $result['commenter'],
				'status'     => ($result['status']) ? $this->data['text_enabled'] : $this->data['text_disabled'],
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'save'       => $this->url->link('blog/comment/save', 'token=' . $this->session->data['token'] . '&comment_id=' . $result['comment_id'] . $url, 'SSL')
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
				'filter_blog' => 'encode',
				'filter_commenter' => 'encode',
				'filter_status',
				'filter_date_added',
			);
		
		$url = $this->request->getUrl($url_data);
	
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_blog'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=ad.title' . $url, 'SSL');
		$this->data['sort_commenter'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=c.commenter' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=c.status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . '&sort=c.date_added' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'filter_blog' => 'encode',
				'filter_commenter' => 'encode',
				'filter_status',
				'filter_date_added',
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		$pagination = new Pagination();
		$pagination->total = $comment_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($comment_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($comment_total - $this->config->get('config_limit_admin'))) ? $comment_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $comment_total, ceil($comment_total / $this->config->get('config_limit_admin')));

		$this->data['filter_blog'] = $filter_blog;
		$this->data['filter_commenter'] = $filter_commenter;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/comment_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['comment_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_blog'] =  isset($this->error['blog']) ? $this->error['blog']: '';
		
		$this->data['error_commenter'] =  isset($this->error['commenter']) ? $this->error['commenter']: '';

		$this->data['error_text'] =  isset($this->error['text']) ? $this->error['text']: '';

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['comment_id'])) {
			$this->data['action'] = $this->url->link('blog/comment/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('blog/comment/save', 'token=' . $this->session->data['token'] . '&comment_id=' . $this->request->get['comment_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('blog/comment', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['comment_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$comment_info = $this->model_blog_comment->getComment($this->request->get['comment_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		if (!empty($comment_info) && !$this->error) {
			$this->data['blog_id'] = $comment_info['blog_id'];
		} else {
			$this->data['blog_id'] = $this->request->post('blog_id', '');
		}

		if (!empty($comment_info) && !$this->error) {
			$this->data['blog'] = $comment_info['blog'];
		} else {
			$this->data['blog'] = $this->request->post('blog', '');
		}

		if (!empty($comment_info) && !$this->error) {
			$this->data['commenter'] = $comment_info['commenter'];
		} else {
			$this->data['commenter'] = $this->request->post('commenter', '');
		}

		if (!empty($comment_info) && !$this->error) {
			$this->data['text'] = $comment_info['text'];
		} else {
			$this->data['text'] = $this->request->post('text', '');
		}

		if (!empty($comment_info) && !$this->error) {
			$this->data['status'] = $comment_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', false);
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/comment_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/comment')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['blog_id']) {
			$this->error['blog'] = $this->data['error_blog'];
		}

		if ((utf8_strlen($this->request->post['commenter']) < 3) || (utf8_strlen($this->request->post['commenter']) > 64)) {
			$this->error['commenter'] = $this->data['error_commenter'];
		}

		if (utf8_strlen($this->request->post['text']) < 1) {
			$this->error['text'] = $this->data['error_text'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'blog/comment')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}