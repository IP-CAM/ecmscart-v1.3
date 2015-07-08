<?php
class ControllerDashboardActivity extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/activity');

		$this->data['token'] = $this->session->data['token'];

		$this->data['activities'] = array();

		$this->load->model('report/activity');

		$results = $this->model_report_activity->getActivities();

		foreach ($results as $result) {
			$comment = vsprintf($this->data['text_' . $result['key']], unserialize($result['data']));

			$find = array(
				'customer_id=',
				'order_id=',
				'affiliate_id=',
				'return_id='
			);

			$replace = array(
				$this->url->link('sale/customer/edit', 'token=' . $this->session->data['token'] . '&customer_id=', 'SSL'),
				$this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=', 'SSL'),
				$this->url->link('marketing/affiliate/edit', 'token=' . $this->session->data['token'] . '&affiliate_id=', 'SSL'),
				$this->url->link('sale/return/edit', 'token=' . $this->session->data['token'] . '&return_id=', 'SSL')
			);

			$this->data['activities'][] = array(
				'comment'    => str_replace($find, $replace, $comment),
				'date_added' => date($this->data['datetime_format'], strtotime($result['date_added']))
			);
		}

		return $this->load->view('dashboard/activity.tpl', $this->data);
	}
}
