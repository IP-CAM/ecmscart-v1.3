<?php
class ControllerAccountVoucher extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('account/voucher');

		$this->document->setTitle($this->data['heading_title']);

		if (!isset($this->session->data['vouchers'])) {
			$this->session->data['vouchers'] = array();
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->session->data['vouchers'][mt_rand()] = array(
				'description'      => sprintf($this->data['text_for'], $this->currency->format($this->currency->convert($this->request->post['amount'], $this->currency->getCode(), $this->config->get('config_currency'))), $this->request->post['to_name']),
				'to_name'          => $this->request->post['to_name'],
				'to_email'         => $this->request->post['to_email'],
				'from_name'        => $this->request->post['from_name'],
				'from_email'       => $this->request->post['from_email'],
				'voucher_theme_id' => $this->request->post['voucher_theme_id'],
				'message'          => $this->request->post['message'],
				'amount'           => $this->currency->convert($this->request->post['amount'], $this->currency->getCode(), $this->config->get('config_currency'))
			);

			$this->response->redirect($this->url->link('account/voucher/success'));
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),	// Link URL
							$this->data['text_voucher'],
							$this->url->link('account/voucher', '', 'SSL')
						));

		$this->data['help_amount'] = sprintf($this->data['help_amount'], $this->currency->format($this->config->get('config_voucher_min')), $this->currency->format($this->config->get('config_voucher_max')));
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_to_name'] =  (isset($this->error['to_name'])?$this->error['to_name']:'');

		$this->data['error_to_email'] =  (isset($this->error['to_email'])?$this->error['to_email']:'');

		$this->data['error_from_name'] =  (isset($this->error['from_name'])?$this->error['from_name']:'');

		$this->data['error_from_email'] =  (isset($this->error['from_email'])?$this->error['from_email']:'');

		$this->data['error_theme'] =  (isset($this->error['theme'])?$this->error['theme']:'');

		$this->data['error_amount'] =  (isset($this->error['amount'])?$this->error['amount']:'');

		$this->data['action'] = $this->url->link('account/voucher', '', 'SSL');

		$this->data['to_name'] = $this->request->post('to_name','');
		
		$this->data['to_email'] = $this->request->post('to_email','');
		
		if ($this->customer->isLogged()) {
			$this->data['from_name'] = $this->customer->getFirstName() . ' '  . $this->customer->getLastName();
		} else {
			$this->data['from_name'] = $this->request->post('from_name','');
		}

		if ($this->customer->isLogged()) {
			$this->data['from_email'] = $this->customer->getEmail();
		} else {
			$this->data['from_email'] =$this->request->post('from_email', '');
		}

		$this->load->model('checkout/voucher_theme');

		$this->data['voucher_themes'] = $this->model_checkout_voucher_theme->getVoucherThemes();

		$this->data['voucher_theme_id'] = $this->request->post('voucher_theme_id','');
		
		$this->data['message'] = $this->request->post('message','');
		

		if (isset($this->request->post['amount'])) {
			$this->data['amount'] = $this->request->post['amount'];
		} else {
			$this->data['amount'] = $this->currency->format($this->config->get('config_voucher_min'), $this->config->get('config_currency'), false, false);
		}
		
		$this->data['agree'] = $this->request->post('agree',false);
		
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/voucher.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/voucher.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/voucher.tpl', $this->data));
		}
	}

	public function success() {
		$this->data = $this->load->language('account/voucher');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							
							$this->data['heading_title'],
							$this->url->link('account/voucher')
						));

		$this->data['continue'] = $this->url->link('checkout/cart');

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

	protected function validate() {
		if ((utf8_strlen($this->request->post['to_name']) < 1) || (utf8_strlen($this->request->post['to_name']) > 64)) {
			$this->error['to_name'] = $this->data['error_to_name'];
		}

		if ((utf8_strlen($this->request->post['to_email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['to_email'])) {
			$this->error['to_email'] = $this->data['error_email'];
		}

		if ((utf8_strlen($this->request->post['from_name']) < 1) || (utf8_strlen($this->request->post['from_name']) > 64)) {
			$this->error['from_name'] = $this->data['error_from_name'];
		}

		if ((utf8_strlen($this->request->post['from_email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['from_email'])) {
			$this->error['from_email'] = $this->data['error_email'];
		}

		if (!isset($this->request->post['voucher_theme_id'])) {
			$this->error['theme'] = $this->data['error_theme'];
		}

		if (($this->currency->convert($this->request->post['amount'], $this->currency->getCode(), $this->config->get('config_currency')) < $this->config->get('config_voucher_min')) || ($this->currency->convert($this->request->post['amount'], $this->currency->getCode(), $this->config->get('config_currency')) > $this->config->get('config_voucher_max'))) {
			$this->error['amount'] = sprintf($this->data['error_amount'], $this->currency->format($this->config->get('config_voucher_min')), $this->currency->format($this->config->get('config_voucher_max')));
		}

		if (!isset($this->request->post['agree'])) {
			$this->error['warning'] = $this->data['error_agree'];
		}

		return !$this->error;
	}
}