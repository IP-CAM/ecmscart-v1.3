<?php
class ControllerLocalisationLanguage extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('localisation/language');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/language');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/language');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/language');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['language_id'])){
				$this->model_localisation_language->editLanguage($this->request->get['language_id'], $this->request->post);
			} else{
				$this->model_localisation_language->addLanguage($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data =  $this->load->language('localisation/language');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/language');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $language_id) {
				$this->model_localisation_language->deleteLanguage($language_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
							$this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('localisation/language/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/language/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['languages'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$language_total = $this->model_localisation_language->getTotalLanguages();

		$results = $this->model_localisation_language->getLanguages($filter_data);

		foreach ($results as $result) {
			$this->data['languages'][] = array(
				'language_id' => $result['language_id'],
				'name'        => $result['name'] . (($result['code'] == $this->config->get('config_language')) ? $this->data['text_default'] : null),
				'code'        => $result['code'],
				'sort_order'  => $result['sort_order'],
				'save'        => $this->url->link('localisation/language/save', 'token=' . $this->session->data['token'] . '&language_id=' . $result['language_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());
		
		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // for sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('localisation/language', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_code'] = $this->url->link('localisation/language', 'token=' . $this->session->data['token'] . '&sort=code' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('localisation/language', 'token=' . $this->session->data['token'] . '&sort=sort_order' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $language_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($language_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($language_total - $this->config->get('config_limit_admin'))) ? $language_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $language_total, ceil($language_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/language_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['language_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: '');
		
		$this->data['error_code'] =  (isset($this->error['code'])? $this->error['code']: '');

		$this->data['error_locale'] =  (isset($this->error['locale'])? $this->error['locale']: '');
		
		$this->data['error_image'] =  (isset($this->error['image'])? $this->error['image']: '');

		$this->data['error_directory'] =  (isset($this->error['directory'])? $this->error['directory']: '');
		
		$this->data['error_filename'] =  (isset($this->error['filename'])? $this->error['filename']: '');

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['language_id'])) {
			$this->data['action'] = $this->url->link('localisation/language/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/language/save', 'token=' . $this->session->data['token'] . '&language_id=' . $this->request->get['language_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/language', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['language_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$language_info = $this->model_localisation_language->getLanguage($this->request->get['language_id']);
		}

		if (!empty($language_info) && !$this->error) {
			$this->data['name'] = $language_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($language_info) && !$this->error) {
			$this->data['code'] = $language_info['code'];
		} else {
			$this->data['code'] = $this->request->post('code', '');
		}

		if (!empty($language_info) && !$this->error) {
			$this->data['locale'] = $language_info['locale'];
		} else {
			$this->data['locale'] = $this->request->post('locale', '');
		}

		if (!empty($language_info) && !$this->error) {
			$this->data['image'] = $language_info['image'];
		} else {
			$this->data['image'] = $this->request->post('image', '');
		}

		if (!empty($language_info) && !$this->error) {
			$this->data['directory'] = $language_info['directory'];
		} else {
			$this->data['directory'] = $this->request->post('directory', '');
		}

		if (!empty($language_info) && !$this->error) {
			$this->data['sort_order'] = $language_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', 1);
		}

		if (!empty($language_info) && !$this->error) {
			$this->data['status'] = $language_info['status'];
		} else {
			$this->data['status'] = $this->request->post('sort_order', true);
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/language_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/language')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->data['error_name'];
		}

		if (utf8_strlen($this->request->post['code']) < 2) {
			$this->error['code'] = $this->data['error_code'];
		}

		if (!$this->request->post['locale']) {
			$this->error['locale'] = $this->data['error_locale'];
		}

		if (!$this->request->post['directory']) {
			$this->error['directory'] = $this->data['error_directory'];
		}

		if ((utf8_strlen($this->request->post['image']) < 3) || (utf8_strlen($this->request->post['image']) > 32)) {
			$this->error['image'] = $this->data['error_image'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/language')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('setting/store');
		$this->load->model('sale/order');

		foreach ($this->request->post['selected'] as $language_id) {
			$language_info = $this->model_localisation_language->getLanguage($language_id);

			if ($language_info) {
				if ($this->config->get('config_language') == $language_info['code']) {
					$this->error['warning'] = $this->data['error_default'];
				}

				if ($this->config->get('config_admin_language') == $language_info['code']) {
					$this->error['warning'] = $this->data['error_admin'];
				}

				$store_total = $this->model_setting_store->getTotalStoresByLanguage($language_info['code']);

				if ($store_total) {
					$this->error['warning'] = sprintf($this->data['error_store'], $store_total);
				}
			}

			$order_total = $this->model_sale_order->getTotalOrdersByLanguageId($language_id);

			if ($order_total) {
				$this->error['warning'] = sprintf($this->data['error_order'], $order_total);
			}
		}

		return !$this->error;
	}
}
