<?php
class ControllerDashboardChart extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/chart');

		$this->data['token'] = $this->session->data['token'];

		return $this->load->view('dashboard/chart.tpl', $this->data);
	}
	
	public function chart() {
		$this->data = $this->load->language('dashboard/chart');

		$json = array();
		
		$this->load->model('report/sale');
		$this->load->model('report/customer');

		$json['order'] = array();
		$json['customer'] = array();
		$json['xaxis'] = array();

		$json['order']['label'] = $this->data['text_order'];
		$json['customer']['label'] = $this->data['text_customer'];
		$json['order']['data'] = array();
		$json['customer']['data'] = array();

		if (isset($this->request->get['range'])) {
			$range = $this->request->get['range'];
		} else {
			$range = 'day';
		}

		switch ($range) {
			default:
			case 'day':
				$results = $this->model_report_sale->getTotalOrdersByDay();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_customer->getTotalCustomersByDay();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
				}

				for ($i = 0; $i < 24; $i++) {
					$json['xaxis'][] = array($i, $i);
				}
				break;
			case 'week':
				$results = $this->model_report_sale->getTotalOrdersByWeek();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}
				
				$results = $this->model_report_customer->getTotalCustomersByWeek();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
				}

				$date_start = strtotime('-' . date('w') . ' days');

				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $date_start + ($i * 86400));

					$json['xaxis'][] = array(date('w', strtotime($date)), date('D', strtotime($date)));
				}
				break;
			case 'month':
				$results = $this->model_report_sale->getTotalOrdersByMonth();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_customer->getTotalCustomersByMonth();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
				}

				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;

					$json['xaxis'][] = array(date('j', strtotime($date)), date('d', strtotime($date)));
				}
				break;
			case 'year':
				$results = $this->model_report_sale->getTotalOrdersByYear();

				foreach ($results as $key => $value) {
					$json['order']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_customer->getTotalCustomersByYear();

				foreach ($results as $key => $value) {
					$json['customer']['data'][] = array($key, $value['total']);
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