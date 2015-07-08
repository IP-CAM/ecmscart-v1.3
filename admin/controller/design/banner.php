<?php
class ControllerDesignBanner extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('design/banner');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('design/banner');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('design/banner');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('design/banner');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['banner_id'])){
				$this->model_design_banner->editBanner($this->request->get['banner_id'], $this->request->post);
			} else{
				$this->model_design_banner->addBanner($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('design/banner', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('design/banner');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('design/banner');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $banner_id) {
				$this->model_design_banner->deleteBanner($banner_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('design/banner', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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
							$this->url->link('design/banner', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->data['save'] = $this->url->link('design/banner/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('design/banner/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['banners'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$banner_total = $this->model_design_banner->getTotalBanners();

		$results = $this->model_design_banner->getBanners($filter_data);

		foreach ($results as $result) {
			$this->data['banners'][] = array(
				'banner_id' => $result['banner_id'],
				'name'      => $result['name'],
				'status'    => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'save'      => $this->url->link('design/banner/save', 'token=' . $this->session->data['token'] . '&banner_id=' . $result['banner_id'] . $url, 'SSL')
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

		$this->data['sort_name'] = $this->url->link('design/banner', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('design/banner', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $banner_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('design/banner', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($banner_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($banner_total - $this->config->get('config_limit_admin'))) ? $banner_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $banner_total, ceil($banner_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/banner_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['banner_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: array());

		if (isset($this->error['banner_image'])) {
			$this->data['error_banner_image'] = $this->error['banner_image'];
		} else {
			$this->data['error_banner_image'] = array();
		}

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('design/banner', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['banner_id'])) {
			$this->data['action'] = $this->url->link('design/banner/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('design/banner/save', 'token=' . $this->session->data['token'] . '&banner_id=' . $this->request->get['banner_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('design/banner', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['banner_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$banner_info = $this->model_design_banner->getBanner($this->request->get['banner_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		if (!empty($banner_info) && !$this->error) {
			$this->data['name'] = $banner_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($banner_info) && !$this->error) {
			$this->data['status'] = $banner_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', true);
		}

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('tool/image');

		if (isset($this->request->get['banner_id']) && !$this->error) {
			$banner_images = $this->model_design_banner->getBannerImages($this->request->get['banner_id']);
		} else {
			$banner_images = $this->request->post('banner_image', array());
		}

		$this->data['banner_images'] = array();

		foreach ($banner_images as $banner_image) {

			$this->data['banner_images'][] = array(
				'banner_image_description' => $banner_image['banner_image_description'],
				'link'                     => $banner_image['link'],
				'image'                    => $banner_image['image'],
				'thumb'                    => $this->model_tool_image->resize($banner_image['image'], 100, 100),
				'sort_order'               => $banner_image['sort_order']
			);
		}

		$this->data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('design/banner_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'design/banner')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->data['error_name'];
		}

		if (isset($this->request->post['banner_image'])) {
			foreach ($this->request->post['banner_image'] as $banner_image_id => $banner_image) {
				foreach ($banner_image['banner_image_description'] as $language_id => $banner_image_description) {
					if ((utf8_strlen($banner_image_description['title']) < 2) || (utf8_strlen($banner_image_description['title']) > 64)) {
						$this->error['banner_image'][$banner_image_id][$language_id] = $this->data['error_title'];
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'design/banner')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}