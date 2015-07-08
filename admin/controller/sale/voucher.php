<?php
class ControllerSaleVoucher extends Controller {
	private $error = array();
	
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('sale/voucher');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/voucher');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('sale/voucher');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/voucher');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['voucher_id'])){
				$this->model_sale_voucher->editVoucher($this->request->get['voucher_id'], $this->request->post);
			} else{
				$this->model_sale_voucher->addVoucher($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('sale/voucher');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/voucher');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $voucher_id) {
				$this->model_sale_voucher->deleteVoucher($voucher_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'v.date_added');
		
		$order = $this->request->get('order', 'DESC');

		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('sale/voucher/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/voucher/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['vouchers'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$voucher_total = $this->model_sale_voucher->getTotalVouchers();

		$results = $this->model_sale_voucher->getVouchers($filter_data);

		foreach ($results as $result) {
			$this->data['vouchers'][] = array(
				'voucher_id' => $result['voucher_id'],
				'code'       => $result['code'],
				'from'       => $result['from_name'],
				'to'         => $result['to_name'],
				'theme'      => $result['theme'],
				'amount'     => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'status'     => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'save'       => $this->url->link('sale/voucher/save', 'token=' . $this->session->data['token'] . '&voucher_id=' . $result['voucher_id'] . $url, 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
			
		$this->data['selected'] =  $this->request->post('selected', array());
		
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; 
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];
		

		$this->data['sort_code'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . '&sort=v.code' . $url, 'SSL');
		$this->data['sort_from'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . '&sort=v.from_name' . $url, 'SSL');
		$this->data['sort_to'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . '&sort=v.to_name' . $url, 'SSL');
		$this->data['sort_theme'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . '&sort=theme' . $url, 'SSL');
		$this->data['sort_amount'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . '&sort=v.amount' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . '&sort=v.date_end' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . '&sort=v.date_added' . $url, 'SSL');

		$pagination = new Pagination();
		$pagination->total = $voucher_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($voucher_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($voucher_total - $this->config->get('config_limit_admin'))) ? $voucher_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $voucher_total, ceil($voucher_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/voucher_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['voucher_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['voucher_id'] = $this->request->get('voucher_id', 0);

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_code'] =  (isset($this->error['code'])?$this->error['code']:'');
		
		$this->data['error_from_name'] =  (isset($this->error['from_name'])?$this->error['from_name']:'');
		
		$this->data['error_from_email'] =  (isset($this->error['from_email'])?$this->error['from_email']:'');
		
		$this->data['error_to_name'] =  (isset($this->error['to_name'])?$this->error['to_name']:'');

		$this->data['error_to_email'] =  (isset($this->error['to_email'])?$this->error['to_email']:'');
		
		$this->data['error_amount'] =  (isset($this->error['amount'])?$this->error['amount']:'');
		//for sorting and paging
		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['voucher_id'])) {
			$this->data['action'] = $this->url->link('sale/voucher/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/voucher/save', 'token=' . $this->session->data['token'] . '&voucher_id=' . $this->request->get['voucher_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/voucher', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['voucher_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
			$voucher_info = $this->model_sale_voucher->getVoucher($this->request->get['voucher_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		if (!empty($voucher_info) && !$this->error) {
			$this->data['code'] = $voucher_info['code'];
		} else {
			$this->data['code'] = $this->request->post('code', '');
		}

		if (!empty($voucher_info) && !$this->error) {
			$this->data['from_name'] = $voucher_info['from_name'];
		} else {
			$this->data['from_name'] = $this->request->post('from_name', '');
		}

		if (!empty($voucher_info) && !$this->error) {
			$this->data['from_email'] = $voucher_info['from_email'];
		} else {
			$this->data['from_email'] = $this->request->post('from_email', '');
		}

		if (!empty($voucher_info) && !$this->error) {
			$this->data['to_name'] = $voucher_info['to_name'];
		} else {
			$this->data['to_name'] = $this->request->post('to_name', '');
		}

		if (!empty($voucher_info) && !$this->error) {
			$this->data['to_email'] = $voucher_info['to_email'];
		} else {
			$this->data['to_email'] =  $this->request->post('to_email', '');
		}

		$this->load->model('sale/voucher_theme');

		$this->data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

		if (!empty($voucher_info) && !$this->error) {
			$this->data['voucher_theme_id'] = $voucher_info['voucher_theme_id'];
		} else {
			$this->data['voucher_theme_id'] = $this->request->post('voucher_theme_id', '');
		}

		if (!empty($voucher_info) && !$this->error) {
			$this->data['message'] = $voucher_info['message'];
		} else {
			$this->data['message'] = $this->request->post('message', '');
		}

		if (!empty($voucher_info) && !$this->error) {
			$this->data['amount'] = $voucher_info['amount'];
		} else {
			$this->data['amount'] = $this->request->post('amount', '');
		}

		if (!empty($voucher_info) && !$this->error) {
			$this->data['status'] = $voucher_info['status'];
		} else {
			$this->data['status'] = true;
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/voucher_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/voucher')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 10)) {
			$this->error['code'] = $this->data['error_code'];
		}

		$voucher_info = $this->model_sale_voucher->getVoucherByCode($this->request->post['code']);

		if ($voucher_info) {
			if (!isset($this->request->get['voucher_id'])) {
				$this->error['warning'] = $this->data['error_exists'];
			} elseif ($voucher_info['voucher_id'] != $this->request->get['voucher_id'])  {
				$this->error['warning'] = $this->data['error_exists'];
			}
		}

		if ((utf8_strlen($this->request->post['to_name']) < 1) || (utf8_strlen($this->request->post['to_name']) > 64)) {
			$this->error['to_name'] = $this->data['error_to_name'];
		}

		if ((utf8_strlen($this->request->post['to_email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['to_email'])) {
			$this->error['to_email'] = $this->data['error_email'];
		}

		if ((utf8_strlen($this->request->post['from_name']) < 1) || (utf8_strlen($this->request->post['from_name']) > 64)) {
			$this->error['from_name'] = $this->data['error_from_name'];
		}

		if ((utf8_strlen($this->request->post['from_email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['from_email'])) {
			$this->error['from_email'] = $this->data['error_email'];
		}

		if ($this->request->post['amount'] < 1) {
			$this->error['amount'] = $this->data['error_amount'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/voucher')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('sale/order');

		foreach ($this->request->post['selected'] as $voucher_id) {
			$order_voucher_info = $this->model_sale_order->getOrderVoucherByVoucherId($voucher_id);

			if ($order_voucher_info) {
				$this->error['warning'] = sprintf($this->data['error_order'], $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $order_voucher_info['order_id'], 'SSL'));

				break;
			}
		}

		return !$this->error;
	}

	public function history() {
		$this->data = $this->load->language('sale/voucher');

		$this->load->model('sale/voucher');

		$page = $this->request->get('page', 1);
		
		$this->data['histories'] = array();

		$results = $this->model_sale_voucher->getVoucherHistories($this->request->get['voucher_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['histories'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'amount'     => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_sale_voucher->getTotalVoucherHistories($this->request->get['voucher_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/voucher/history', 'token=' . $this->session->data['token'] . '&voucher_id=' . $this->request->get['voucher_id'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('sale/voucher_history.tpl', $this->data));
	}

	public function send() {
		$this->data = $this->load->language('sale/voucher');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/voucher')) {
			$json['error'] = $this->data['error_permission'];
		}

		if (!$json) {
			$this->load->model('sale/voucher');

			$vouchers = array();

			if (isset($this->request->post['selected'])) {
				$vouchers = $this->request->post['selected'];
			} elseif (isset($this->request->post['voucher_id'])) {
				$vouchers[] = $this->request->post['voucher_id'];
			}

			if ($vouchers) {
				foreach ($vouchers as $voucher_id) {
					$this->model_sale_voucher->sendVoucher($voucher_id);
				}

				$json['success'] = $this->data['text_sent'];
			} else {
				$json['error'] = $this->data['error_selection'];
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}