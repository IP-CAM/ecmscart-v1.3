<?php
class ControllerLocalisationWeightClass extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('localisation/weight_class');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/weight_class');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/weight_class');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/weight_class');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['weight_class_id'])){
				$this->model_localisation_weight_class->editWeightClass($this->request->get['weight_class_id'], $this->request->post);
			} else{
				$this->model_localisation_weight_class->addWeightClass($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('localisation/weight_class');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/weight_class');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $weight_class_id) {
				$this->model_localisation_weight_class->deleteWeightClass($weight_class_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
							$this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('localisation/weight_class/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/weight_class/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['weight_classes'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$weight_class_total = $this->model_localisation_weight_class->getTotalWeightClasses();

		$results = $this->model_localisation_weight_class->getWeightClasses($filter_data);

		foreach ($results as $result) {
			$this->data['weight_classes'][] = array(
				'weight_class_id' => $result['weight_class_id'],
				'title'           => $result['title'] . (($result['weight_class_id'] == $this->config->get('config_weight_class_id')) ? $this->data['text_default'] : null),
				'unit'            => $result['unit'],
				'value'           => $result['value'],
				'save'            => $this->url->link('localisation/weight_class/save', 'token=' . $this->session->data['token'] . '&weight_class_id=' . $result['weight_class_id'] . $url, 'SSL')
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

		$this->data['sort_title'] = $this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . '&sort=title' . $url, 'SSL');
		$this->data['sort_unit'] = $this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . '&sort=unit' . $url, 'SSL');
		$this->data['sort_value'] = $this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . '&sort=value' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $weight_class_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($weight_class_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($weight_class_total - $this->config->get('config_limit_admin'))) ? $weight_class_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $weight_class_total, ceil($weight_class_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/weight_class_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['weight_class_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_title'] =  (isset($this->error['title'])? $this->error['title']: '');
		
		$this->data['error_unit'] =  (isset($this->error['unit'])? $this->error['unit']: '');
	

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['weight_class_id'])) {
			$this->data['action'] = $this->url->link('localisation/weight_class/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/weight_class/save', 'token=' . $this->session->data['token'] . '&weight_class_id=' . $this->request->get['weight_class_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/weight_class', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['weight_class_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$weight_class_info = $this->model_localisation_weight_class->getWeightClass($this->request->get['weight_class_id']);
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['weight_class_id']) && !$this->error) {
			$this->data['weight_class_description'] = $this->model_localisation_weight_class->getWeightClassDescriptions($this->request->get['weight_class_id']);
		} else {
			$this->data['weight_class_description'] = $this->request->post('weight_class_description', array());
		}

		if (!empty($tax_class_info) && !$this->error) {
			$this->data['value'] = $weight_class_info['value'];
		} else {
			$this->data['value'] = $this->request->post('value', '');
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/weight_class_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/weight_class')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['weight_class_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 32)) {
				$this->error['title'][$language_id] = $this->data['error_title'];
			}

			if (!$value['unit'] || (utf8_strlen($value['unit']) > 4)) {
				$this->error['unit'][$language_id] = $this->data['error_unit'];
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/weight_class')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('catalog/product');

		foreach ($this->request->post['selected'] as $weight_class_id) {
			if ($this->config->get('config_weight_class_id') == $weight_class_id) {
				$this->error['warning'] = $this->data['error_default'];
			}

			$product_total = $this->model_catalog_product->getTotalProductsByWeightClassId($weight_class_id);

			if ($product_total) {
				$this->error['warning'] = sprintf($this->data['error_product'], $product_total);
			}
		}

		return !$this->error;
	}
}