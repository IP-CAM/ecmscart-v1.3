<?php
class ControllerAffiliatePayment extends Controller {
	private $error = array();

	public function index() {
		if (!$this->affiliate->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/payment', '', 'SSL');

			$this->response->redirect($this->url->link('affiliate/login', '', 'SSL'));
		}

		$this->data = $this->load->language('affiliate/payment');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('affiliate/affiliate');

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->model_affiliate_affiliate->editPayment($this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			// Add to activity log
			$this->load->model('affiliate/activity');

			$activity_data = array(
				'affiliate_id' => $this->affiliate->getId(),
				'name'         => $this->affiliate->getFirstName() . ' ' . $this->affiliate->getLastName()
			);

			$this->model_affiliate_activity->addActivity('payment', $activity_data);

			$this->response->redirect($this->url->link('affiliate/account', '', 'SSL'));
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),	// Link URL
							$this->data['text_payment'],
							$this->url->link('affiliate/payment', '', 'SSL')
						));

		$this->data['action'] = $this->url->link('affiliate/payment', '', 'SSL');

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$affiliate_info = $this->model_affiliate_affiliate->getAffiliate($this->affiliate->getId());
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['tax'] = $affiliate_info['tax'];
		} else {
			$this->data['tax'] = $this->request->post('tax','');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['payment'] = $affiliate_info['payment'];
		} else {
			$this->data['payment'] = $this->request->post('payment','cheque');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['cheque'] = $affiliate_info['cheque'];
		} else {
			$this->data['cheque'] = $this->request->post('cheque','');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['paypal'] = $affiliate_info['paypal'];
		} else {
			$this->data['paypal'] = $this->request->post('paypal','');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_name'] = $affiliate_info['bank_name'];
		} else {
			$this->data['bank_name'] = $this->request->post('bank_name','');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_branch_number'] = $affiliate_info['bank_branch_number'];
		} else {
			$this->data['bank_branch_number'] = $this->request->post('bank_branch_number','');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_swift_code'] = $affiliate_info['bank_swift_code'];
		} else {
			$this->data['bank_swift_code'] = $this->request->post('bank_swift_code','');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_account_name'] = $affiliate_info['bank_account_name'];
		} else {
			$this->data['bank_account_name'] = $this->request->post('bank_account_name','');
		}

		if (!empty($affiliate_info) && !$this->error) {
			$this->data['bank_account_number'] = $affiliate_info['bank_account_number'];
		} else {
			$this->data['bank_account_number'] = $this->request->post('bank_account_number','');
		}

		$this->data['back'] = $this->url->link('affiliate/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/payment.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/affiliate/payment.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/affiliate/payment.tpl', $this->data));
		}
	}
}