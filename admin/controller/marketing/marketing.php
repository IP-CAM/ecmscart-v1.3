<?php
class ControllerMarketingMarketing extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'filter_name' => 'encode', // for using these functions urlencode(html_entity_decode($this->get[$item], ENT_QUOTES, 'UTF-8'));
				'filter_code',
				'filter_date_added',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('marketing/marketing');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/marketing');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('marketing/marketing');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/marketing');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['marketing_id'])){
				$this->model_marketing_marketing->editMarketing($this->request->get['marketing_id'], $this->request->post);
			} else{
				$this->model_marketing_marketing->addMarketing($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('marketing/marketing');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/marketing');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $marketing_id) {
				$this->model_marketing_marketing->deleteMarketing($marketing_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_name = $this->request->get('filter_name', null);
		
		$filter_code = $this->request->get('filter_code', null);
		
		$filter_date_added = $this->request->get('filter_date_added', null);		
		
		$sort = $this->request->get('sort', 'name');
		
		$order = $this->request->get('order', 'ASC');
		
		$page = $this->request->get('page', 1);
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('marketing/marketing/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('marketing/marketing/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['marketings'] = array();

		$filter_data = array(
			'filter_name'       => $filter_name,
			'filter_code'       => $filter_code,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$marketing_total = $this->model_marketing_marketing->getTotalMarketings($filter_data);

		$results = $this->model_marketing_marketing->getMarketings($filter_data);

		foreach ($results as $result) {
			$this->data['marketings'][] = array(
				'marketing_id' => $result['marketing_id'],
				'name'         => $result['name'],
				'code'         => $result['code'],
				'clicks'       => $result['clicks'],
				'orders'       => $result['orders'],
				'date_added'   => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'save'         => $this->url->link('marketing/marketing/save', 'token=' . $this->session->data['token'] . '&marketing_id=' . $result['marketing_id'] . $url, 'SSL')
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
				'filter_name' => 'encode', 
				'filter_code',
				'filter_date_added',
			);
		
		$url = $this->request->getUrl($url_data);

		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . '&sort=m.name' . $url, 'SSL');
		$this->data['sort_code'] = $this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . '&sort=m.code' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . '&sort=m.date_added' . $url, 'SSL');

		// Sorting and Filter Function for filter variable again
		$url_data = array(
				'filter_name' => 'encode', 
				'filter_code',
				'filter_date_added',
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $marketing_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($marketing_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($marketing_total - $this->config->get('config_limit_admin'))) ? $marketing_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $marketing_total, ceil($marketing_total / $this->config->get('config_limit_admin')));

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_code'] = $filter_code;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/marketing_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['marketing_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']:'');
		
		$this->data['error_code'] =  (isset($this->error['code'])?$this->error['code']:'');

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['marketing_id'])) {
			$this->data['action'] = $this->url->link('marketing/marketing/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('marketing/marketing/save', 'token=' . $this->session->data['token'] . '&marketing_id=' . $this->request->get['marketing_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('marketing/marketing', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['marketing_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$marketing_info = $this->model_marketing_marketing->getMarketing($this->request->get['marketing_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['store'] = HTTP_CATALOG;

		if (!empty($marketing_info) && !$this->error) {
			$this->data['name'] = $marketing_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($marketing_info) && !$this->error) {
			$this->data['description'] = $marketing_info['description'];
		} else {
			$this->data['description'] = $this->request->post('description', '');
		}

		if (!empty($marketing_info) && !$this->error) {
			$this->data['code'] = $marketing_info['code'];
		} else {
			$this->data['code'] = $this->request->post('code', uniqid());
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/marketing_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'marketing/marketing')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->data['error_name'];
		}

		if (!$this->request->post['code']) {
			$this->error['code'] = $this->data['error_code'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'marketing/marketing')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}