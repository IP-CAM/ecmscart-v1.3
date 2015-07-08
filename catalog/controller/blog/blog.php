<?php
class ControllerBlogBlog extends Controller {
	public function index() {
		
		$this->data = $this->load->language('blog/blog');

		$this->load->model('blog/blog');

		$this->load->model('tool/image');

		$this->document->setTitle($this->data['heading_title']);

		$this->data['text_login'] = sprintf($this->data['text_login'], $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'));	
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							$this->data['text_blog_category'],	// Text to display link
							$this->url->link('blog/family')
						));	
		
		if ($this->config->get('config_blog_comment_guest') || $this->customer->isLogged()) {
			$this->data['comment_guest'] = true;
		} else {
			$this->data['comment_guest'] = false;
		}
			
		if ($this->customer->isLogged()) {
			$this->data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
		} else {
			$this->data['customer_name'] = '';
		}
				
		$blog_id = (int)$this->request->get('blog_id',0);
				
		$result = $this->model_blog_blog->getBlog($blog_id);
				
		if ($result) {			
			$this->document->setTitle($result['meta_title']);
			$this->document->setDescription($result['meta_description']);
			$this->document->setKeywords($result['meta_keyword']);
			$this->document->addLink($this->url->link('blog/blog', 'blog_id=' . $this->request->get['blog_id']), 'canonical');
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							$this->data['text_blog_category'],	// Text to display link
							$this->url->link('blog/family'),
							$result['title'],	// Text to display link
							$this->url->link('blog/blog', 'blog_id=' .  $blog_id)
							
						));	
			
			$this->data['blog_id'] = $blog_id;
			
			$this->load->model('blog/comment');
			
			$this->data['text_comments'] = sprintf($this->data['text_comments'], $this->model_blog_comment->getTotalCommentsByBlogId($blog_id));
					
			if ($this->config->get('config_blog_comment_status') && $result['comments']) {
				$this->data['comment_status'] = true;
			} else {
				$this->data['comment_status'] = false;
			}
			
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_blog_width'), $this->config->get('config_image_blog_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_blog_width'), $this->config->get('config_image_blog_height'));
			}
					
			$this->data['blog']= array(
				'title' 		=> $result['title'],
				'image'	 		=> $image,
				'description' 	=> html_entity_decode($result['description'],ENT_QUOTES, 'UTF-8'),
				'name' 			=> $result['name'],
				'author_href' 	=> $this->url->link('blog/author', 'author_id=' . $result['author_id']),
				'date_added' 	=> $result['date_added']
			);

			$this->data['continue'] = $this->url->link('common/home');

			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['column_right'] = $this->load->controller('common/column_right');
			$this->data['content_top'] = $this->load->controller('common/content_top');
			$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/blog.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/blog/blog.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/blog/blog.tpl', $this->data));
			}
		} else {
			$this->data['breadcrumbs'][] = array(
							$this->data['text_error'],	// Text to display link
							 $this->url->link('blog/blog', 'blog_id=' .  $blog_id)
						);
			
			$this->document->setTitle($this->data['text_error']);

			$this->data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['column_right'] = $this->load->controller('common/column_right');
			$this->data['content_top'] = $this->load->controller('common/content_top');
			$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/error/not_found.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/error/not_found.tpl', $this->data));
			}
		}
	}
	
	public function comment() {
		$this->data = $this->load->language('blog/blog');

		$this->load->model('blog/comment');

		$this->data['text_no_comments'] = $this->data['text_no_comments'];

		$page = $this->request->get('page',1);
		
		$this->data['comments'] = array();

		$comment_total = $this->model_blog_comment->getTotalCommentsByBlogId($this->request->get['blog_id']);

		$results = $this->model_blog_comment->getCommentsByBlogId($this->request->get['blog_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$this->data['comments'][] = array(
				'commenter'     => $result['commenter'],
				'text'       => nl2br($result['text']),
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $comment_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('blog/blog/comment', 'blog_id=' . $this->request->get['blog_id'] . '&page={page}');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($comment_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($comment_total - 5)) ? $comment_total : ((($page - 1) * 5) + 5), $comment_total, ceil($comment_total / 5));

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/comment.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/blog/comment.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/blog/comment.tpl', $this->data));
		}
	}


	public function write() {
		$this->data = $this->load->language('blog/blog');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['commenter']) < 3) || (utf8_strlen($this->request->post['commenter']) > 25)) {
				$json['error'] = $this->data['error_commenter'];
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->data['error_text'];
			}

			if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
				$json['error'] = $this->data['error_captcha'];
			}

			unset($this->session->data['captcha']);

			if (!isset($json['error'])) {
				$this->load->model('blog/comment');

				$this->model_blog_comment->addComment($this->request->get['blog_id'], $this->request->post);

				$json['success'] = $this->data['text_success'];
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
 }