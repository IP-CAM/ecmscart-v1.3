<?php
class ControllerSaleCustomer extends Controller {
	private $error = array();
	
	private $url_data = array(
				'filter_name' => 'encode', 
				'filter_email' => 'encode',
				'filter_customer_group_id',
				'filter_status',
				'filter_approved',
				'filter_ip',
				'filter_date_added',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('sale/customer');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('sale/customer');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['customer_id'])){
				$this->model_sale_customer->editCustomer($this->request->get['customer_id'], $this->request->post);
			} else{
				$this->model_sale_customer->addCustomer($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('sale/customer');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $customer_id) {
				$this->model_sale_customer->deleteCustomer($customer_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function approve() {
		$this->data = $this->load->language('sale/customer');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer');

		$customers = array();

		if (isset($this->request->post['selected'])) {
			$customers = $this->request->post['selected'];
		} elseif (isset($this->request->get['customer_id'])) {
			$customers[] = $this->request->get['customer_id'];
		}

		if ($customers && $this->validateApprove()) {
			$this->model_sale_customer->approve($this->request->get['customer_id']);

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);


			$this->response->redirect($this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function unlock() {
		$this->data = $this->load->language('sale/customer');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/customer');

		if (isset($this->request->get['email']) && $this->validateUnlock()) {
			$this->model_sale_customer->deleteLoginAttempts($this->request->get['email']);

			$this->session->data['success'] = $this->data['text_success'];

			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}
			
	protected function getList() {
		$filter_name = $this->request->get('filter_name', null);
		
		$filter_email = $this->request->get('filter_email', null);
		
		$filter_customer_group_id = $this->request->get('filter_customer_group_id', null);
		
		$filter_status = $this->request->get('filter_status', null);
		
		$filter_approved = $this->request->get('filter_approved', null);
		
		$filter_ip = $this->request->get('filter_ip', null);
		
		$filter_date_added = $this->request->get('filter_date_added', null);
		
		$sort = $this->request->get('sort','name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);

		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(
					array(
							$this->data['text_home'],	
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		
							$this->data['heading_title'],	
							$this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL')	
						)
				);
				
		$this->data['save'] = $this->url->link('sale/customer/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/customer/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['customers'] = array();

		$filter_data = array(
			'filter_name'              => $filter_name,
			'filter_email'             => $filter_email,
			'filter_customer_group_id' => $filter_customer_group_id,
			'filter_status'            => $filter_status,
			'filter_approved'          => $filter_approved,
			'filter_date_added'        => $filter_date_added,
			'filter_ip'                => $filter_ip,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);

		$customer_total = $this->model_sale_customer->getTotalCustomers($filter_data);

		$results = $this->model_sale_customer->getCustomers($filter_data);

		foreach ($results as $result) {
			if (!$result['approved']) {
				$approve = $this->url->link('sale/customer/approve', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['customer_id'] . $url, 'SSL');
			} else {
				$approve = '';
			}			
			
			$login_info = $this->model_sale_customer->getTotalLoginAttempts($result['email']);
			
			if ($login_info && $login_info['total'] > $this->config->get('config_login_attempts')) {
				$unlock = $this->url->link('sale/customer/unlock', 'token=' . $this->session->data['token'] . '&email=' . $result['email'] . $url, 'SSL');
			} else {
				$unlock = '';
			}
						
			$this->data['customers'][] = array(
				'customer_id'    => $result['customer_id'],
				'name'           => $result['name'],
				'email'          => $result['email'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'ip'             => $result['ip'],
				'date_added'     => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'approve'        => $approve,
				'unlock'         => $unlock,
				'save'           => $this->url->link('sale/customer/save', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['customer_id'] . $url, 'SSL')
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
				'filter_email' => 'encode',
				'filter_customer_group_id',
				'filter_status',
				'filter_approved',
				'filter_ip',
				'filter_date_added',
			);
		
		$url = $this->request->getUrl($url_data);
	
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];		

		$this->data['sort_name'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_email'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.email' . $url, 'SSL');
		$this->data['sort_customer_group'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=customer_group' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.status' . $url, 'SSL');
		$this->data['sort_ip'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.ip' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.date_added' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'filter_name' => 'encode', 
				'filter_model' => 'encode',
				'filter_price',
				'filter_quantity',
				'filter_status',
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_total - $this->config->get('config_limit_admin'))) ? $customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($customer_total / $this->config->get('config_limit_admin')));

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_email'] = $filter_email;
		$this->data['filter_customer_group_id'] = $filter_customer_group_id;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_approved'] = $filter_approved;
		$this->data['filter_ip'] = $filter_ip;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/customer_list.tpl', $this->data));
	}

	protected function getForm() {		

		$this->data['text_form'] = !isset($this->request->get['customer_id']) ? $this->data['text_add'] : $this->data['text_edit'];
				
		$this->data['token'] = $this->session->data['token'];

		$this->data['customer_id'] = $this->request->get('customer_id', 0);

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_firstname'] =  (isset($this->error['firstname'])?$this->error['firstname']:'');
		
		$this->data['error_lastname'] =  (isset($this->error['lastname'])?$this->error['lastname']:'');
		
		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');
		
		$this->data['error_telephone'] =  (isset($this->error['email'])?$this->error['telephone']:'');
		
		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');
		
		$this->data['error_confirm'] =  (isset($this->error['confirm'])?$this->error['confirm']:'');
		
		$this->data['error_address'] =  (isset($this->error['address'])?$this->error['address']:'');
		// for sorting and paging
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['customer_id'])) {
			$this->data['action'] = $this->url->link('sale/customer/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/customer/save', 'token=' . $this->session->data['token'] . '&customer_id=' . $this->request->get['customer_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['customer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$customer_info = $this->model_sale_customer->getCustomer($this->request->get['customer_id']);
		}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		if (!empty($customer_info) && !$this->error) {
			$this->data['customer_group_id'] = $customer_info['customer_group_id'];
		} else {
			$this->data['customer_group_id'] = $this->request->post('customer_group_id', $this->config->get('config_customer_group_id'));
		}

		if (!empty($customer_info) && !$this->error) {
			$this->data['firstname'] = $customer_info['firstname'];
		} else {
			$this->data['firstname'] = $this->request->post('firstname', '');
		}

		if (!empty($customer_info) && !$this->error) {
			$this->data['lastname'] = $customer_info['lastname'];
		} else {
			$this->data['lastname'] = $this->request->post('lastname', '');
		}
		
		if (!empty($customer_info) && !$this->error) {
			$this->data['email'] = $customer_info['email'];
		} else {
			$this->data['email'] = $this->request->post('email', '');
		}
		
		if (!empty($customer_info) && !$this->error) {
			$this->data['telephone'] = $customer_info['telephone'];
		} else {
			$this->data['telephone'] = $this->request->post('telephone', '');
		}
		
		if (!empty($customer_info) && !$this->error) {
			$this->data['fax'] = $customer_info['fax'];
		} else {
			$this->data['fax'] = $this->request->post('fax', '');
		}
					
		// Custom Fields
		$this->load->model('sale/custom_field');

		$this->data['custom_fields'] = array();

		$custom_fields = $this->model_sale_custom_field->getCustomFields();

		foreach ($custom_fields as $custom_field) {
			$this->data['custom_fields'][] = array(
				'custom_field_id'    => $custom_field['custom_field_id'],
				'custom_field_value' => $this->model_sale_custom_field->getCustomFieldValues($custom_field['custom_field_id']),
				'name'               => $custom_field['name'],
				'value'              => $custom_field['value'],
				'type'               => $custom_field['type'],
				'location'           => $custom_field['location']
			);
		}
		
		if (!empty($customer_info) && !$this->error) {
			$this->data['account_custom_field'] = unserialize($customer_info['custom_field']);
		} else {
			$this->data['account_custom_field'] = $this->request->post('custom_field', array());
		}

		if (!empty($customer_info) && !$this->error) {
			$this->data['newsletter'] = $customer_info['newsletter'];
		} else {
			$this->data['newsletter'] = $this->request->post('newsletter', '');
		}

		if (!empty($customer_info) && !$this->error) {
			$this->data['status'] = $customer_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', true);
		}
		
		if (!empty($customer_info) && !$this->error) {
			$this->data['approved'] = $customer_info['approved'];
		} else {
			$this->data['approved'] = $this->request->post('approved', true);
		}
		
		if (!empty($customer_info) && !$this->error) {
			$this->data['safe'] = $customer_info['safe'];
		} else {
			$this->data['safe'] = $this->request->post('safe', 0);
		}
	
		$this->data['password'] = $this->request->post('password', '');
		
		$this->data['confirm'] = $this->request->post('confirm', '');		

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		if (isset($this->request->get['customer_id']) && !$this->error) {
			$this->data['addresses'] = $this->model_sale_customer->getAddresses($this->request->get['customer_id']);
		} else {
			$this->data['addresses'] = $this->request->post('address', array());
		}

		if (!empty($customer_info) && !$this->error) {
			$this->data['address_id'] = $customer_info['address_id'];
		} else {
			$this->data['address_id'] = $this->request->post('address_id', '');
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/customer_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/customer')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->data['error_firstname'];
		}

		if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->data['error_lastname'];
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->data['error_email'];
		}

		$customer_info = $this->model_sale_customer->getCustomerByEmail($this->request->post['email']);

		if (!isset($this->request->get['customer_id'])) {
			if ($customer_info) {
				$this->error['warning'] = $this->data['error_exists'];
			}
		} else {
			if ($customer_info && ($this->request->get['customer_id'] != $customer_info['customer_id'])) {
				$this->error['warning'] = $this->data['error_exists'];
			}
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->data['error_telephone'];
		}

		// Custom field validation
		$this->load->model('sale/custom_field');

		$custom_fields = $this->model_sale_custom_field->getCustomFields(array('filter_customer_group_id' => $this->request->post['customer_group_id']));

		foreach ($custom_fields as $custom_field) {
			if (($custom_field['location'] == 'account') && $custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
				$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->data['error_custom_field'], $custom_field['name']);
			}
		}

		if ($this->request->post['password'] || (!isset($this->request->get['customer_id']))) {
			if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
				$this->error['password'] = $this->data['error_password'];
			}

			if ($this->request->post['password'] != $this->request->post['confirm']) {
				$this->error['confirm'] = $this->data['error_confirm'];
			}
		}

		if (isset($this->request->post['address'])) {
			foreach ($this->request->post['address'] as $key => $value) {
				if ((utf8_strlen($value['firstname']) < 1) || (utf8_strlen($value['firstname']) > 32)) {
					$this->error['address'][$key]['firstname'] = $this->data['error_firstname'];
				}

				if ((utf8_strlen($value['lastname']) < 1) || (utf8_strlen($value['lastname']) > 32)) {
					$this->error['address'][$key]['lastname'] = $this->data['error_lastname'];
				}

				if ((utf8_strlen($value['address_1']) < 3) || (utf8_strlen($value['address_1']) > 128)) {
					$this->error['address'][$key]['address_1'] = $this->data['error_address_1'];
				}

				if ((utf8_strlen($value['city']) < 2) || (utf8_strlen($value['city']) > 128)) {
					$this->error['address'][$key]['city'] = $this->data['error_city'];
				}

				$this->load->model('localisation/country');

				$country_info = $this->model_localisation_country->getCountry($value['country_id']);

				if ($country_info && $country_info['postcode_required'] && (utf8_strlen($value['postcode']) < 2 || utf8_strlen($value['postcode']) > 10)) {
					$this->error['address'][$key]['postcode'] = $this->data['error_postcode'];
				}

				if ($value['country_id'] == '') {
					$this->error['address'][$key]['country'] = $this->data['error_country'];
				}

				if (!isset($value['zone_id']) || $value['zone_id'] == '') {
					$this->error['address'][$key]['zone'] = $this->data['error_zone'];
				}

				foreach ($custom_fields as $custom_field) {
					if (($custom_field['location'] == 'address') && $custom_field['required'] && empty($value['custom_field'][$custom_field['custom_field_id']])) {
						$this->error['address'][$key]['custom_field'][$custom_field['custom_field_id']] = sprintf($this->data['error_custom_field'], $custom_field['name']);
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->data['error_warning'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/customer')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	protected function validateApprove() {
		if (!$this->user->hasPermission('modify', 'sale/customer')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
	
	protected function validateUnlock() {
		if (!$this->user->hasPermission('modify', 'sale/customer')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
	
	protected function validateHistory() {
		if (!$this->user->hasPermission('modify', 'sale/customer')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!isset($this->request->post['comment']) || utf8_strlen($this->request->post['comment']) < 1) {
			$this->error['warning'] = $this->data['error_comment'];
		}

		return !$this->error;
	}

	public function login() {
		$json = array();

		$customer_id = $this->request->get('customer_id', 0);

		$this->load->model('sale/customer');

		$customer_info = $this->model_sale_customer->getCustomer($customer_id);

		if ($customer_info) {
			$token = md5(mt_rand());

			$this->model_sale_customer->editToken($customer_id, $token);
		
			$store_id = $this->request->post('store_id', 0);			

			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($store_id);

			if ($store_info) {
				$this->response->redirect($store_info['url'] . 'index.php?route=account/login&token=' . $token);
			} else {
				$this->response->redirect(HTTP_CATALOG . 'index.php?route=account/login&token=' . $token);
			}
		} else {
			$this->data = $this->load->language('error/not_found');

			$this->document->setTitle($this->data['heading_title']);
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		
							$this->data['heading_title'],	// Text to display link
							$this->url->link('error/not_found', 'token=' . $this->session->data['token'] . $url, 'SSL')	
					));

			$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found.tpl', $this->data));
		}
	}

	public function history() {
		$this->data = $this->load->language('sale/customer');

		$this->load->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateHistory()) {
			$this->model_sale_customer->addHistory($this->request->get['customer_id'], $this->request->post['comment']);

			$this->data['success'] = $this->data['text_success'];
		} else {
			$this->data['success'] = '';
		}		

		$this->data['error_warning'] =  isset($this->error['warning'])? $this->error['warning']: '';		

		$page = $this->request->get('page',1);		

		$this->data['histories'] = array();

		$results = $this->model_sale_customer->getHistories($this->request->get['customer_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['histories'][] = array(
				'comment'     => $result['comment'],
				'date_added'  => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_sale_customer->getTotalHistories($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/customer/history', 'token=' . $this->session->data['token'] . '&customer_id=' . $this->request->get['customer_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('sale/customer_history.tpl', $this->data));
	}

	public function transaction() {
		$this->data = $this->load->language('sale/customer');

		$this->load->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/customer')) {
			$this->model_sale_customer->addTransaction($this->request->get['customer_id'], $this->request->post['description'], $this->request->post['amount']);

			$this->data['success'] = $this->data['text_success'];
		} else {
			$this->data['error_warning'] = (!$this->user->hasPermission('modify', 'sale/customer'))? $this->data['error_permission']: ''; 
			
			$this->data['success'] = '';
		}

		$page = $this->request->get('page',1);

		$this->data['transactions'] = array();

		$results = $this->model_sale_customer->getTransactions($this->request->get['customer_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['transactions'][] = array(
				'amount'      => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'description' => $result['description'],
				'date_added'  => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$this->data['balance'] = $this->currency->format($this->model_sale_customer->getTransactionTotal($this->request->get['customer_id']), $this->config->get('config_currency'));

		$transaction_total = $this->model_sale_customer->getTotalTransactions($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/customer/transaction', 'token=' . $this->session->data['token'] . '&customer_id=' . $this->request->get['customer_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($transaction_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($transaction_total - 10)) ? $transaction_total : ((($page - 1) * 10) + 10), $transaction_total, ceil($transaction_total / 10));

		$this->response->setOutput($this->load->view('sale/customer_transaction.tpl', $this->data));
	}

	public function reward() {
		$this->data = $this->load->language('sale/customer');

		$this->load->model('sale/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/customer')) {
			$this->model_sale_customer->addReward($this->request->get['customer_id'], $this->request->post['description'], $this->request->post['points']);

			$this->data['success'] = $this->data['text_success'];
		} else {
			$this->data['error_warning'] = (!$this->user->hasPermission('modify', 'sale/customer'))? $this->data['error_permission']: '';
			
			$this->data['success'] = '';
		}

		$page = $this->request->get('page',1);

		$this->data['rewards'] = array();

		$results = $this->model_sale_customer->getRewards($this->request->get['customer_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['rewards'][] = array(
				'points'      => $result['points'],
				'description' => $result['description'],
				'date_added'  => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$this->data['balance'] = $this->model_sale_customer->getRewardTotal($this->request->get['customer_id']);

		$reward_total = $this->model_sale_customer->getTotalRewards($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $reward_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/customer/reward', 'token=' . $this->session->data['token'] . '&customer_id=' . $this->request->get['customer_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($reward_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($reward_total - 10)) ? $reward_total : ((($page - 1) * 10) + 10), $reward_total, ceil($reward_total / 10));

		$this->response->setOutput($this->load->view('sale/customer_reward.tpl', $this->data));
	}

	public function ip() {
		$this->data = $this->load->language('sale/customer');

		$this->load->model('sale/customer');

		$page = $this->request->get('page',1);

		$this->data['ips'] = array();

		$results = $this->model_sale_customer->getIps($this->request->get['customer_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$ban_ip_total = $this->model_sale_customer->getTotalBanIpsByIp($result['ip']);

			$this->data['ips'][] = array(
				'ip'         => $result['ip'],
				'total'      => $this->model_sale_customer->getTotalCustomersByIp($result['ip']),
				'date_added' => date('d/m/y', strtotime($result['date_added'])),
				'filter_ip'  => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&filter_ip=' . $result['ip'], 'SSL'),
				'ban_ip'     => $ban_ip_total
			);
		}

		$ip_total = $this->model_sale_customer->getTotalIps($this->request->get['customer_id']);

		$pagination = new Pagination();
		$pagination->total = $ip_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/customer/ip', 'token=' . $this->session->data['token'] . '&customer_id=' . $this->request->get['customer_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($ip_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($ip_total - 10)) ? $ip_total : ((($page - 1) * 10) + 10), $ip_total, ceil($ip_total / 10));

		$this->response->setOutput($this->load->view('sale/customer_ip.tpl', $this->data));
	}

	public function addBanIp() {
		$this->data = $this->load->language('sale/customer');

		$json = array();

		if (isset($this->request->post['ip'])) {
			if (!$this->user->hasPermission('modify', 'sale/customer')) {
				$json['error'] = $this->data['error_permission'];
			} else {
				$this->load->model('sale/customer');

				$this->model_sale_customer->addBanIp($this->request->post['ip']);

				$json['success'] = $this->data['text_success'];
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeBanIp() {
		$this->load->language('sale/customer');

		$json = array();

		if (isset($this->request->post['ip'])) {
			if (!$this->user->hasPermission('modify', 'sale/customer')) {
				$json['error'] = $this->data['error_permission'];
			} else {
				$this->load->model('sale/customer');

				$this->model_sale_customer->removeBanIp($this->request->post['ip']);

				$json['success'] = $this->data['text_success'];
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_email'])) {
			
			$this->load->model('sale/customer');

			$filter_data = array(
				'filter_name'  => $this->request->get('filter_name', ''),
				'filter_email' => $this->request->get('filter_email', ''),
				'start'        => 0,
				'limit'        => 5
			);

			$results = $this->model_sale_customer->getCustomers($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'customer_id'       => $result['customer_id'],
					'customer_group_id' => $result['customer_group_id'],
					'name'              => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'customer_group'    => $result['customer_group'],
					'firstname'         => $result['firstname'],
					'lastname'          => $result['lastname'],
					'email'             => $result['email'],
					'telephone'         => $result['telephone'],
					'fax'               => $result['fax'],
					'custom_field'      => unserialize($result['custom_field']),
					'address'           => $this->model_sale_customer->getAddresses($result['customer_id'])
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function customfield() {
		$json = array();

		$this->load->model('sale/custom_field');

		// Customer Group
		$customer_group_id = $this->request->get('customer_group_id', $this->config->get('config_customer_group_id'));

		$custom_fields = $this->model_sale_custom_field->getCustomFields(array('filter_customer_group_id' => $customer_group_id));

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => empty($custom_field['required']) || $custom_field['required'] == 0 ? false : true
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function address() {
		$json = array();

		if (!empty($this->request->get['address_id'])) {
			$this->load->model('sale/customer');

			$json = $this->model_sale_customer->getAddress($this->request->get['address_id']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

}