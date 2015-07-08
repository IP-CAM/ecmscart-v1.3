<?php
class ControllerDashboardProductChart extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/product_chart');
		
		$this->data['token'] = $this->session->data['token'];

		return $this->load->view('dashboard/product_chart.tpl', $this->data);
	}
	
	public function chart() {
		$this->data = $this->load->language('dashboard/product_chart');

		$json = array();
		
		$this->load->model('report/product');

		$json['product_created'] = array();
		$json['product_modified'] = array();
		$json['xaxis'] = array();

		$json['product_created']['label'] = $this->data['text_product_created'];
		$json['product_modified']['label'] = $this->data['text_product_modified'];
		$json['product_created']['data'] = array();
		$json['product_modified']['data'] = array();

		$range = $this->request->get('range', 'day');
	
		switch ($range) {
			default:
			case 'day':
				$results = $this->model_report_product->getTotalProductsCreatedByDay();

				foreach ($results as $key => $value) {
					$json['product_created']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_product->getTotalProductsModifiedByDay();

				foreach ($results as $key => $value) {
					$json['product_modified']['data'][] = array($key, $value['total']);
				}

				for ($i = 0; $i < 24; $i++) {
					$json['xaxis'][] = array($i, $i);
				}
				break;
			case 'week':
				$results = $this->model_report_product->getTotalProductsCreatedByWeek();

				foreach ($results as $key => $value) {
					$json['product_created']['data'][] = array($key, $value['total']);
				}
				
				$results = $this->model_report_product->getTotalProductsModifiedByWeek();

				foreach ($results as $key => $value) {
					$json['product_modified']['data'][] = array($key, $value['total']);
				}

				$date_start = strtotime('-' . date('w') . ' days');

				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $date_start + ($i * 86400));

					$json['xaxis'][] = array(date('w', strtotime($date)), date('D', strtotime($date)));
				}
				break;
			case 'month':
				$results = $this->model_report_product->getTotalProductsCreatedByMonth();

				foreach ($results as $key => $value) {
					$json['product_created']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_product->getTotalProductsModifiedByMonth();

				foreach ($results as $key => $value) {
					$json['product_modified']['data'][] = array($key, $value['total']);
				}

				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;

					$json['xaxis'][] = array(date('j', strtotime($date)), date('d', strtotime($date)));
				}
				break;
			case 'year':
				$results = $this->model_report_product->getTotalProductsCreatedByYear();

				foreach ($results as $key => $value) {
					$json['product_created']['data'][] = array($key, $value['total']);
				}

				$results = $this->model_report_product->getTotalProductsModifiedByYear();

				foreach ($results as $key => $value) {
					$json['product_modified']['data'][] = array($key, $value['total']);
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