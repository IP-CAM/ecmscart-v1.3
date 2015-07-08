<?php
class ControllerUserApi extends Controller {
	private $error = array();
	
	private $url_data = array(//array for paging
				'sort' ,
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('user/api');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('user/api');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('user/api');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('user/api');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['api_id'])){
				$this->model_user_api->editApi($this->request->get['api_id'], $this->request->post);
			} else{
				$this->model_user_api->addApi($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('user/api', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('user/api');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('user/api');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $api_id) {
				$this->model_user_api->deleteApi($api_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('user/api', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'username');
		
		$order = $this->request->get('order', 'ASC');

		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('user/api', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('user/api/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('user/api/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['apis'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$user_total = $this->model_user_api->getTotalApis();

		$results = $this->model_user_api->getApis($filter_data);

		foreach ($results as $result) {
			$this->data['apis'][] = array(
				'api_id'     => $result['api_id'],
				'username'   => $result['username'],
				'status'     => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'save'       => $this->url->link('user/api/save', 'token=' . $this->session->data['token'] . '&api_id=' . $result['api_id'] . $url, 'SSL')
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

		$this->data['sort_username'] = $this->url->link('user/api', 'token=' . $this->session->data['token'] . '&sort=username' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('user/api', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('user/api', 'token=' . $this->session->data['token'] . '&sort=date_added' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('user/api', 'token=' . $this->session->data['token'] . '&sort=date_modified' . $url, 'SSL');
		
		$url_data = array(//array for paging
				'sort' ,
				'order',	
			);	
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $user_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('user/api', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($user_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($user_total - $this->config->get('config_limit_admin'))) ? $user_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $user_total, ceil($user_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('user/api_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['api_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_username'] =  (isset($this->error['username'])?$this->error['username']:'');

		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');

		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('user/api', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['api_id'])) {
			$this->data['action'] = $this->url->link('user/api/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('user/api/save', 'token=' . $this->session->data['token'] . '&api_id=' . $this->request->get['api_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('user/api', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['api_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$api_info = $this->model_user_api->getApi($this->request->get['api_id']);
		}
		
		if (!empty($api_info) && !$this->error) {
			$this->data['username'] = $api_info['username'];
		} else {
			$this->data['username'] = $this->request->post('username', '');
		}
		
		if (!empty($api_info) && !$this->error) {
			$this->data['password'] = $api_info['password'];
		} else {
			$this->data['password'] = $this->request->post('password', '');
		}
		
		if (!empty($api_info) && !$this->error) {
			$this->data['status'] = $api_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', 0);
		}
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('user/api_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'user/user')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen(trim($this->request->post['username'])) < 3) || (utf8_strlen(trim($this->request->post['username'])) > 64)) {
			$this->error['username'] = $this->data['error_username'];
		}

		if ((utf8_strlen($this->request->post['password']) < 3) || (utf8_strlen($this->request->post['password']) > 256)) {
			$this->error['password'] = $this->data['error_password'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'user/api')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}