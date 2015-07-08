<?php
class ControllerReportCustomerOrder extends Controller {
	private $url_data = array(
				'filter_date_start' ,
				'filter_date_end' ,
				'filter_order_status_id',
				'page'
			);
	public function index() {
		$this->data = $this->load->language('report/customer_order');

		$this->document->setTitle($this->data['heading_title']);
		
		$filter_date_start = $this->request->get('filter_date_start', '');
		$filter_date_end = $this->request->get('filter_date_end', '');
		$filter_order_status_id = $this->request->get('filter_order_status_id',0);
		$page = $this->request->get('page', 1);
		
		$url = $this->request->getUrl($this->url_data);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('report/customer_order', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->load->model('report/customer');

		$this->data['customers'] = array();

		$filter_data = array(
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$customer_total = $this->model_report_customer->getTotalOrders($filter_data);

		$results = $this->model_report_customer->getOrders($filter_data);

		foreach ($results as $result) {
			$this->data['customers'][] = array(
				'customer'       => $result['customer'],
				'email'          => $result['email'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'orders'         => $result['orders'],
				'products'       => $result['products'],
				'total'          => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'save'           => $this->url->link('sale/customer/save', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['customer_id'] . $url, 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$url_data = array(
				'filter_date_start' ,
				'filter_date_end' ,
				'filter_order_status_id',	
			);
			
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/customer_order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_total - $this->config->get('config_limit_admin'))) ? $customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($customer_total / $this->config->get('config_limit_admin')));

		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;
		$this->data['filter_order_status_id'] = $filter_order_status_id;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/customer_order.tpl', $this->data));
	}
}