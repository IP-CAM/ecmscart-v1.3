<?php
class ControllerReportSaleOrder extends Controller {
	private $url_data = array(
				'filter_date_start' ,
				'filter_date_end' ,
				'filter_group',
				'filter_order_status_id',
				'page'
			);
			
	public function index() {
		$this->data = $this->load->language('report/sale_order');

		$this->document->setTitle($this->data['heading_title']);
		
		$filter_date_start = $this->request->get('filter_date_start',date('Y-m-d', strtotime(date('Y') . '-' . date('m') . '-01')));

		$filter_date_end = $this->request->get('filter_date_end', date('Y-m-d'));

		$filter_group = $this->request->get('filter_group', 'week');

		$filter_order_status_id = $this->request->get('filter_order_status_id', 1);

		$page = $this->request->get('page', 0);

		$url = $this->request->getUrl($this->url_data);
		
// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('report/sale_order', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->load->model('report/sale');

		$this->data['orders'] = array();

		$filter_data = array(
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_group'           => $filter_group,
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$order_total = $this->model_report_sale->getTotalOrders($filter_data);

		$results = $this->model_report_sale->getOrders($filter_data);

		foreach ($results as $result) {
			$this->data['orders'][] = array(
				'date_start' => date($this->data['date_format_short'], strtotime($result['date_start'])),
				'date_end'   => date($this->data['date_format_short'], strtotime($result['date_end'])),
				'orders'     => $result['orders'],
				'products'   => $result['products'],
				'tax'        => $this->currency->format($result['tax'], $this->config->get('config_currency')),
				'total'      => $this->currency->format($result['total'], $this->config->get('config_currency'))
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->data['groups'] = array();

		$this->data['groups'][] = array(
			'text'  => $this->data['text_year'],
			'value' => 'year',
		);

		$this->data['groups'][] = array(
			'text'  => $this->data['text_month'],
			'value' => 'month',
		);

		$this->data['groups'][] = array(
			'text'  => $this->data['text_week'],
			'value' => 'week',
		);

		$this->data['groups'][] = array(
			'text'  => $this->data['text_day'],
			'value' => 'day',
		);
		//for Paging
		$url_data = array(
				'filter_date_start', 
				'filter_date_end',
				'filter_group',
				'filter_order_status_id'
			);
			
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/sale_order', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;
		$this->data['filter_group'] = $filter_group;
		$this->data['filter_order_status_id'] = $filter_order_status_id;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/sale_order.tpl', $this->data));
	}
}