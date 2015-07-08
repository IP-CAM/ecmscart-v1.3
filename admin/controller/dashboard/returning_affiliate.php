<?php
class ControllerDashboardReturningAffiliate extends Controller {
	public function index() {
		
		$this->data = $this->load->language('dashboard/returning_affiliate');

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('report/affiliate');
		
		$this->load->model('marketing/affiliate');
		
		$this->data['returning'] = $this->model_report_affiliate->getAffiliateTotalKeyActivity(array('filter_date_start' => date('Y-m-d', strtotime('-30 day')), 'filter_key' => "login"));
		
		$affiliate_total = $this->model_marketing_affiliate->getTotalAffiliates();
		
		if (($this->data['returning']>0)&&($affiliate_total)) {
			$this->data['percentage']	 = round($this->data['returning'] / $affiliate_total * 100, 2);
		} else {
			$this->data['percentage']	 = 0;
		}	
			
		$this->data['activity'] = $this->url->link('report/affiliate_activity', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('dashboard/returning_affiliate.tpl', $this->data);
	}
}
