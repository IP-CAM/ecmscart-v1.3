<?php
class ControllerMarketingAffiliate extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'filter_name' => 'encode', // for using these functions urlencode(html_entity_decode($this->get[$item], ENT_QUOTES, 'UTF-8'));
				'filter_email' => 'encode',
				'filter_status',
				'filter_approved',
				'filter_date_added',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('marketing/affiliate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/affiliate');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('marketing/affiliate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/affiliate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['affiliate_id'])){
				$this->model_marketing_affiliate->editAffiliate($this->request->get['affiliate_id'], $this->request->post);
			} else{
				$this->model_marketing_affiliate->addAffiliate($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('marketing/affiliate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/affiliate');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $affiliate_id) {
				$this->model_marketing_affiliate->deleteAffiliate($affiliate_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function approve() {
		$this->data = $this->load->language('marketing/affiliate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/affiliate');

		if (isset($this->request->get['affiliate_id']) && $this->validateApprove()) {
			$this->model_marketing_affiliate->approve($this->request->get['affiliate_id']);

			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}
	
	public function unlock() {
		$this->data = $this->load->language('marketing/affiliate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/affiliate');

		if (isset($this->request->get['email']) && $this->validateUnlock()) {
			$this->model_marketing_affiliate->deleteLoginAttempts($this->request->get['email']);

			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}
	
	protected function getList() {
		$filter_name = $this->request->get('filter_name', null);
		
		$filter_email = $this->request->get('filter_email', null);
		
		$filter_status = $this->request->get('filter_status', null);
		
		$filter_approved = $this->request->get('filter_approved', null);

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
							$this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['approve'] = $this->url->link('marketing/affiliate/approve', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['save'] = $this->url->link('marketing/affiliate/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('marketing/affiliate/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['affiliates'] = array();

		$filter_data = array(
			'filter_name'       => $filter_name,
			'filter_email'      => $filter_email,
			'filter_status'     => $filter_status,
			'filter_approved'   => $filter_approved,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$affiliate_total = $this->model_marketing_affiliate->getTotalAffiliates($filter_data);

		$results = $this->model_marketing_affiliate->getAffiliates($filter_data);

		foreach ($results as $result) {
			if (!$result['approved']) {
				$approve = $this->url->link('marketing/affiliate/approve', 'token=' . $this->session->data['token'] . '&affiliate_id=' . $result['affiliate_id'] . $url, 'SSL');
			} else {
				$approve = '';
			}			
			
			$login_info = $this->model_marketing_affiliate->getTotalLoginAttempts($result['email']);
			
			if ($login_info && $login_info['total'] > $this->config->get('config_login_attempts')) {
				$unlock = $this->url->link('marketing/affiliate/unlock', 'token=' . $this->session->data['token'] . '&email=' . $result['email'] . $url, 'SSL');
			} else {
				$unlock = '';
			}
						
			$this->data['affiliates'][] = array(
				'affiliate_id' => $result['affiliate_id'],
				'name'         => $result['name'],
				'email'        => $result['email'],
				'balance'      => $this->currency->format($result['balance'], $this->config->get('config_currency')),
				'status'       => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'date_added'   => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'approve'      => $approve,
				'unlock'       => $unlock,
				'save'         => $this->url->link('marketing/affiliate/save', 'token=' . $this->session->data['token'] . '&affiliate_id=' . $result['affiliate_id'] . $url, 'SSL')
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
				'filter_status',
				'filter_approved',
				'filter_date_added',
			);
		
		$url = $this->request->getUrl($url_data);
	
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_email'] = $this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . '&sort=a.email' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . '&sort=a.status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . '&sort=a.date_added' . $url, 'SSL');

		// Sorting and Filter Function for filter variable again
		$url_data = array(
				'filter_name' => 'encode', 
				'filter_email' => 'encode',
				'filter_status',
				'filter_approved',
				'filter_date_added',
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $affiliate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($affiliate_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($affiliate_total - $this->config->get('config_limit_admin'))) ? $affiliate_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $affiliate_total, ceil($affiliate_total / $this->config->get('config_limit_admin')));

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_email'] = $filter_email;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_approved'] = $filter_approved;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/affiliate_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['affiliate_id']) ? $this->data['text_add'] : $this->data['text_edit'];
	
		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_firstname'] =  (isset($this->error['firstname'])? $this->error['firstname']: '');
		
		$this->data['error_lastname'] =  (isset($this->error['lastname'])? $this->error['lastname']: '');
		
		$this->data['error_email'] =  (isset($this->error['email'])? $this->error['email']: '');

		$this->data['error_cheque'] =  (isset($this->error['cheque'])? $this->error['cheque']: '');

		$this->data['error_paypal'] =  (isset($this->error['paypal'])? $this->error['paypal']: '');
	
		$this->data['error_bank_account_name'] =  (isset($this->error['bank_account_name'])? $this->error['bank_account_name']: '');
	
		$this->data['error_bank_account_number'] =  (isset($this->error['bank_account_number'])? $this->error['bank_account_number']: '');	
		
		$this->data['error_telephone'] =  (isset($this->error['telephone'])? $this->error['telephone']: '');
		
		$this->data['error_password'] =  (isset($this->error['password'])? $this->error['password']: '');

		$this->data['error_confirm'] =  (isset($this->error['confirm'])? $this->error['confirm']: '');

		$this->data['error_address_1'] =  (isset($this->error['address_1'])? $this->error['address_1']: '');

		$this->data['error_city'] =  (isset($this->error['city'])? $this->error['city']: '');

		$this->data['error_postcode'] =  (isset($this->error['postcode'])? $this->error['postcode']: '');

		$this->data['error_country'] =  (isset($this->error['country'])? $this->error['country']: '');

		$this->data['error_zone'] =  (isset($this->error['zone'])? $this->error['zone']: '');
		
		$this->data['error_code'] =  (isset($this->error['code'])? $this->error['code']: '');
		
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['affiliate_id'])) {
			$this->data['action'] = $this->url->link('marketing/affiliate/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('marketing/affiliate/save', 'token=' . $this->session->data['token'] . '&affiliate_id=' . $this->request->get['affiliate_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('marketing/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['affiliate_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$affiliate_info = $this->model_marketing_affiliate->getAffiliate($this->request->get['affiliate_id']);
		}

		$this->data['token'] = $this->session->data['token'];
		
		$this->data['affiliate_id'] = $this->request->get('affiliate_id', 0);
		
		if (!empty($affiliate_info) && !$this->error) {
			$this->data['firstname'] = $affiliate_info['firstname'];
		} else {
			$this->data['firstname'] = $this->request->post('firstname', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['lastname'] = $affiliate_info['lastname'];
		} else {
			$this->data['lastname'] = $this->request->post('lastname', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['email'] = $affiliate_info['email'];
		} else {
			$this->data['email'] = $this->request->post('email', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['telephone'] = $affiliate_info['telephone'];
		} else {
			$this->data['telephone'] = $this->request->post('telephone', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['fax'] = $affiliate_info['fax'];
		} else {
			$this->data['fax'] = $this->request->post('fax', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['company'] = $affiliate_info['company'];
		} else {
			$this->data['company'] =  $this->request->post('company', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['website'] = $affiliate_info['website'];
		} else {
			$this->data['website'] =  $this->request->post('website', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['address_1'] = $affiliate_info['address_1'];
		} else {
			$this->data['address_1'] = $this->request->post('address_1', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['address_2'] = $affiliate_info['address_2'];
		} else {
			$this->data['address_2'] = $this->request->post('address_2', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['city'] = $affiliate_info['city'];
		} else {
			$this->data['city'] = $this->request->post('city', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['postcode'] = $affiliate_info['postcode'];
		} else {
			$this->data['postcode'] = $this->request->post('postcode', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['country_id'] = $affiliate_info['country_id'];
		} else {
			$this->data['country_id'] = $this->request->post('country_id', '');
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['zone_id'] = $affiliate_info['zone_id'];
		} else {
			$this->data['zone_id'] = $this->request->post('zone_id', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['code'] = $affiliate_info['code'];
		} else {
			$this->data['code'] = $this->request->post('code', uniqid());
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['commission'] = $affiliate_info['commission'];
		} else {
			$this->data['commission'] = $this->request->post('commission', $this->config->get('config_affiliate_commission'));
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['tax'] = $affiliate_info['tax'];
		} else {
			$this->data['tax'] = $this->request->post('tax', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['payment'] = $affiliate_info['payment'];
		} else {
			$this->data['payment'] = $this->request->post('payment', 'cheque');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['cheque'] = $affiliate_info['cheque'];
		} else {
			$this->data['cheque'] = $this->request->post('cheque', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['paypal'] = $affiliate_info['paypal'];
		} else {
			$this->data['paypal'] = $this->request->post('paypal', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_name'] = $affiliate_info['bank_name'];
		} else {
			$this->data['bank_name'] = '';$this->request->post('bank_name', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_branch_number'] = $affiliate_info['bank_branch_number'];
		} else {
			$this->data['bank_branch_number'] = $this->request->post('bank_branch_number', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_swift_code'] = $affiliate_info['bank_swift_code'];
		} else {
			$this->data['bank_swift_code'] = $this->request->post('bank_swift_code', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_account_name'] = $affiliate_info['bank_account_name'];
		} else {
			$this->data['bank_account_name'] = $this->request->post('bank_account_name', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_account_number'] = $affiliate_info['bank_account_number'];
		} else {
			$this->data['bank_account_number'] = $this->request->post('bank_account_number', '');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['status'] = $affiliate_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', true);
		}

		$this->data['password'] = $this->request->post('password', '');
		
		$this->data['confirm'] = $this->request->post('confirm', '');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/affiliate_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'marketing/affiliate')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->data['error_firstname'];
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->data['error_lastname'];
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email']))) {
			$this->error['email'] = $this->data['error_email'];
		}

		if ($this->request->post['payment'] == 'cheque') {
			if ($this->request->post['cheque'] == '') {
				$this->error['cheque'] = $this->data['error_cheque'];
			}
		} elseif ($this->request->post['payment'] == 'paypal') {
			if ((utf8_strlen($this->request->post['paypal']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['paypal'])) {
				$this->error['paypal'] = $this->data['error_paypal'];
			}
		} elseif ($this->request->post['payment'] == 'bank') {
			if ($this->request->post['bank_account_name'] == '') {
				$this->error['bank_account_name'] = $this->data['error_bank_account_name'];
			}

			if ($this->request->post['bank_account_number'] == '') {
				$this->error['bank_account_number'] = $this->data['error_bank_account_number'];
			}
		}

		$affiliate_info = $this->model_marketing_affiliate->getAffiliateByEmail($this->request->post['email']);

		if (!isset($this->request->get['affiliate_id'])) {
			if ($affiliate_info) {
				$this->error['warning'] = $this->data['error_exists'];
			}
		} else {
			if ($affiliate_info && ($this->request->get['affiliate_id'] != $affiliate_info['affiliate_id'])) {
				$this->error['warning'] = $this->data['error_exists'];
			}
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->data['error_telephone'];
		}

		if ($this->request->post['password'] || (!isset($this->request->get['affiliate_id']))) {
			if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
				$this->error['password'] = $this->data['error_password'];
			}

			if ($this->request->post['password'] != $this->request->post['confirm']) {
				$this->error['confirm'] = $this->data['error_confirm'];
			}
		}

		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$this->error['address_1'] = $this->data['error_address_1'];
		}

		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$this->error['city'] = $this->data['error_city'];
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$this->error['postcode'] = $this->data['error_postcode'];
		}

		if ($this->request->post['country_id'] == '') {
			$this->error['country'] = $this->data['error_country'];
		}

		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
			$this->error['zone'] = $this->data['error_zone'];
		}

		if (!$this->request->post['code']) {
			$this->error['code'] = $this->data['error_code'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'marketing/affiliate')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	protected function validateApprove() {
		if (!$this->user->hasPermission('modify', 'marketing/affiliate')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
	
	protected function validateUnlock() {
		if (!$this->user->hasPermission('modify', 'marketing/affiliate')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
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

	public function transaction() {
		$this->data = $this->load->language('marketing/affiliate');

		$this->load->model('marketing/affiliate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'marketing/affiliate')) {
			$this->model_marketing_affiliate->addTransaction($this->request->get['affiliate_id'], $this->request->post['description'], $this->request->post['amount']);

			$this->data['success'] = $this->data['text_success'];
		} else {
			$this->data['success'] = '';
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->user->hasPermission('modify', 'marketing/affiliate')) {
			$this->data['error_warning'] = $this->data['error_permission'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		$page = $this->request->get('page', 1);

		$this->data['transactions'] = array();

		$results = $this->model_marketing_affiliate->getTransactions($this->request->get['affiliate_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['transactions'][] = array(
				'amount'      => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'description' => $result['description'],
				'date_added'  => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$this->data['balance'] = $this->currency->format($this->model_marketing_affiliate->getTransactionTotal($this->request->get['affiliate_id']), $this->config->get('config_currency'));

		$transaction_total = $this->model_marketing_affiliate->getTotalTransactions($this->request->get['affiliate_id']);

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/affiliate/transaction', 'token=' . $this->session->data['token'] . '&affiliate_id=' . $this->request->get['affiliate_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($transaction_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($transaction_total - 10)) ? $transaction_total : ((($page - 1) * 10) + 10), $transaction_total, ceil($transaction_total / 10));

		$this->response->setOutput($this->load->view('marketing/affiliate_transaction.tpl', $this->data));
	}

	public function autocomplete() {
		$affiliate_data = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_email'])) {
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_email'])) {
				$filter_email = $this->request->get['filter_email'];
			} else {
				$filter_email = '';
			}

			$this->load->model('marketing/affiliate');

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_email' => $filter_email,
				'start'        => 0,
				'limit'        => 5
			);

			$results = $this->model_marketing_affiliate->getAffiliates($filter_data);

			foreach ($results as $result) {
				$affiliate_data[] = array(
					'affiliate_id' => $result['affiliate_id'],
					'name'         => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'email'        => $result['email']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($affiliate_data));
	}
}