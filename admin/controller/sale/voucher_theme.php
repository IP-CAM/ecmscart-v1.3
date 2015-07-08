<?php
class ControllerSaleVoucherTheme extends Controller {
	private $error = array();
	
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('sale/voucher_theme');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/voucher_theme');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('sale/voucher_theme');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/voucher_theme');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['voucher_theme_id'])){
				$this->model_sale_voucher_theme->editVoucherTheme($this->request->get['voucher_theme_id'], $this->request->post);
			} else{
				$this->model_sale_voucher_theme->addVoucherTheme($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/voucher_theme', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('sale/voucher_theme');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/voucher_theme');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $voucher_theme_id) {
				$this->model_sale_voucher_theme->deleteVoucherTheme($voucher_theme_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/voucher_theme', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'vtd.name');
		
		$order = $this->request->get('order', 'ASC');

		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/voucher_theme', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('sale/voucher_theme/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/voucher_theme/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['voucher_themes'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$voucher_theme_total = $this->model_sale_voucher_theme->getTotalVoucherThemes();

		$results = $this->model_sale_voucher_theme->getVoucherThemes($filter_data);

		foreach ($results as $result) {
			$this->data['voucher_themes'][] = array(
				'voucher_theme_id' => $result['voucher_theme_id'],
				'name'             => $result['name'],
				'save'             => $this->url->link('sale/voucher_theme/save', 'token=' . $this->session->data['token'] . '&voucher_theme_id=' . $result['voucher_theme_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
			
		$this->data['selected'] =  $this->request->post('selected', array());
		
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; 

		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
		
		$this->data['sort_name'] = $this->url->link('sale/voucher_theme', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');

		$pagination = new Pagination();
		$pagination->total = $voucher_theme_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/voucher_theme', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($voucher_theme_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($voucher_theme_total - $this->config->get('config_limit_admin'))) ? $voucher_theme_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $voucher_theme_total, ceil($voucher_theme_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/voucher_theme_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['voucher_theme_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']: array());
		
		$this->data['error_image'] =  (isset($this->error['image'])?$this->error['image']: '');
		//for sorting and paging
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/voucher_theme', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['voucher_theme_id'])) {
			$this->data['action'] = $this->url->link('sale/voucher_theme/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/voucher_theme/save', 'token=' . $this->session->data['token'] . '&voucher_theme_id=' . $this->request->get['voucher_theme_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/voucher_theme', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['voucher_theme_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$voucher_theme_info = $this->model_sale_voucher_theme->getVoucherTheme($this->request->get['voucher_theme_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['voucher_theme_id'])) {
			$this->data['voucher_theme_description'] = $this->model_sale_voucher_theme->getVoucherThemeDescriptions($this->request->get['voucher_theme_id']);
		} else {
			$this->data['voucher_theme_description'] = array();
		}

		if (!empty($voucher_theme_info) && !$this->error) {
			$this->data['image'] = $voucher_theme_info['image'];
		} else {
			$this->data['image'] =  $this->request->post('image', '');
		}

		$this->load->model('tool/image');
		
		if (!empty($voucher_theme_info) && !$this->error) {
			$this->data['thumb'] = $this->model_tool_image->resize($voucher_theme_info['image'], 100, 100);
		} else {
			$this->data['thumb'] = $this->model_tool_image->resize($this->request->post('image', ''), 100, 100); 
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/voucher_theme_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/voucher_theme')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['voucher_theme_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 32)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}
		}

		if (!$this->request->post['image']) {
			$this->error['image'] = $this->data['error_image'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/voucher_theme')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('sale/voucher');

		foreach ($this->request->post['selected'] as $voucher_theme_id) {
			$voucher_total = $this->model_sale_voucher->getTotalVouchersByVoucherThemeId($voucher_theme_id);

			if ($voucher_total) {
				$this->error['warning'] = sprintf($this->data['error_voucher'], $voucher_total);
			}
		}

		return !$this->error;
	}
}