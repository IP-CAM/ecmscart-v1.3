<?php
class ControllerReportMarketing extends Controller {
	// sorting and filter array
	private $url_data = array(
				'filter_date_start' ,
				'filter_date_end',
				'filter_order_status_id',
				'page',
			);
	public function index() {
		$this->data = $this->load->language('report/marketing');

		$this->document->setTitle($this->data['heading_title']);

		$filter_date_start = $this->request->get('filter_date_start', '');
		
		$filter_date_end = $this->request->get('filter_date_end', '');
		
		$filter_order_status_id = $this->request->get('filter_order_status_id', 0);
		
		$page = $this->request->get('page',1);
		
		// Filter and Sorting Function
			$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('report/marketing', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->load->model('report/marketing');

		$this->data['marketings'] = array();

		$filter_data = array(
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$marketing_total = $this->model_report_marketing->getTotalMarketing($filter_data);

		$results = $this->model_report_marketing->getMarketing($filter_data);

		foreach ($results as $result) {
			$action = array();

			$action[] = array(
				'text' => $this->data['text_edit'],
				'href' => $this->url->link('marketing/marketing/save', 'token=' . $this->session->data['token'] . '&marketing_id=' . $result['marketing_id'] . $url, 'SSL')//check late save function
			);

			$this->data['marketings'][] = array(
				'campaign' => $result['campaign'],
				'code'     => $result['code'],
				'clicks'   => $result['clicks'],
				'orders'   => $result['orders'],
				'total'    => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'action'   => $action
			);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
///for paging url function is used
		$url_data = array(
				'filter_date_start',
				'filter_date_end',
				'filter_order_status_id',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $marketing_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/marketing', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($marketing_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($marketing_total - $this->config->get('config_limit_admin'))) ? $marketing_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $marketing_total, ceil($marketing_total / $this->config->get('config_limit_admin')));

		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;
		$this->data['filter_order_status_id'] = $filter_order_status_id;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/marketing.tpl', $this->data));
	}
}