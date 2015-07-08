<?php
class ControllerModuleEbayListing extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('module/ebay_listing');

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('view/javascript/openbay/js/faq.js');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ebay_listing', $this->request->post);
			
			$this->session->data['success'] = $this->data['text_success'];

			$this->cache->delete('ebay');

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] = (isset($this->error['warning']))? $this->error['warning']: '';

		$this->data['error_width'] = (isset($this->error['width']))? $this->error['width']: '';
		
		$this->data['error_height'] = (isset($this->error['height']))? $this->error['height']: '';
		

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_module'],	// Text to display link
							$this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('module/ebay_listing', 'token=' . $this->session->data['token'], 'SSL') ,	// Link URL describe above
						));		

		$this->data['action'] = $this->url->link('module/ebay_listing', 'token=' . $this->session->data['token'], 'SSL');
				
		$this->data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
					
		$this->data['ebay_listing_username'] = $this->request->post('ebay_listing_username', $this->config->get('ebay_listing_username'));
		
		$this->data['ebay_listing_keywords'] = $this->request->post('ebay_listing_keywords', $this->config->get('ebay_listing_keywords'));
		
		$this->data['ebay_listing_description'] = $this->request->post('ebay_listing_description', $this->config->get('ebay_listing_description'));
		
		if ($this->config->has('ebay_listing_limit') && !$this->error) {
			$this->data['ebay_listing_limit'] = $this->config->get('ebay_listing_limit');
		} else {
			$this->data['ebay_listing_limit'] = $this->request->post('ebay_listing_limit', 5);
		}
			
		if ($this->config->has('ebay_listing_width') && !$this->error) {
			$this->data['ebay_listing_width'] = $this->config->get('ebay_listing_width');		
		} else {
			$this->data['ebay_listing_width'] = $this->request->post('ebay_listing_width', 200);
		}	
			
		if ($this->config->has('ebay_listing_height') && !$this->error) {
			$this->data['ebay_listing_height'] = $this->config->get('ebay_listing_height');		
		} else {
			$this->data['ebay_listing_height'] = $this->request->post('ebay_listing_height', 200);
		}	
		
		if ($this->config->has('ebay_listing_sort') && !$this->error) {
			$this->data['ebay_listing_sort'] = $this->config->get('ebay_listing_sort');		
		} else {
			$this->data['ebay_listing_sort'] = $this->request->post('ebay_listing_sort', 'StartTimeNewest');
		}	
		
		$this->data['ebay_listing_site'] = $this->request->post('ebay_listing_site', $this->config->get('ebay_listing_site'));
		
		$this->data['sites'] = array();
		
		$this->data['sites'][] = array(
			'text'  => 'USA',
			'value' => 0
		);

		$this->data['sites'][] = array(
			'text'  => 'UK',
			'value' => 3
		);
		$this->data['sites'][] = array(
			'text'  => 'Australia',
			'value' => 15
		);
		
		$this->data['sites'][] = array(
			'text'  => 'Canada (English)',
			'value' => 2
		);
		
		$this->data['sites'][] = array(
			'text'  => 'France',
			'value' => 71
		);
		$this->data['sites'][] = array(
			'text'  => 'Germany',
			'value' => 77
		);
		$this->data['sites'][] = array(
			'text'  => 'Italy',
			'value' => 101
		);
		$this->data['sites'][] = array(
			'text'  => 'Spain',
			'value' => 186
		);
		$this->data['sites'][] = array(
			'text'  => 'Ireland',
			'value' => 205
		);
		
		$this->data['sites'][] = array(
			'text'  => 'Austria',
			'value' => 16
		);
		
		$this->data['sites'][] = array(
			'text'  => 'Netherlands',
			'value' => 146
		);	
		
		$this->data['sites'][] = array(
			'text'  => 'Belgium (French)',
			'value' => 23
		);	
		
		$this->data['sites'][] = array(
			'text'  => 'Belgium (Dutch)',
			'value' => 123
		);	
		
		$this->data['ebay_listing_status'] = $this->request->post('ebay_listing_status', $this->config->get('ebay_listing_status'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/ebay_listing.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/ebay_listing')) {
			$this->error['warning'] = $this->data['error_permission'];
		}
				
		if (!$this->request->post['ebay_listing_width']) {
			$this->error['width'] = $this->data['error_width'];
		}
		
		if (!$this->request->post['ebay_listing_height']) {
			$this->error['height'] = $this->data['error_height'];
		}		

		return !$this->error;
	}
}