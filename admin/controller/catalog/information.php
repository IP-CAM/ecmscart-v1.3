<?php
class ControllerCatalogInformation extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('catalog/information');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/information');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('catalog/information');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/information');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['information_id'])){
				$this->model_catalog_information->editInformation($this->request->get['information_id'], $this->request->post);
			} else{
				$this->model_catalog_information->addInformation($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/information', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}
	
	public function delete() {
		$this->data = $this->load->language('catalog/information');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('catalog/information');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $information_id) {
				$this->model_catalog_information->deleteInformation($information_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('catalog/information', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','id.title');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/information', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->data['save'] = $this->url->link('catalog/information/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('catalog/information/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['informations'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$information_total = $this->model_catalog_information->getTotalInformations();

		$results = $this->model_catalog_information->getInformations($filter_data);

		foreach ($results as $result) {
			$this->data['informations'][] = array(
				'information_id' => $result['information_id'],
				'title'          => $result['title'],
				'sort_order'     => $result['sort_order'],
				'save'           => $this->url->link('catalog/information/save', 'token=' . $this->session->data['token'] . '&information_id=' . $result['information_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());

		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; //For sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_title'] = $this->url->link('catalog/information', 'token=' . $this->session->data['token'] . '&sort=id.title' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('catalog/information', 'token=' . $this->session->data['token'] . '&sort=i.sort_order' . $url, 'SSL');
		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $information_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/information', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($information_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($information_total - $this->config->get('config_limit_admin'))) ? $information_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $information_total, ceil($information_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/information_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['information_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_title'] =  (isset($this->error['title'])?$this->error['title']:array());
		
		$this->data['error_description'] =  (isset($this->error['description'])?$this->error['description']:array());
		
		$this->data['error_meta_title'] =  (isset($this->error['meta_title'])?$this->error['meta_title']:array());
		
		$this->data['error_keyword'] =  isset($this->error['keyword']) ? $this->error['keyword']: array();
						
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/information', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['information_id'])) {
			$this->data['action'] = $this->url->link('catalog/information/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('catalog/information/save', 'token=' . $this->session->data['token'] . '&information_id=' . $this->request->get['information_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('catalog/information', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['information_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$information_info = $this->model_catalog_information->getInformation($this->request->get['information_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['information_id']) && !$this->error) {
			$this->data['information_description'] = $this->model_catalog_information->getInformationDescriptions($this->request->get['information_id']);
		} else {
			$this->data['information_description'] = $this->request->post('information_description', array());
		}
				
		if (isset($this->request->get['information_id']) && !$this->error) {
			$this->data['information_keyword'] = $this->model_catalog_information->getInformationKeyword($this->request->get['information_id']);
		} else {
			$this->data['information_keyword'] = $this->request->post('information_keyword', array());
		}

		$this->load->model('setting/store');

		$this->data['stores'] = $this->model_setting_store->getStores();

		if (isset($this->request->get['information_id']) && !$this->error) {
			$this->data['information_store'] = $this->model_catalog_information->getInformationStores($this->request->get['information_id']);
		} else {
			$this->data['information_store'] = $this->request->post('information_store', array(0));
		}

		if (!empty($information_info) && !$this->error) {
			$this->data['bottom'] = $information_info['bottom'];
		} else {
			$this->data['bottom'] = $this->request->post('bottom', 0);
		}

		if (!empty($information_info) && !$this->error) {
			$this->data['status'] = $information_info['status'];
		} else {
			$this->data['status'] =  $this->request->post('status', true);
		}

		if (!empty($information_info) && !$this->error) {
			$this->data['sort_order'] = $information_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', 0);
		}

		if (isset($this->request->get['information_id']) && !$this->error) {
			$this->data['information_layout'] = $this->model_catalog_information->getInformationLayouts($this->request->get['information_id']);
		} else {
			$this->data['information_layout'] = $this->request->post('information_layout', array());
		}

		$this->load->model('design/layout');

		$this->data['layouts'] = $this->model_design_layout->getLayouts();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/information_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/information')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['information_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 64)) {
				$this->error['title'][$language_id] = $this->data['error_title'];
			}

			if (utf8_strlen($value['description']) < 3) {
				$this->error['description'][$language_id] = $this->data['error_description'];
			}

			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->data['error_meta_title'];
			}
		}

		foreach ($this->request->post['information_keyword'] as $language_id => $value) {	
			if (utf8_strlen($value['keyword']) > 0) {
				$this->load->model('catalog/url_alias');

				$url_alias_info = $this->model_catalog_url_alias->getUrlAlias($value['keyword']);
	
				if ($url_alias_info && isset($this->request->get['information_id']) && $url_alias_info['query'] != 'information_id=' . $this->request->get['information_id']) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}

				if ($url_alias_info && !isset($this->request->get['information_id'])) {
				$this->error['keyword'][$language_id] = sprintf($this->data['error_keyword']);
				}			
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->data['error_warning'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/information')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('setting/store');

		foreach ($this->request->post['selected'] as $information_id) {
			if ($this->config->get('config_account_id') == $information_id) {
				$this->error['warning'] = $this->data['error_account'];
			}

			if ($this->config->get('config_checkout_id') == $information_id) {
				$this->error['warning'] = $this->data['error_checkout'];
			}

			if ($this->config->get('config_affiliate_id') == $information_id) {
				$this->error['warning'] = $this->data['error_affiliate'];
			}

			if ($this->config->get('config_return_id') == $information_id) {
				$this->error['warning'] = $this->data['error_return'];
			}

			$store_total = $this->model_setting_store->getTotalStoresByInformationId($information_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->data['error_store'], $store_total);
			}
		}

		return !$this->error;
	}
}