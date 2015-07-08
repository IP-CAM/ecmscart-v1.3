<?php
class ControllerDashboardMarketing extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/marketing');	

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('report/marketing');
		
		$this->data['clicks'] = $this->model_report_marketing->getTotalMarketingClicks();
		
		$total_campaigns = $this->model_report_marketing->getTotalMarketing();
		
		if (($this->data['clicks']>0)&&($total_campaigns)) {
			$this->data['percentage']	 = round($this->data['clicks'] / $total_campaigns * 100, 2);
		} else {
			$this->data['percentage']	 = 0;
		}
				
		$this->data['marketing'] = $this->url->link('report/marketing', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('dashboard/marketing.tpl', $this->data);
	}
}
