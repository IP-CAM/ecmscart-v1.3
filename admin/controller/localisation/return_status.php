<?php
class ControllerLocalisationReturnStatus extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('localisation/return_status');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/return_status');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/return_status');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/return_status');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['return_status_id'])){
				$this->model_localisation_return_status->editReturnStatus($this->request->get['return_status_id'], $this->request->post);
			} else{
				$this->model_localisation_return_status->addReturnStatus($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/return_status', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('localisation/return_status');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/return_status');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $return_status_id) {
				$this->model_localisation_return_status->deleteReturnStatus($return_status_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('localisation/return_status', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/return_status', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('localisation/return_status/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/return_status/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['return_statuses'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$return_status_total = $this->model_localisation_return_status->getTotalReturnStatuses();

		$results = $this->model_localisation_return_status->getReturnStatuses($filter_data);

		foreach ($results as $result) {
			$this->data['return_statuses'][] = array(
				'return_status_id' => $result['return_status_id'],
				'name'             => $result['name'] . (($result['return_status_id'] == $this->config->get('config_return_status_id')) ? $this->data['text_default'] : null),
				'save'             => $this->url->link('localisation/return_status/save', 'token=' . $this->session->data['token'] . '&return_status_id=' . $result['return_status_id'] . $url, 'SSL')
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

		$this->data['sort_name'] = $this->url->link('localisation/return_status', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $return_status_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/return_status', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');
		
		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($return_status_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($return_status_total - $this->config->get('config_limit_admin'))) ? $return_status_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $return_status_total, ceil($return_status_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/return_status_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['return_status_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: '');
		
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/return_status', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['return_status_id'])) {
			$this->data['action'] = $this->url->link('localisation/return_status/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/return_status/save', 'token=' . $this->session->data['token'] . '&return_status_id=' . $this->request->get['return_status_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/return_status', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['return_status_id']) && !$this->error) {
			$this->data['return_status'] = $this->model_localisation_return_status->getReturnStatusDescriptions($this->request->get['return_status_id']);
		} else {
			$this->data['return_status'] = $this->request->post('return_status', array());
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/return_status_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/return_status')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['return_status'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/return_status')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('sale/return');

		foreach ($this->request->post['selected'] as $return_status_id) {
			if ($this->config->get('config_return_status_id') == $return_status_id) {
				$this->error['warning'] = $this->data['error_default'];
			}

			$return_total = $this->model_sale_return->getTotalReturnsByReturnStatusId($return_status_id);

			if ($return_total) {
				$this->error['warning'] = sprintf($this->data['error_return'], $return_total);
			}

			$return_total = $this->model_sale_return->getTotalReturnHistoriesByReturnStatusId($return_status_id);

			if ($return_total) {
				$this->error['warning'] = sprintf($this->data['error_return'], $return_total);
			}
		}

		return !$this->error;
	}
}