<?php
class ControllerMarketingCoupon extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);
	public function index() {
		$this->data = $this->load->language('marketing/coupon');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/coupon');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('marketing/coupon');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/coupon');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['coupon_id'])){
				$this->model_marketing_coupon->editCoupon($this->request->get['coupon_id'], $this->request->post);
			} else{
				$this->model_marketing_coupon->addCoupon($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('marketing/coupon');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('marketing/coupon');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $coupon_id) {
				$this->model_marketing_coupon->deleteCoupon($coupon_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'name');
		
		$order = $this->request->get('order', 'ASC');
		
		$page = $this->request->get('page', 1);
		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->data['save'] = $this->url->link('marketing/coupon/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('marketing/coupon/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['coupons'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$coupon_total = $this->model_marketing_coupon->getTotalCoupons();

		$results = $this->model_marketing_coupon->getCoupons($filter_data);

		foreach ($results as $result) {
			$this->data['coupons'][] = array(
				'coupon_id'  => $result['coupon_id'],
				'name'       => $result['name'],
				'code'       => $result['code'],
				'discount'   => $result['discount'],
				'date_start' => date($this->data['date_format_short'], strtotime($result['date_start'])),
				'date_end'   => date($this->data['date_format_short'], strtotime($result['date_end'])),
				'status'     => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'save'       => $this->url->link('marketing/coupon/save', 'token=' . $this->session->data['token'] . '&coupon_id=' . $result['coupon_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['selected'] =  $this->request->post('selected', array());

		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // important part for order in url to set ASC or DESC
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
			
		$this->data['sort_name'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_code'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=code' . $url, 'SSL');
		$this->data['sort_discount'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=discount' . $url, 'SSL');
		$this->data['sort_date_start'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=date_start' . $url, 'SSL');
		$this->data['sort_date_end'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=date_end' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . '&sort=status' . $url, 'SSL');

		// Sorting and Filter Function for filter variable again
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $coupon_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($coupon_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($coupon_total - $this->config->get('config_limit_admin'))) ? $coupon_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $coupon_total, ceil($coupon_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/coupon_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['coupon_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['token'] = $this->session->data['token'];

		$this->data['coupon_id'] = $this->request->get('coupon_id', 0);

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: '');

		$this->data['error_code'] =  (isset($this->error['code'])? $this->error['code']: '');

		$this->data['error_date_start'] =  (isset($this->error['date_start'])? $this->error['date_start']: '');
		
		$this->data['error_date_end'] =  (isset($this->error['date_end'])? $this->error['date_end']: '');

		// Filter and Sorting Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['coupon_id'])) {
			$this->data['action'] = $this->url->link('marketing/coupon/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('marketing/coupon/save', 'token=' . $this->session->data['token'] . '&coupon_id=' . $this->request->get['coupon_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('marketing/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['coupon_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
			$coupon_info = $this->model_marketing_coupon->getCoupon($this->request->get['coupon_id']);
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['name'] = $coupon_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['code'] = $coupon_info['code'];
		} else {
			$this->data['code'] = $this->request->post('code', '');
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['type'] = $coupon_info['type'];
		} else {
			$this->data['type'] = $this->request->post('type', '');
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['discount'] = $coupon_info['discount'];
		} else {
			$this->data['discount'] = $this->request->post('discount', '');
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['logged'] = $coupon_info['logged'];
		} else {
			$this->data['logged'] = $this->request->post('logged', '');
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['shipping'] = $coupon_info['shipping'];
		} else {
			$this->data['shipping'] = $this->request->post('shipping', '');
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['total'] = $coupon_info['total'];
		} else {
			$this->data['total'] = $this->request->post('total', '');
		}

		if (isset($this->request->get['coupon_id'])) {
			$products = $this->model_marketing_coupon->getCouponProducts($this->request->get['coupon_id']);
		} else {
			$products = $this->request->post('coupon_product', array());
		}

		$this->load->model('catalog/product');

		$this->data['coupon_product'] = array();

		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$this->data['coupon_product'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				);
			}
		}

		if (isset($this->request->get['coupon_id'])) {
			$categories = $this->model_marketing_coupon->getCouponCategories($this->request->get['coupon_id']);
		} else {
			$categories = $this->request->post('coupon_category', array());
		}

		$this->load->model('catalog/category');

		$this->data['coupon_category'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$this->data['coupon_category'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path'] ? $category_info['path'] . ' &gt; ' : '') . $category_info['name']
				);
			}
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['date_start'] = ($coupon_info['date_start'] != '0000-00-00' ? $coupon_info['date_start'] : '');
		} else {
			$this->data['date_start'] = $this->request->post('date_start', date('Y-m-d', time()));
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['date_end'] = ($coupon_info['date_end'] != '0000-00-00' ? $coupon_info['date_end'] : '');
		} else {
			$this->data['date_end'] = $this->request->post('date_end', date('Y-m-d', strtotime('+1 month')));
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['uses_total'] = $coupon_info['uses_total'];
		} else {
			$this->data['uses_total'] = $this->request->post('uses_total', 1);
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['uses_customer'] = $coupon_info['uses_customer'];
		} else {
			$this->data['uses_customer'] = $this->request->post('uses_customer', 1);
		}

		if (!empty($coupon_info) && !$this->error) {
			$this->data['status'] = $coupon_info['status'];
		} else {
			$this->data['status'] = $this->request->post('uses_customer', true);
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/coupon_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'marketing/coupon')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 128)) {
			$this->error['name'] = $this->data['error_name'];
		}

		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 10)) {
			$this->error['code'] = $this->data['error_code'];
		}

		$coupon_info = $this->model_marketing_coupon->getCouponByCode($this->request->post['code']);

		if ($coupon_info) {
			if (!isset($this->request->get['coupon_id'])) {
				$this->error['warning'] = $this->data['error_exists'];
			} elseif ($coupon_info['coupon_id'] != $this->request->get['coupon_id']) {
				$this->error['warning'] = $this->data['error_exists'];
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'marketing/coupon')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function history() {
		$this->data = $this->load->language('marketing/coupon');

		$this->load->model('marketing/coupon');

		$page = $this->request->get('page', 1);
		
		$this->data['histories'] = array();

		$results = $this->model_marketing_coupon->getCouponHistories($this->request->get['coupon_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['histories'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'amount'     => $result['amount'],
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_marketing_coupon->getTotalCouponHistories($this->request->get['coupon_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('marketing/coupon/history', 'token=' . $this->session->data['token'] . '&coupon_id=' . $this->request->get['coupon_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('marketing/coupon_history.tpl', $this->data));
	}
}
