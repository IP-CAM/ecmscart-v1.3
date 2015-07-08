<?php
class ControllerCommonDashboard extends Controller {
	public function index() {
		$this->data = $this->load->language('common/dashboard');

		$this->document->setTitle($this->data['heading_title']);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('catalog/dashboard', 'token=' . $this->session->data['token'], 'SSL')	// Link URL
						));
		
		// Check install directory exists
		if (!is_dir(dirname(DIR_APPLICATION) . '/install')) {
			$this->data['error_install'] = '';
		} 

		$this->data['token'] = $this->session->data['token'];

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['order'] = $this->load->controller('dashboard/order');
		$this->data['sale'] = $this->load->controller('dashboard/sale');
		$this->data['customer'] = $this->load->controller('dashboard/customer');
		$this->data['online'] = $this->load->controller('dashboard/online');
		$this->data['product_viewed'] = $this->load->controller('dashboard/product_viewed');
		$this->data['returning_customer'] = $this->load->controller('dashboard/returning_customer');
		$this->data['returning_affiliate'] = $this->load->controller('dashboard/returning_affiliate');
		$this->data['marketing'] = $this->load->controller('dashboard/marketing');
		$this->data['map'] = $this->load->controller('dashboard/map');
		$this->data['chart'] = $this->load->controller('dashboard/chart');
		$this->data['product_chart'] = $this->load->controller('dashboard/product_chart');
		$this->data['affiliate_chart'] = $this->load->controller('dashboard/affiliate_chart');
		$this->data['activity'] = $this->load->controller('dashboard/activity');
		$this->data['recent'] = $this->load->controller('dashboard/recent');
		$this->data['footer'] = $this->load->controller('common/footer');

		// Run currency update
		if ($this->config->get('config_currency_auto')) {
			$this->load->model('localisation/currency');

			$this->model_localisation_currency->refresh();
		}
			
		$this->response->setOutput($this->load->view('common/dashboard.tpl', $this->data));
	}

}
