<?php
class ControllerUserUser extends Controller {
	private $error = array();
	
	private $url_data = array(//array for paging
				'sort' ,
				'order',
				'page',
			);
	
	public function index() {
		$this->data = $this->load->language('user/user');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('user/user');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('user/user');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('user/user');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['user_id'])){
				$this->model_user_user->editUser($this->request->get['user_id'], $this->request->post);
			} else{
				$this->model_user_user->addUser($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('user/user');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('user/user');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $user_id) {
				$this->model_user_user->deleteUser($user_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		
		$sort = $this->request->get('sort', 'username');
		
		$order = $this->request->get('order', 'ASC');
		
		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(
					array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						)
				);
		
		$this->data['save'] = $this->url->link('user/user/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('user/user/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['users'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$user_total = $this->model_user_user->getTotalUsers();

		$results = $this->model_user_user->getUsers($filter_data);

		foreach ($results as $result) {
			$this->data['users'][] = array(
				'user_id'    => $result['user_id'],
				'username'   => $result['username'],
				'status'     => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'save'       => $this->url->link('user/user/save', 'token=' . $this->session->data['token'] . '&user_id=' . $result['user_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['selected'] =  $this->request->post('selected', array());

		$url_data = array(//array for paging
				'sort' ,
				'order',
				'page',
			);

		$url = $this->request->getUrl($url_data);
		
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; 
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_username'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . '&sort=username' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . '&sort=date_added' . $url, 'SSL');

		$url_data = array(//array for paging
				'sort' ,
				'order',
			);	
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $user_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($user_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($user_total - $this->config->get('config_limit_admin'))) ? $user_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $user_total, ceil($user_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('user/user_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['user_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_username'] =  (isset($this->error['username'])?$this->error['username']:'');
		
		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');
		
		$this->data['error_confirm'] =  (isset($this->error['confirm'])?$this->error['confirm']:'');
		
		$this->data['error_firstname'] =  (isset($this->error['firstname'])?$this->error['firstname']:'');
		
		$this->data['error_lastname'] =  (isset($this->error['lastname'])?$this->error['lastname']:'');
		
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['user_id'])) {
			$this->data['action'] = $this->url->link('user/user/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('user/user/save', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('user/user', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['user_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$user_info = $this->model_user_user->getUser($this->request->get['user_id']);
		}
		
		if (!empty($user_info) && !$this->error) {
			$this->data['username'] = $user_info['username'];
		} else {
			$this->data['username'] = $this->request->post('username', '');
		}
		if (!empty($user_info) && !$this->error) {
			$this->data['user_group_id'] = $user_info['user_group_id'];
		} else {
			$this->data['user_group_id'] = $this->request->post('user_group_id', '');
		}
		
		$this->load->model('user/user_group');

		$this->data['user_groups'] = $this->model_user_user_group->getUserGroups();
		
		$this->data['password'] = $this->request->post('password', '');
		
		$this->data['confirm'] = $this->request->post('confirm', '');

		if (!empty($user_info) && !$this->error) {
			$this->data['firstname'] = $user_info['firstname'];
		} else {
			$this->data['firstname'] = $this->request->post('firstname', '');
		}
		if (!empty($user_info) && !$this->error) {
			$this->data['lastname'] = $user_info['lastname'];
		} else {
			$this->data['lastname'] = $this->request->post('lastname', '');
		}
		if (!empty($user_info) && !$this->error) {
			$this->data['email'] = $user_info['email'];
		} else {
			$this->data['email'] = $this->request->post('email', '');
		}
		if (!empty($user_info) && !$this->error) {
			$this->data['image'] = $user_info['image'];
		} else {
			$this->data['image'] = $this->request->post('image', '');
		}
		
		$this->load->model('tool/image');
		
		if (!empty($user_info) && $user_info['image']  && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($user_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('image', ''), 100, 100); 
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		if (!empty($user_info) && !$this->error) {
			$this->data['status'] = $user_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', 0);
		}
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('user/user_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'user/user')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['username']) < 3) || (utf8_strlen($this->request->post['username']) > 20)) {
			$this->error['username'] = $this->data['error_username'];
		}

		$user_info = $this->model_user_user->getUserByUsername($this->request->post['username']);

		if (!isset($this->request->get['user_id'])) {
			if ($user_info) {
				$this->error['warning'] = $this->data['error_exists'];
			}
		} else {
			if ($user_info && ($this->request->get['user_id'] != $user_info['user_id'])) {
				$this->error['warning'] = $this->data['error_exists'];
			}
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->data['error_firstname'];
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->data['error_lastname'];
		}

		if ($this->request->post['password'] || (!isset($this->request->get['user_id']))) {
			if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
				$this->error['password'] = $this->data['error_password'];
			}

			if ($this->request->post['password'] != $this->request->post['confirm']) {
				$this->error['confirm'] = $this->data['error_confirm'];
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'user/user')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['selected'] as $user_id) {
			if ($this->user->getId() == $user_id) {
				$this->error['warning'] = $this->data['error_account'];
			}
		}

		return !$this->error;
	}
}