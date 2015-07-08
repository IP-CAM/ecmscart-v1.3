<?php
class ControllerErrorNotFound extends Controller {
	public function index() {
		$this->data = $this->load->language('error/not_found');

		$this->document->setTitle($this->data['heading_title']);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL')	// Link URL
						));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('error/not_found.tpl', $this->data));
	}
}