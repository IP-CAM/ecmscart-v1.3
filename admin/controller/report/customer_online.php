<?php
class ControllerReportCustomerOnline extends Controller {
	private $url_data = array(
				'filter_ip' ,
				'filter_customer' ,
				'page'
			);
	public function index() {
		$this->data = $this->load->language('report/customer_online');

		$this->document->setTitle($this->data['heading_title']);
		
		$filter_ip = $this->request->get('filter_ip', null);
		$filter_customer = $this->request->get('filter_customer', null);
		$page = $this->request->get('page', 1);
		
		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('report/customer_online', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->load->model('report/customer');
		$this->load->model('sale/customer');

		$this->data['customers'] = array();

		$filter_data = array(
			'filter_ip'       => $filter_ip,
			'filter_customer' => $filter_customer,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);

		$customer_total = $this->model_report_customer->getTotalCustomersOnline($filter_data);

		$results = $this->model_report_customer->getCustomersOnline($filter_data);

		foreach ($results as $result) {
			$customer_info = $this->model_sale_customer->getCustomer($result['customer_id']);

			if ($customer_info) {
				$customer = $customer_info['firstname'] . ' ' . $customer_info['lastname'];
			} else {
				$customer = $this->data['text_guest'];
			}

			$this->data['customers'][] = array(
				'customer_id' => $result['customer_id'],
				'ip'          => $result['ip'],
				'customer'    => $customer,
				'url'         => $result['url'],
				'referer'     => $result['referer'],
				'date_added'  => date($this->data['datetime_format'], strtotime($result['date_added'])),
				'save'        => $this->url->link('sale/customer/save', 'token=' . $this->session->data['token'] . '&customer_id=' . $result['customer_id'], 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];
		// For Paging
		$url_data = array(
				'filter_ip' ,
				'filter_customer' ,
			);
			
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/customer_online', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_total - $this->config->get('config_limit_admin'))) ? $customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($customer_total / $this->config->get('config_limit_admin')));

		$this->data['filter_customer'] = $filter_customer;
		$this->data['filter_ip'] = $filter_ip;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/customer_online.tpl', $this->data));
	}
}
