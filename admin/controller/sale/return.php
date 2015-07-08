<?php
class ControllerSaleReturn extends Controller {
	private $error = array();
	
	private $url_data = array(
				'filter_return_id',
				'filter_order_id',
				'filter_customer' => 'encode',
				'filter_product' => 'encode',
				'filter_model' => 'encode',
				'filter_return_status_id',
				'filter_date_added',
				'filter_date_modified',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('sale/return');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/return');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('sale/return');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/return');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['return_id'])){
				$this->model_sale_return->editReturn($this->request->get['return_id'], $this->request->post);
			} else{
				$this->model_sale_return->addReturn($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/return', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('sale/return');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/return');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $return_id) {
				$this->model_sale_return->deleteReturn($return_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/return', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_return_id = $this->request->get('filter_return_id', null);
		
		$filter_order_id = $this->request->get('filter_order_id', null);
		
		$filter_customer = $this->request->get('filter_customer', null);

		$filter_product = $this->request->get('filter_product', null);
		
		$filter_model = $this->request->get('filter_model', null);
		
		$filter_return_status_id = $this->request->get('filter_return_status_id', null);
		
		$filter_date_added = $this->request->get('filter_date_added', null);
		
		$filter_date_modified = $this->request->get('filter_date_modified', null);
		
		$sort = $this->request->get('sort', 'r.return_id');
		
		$order = $this->request->get('order', 'DESC');
		
		$page = $this->request->get('page', 1);
		
		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/return', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('sale/return/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/return/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['returns'] = array();

		$filter_data = array(
			'filter_return_id'        => $filter_return_id,
			'filter_order_id'         => $filter_order_id,
			'filter_customer'         => $filter_customer,
			'filter_product'          => $filter_product,
			'filter_model'            => $filter_model,
			'filter_return_status_id' => $filter_return_status_id,
			'filter_date_added'       => $filter_date_added,
			'filter_date_modified'    => $filter_date_modified,
			'sort'                    => $sort,
			'order'                   => $order,
			'start'                   => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                   => $this->config->get('config_limit_admin')
		);

		$return_total = $this->model_sale_return->getTotalReturns($filter_data);

		$results = $this->model_sale_return->getReturns($filter_data);

		foreach ($results as $result) {
			$this->data['returns'][] = array(
				'return_id'     => $result['return_id'],
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'product'       => $result['product'],
				'model'         => $result['model'],
				'status'        => $result['status'],
				'date_added'    => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'date_modified' => date($this->data['date_format_short'], strtotime($result['date_modified'])),
				'save'          => $this->url->link('sale/return/save', 'token=' . $this->session->data['token'] . '&return_id=' . $result['return_id'] . $url, 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
			
		$this->data['selected'] =  $this->request->post('selected', array());

		$url_data = array(
				'filter_return_id',
				'filter_order_id',
				'filter_customer' => 'encode',
				'filter_product' => 'encode',
				'filter_model' => 'encode',
				'filter_return_status_id',
				'filter_date_added',
				'filter_date_modified',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; 

		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
		
		$this->data['sort_return_id'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=r.return_id' . $url, 'SSL');
		$this->data['sort_order_id'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=r.order_id' . $url, 'SSL');
		$this->data['sort_customer'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=customer' . $url, 'SSL');
		$this->data['sort_product'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=product' . $url, 'SSL');
		$this->data['sort_model'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=model' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=r.date_added' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . '&sort=r.date_modified' . $url, 'SSL');
		
		$url_data = array(
				'filter_return_id',
				'filter_order_id',
				'filter_customer' => 'encode',
				'filter_product' => 'encode',
				'filter_model' => 'encode',
				'filter_return_status_id',
				'filter_date_added',
				'filter_date_modified',
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($return_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($return_total - $this->config->get('config_limit_admin'))) ? $return_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $return_total, ceil($return_total / $this->config->get('config_limit_admin')));

		$this->data['filter_return_id'] = $filter_return_id;
		$this->data['filter_order_id'] = $filter_order_id;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_product'] = $filter_product;
		$this->data['filter_model'] = $filter_model;
		$this->data['filter_return_status_id'] = $filter_return_status_id;
		$this->data['filter_date_added'] = $filter_date_added;
		$this->data['filter_date_modified'] = $filter_date_modified;

		$this->load->model('localisation/return_status');

		$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/return_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['return_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['token'] = $this->session->data['token'];
		
		$this->data['return_id'] = $this->request->get('return_id', 0);

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_order_id'] =  (isset($this->error['order_id'])?$this->error['order_id']:'');

		$this->data['error_firstname'] =  (isset($this->error['firstname'])?$this->error['firstname']:'');

		$this->data['error_lastname'] =  (isset($this->error['lastname'])?$this->error['lastname']:'');

		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');

		$this->data['error_telephone'] =  (isset($this->error['telephone'])?$this->error['telephone']:'');

		$this->data['error_product'] =  (isset($this->error['product'])?$this->error['product']:'');

		$this->data['error_model'] =  (isset($this->error['model'])?$this->error['model']:'');
		//for filter, sorting and paging
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/return', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['return_id'])) {
			$this->data['action'] = $this->url->link('sale/return/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/return/save', 'token=' . $this->session->data['token'] . '&return_id=' . $this->request->get['return_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/return', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['return_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$return_info = $this->model_sale_return->getReturn($this->request->get['return_id']);
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['order_id'] = $return_info['order_id'];
		} else {
			$this->data['order_id'] = $this->request->post('order_id', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['date_ordered'] = ($return_info['date_ordered'] != '0000-00-00' ? $return_info['date_ordered'] : '');
		} else {
			$this->data['date_ordered'] = $this->request->post('date_ordered', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['customer'] = $return_info['customer'];
		} else {
			$this->data['customer'] = $this->request->post('customer', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['customer_id'] = $return_info['customer_id'];
		} else {
			$this->data['customer_id'] = $this->request->post('customer_id', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['firstname'] = $return_info['firstname'];
		} else {
			$this->data['firstname'] = $this->request->post('firstname', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['lastname'] = $return_info['lastname'];
		} else {
			$this->data['lastname'] = $this->request->post('lastname', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['email'] = $return_info['email'];
		} else {
			$this->data['email'] = $this->request->post('email', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['telephone'] = $return_info['telephone'];
		} else {
			$this->data['telephone'] = $this->request->post('telephone', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['product'] = $return_info['product'];
		} else {
			$this->data['product'] = $this->request->post('product', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['product_id'] = $return_info['product_id'];
		} else {
			$this->data['product_id'] = $this->request->post('product_id', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['model'] = $return_info['model'];
		} else {
			$this->data['model'] = $this->request->post('model', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['quantity'] = $return_info['quantity'];
		} else {
			$this->data['quantity'] = $this->request->post('quantity', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['opened'] = $return_info['opened'];
		} else {
			$this->data['opened'] = $this->request->post('opened', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['return_reason_id'] = $return_info['return_reason_id'];
		} else {
			$this->data['return_reason_id'] = $this->request->post('return_reason_id', '');
		}

		$this->load->model('localisation/return_reason');

		$this->data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();

		if (!empty($return_info) && !$this->error) {
			$this->data['return_action_id'] = $return_info['return_action_id'];
		} else {
			$this->data['return_action_id'] = $this->request->post('return_action_id', '');
		}

		$this->load->model('localisation/return_action');

		$this->data['return_actions'] = $this->model_localisation_return_action->getReturnActions();

		if (!empty($return_info) && !$this->error) {
			$this->data['comment'] = $return_info['comment'];
		} else {
			$this->data['comment'] = $this->request->post('comment', '');
		}

		if (!empty($return_info) && !$this->error) {
			$this->data['return_status_id'] = $return_info['return_status_id'];
		} else {
			$this->data['return_status_id'] = $this->request->post('return_status_id', '');
		}

		$this->load->model('localisation/return_status');

		$this->data['return_statuses'] = $this->model_localisation_return_status->getReturnStatuses();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/return_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/return')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (empty($this->request->post['order_id'])) {
			$this->error['order_id'] = $this->data['error_order_id'];
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->data['error_firstname'];
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->data['error_lastname'];
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->data['error_email'];
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->data['error_telephone'];
		}

		if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
			$this->error['product'] = $this->data['error_product'];
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->data['error_model'];
		}

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->data['error_reason'];
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->data['error_warning'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/return')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function history() {
		$this->data = $this->load->language('sale/return');

		$this->data['error'] = '';
		$this->data['success'] = '';

		$this->load->model('sale/return');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->user->hasPermission('modify', 'sale/return')) {
				$this->data['error'] = $this->data['error_permission'];
			}

			if (!$this->data['error']) {
				$this->model_sale_return->addReturnHistory($this->request->get['return_id'], $this->request->post);

				$this->data['success'] = $this->data['text_success'];
			}
		}
		
		$page = $this->request->get('page', 1);
		
		$this->data['histories'] = array();

		$results = $this->model_sale_return->getReturnHistories($this->request->get['return_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->data['text_yes'] : $this->data['text_no'],
				'status'     => $result['status'],
				'comment'    => nl2br($result['comment']),
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_sale_return->getTotalReturnHistories($this->request->get['return_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/return/history', 'token=' . $this->session->data['token'] . '&return_id=' . $this->request->get['return_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('sale/return_history.tpl', $this->data));
	}
}