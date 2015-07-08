<?php
class ControllerLocalisationCurrency extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('localisation/currency');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/currency');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/currency');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/currency');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['currency_id'])){
				$this->model_localisation_currency->editCurrency($this->request->get['currency_id'], $this->request->post);
			} else{
				$this->model_localisation_currency->addCurrency($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}


	public function delete() {
		$this->data = $this->load->language('localisation/currency');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/currency');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $currency_id) {
				$this->model_localisation_currency->deleteCurrency($currency_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}
	
	public function refresh() {
		$this->data = $this->load->language('localisation/currency');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/currency');

		if ($this->validateRefresh()) {
			$this->model_localisation_currency->refresh(true);		
			
			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}
	
	protected function getList() {
		$sort = $this->request->get('sort','title');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('localisation/currency/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/currency/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['refresh'] = $this->url->link('localisation/currency/refresh', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['currencies'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$currency_total = $this->model_localisation_currency->getTotalCurrencies();

		$results = $this->model_localisation_currency->getCurrencies($filter_data);

		foreach ($results as $result) {
			$this->data['currencies'][] = array(
				'currency_id'   => $result['currency_id'],
				'title'         => $result['title'] . (($result['code'] == $this->config->get('config_currency')) ? $this->data['text_default'] : null),
				'code'          => $result['code'],
				'value'         => $result['value'],
				'date_modified' => date($this->data['date_format_short'], strtotime($result['date_modified'])),
				'save'          => $this->url->link('localisation/currency/save', 'token=' . $this->session->data['token'] . '&currency_id=' . $result['currency_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
 
 		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');

		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());
		
		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // for sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_title'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=title' . $url, 'SSL');
		$this->data['sort_code'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=code' . $url, 'SSL');
		$this->data['sort_value'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=value' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . '&sort=date_modified' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $currency_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($currency_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($currency_total - $this->config->get('config_limit_admin'))) ? $currency_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $currency_total, ceil($currency_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/currency_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['currency_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');

		$this->data['error_title'] =  (isset($this->error['title'])? $this->error['title']: '');
		
		$this->data['error_code'] =  (isset($this->error['code'])? $this->error['code']: '');

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['currency_id'])) {
			$this->data['action'] = $this->url->link('localisation/currency/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/currency/save', 'token=' . $this->session->data['token'] . '&currency_id=' . $this->request->get['currency_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/currency', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['currency_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$currency_info = $this->model_localisation_currency->getCurrency($this->request->get['currency_id']);
		}

		if (!empty($currency_info) && !$this->error) {
			$this->data['title'] = $currency_info['title'];
		} else {
			$this->data['title'] = $this->request->post('title', '');
		}

		if (!empty($currency_info) && !$this->error) {
			$this->data['code'] = $currency_info['code'];
		} else {
			$this->data['code'] = $this->request->post('code', '');
		}

		if (!empty($currency_info) && !$this->error) {
			$this->data['symbol_left'] = $currency_info['symbol_left'];
		} else {
			$this->data['symbol_left'] = $this->request->post('symbol_left', '');
		}

		if (!empty($currency_info) && !$this->error) {
			$this->data['symbol_right'] = $currency_info['symbol_right'];
		} else {
			$this->data['symbol_right'] = $this->request->post('symbol_right', '');
		}

		if (!empty($currency_info) && !$this->error) {
			$this->data['decimal_place'] = $currency_info['decimal_place'];
		} else {
			$this->data['decimal_place'] = $this->request->post('decimal_place', '');
		}

		if (!empty($currency_info) && !$this->error) {
			$this->data['value'] = $currency_info['value'];
		} else {
			$this->data['value'] = $this->request->post('value', '');
		}

		if (!empty($currency_info) && !$this->error) {
			$this->data['status'] = $currency_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', false);
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/currency_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/currency')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['title']) < 3) || (utf8_strlen($this->request->post['title']) > 32)) {
			$this->error['title'] = $this->data['error_title'];
		}

		if (utf8_strlen($this->request->post['code']) != 3) {
			$this->error['code'] = $this->data['error_code'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/currency')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('setting/store');
		$this->load->model('sale/order');

		foreach ($this->request->post['selected'] as $currency_id) {
			$currency_info = $this->model_localisation_currency->getCurrency($currency_id);

			if ($currency_info) {
				if ($this->config->get('config_currency') == $currency_info['code']) {
					$this->error['warning'] = $this->data['error_default'];
				}

				$store_total = $this->model_setting_store->getTotalStoresByCurrency($currency_info['code']);

				if ($store_total) {
					$this->error['warning'] = sprintf($this->data['error_store'], $store_total);
				}
			}

			$order_total = $this->model_sale_order->getTotalOrdersByCurrencyId($currency_id);

			if ($order_total) {
				$this->error['warning'] = sprintf($this->data['error_order'], $order_total);
			}
		}

		return !$this->error;
	}
	
	protected function validateRefresh() {
		if (!$this->user->hasPermission('modify', 'localisation/currency')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}	
}