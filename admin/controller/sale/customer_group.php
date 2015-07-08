<?php
class ControllerSaleCustomerGroup extends Controller {
	private $error = array();
	
	private $url_data = array(
				'sort',
				'order',
				'page',
			);
	
	public function index() {
		$this->data = $this->load->language('sale/customer_group');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer_group');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('sale/customer_group');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer_group');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['customer_group_id'])){
				$this->model_sale_customer_group->editCustomerGroup($this->request->get['customer_group_id'], $this->request->post);
			} else{
				$this->model_sale_customer_group->addCustomerGroup($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('sale/customer_group');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer_group');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $customer_group_id) {
				$this->model_sale_customer_group->deleteCustomerGroup($customer_group_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'cgd.name');
		
		$order = $this->request->get('order', 'ASC');

		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(
					array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						)
				);
		
		$this->data['save'] = $this->url->link('sale/customer_group/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/customer_group/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['customer_groups'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$customer_group_total = $this->model_sale_customer_group->getTotalCustomerGroups();

		$results = $this->model_sale_customer_group->getCustomerGroups($filter_data);

		foreach ($results as $result) {
			$this->data['customer_groups'][] = array(
				'customer_group_id' => $result['customer_group_id'],
				'name'              => $result['name'] . (($result['customer_group_id'] == $this->config->get('config_customer_group_id')) ? $this->data['text_default'] : null),
				'sort_order'        => $result['sort_order'],
				'save'              => $this->url->link('sale/customer_group/save', 'token=' . $this->session->data['token'] . '&customer_group_id=' . $result['customer_group_id'] . $url, 'SSL')
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
		

		$this->data['sort_name'] = $this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . '&sort=cgd.name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . '&sort=cg.sort_order' . $url, 'SSL');
		$url_data = array(
				'sort',
				'order',
			);

		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $customer_group_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($customer_group_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_group_total - $this->config->get('config_limit_admin'))) ? $customer_group_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_group_total, ceil($customer_group_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/customer_group_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['customer_group_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']:'');
		//for sorting and paging
		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['customer_group_id'])) {
			$this->data['action'] = $this->url->link('sale/customer_group/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/customer_group/save', 'token=' . $this->session->data['token'] . '&customer_group_id=' . $this->request->get['customer_group_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/customer_group', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['customer_group_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$customer_group_info = $this->model_sale_customer_group->getCustomerGroup($this->request->get['customer_group_id']);
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->get['customer_group_id'])) {
			$this->data['customer_group_description'] = $this->model_sale_customer_group->getCustomerGroupDescriptions($this->request->get['customer_group_id']);
		} else {
			$this->data['customer_group_description'] = $this->request->post('customer_group_description', array());
		}
		
		if (!empty($customer_group_info) && !$this->error) {
			$this->data['approval'] = $customer_group_info['approval'];
		} else {
			$this->data['approval'] = $this->request->post('approval', '');
		}
		
		if (!empty($customer_group_info) && !$this->error) {
			$this->data['sort_order'] = $customer_group_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', '');
		}
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/customer_group_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/customer_group')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['customer_group_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/customer_group')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('setting/store');
		$this->load->model('sale/customer');

		foreach ($this->request->post['selected'] as $customer_group_id) {
			if ($this->config->get('config_customer_group_id') == $customer_group_id) {
				$this->error['warning'] = $this->data['error_default'];
			}

			$store_total = $this->model_setting_store->getTotalStoresByCustomerGroupId($customer_group_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->data['error_store'], $store_total);
			}

			$customer_total = $this->model_sale_customer->getTotalCustomersByCustomerGroupId($customer_group_id);

			if ($customer_total) {
				$this->error['warning'] = sprintf($this->data['error_customer'], $customer_total);
			}
		}

		return !$this->error;
	}
}