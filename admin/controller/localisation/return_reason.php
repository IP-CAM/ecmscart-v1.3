<?php
class ControllerLocalisationReturnReason extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('localisation/return_reason');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/return_reason');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/return_reason');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/return_reason');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['return_reason_id'])){
				$this->model_localisation_return_reason->editReturnReason($this->request->get['return_reason_id'], $this->request->post);
			} else{
				$this->model_localisation_return_reason->addReturnReason($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('localisation/return_reason');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/return_reason');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $return_reason_id) {
				$this->model_localisation_return_reason->deleteReturnReason($return_reason_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
							$this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('localisation/return_reason/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/return_reason/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['return_reasons'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$return_reason_total = $this->model_localisation_return_reason->getTotalReturnReasons();

		$results = $this->model_localisation_return_reason->getReturnReasons($filter_data);

		foreach ($results as $result) {
			$this->data['return_reasons'][] = array(
				'return_reason_id' => $result['return_reason_id'],
				'name'             => $result['name'],
				'save'             => $this->url->link('localisation/return_reason/save', 'token=' . $this->session->data['token'] . '&return_reason_id=' . $result['return_reason_id'] . $url, 'SSL')
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

		$this->data['sort_name'] = $this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $return_reason_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($return_reason_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($return_reason_total - $this->config->get('config_limit_admin'))) ? $return_reason_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $return_reason_total, ceil($return_reason_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/return_reason_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['return_reason_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: array());
		
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));


		if (!isset($this->request->get['return_reason_id']) && !$this->error) {
			$this->data['action'] = $this->url->link('localisation/return_reason/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/return_reason/save', 'token=' . $this->session->data['token'] . '&return_reason_id=' . $this->request->get['return_reason_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/return_reason', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['return_reason_id'])) {
			$this->data['return_reason'] = $this->model_localisation_return_reason->getReturnReasonDescriptions($this->request->get['return_reason_id']);
		} else {
			$this->data['return_reason'] = $this->request->post('return_reason', array());
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/return_reason_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/return_reason')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['return_reason'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 128)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/return_reason')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('sale/return');

		foreach ($this->request->post['selected'] as $return_reason_id) {
			$return_total = $this->model_sale_return->getTotalReturnsByReturnReasonId($return_reason_id);

			if ($return_total) {
				$this->error['warning'] = sprintf($this->data['error_return'], $return_total);
			}
		}

		return !$this->error;
	}
}
