<?php
class ControllerReportAffiliate extends Controller {
	private $url_data = array(
				'filter_date_start', 
				'filter_date_end',
				'page',
			);
	public function index() {
		$this->data = $this->load->language('report/affiliate');

		$this->document->setTitle($this->data['heading_title']);
		
		$filter_date_start = $this->request->get('filter_date_start','');
		
		$filter_date_end = $this->request->get('filter_date_end','');
		
		$page = $this->request->get('page',1);

		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('report/affiliate', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->load->model('report/affiliate');

		$this->data['affiliates'] = array();

		$filter_data = array(
			'filter_date_start'	=> $filter_date_start,
			'filter_date_end'	=> $filter_date_end,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$affiliate_total = $this->model_report_affiliate->getTotalCommission($filter_data);

		$results = $this->model_report_affiliate->getCommission($filter_data);

		foreach ($results as $result) {
			$this->data['affiliates'][] = array(
				'affiliate'  => $result['affiliate'],
				'email'      => $result['email'],
				'status'     => ($result['status'] ? $this->data['text_enabled'] : $this->data['text_disabled']),
				'commission' => $this->currency->format($result['commission'], $this->config->get('config_currency')),
				'orders'     => $result['orders'],
				'total'      => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'save'       => $this->url->link('marketing/affiliate/save', 'token=' . $this->session->data['token'] . '&affiliate_id=' . $result['affiliate_id'] . $url, 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];
		//For paging
		$url_data = array(
				'filter_date_start', 
				'filter_date_end',
				'page',
			);
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $affiliate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/affiliate', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($affiliate_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($affiliate_total - $this->config->get('config_limit_admin'))) ? $affiliate_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $affiliate_total, ceil($affiliate_total / $this->config->get('config_limit_admin')));

		$this->data['filter_date_start'] = $filter_date_start;
		$this->data['filter_date_end'] = $filter_date_end;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/affiliate.tpl', $this->data));
	}
}