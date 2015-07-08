<?php
class ControllerDashboardOnline extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/online');

		$this->data['token'] = $this->session->data['token'];

		// Total Orders
		$this->load->model('report/customer');
		
		// Customers Online
		$online_total = $this->model_report_customer->getTotalCustomersOnline();
		
		if ($online_total > 1000000000000) {
			$this->data['total'] = round($online_total / 1000000000000, 1) . 'T';
		} elseif ($online_total > 1000000000) {
			$this->data['total'] = round($online_total / 1000000000, 1) . 'B';
		} elseif ($online_total > 1000000) {
			$this->data['total'] = round($online_total / 1000000, 1) . 'M';
		} elseif ($online_total > 1000) {
			$this->data['total'] = round($online_total / 1000, 1) . 'K';						
		} else {
			$this->data['total'] = $online_total;
		}			
		
		$this->data['online'] = $this->url->link('report/customer_online', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('dashboard/online.tpl', $this->data);
	}
}
