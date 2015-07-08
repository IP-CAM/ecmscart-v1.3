<?php
class ControllerAccountNewsletter extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/newsletter', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->load->language('account/newsletter');

		$this->document->setTitle($this->data['heading_title']);

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('account/customer');

			$this->model_account_customer->editNewsletter($this->request->post['newsletter']);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('account/account', '', 'SSL'));
		}
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),	// Link URL
							$this->data['text_newsletter'],
							$this->url->link('account/newsletter', '', 'SSL')
						));
		
		$this->data['action'] = $this->url->link('account/newsletter', '', 'SSL');

		$this->data['newsletter'] = $this->customer->getNewsletter();

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/newsletter.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/newsletter.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/newsletter.tpl', $this->data));
		}
	}
}