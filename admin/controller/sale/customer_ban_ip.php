<?php
class ControllerSaleCustomerBanIp extends Controller {
	private $error = array();
	
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('sale/customer_ban_ip');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer_ban_ip');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('sale/customer_ban_ip');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer_ban_ip');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['customer_ban_ip_id'])){
				$this->model_sale_customer_ban_ip->editCustomerBanIp($this->request->get['customer_ban_ip_id'], $this->request->post);
			} else{
				$this->model_sale_customer_ban_ip->addCustomerBanIp($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/customer_ban_ip', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('sale/customer_ban_ip');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer_ban_ip');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $customer_ban_ip_id) {
				$this->model_sale_customer_ban_ip->deleteCustomerBanIp($customer_ban_ip_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/customer_ban_ip', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'ip');
		
		$order = $this->request->get('order', 'ASC');

		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(
					array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/customer_ban_ip', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						)
				);

		$this->data['save'] = $this->url->link('sale/customer_ban_ip/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/customer_ban_ip/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['customer_ban_ips'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$customer_ban_ip_total = $this->model_sale_customer_ban_ip->getTotalCustomerBanIps($filter_data);

		$results = $this->model_sale_customer_ban_ip->getCustomerBanIps($filter_data);

		foreach ($results as $result) {
			$this->data['customer_ban_ips'][] = array(
				'customer_ban_ip_id' => $result['customer_ban_ip_id'],
				'ip'                 => $result['ip'],
				'total'              => $result['total'],
				'customer'           => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&filter_ip=' . $result['ip'], 'SSL'),
				'save'               => $this->url->link('sale/customer_ban_ip/save', 'token=' . $this->session->data['token'] . '&customer_ban_ip_id=' . $result['customer_ban_ip_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['selected'] =  $this->request->post('selected', array());

		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ;

		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_ip'] = $this->url->link('sale/customer_ban_ip', 'token=' . $this->session->data['token'] . '&sort=ip' . $url, 'SSL');
		
		$url_data = array(
				'sort',
				'order',
			);

		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $customer_ban_ip_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/customer_ban_ip', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($customer_ban_ip_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_ban_ip_total - $this->config->get('config_limit_admin'))) ? $customer_ban_ip_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_ban_ip_total, ceil($customer_ban_ip_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/customer_ban_ip_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['customer_ban_ip_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_ip'] =  (isset($this->error['ip'])?$this->error['ip']:'');

		//for sorting and paging
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/customer_ban_ip', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['customer_ban_ip_id'])) {
			$this->data['action'] = $this->url->link('sale/customer_ban_ip/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/customer_ban_ip/save', 'token=' . $this->session->data['token'] . '&customer_ban_ip_id=' . $this->request->get['customer_ban_ip_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/customer_ban_ip', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['customer_ban_ip_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$customer_ban_ip_info = $this->model_sale_customer_ban_ip->getCustomerBanIp($this->request->get['customer_ban_ip_id']);
		}
		
		if (!empty($customer_ban_ip_info) && !$this->error) {
			$this->data['ip'] = $customer_ban_ip_info['ip'];
		} else {
			$this->data['ip'] = $this->request->post('ip', '');
		}
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/customer_ban_ip_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/customer_ban_ip')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['ip']) < 1) || (utf8_strlen($this->request->post['ip']) > 40)) {
			$this->error['ip'] = $this->data['error_ip'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/customer_ban_ip')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}