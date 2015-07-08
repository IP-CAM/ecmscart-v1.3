<?php
class ControllerSaleRecurring extends Controller {
	private $error = array();
	
	private $url_data = array(
				'filter_order_recurring_id',
				'filter_order_id',
				'filter_reference',
				'filter_customer',
				'filter_status',
				'filter_date_added',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->language->load('sale/recurring');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/recurring');

		$this->getList();
	}

	protected function getList() {
		$filter_order_recurring_id = $this->request->get('filter_order_recurring_id', null);
		
		$filter_order_id = $this->request->get('filter_order_id', null);

		$filter_reference = $this->request->get('filter_reference', null);

		$filter_customer = $this->request->get('filter_customer', null);

		$filter_status = $this->request->get('filter_status', 0);
		
		$filter_date_added = $this->request->get('filter_date_added', null);

		$sort = $this->request->get('sort', 'order_recurring_id');

		$order = $this->request->get('order', 'DESC');
		
		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$filter_data = array(
			'filter_order_recurring_id' => $filter_order_recurring_id,
			'filter_order_id'           => $filter_order_id,
			'filter_reference'  => $filter_reference,
			'filter_customer'           => $filter_customer,
			'filter_status'             => $filter_status,
			'filter_date_added'         => $filter_date_added,
			'order'                     => $order,
			'sort'                      => $sort,
			'start'                     => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit'                     => $this->config->get('config_admin_limit'),
		);

		$recurrings_total = $this->model_sale_recurring->getTotalRecurrings($filter_data);

		$results = $this->model_sale_recurring->getRecurrings($filter_data);

		$this->data['recurrings'] = array();

		foreach ($results as $result) {
			$date_added = date($this->data['date_format_short'], strtotime($result['date_added']));

			$this->data['recurrings'][] = array(
				'order_recurring_id' => $result['order_recurring_id'],
				'order_id'           => $result['order_id'],
				'order_link'         => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL'),
				'reference'          => $result['reference'],
				'customer'           => $result['customer'],
				'status'             => $result['status'],
				'date_added'         => $date_added,
				'view'               => $this->url->link('sale/recurring/info', 'token=' . $this->session->data['token'] . '&order_recurring_id=' . $result['order_recurring_id'] . $url, 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
			
		$url_data = array(
				'filter_order_recurring_id',
				'filter_order_id',
				'filter_reference',
				'filter_customer',
				'filter_status',
				'filter_date_added',
				'sort',
				'order',
				'page',
			);	

		$url = $this->request->getUrl($url_data);

		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; 

		if (isset($this->request->get['page']))
			$url .= '&page=' . $this->request->get['page'];
		
		$this->data['sort_order_recurring'] = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . '&sort=or.order_recurring_id' . $url, 'SSL');
		$this->data['sort_order'] = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . '&sort=or.order_id' . $url, 'SSL');
		$this->data['sort_reference'] = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . '&sort=or.reference' . $url, 'SSL');
		$this->data['sort_customer'] = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . '&sort=customer' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . '&sort=or.status' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . '&sort=or.date_added' . $url, 'SSL');
		
		$url_data = array(
				'filter_order_recurring_id',
				'filter_order_id',
				'filter_reference',
				'filter_customer',
				'filter_status',
				'filter_date_added',
				'sort',
				'order',
				
			);	
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $recurrings_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->data['text_pagination'];
		$pagination->url = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . '&page={page}' . $url, 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($recurrings_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($recurrings_total - $this->config->get('config_limit_admin'))) ? $recurrings_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $recurrings_total, ceil($recurrings_total / $this->config->get('config_limit_admin')));

		$this->data['filter_order_recurring_id'] = $filter_order_recurring_id;
		$this->data['filter_order_id'] = $filter_order_id;
		$this->data['filter_reference'] = $filter_reference;
		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_status'] = $filter_status;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->data['statuses'] = array(
			'0' => '',
			'1' => $this->data['text_status_inactive'],
			'2' => $this->data['text_status_active'],
			'3' => $this->data['text_status_suspended'],
			'4' => $this->data['text_status_cancelled'],
			'5' => $this->data['text_status_expired'],
			'6' => $this->data['text_status_pending'],
		);

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/recurring_list.tpl', $this->data));
	}

	public function info() {
		$this->load->model('sale/recurring');
		$this->load->model('sale/order');
		$this->load->model('catalog/product');

		$this->data = $this->language->load('sale/recurring');

		$order_recurring = $this->model_sale_recurring->getRecurring($this->request->get['order_recurring_id']);

		if ($order_recurring) {
			$order = $this->model_sale_order->getOrder($order_recurring['order_id']);

			$this->document->setTitle($this->data['heading_title']);
			
			$url_data = array(
				'filter_order_recurring_id',
				'filter_order_id',
				'filter_reference',
				'filter_customer',
				'filter_status',
				'filter_date_added',
				'sort',
				'order',
				'page',
			);
			//for sorting, filter and paging
			$url = $this->request->getUrl($url_data);
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(
					array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						)
				);
			
			$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
			
			$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
			
			$this->data['order_recurring_id'] = $order_recurring['order_recurring_id'];
			$this->data['product'] = $order_recurring['product_name'];
			$this->data['quantity'] = $order_recurring['product_quantity'];
			$this->data['status'] = $order_recurring['status'];
			$this->data['reference'] = $order_recurring['reference'];
			$this->data['recurring_description'] = $order_recurring['recurring_description'];
			$this->data['recurring_name'] = $order_recurring['recurring_name'];

			$this->data['order_id'] = $order['order_id'];
			$this->data['order_href'] = $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $order['order_id'], 'SSL');

			$this->data['customer'] = $order['customer'];
			$this->data['email'] = $order['email'];
			$this->data['payment_method'] = $order['payment_method'];
			$this->data['date_added'] = date($this->data['date_format_short'], strtotime($order['date_added']));

			$this->data['options'] = array();

			if ($order['customer_id']) {
				$this->data['customer_href'] = $this->url->link('sale/customer/edit', 'token=' . $this->session->data['token'] . '&customer_id=' . $order['customer_id'], 'SSL');
			} else {
				$this->data['customer_href'] = '';
			}

			if ($order_recurring['recurring_id'] != '0') {
				$this->data['recurring'] = $this->url->link('catalog/recurring/edit', 'token=' . $this->session->data['token'] . '&recurring_id=' . $order_recurring['recurring_id'], 'SSL');
			} else {
				$this->data['recurring'] = '';
			}

			$this->data['transactions'] = array();
			$transactions = $this->model_sale_recurring->getRecurringTransactions($order_recurring['order_recurring_id']);

			foreach ($transactions as $transaction) {
				$this->data['transactions'][] = array(
					'date_added' => $transaction['date_added'],
					'type'       => $transaction['type'],
					'amount'     => $this->currency->format($transaction['amount'], $order['currency_code'], $order['currency_value'])
				);
			}

			$this->data['return'] = $this->url->link('sale/recurring', 'token=' . $this->session->data['token'] . $url, 'SSL');

			$this->data['token'] = $this->request->get['token'];

			$this->data['buttons'] = $this->load->controller('payment/' . $order['payment_code'] . '/recurringButtons');

			$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('sale/recurring_info.tpl', $this->data));
		} else {
			return new Action('error/not_found');
		}
	}
}