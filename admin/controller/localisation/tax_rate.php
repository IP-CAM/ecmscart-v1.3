<?php
class ControllerLocalisationTaxRate extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);


	public function index() {
		$this->data = $this->load->language('localisation/tax_rate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/tax_rate');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/tax_rate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/tax_rate');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['tax_rate_id'])){
				$this->model_localisation_tax_rate->editTaxRate($this->request->get['tax_rate_id'], $this->request->post);
			} else{
				$this->model_localisation_tax_rate->addTaxRate($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('localisation/tax_rate');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/tax_rate');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $tax_rate_id) {
				$this->model_localisation_tax_rate->deleteTaxRate($tax_rate_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','tr.name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
						
		$this->data['save'] = $this->url->link('localisation/tax_rate/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/tax_rate/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['tax_rates'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$tax_rate_total = $this->model_localisation_tax_rate->getTotalTaxRates();

		$results = $this->model_localisation_tax_rate->getTaxRates($filter_data);

		foreach ($results as $result) {
			$this->data['tax_rates'][] = array(
				'tax_rate_id'   => $result['tax_rate_id'],
				'name'          => $result['name'],
				'rate'          => $result['rate'],
				'type'          => ($result['type'] == 'F' ? $this->data['text_amount'] : $this->data['text_percent']),
				'geo_zone'      => $result['geo_zone'],
				'date_added'    => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'date_modified' => date($this->data['date_format_short'], strtotime($result['date_modified'])),
				'save'          => $this->url->link('localisation/tax_rate/save', 'token=' . $this->session->data['token'] . '&tax_rate_id=' . $result['tax_rate_id'] . $url, 'SSL')
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

		$this->data['sort_name'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.name' . $url, 'SSL');
		$this->data['sort_rate'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.rate' . $url, 'SSL');
		$this->data['sort_type'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.type' . $url, 'SSL');
		$this->data['sort_geo_zone'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=gz.name' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.date_added' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.date_modified' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $tax_rate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($tax_rate_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($tax_rate_total - $this->config->get('config_limit_admin'))) ? $tax_rate_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $tax_rate_total, ceil($tax_rate_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/tax_rate_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['tax_rate_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: '');
		
		$this->data['error_rate'] =  (isset($this->error['rate'])? $this->error['rate']: '');

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['tax_rate_id'])) {
			$this->data['action'] = $this->url->link('localisation/tax_rate/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/tax_rate/save', 'token=' . $this->session->data['token'] . '&tax_rate_id=' . $this->request->get['tax_rate_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['tax_rate_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$tax_rate_info = $this->model_localisation_tax_rate->getTaxRate($this->request->get['tax_rate_id']);
		}

		if (!empty($tax_rate_info) && !$this->error) {
			$this->data['name'] = $tax_rate_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($tax_rate_info) && !$this->error) {
			$this->data['rate'] = $tax_rate_info['rate'];
		} else {
			$this->data['rate'] = $this->request->post('rate', '');
		}

		if (!empty($tax_rate_info) && !$this->error) {
			$this->data['type'] = $tax_rate_info['type'];
		} else {
			$this->data['type'] = $this->request->post('type', '');
		}

		if (isset($this->request->get['tax_rate_id']) && !$this->error) {
			$this->data['tax_rate_customer_group'] = $this->model_localisation_tax_rate->getTaxRateCustomerGroups($this->request->get['tax_rate_id']);
		} else {
			$this->data['tax_rate_customer_group'] = $this->request->post('tax_rate_customer_group', array($this->config->get('config_customer_group_id'))); 
		}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		if (!empty($tax_rate_info) && !$this->error) {
			$this->data['geo_zone_id'] = $tax_rate_info['geo_zone_id'];
		} else {
			$this->data['geo_zone_id'] = $this->request->post('geo_zone_id', '');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/tax_rate_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/tax_rate')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->data['error_name'];
		}

		if (!$this->request->post['rate']) {
			$this->error['rate'] = $this->data['error_rate'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/tax_rate')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('localisation/tax_class');

		foreach ($this->request->post['selected'] as $tax_rate_id) {
			$tax_rule_total = $this->model_localisation_tax_class->getTotalTaxRulesByTaxRateId($tax_rate_id);

			if ($tax_rule_total) {
				$this->error['warning'] = sprintf($this->data['error_tax_rule'], $tax_rule_total);
			}
		}

		return !$this->error;
	}
}