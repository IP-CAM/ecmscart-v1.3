<?php
class ControllerDashboardRecent extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/recent');

		$this->data['token'] = $this->session->data['token'];

		// Last 5 Orders
		$this->data['orders'] = array();

		$filter_data = array(
			'sort'  => 'o.date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => 5
		);
		
		$results = $this->model_sale_order->getOrders($filter_data);

		foreach ($results as $result) {
			$this->data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'status'     => $result['status'],
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'view'       => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL'),
			);
		}

		return $this->load->view('dashboard/recent.tpl', $this->data);
	}
}
