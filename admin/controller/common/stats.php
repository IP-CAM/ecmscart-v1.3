<?php
class ControllerCommonStats extends Controller {
	public function index() {
		$this->data = $this->load->language('common/stats');

		$this->load->model('sale/order');

		$order_total = $this->model_sale_order->getTotalOrders();

		$complete_total = $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $this->config->get('config_complete_status'))));

		if ($complete_total) {
			$this->data['complete_status'] = round(($complete_total / $order_total) * 100);
		} else {
			$this->data['complete_status'] = 0;
		}

		$processing_total = $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $this->config->get('config_processing_status'))));

		if ($processing_total) {
			$this->data['processing_status'] = round(($processing_total / $order_total) * 100);
		} else {
			$this->data['processing_status'] = 0;
		}

		$this->load->model('localisation/order_status');

		$order_status_data = array();

		$results = $this->model_localisation_order_status->getOrderStatuses();

		foreach ($results as $result) {
			if (!in_array($result['order_status_id'], array_merge($this->config->get('config_complete_status'), $this->config->get('config_processing_status')))) {
				$order_status_data[] = $result['order_status_id'];
			}
		}

		$other_total = $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $order_status_data)));

		if ($other_total) {
			$this->data['other_status'] = round(($other_total / $order_total) * 100);
		} else {
			$this->data['other_status'] = 0;
		}

		return $this->load->view('common/stats.tpl', $this->data);
	}
}