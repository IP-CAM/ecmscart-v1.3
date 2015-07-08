<?php
class ControllerDashboardOrder extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/order');

		// Total Orders
		$this->load->model('sale/order');
		
		$today = $this->model_sale_order->getTotalOrders(array('filter_date_added' => date('Y-m-d', strtotime('-1 day'))));

		$yesterday = $this->model_sale_order->getTotalOrders(array('filter_date_added' => date('Y-m-d', strtotime('-2 day'))));

		$difference = $today - $yesterday;

		if ($difference && $today) {
			$this->data['percentage'] = round(($difference / $today) * 100);
		} else {
			$this->data['percentage'] = 0;
		}
		
		$order_total = $this->model_sale_order->getTotalOrders();
		
		if ($order_total > 1000000000000) {
			$this->data['total'] = round($order_total / 1000000000000, 1) . 'T';
		} elseif ($order_total > 1000000000) {
			$this->data['total'] = round($order_total / 1000000000, 1) . 'B';
		} elseif ($order_total > 1000000) {
			$this->data['total'] = round($order_total / 1000000, 1) . 'M';
		} elseif ($order_total > 1000) {
			$this->data['total'] = round($order_total / 1000, 1) . 'K';						
		} else {
			$this->data['total'] = $order_total;
		}
				
		$this->data['order'] = $this->url->link('sale/order', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('dashboard/order.tpl', $this->data);
	}
}
