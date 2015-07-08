<?php
class ControllerModuleAffiliate extends Controller {
	public function index() {
		$this->data = $this->load->language('module/affiliate');

		$this->data['logged'] = $this->affiliate->isLogged();
		$this->data['register'] = $this->url->link('affiliate/register', '', 'SSL');
		$this->data['login'] = $this->url->link('affiliate/login', '', 'SSL');
		$this->data['logout'] = $this->url->link('affiliate/logout', '', 'SSL');
		$this->data['forgotten'] = $this->url->link('affiliate/forgotten', '', 'SSL');
		$this->data['account'] = $this->url->link('affiliate/account', '', 'SSL');
		$this->data['edit'] = $this->url->link('affiliate/edit', '', 'SSL');
		$this->data['password'] = $this->url->link('affiliate/password', '', 'SSL');
		$this->data['payment'] = $this->url->link('affiliate/payment', '', 'SSL');
		$this->data['tracking'] = $this->url->link('affiliate/tracking', '', 'SSL');
		$this->data['transaction'] = $this->url->link('affiliate/transaction', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/affiliate.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/module/affiliate.tpl', $this->data);
		} else {
			return $this->load->view('default/template/module/affiliate.tpl', $this->data);
		}
	}
}