<?php
class ControllerAccountRecurring extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->language->load('account/recurring');

		$this->load->model('account/recurring');

		$this->document->setTitle($this->data['heading_title']);

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('account/recurring', $url, 'SSL')
						));

		$page = $this->request->get('page',1);
		
		$this->data['orders'] = array();

		$recurring_total = $this->model_account_recurring->getTotalRecurring();

		$results = $this->model_account_recurring->getAllProfiles(($page - 1) * 10, 10);

		$this->data['recurrings'] = array();

		if ($results) {
			foreach ($results as $result) {
				$this->data['recurrings'][] = array(
					'id'                    => $result['order_recurring_id'],
					'name'                  => $result['product_name'],
					'status'                => $result['status'],
					'date_added'               => date($this->data['date_format_short'], strtotime($result['date_added'])),
					'href'                  => $this->url->link('account/recurring/info', 'recurring_id=' . $result['order_recurring_id'], 'SSL'),
				);
			}
		}

		$this->data['status_types'] = array(
			1 => $this->data['text_status_inactive'],
			2 => $this->data['text_status_active'],
			3 => $this->data['text_status_suspended'],
			4 => $this->data['text_status_cancelled'],
			5 => $this->data['text_status_expired'],
			6 => $this->data['text_status_pending'],
		);

		$pagination = new Pagination();
		$pagination->total = $recurring_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->text = $this->data['text_pagination'];
		$pagination->url = $this->url->link('account/recurring', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/recurring_list.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/recurring_list.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/recurring_list.tpl', $this->data));
		}
	}

	public function info() {
		$this->load->model('account/recurring');
		$this->data = $this->load->language('account/recurring');

		$recurring_id = $this->request->get('recurring_id',0);
		
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/recurring/info', 'recurring_id=' . $recurring_id, 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}
		
		$this->data['error_warning'] =  (isset($this->session->data['error'])? $this->session->data['error']: '');
		
		if (isset($this->session->data['error']))  // To unset success session variable.
			unset($this->session->data['error']);

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$recurring = $this->model_account_recurring->getProfile($this->request->get['recurring_id']);

		$this->data['status_types'] = array(
			1 => $this->data['text_status_inactive'],
			2 => $this->data['text_status_active'],
			3 => $this->data['text_status_suspended'],
			4 => $this->data['text_status_cancelled'],
			5 => $this->data['text_status_expired'],
			6 => $this->data['text_status_pending'],
		);

		$this->data['transaction_types'] = array(
			0 => $this->data['text_transaction_date_added'],
			1 => $this->data['text_transaction_payment'],
			2 => $this->data['text_transaction_outstanding_payment'],
			3 => $this->data['text_transaction_skipped'],
			4 => $this->data['text_transaction_failed'],
			5 => $this->data['text_transaction_cancelled'],
			6 => $this->data['text_transaction_suspended'],
			7 => $this->data['text_transaction_suspended_failed'],
			8 => $this->data['text_transaction_outstanding_failed'],
			9 => $this->data['text_transaction_expired'],
		);

		if ($recurring) {
			$recurring['transactions'] = $this->model_account_recurring->getProfileTransactions($this->request->get['recurring_id']);

			$recurring['date_added'] = date($this->data['date_format_short'], strtotime($recurring['date_added']));
			$recurring['product_link'] = $this->url->link('product/product', 'product_id=' . $recurring['product_id'], 'SSL');
			$recurring['order_link'] = $this->url->link('account/order/info', 'order_id=' . $recurring['order_id'], 'SSL');

			$this->document->setTitle($this->data['text_recurring']);
			
			if (isset($this->request->get['page'])) {
				$url = '&page=' . $this->request->get['page'];
			}
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),	// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('account/recurring', $url, 'SSL'),		// Link URL
							$this->data['text_recurring'],	// Text to display link
							$this->url->link('account/recurring/info', 'recurring_id=' . $this->request->get['recurring_id'] . $url, 'SSL'),	// Link URL
						));

			$this->data['recurring'] = $recurring;

			$this->data['buttons'] = $this->load->controller('payment/' . $recurring['payment_code'] . '/recurringButtons');
			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['column_right'] = $this->load->controller('common/column_right');
			$this->data['content_top'] = $this->load->controller('common/content_top');
			$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/recurring_info.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/recurring_info.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/account/recurring_info.tpl', $this->data));
			}
		} else {
			$this->response->redirect($this->url->link('account/recurring', '', 'SSL'));
		}
	}
}