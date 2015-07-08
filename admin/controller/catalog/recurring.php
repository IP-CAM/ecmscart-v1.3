<?php
class ControllerCatalogRecurring extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('catalog/recurring');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/recurring');

		$this->getList();
	}

	public function save() { // Create by Manish in place of add and edit
		$this->data = $this->load->language('catalog/recurring');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/recurring');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['recurring_id'])){
				$this->model_catalog_recurring->editRecurring($this->request->get['recurring_id'], $this->request->post);
			} else{
				$this->model_catalog_recurring->addRecurring($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('catalog/recurring');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/recurring');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $recurring_id) {
				$this->model_catalog_recurring->deleteRecurring($recurring_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	public function copy() {
		$this->data = $this->load->language('catalog/recurring');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/recurring');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $recurring_id) {
				$this->model_catalog_recurring->copyRecurring($recurring_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','rd.name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->data['save'] = $this->url->link('catalog/recurring/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['copy'] = $this->url->link('catalog/recurring/copy', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/recurring/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['recurrings'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$recurring_total = $this->model_catalog_recurring->getTotalRecurrings($filter_data);

		$results = $this->model_catalog_recurring->getRecurrings($filter_data);

		foreach ($results as $result) {
			$this->data['recurrings'][] = array(
				'recurring_id' => $result['recurring_id'],
				'name'         => $result['name'],
				'sort_order'   => $result['sort_order'],
				'save'         => $this->url->link('catalog/recurring/save', 'token=' . $this->session->data['token'] . '&recurring_id=' . $result['recurring_id'] . $url, 'SSL')
			);
		}
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());

		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ;
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . '&sort=pd.name' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . '&sort=p.sort_order' . $url, 'SSL');
		
		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $recurring_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($recurring_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($recurring_total - $this->config->get('config_limit_admin'))) ? $recurring_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $recurring_total, ceil($recurring_total / $this->config->get('config_limit_admin')));
		
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/recurring_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['recurring_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']:array());

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['recurring_id'])) {
			$this->data['action'] = $this->url->link('catalog/recurring/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/recurring/save', 'token=' . $this->session->data['token'] . '&recurring_id=' . $this->request->get['recurring_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['recurring_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$recurring_info = $this->model_catalog_recurring->getRecurring($this->request->get['recurring_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (!empty($recurring_info) && !$this->error) {
			$this->data['recurring_description'] = $this->model_catalog_recurring->getRecurringDescription($recurring_info['recurring_id']);
		} else {
			$this->data['recurring_description'] = $this->request->post('recurring_description', array());
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['price'] = $recurring_info['price'];
		} else {
			$this->data['price'] = $this->request->post('price', 0);
		}

		$this->data['frequencies'] = array();

		$this->data['frequencies'][] = array(
			'text'  => $this->data['text_day'],
			'value' => 'day'
		);

		$this->data['frequencies'][] = array(
			'text'  => $this->data['text_week'],
			'value' => 'week'
		);

		$this->data['frequencies'][] = array(
			'text'  => $this->data['text_semi_month'],
			'value' => 'semi_month'
		);

		$this->data['frequencies'][] = array(
			'text'  => $this->data['text_month'],
			'value' => 'month'
		);

		$this->data['frequencies'][] = array(
			'text'  => $this->data['text_year'],
			'value' => 'year'
		);

		if (!empty($recurring_info) && !$this->error) {
			$this->data['frequency'] = $recurring_info['frequency'];
		} else {
			$this->data['frequency'] = $this->request->post('frequency', '');
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['duration'] = $recurring_info['duration'];
		} else {
			$this->data['duration'] = $this->request->post('duration', 0);
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['cycle'] = $recurring_info['cycle'];
		} else {
			$this->data['cycle'] = $this->request->post('cycle', 1);
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['status'] = $recurring_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', 0);
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['trial_price'] = $recurring_info['trial_price'];
		} else {
			$this->data['trial_price'] = $this->request->post('trial_price', 0.00);
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['trial_frequency'] = $recurring_info['trial_frequency'];
		} else {
			$this->data['trial_frequency'] = $this->request->post('trial_frequency', '');
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['trial_duration'] = $recurring_info['trial_duration'];
		} else {
			$this->data['trial_duration'] = $this->request->post('trial_duration', '0');
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['trial_cycle'] = $recurring_info['trial_cycle'];
		} else {
			$this->data['trial_cycle'] = $this->request->post('trial_cycle', '1');
		}
		if (!empty($recurring_info) && !$this->error) {
			$this->data['trial_status'] = $recurring_info['trial_status'];
		} else {
			$this->data['trial_status'] = $this->request->post('trial_status', 0);
		}

		if (!empty($recurring_info) && !$this->error) {
			$this->data['sort_order'] = $recurring_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', 0);
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/recurring_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/recurring')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['recurring_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->data['error_warning'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/recurring')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $recurring_id) {
			$product_total = $this->model_catalog_product->getTotalProductsByProfileId($recurring_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->data['error_product'], $product_total);
			}
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'catalog/recurring')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}