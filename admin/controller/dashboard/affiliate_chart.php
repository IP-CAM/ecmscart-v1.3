<?php
class ControllerDashboardAffiliateChart extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/affiliate_chart');

		$this->data['token'] = $this->session->data['token'];

		return $this->load->view('dashboard/affiliate_chart.tpl', $this->data);
	}
	
	public function chart() {
		$this->data = $this->load->language('dashboard/affiliate_chart');

		$json = array();
		
		$this->load->model('report/affiliate');

		$json['transaction'] = array();
		$json['affiliate'] = array();
		$json['xaxis'] = array();

		$json['transaction']['label'] = $this->data['text_transaction'];
		$json['affiliate']['label'] = $this->data['text_affiliate'];
		$json['transaction']['data'] = array();
		$json['affiliate']['data'] = array();

		if (isset($this->request->get['range'])) {
			$range = $this->request->get['range'];
		} else {
			$range = 'day';
		}

		switch ($range) {
			default:
			case 'day':
				$results = $this->model_report_affiliate->getTotalTransactionsByDay();

				foreach ($results as $key => $value) {
					$json['transaction']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_affiliate->getTotalAffiliatesByDay();

				foreach ($results as $key => $value) {
					$json['affiliate']['data'][] = array($key, $value['total']);
				}

				for ($i = 0; $i < 24; $i++) {
					$json['xaxis'][] = array($i, $i);
				}
				break;
			case 'week':
				$results = $this->model_report_affiliate->getTotalTransactionsByWeek();

				foreach ($results as $key => $value) {
					$json['transaction']['data'][] = array($key, $value['total']);
				}
				
				$results = $this->model_report_affiliate->getTotalAffiliatesByWeek();

				foreach ($results as $key => $value) {
					$json['affiliate']['data'][] = array($key, $value['total']);
				}

				$date_start = strtotime('-' . date('w') . ' days');

				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $date_start + ($i * 86400));

					$json['xaxis'][] = array(date('w', strtotime($date)), date('D', strtotime($date)));
				}
				break;
			case 'month':
				$results = $this->model_report_affiliate->getTotalTransactionsByMonth();

				foreach ($results as $key => $value) {
					$json['transaction']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_affiliate->getTotalAffiliatesByMonth();

				foreach ($results as $key => $value) {
					$json['affiliate']['data'][] = array($key, $value['total']);
				}

				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;

					$json['xaxis'][] = array(date('j', strtotime($date)), date('d', strtotime($date)));
				}
				break;
			case 'year':
				$results = $this->model_report_affiliate->getTotalTransactionsByYear();

				foreach ($results as $key => $value) {
					$json['transaction']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_affiliate->getTotalAffiliatesByYear();

				foreach ($results as $key => $value) {
					$json['affiliate']['data'][] = array($key, $value['total']);
				}

				for ($i = 1; $i <= 12; $i++) {
					$json['xaxis'][] = array($i, date('M', mktime(0, 0, 0, $i)));
				}
				break;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}