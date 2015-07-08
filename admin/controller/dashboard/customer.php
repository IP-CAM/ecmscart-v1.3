<?php
class ControllerDashboardCustomer extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/customer');

		// Total Orders
		$this->load->model('sale/customer');
		
		$today = $this->model_sale_customer->getTotalCustomers(array('filter_date_added' => date('Y-m-d', strtotime('-1 day'))));

		$yesterday = $this->model_sale_customer->getTotalCustomers(array('filter_date_added' => date('Y-m-d', strtotime('-2 day'))));

		$difference = $today - $yesterday;

		if ($difference && $today) {
			$this->data['percentage'] = round(($difference / $today) * 100);
		} else {
			$this->data['percentage'] = 0;
		}
		
		$customer_total = $this->model_sale_customer->getTotalCustomers();
		
		if ($customer_total > 1000000000000) {
			$this->data['total'] = round($customer_total / 1000000000000, 1) . 'T';
		} elseif ($customer_total > 1000000000) {
			$this->data['total'] = round($customer_total / 1000000000, 1) . 'B';
		} elseif ($customer_total > 1000000) {
			$this->data['total'] = round($customer_total / 1000000, 1) . 'M';
		} elseif ($customer_total > 1000) {
			$this->data['total'] = round($customer_total / 1000, 1) . 'K';						
		} else {
			$this->data['total'] = $customer_total;
		}
				
		$this->data['customer'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('dashboard/customer.tpl', $this->data);
	}
}
