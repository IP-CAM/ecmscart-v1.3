<?php
class ControllerAffiliateSuccess extends Controller {
	public function index() {
		$this->data = $this->load->language('affiliate/success');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),	// Link URL
							$this->data['text_success'],
							$this->url->link('affiliate/success')
						));

		$this->data['heading_title'] = $this->data['heading_title'];

		if (!$this->config->get('config_affiliate_approval')) {
			$this->data['text_message'] = sprintf($this->data['text_message'], $this->config->get('config_name'), $this->url->link('information/contact'));
		} else {
			$this->data['text_message'] = sprintf($this->data['text_approval'], $this->config->get('config_name'), $this->url->link('information/contact'));
		}

		$this->data['continue'] = $this->url->link('affiliate/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/success.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/common/success.tpl', $this->data));
		}
	}
}