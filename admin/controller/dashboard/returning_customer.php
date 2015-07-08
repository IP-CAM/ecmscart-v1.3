<?php
class ControllerDashboardReturningCustomer extends Controller {
	public function index() {
		
		$this->data = $this->load->language('dashboard/returning_customer');

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('report/customer');
		
		$this->load->model('sale/customer');
		
		$this->data['returning'] = $this->model_report_customer->getCustomerTotalKeyActivity(array('filter_date_start' => date('Y-m-d', strtotime('-30 day')), 'filter_key' => "login"));
		
		$customer_total = $this->model_sale_customer->getTotalCustomers();
		
		if (($this->data['returning']>0)&&($customer_total)) {
			$this->data['percentage']	 = round($this->data['returning'] / $customer_total * 100, 2);
		} else {
			$this->data['percentage']	 = 0;
		}	
			
		$this->data['activity'] = $this->url->link('report/customer_activity', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('dashboard/returning_customer.tpl', $this->data);
	}
}
