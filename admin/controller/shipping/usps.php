<?php
class ControllerShippingUsps extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('shipping/usps');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('usps', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_user_id'] =  (isset($this->error['user_id'])?$this->error['user_id']:'');

		$this->data['error_postcode'] =  (isset($this->error['postcode'])?$this->error['postcode']:'');
		
		$this->data['error_dimension'] =  (isset($this->error['dimension'])?$this->error['dimension']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_shipping'],
							$this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],	// Text to display link
							$this->url->link('shipping/usps', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));

		$this->data['action'] = $this->url->link('shipping/usps', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['usps_user_id'] = $this->request->post('usps_user_id',$this->config->get('usps_user_id'));
	
		$this->data['usps_postcode'] = $this->request->post('usps_postcode',$this->config->get('usps_postcode'));
		
		$this->data['usps_domestic_00'] = $this->request->post('usps_domestic_00',$this->config->get('usps_domestic_00'));
		
		$this->data['usps_domestic_01'] = $this->request->post('usps_domestic_01',$this->config->get('usps_domestic_01'));
		
		$this->data['usps_domestic_02'] = $this->request->post('usps_domestic_02',$this->config->get('usps_domestic_02'));
		
		$this->data['usps_domestic_03'] = $this->request->post('usps_domestic_03',$this->config->get('usps_domestic_03'));
		
		$this->data['usps_domestic_1'] = $this->request->post('usps_domestic_1',$this->config->get('usps_domestic_1'));
		
	    $this->data['usps_domestic_2'] = $this->request->post('usps_domestic_2',$this->config->get('usps_domestic_2'));
	
	    $this->data['usps_domestic_3'] = $this->request->post('usps_domestic_3',$this->config->get('usps_domestic_3'));
		
	   	$this->data['usps_domestic_4'] = $this->request->post('usps_domestic_4',$this->config->get('usps_domestic_4'));
		
	   	$this->data['usps_domestic_5'] = $this->request->post('usps_domestic_5',$this->config->get('usps_domestic_5'));
		
	   	$this->data['usps_domestic_6'] = $this->request->post('usps_domestic_6',$this->config->get('usps_domestic_6'));
		
	   	$this->data['usps_domestic_7'] = $this->request->post('usps_domestic_7',$this->config->get('usps_domestic_7'));
		
	   	$this->data['usps_domestic_12'] = $this->request->post('usps_domestic_12',$this->config->get('usps_domestic_12'));
		
	   	$this->data['usps_domestic_13'] = $this->request->post('usps_domestic_13',$this->config->get('usps_domestic_13'));
		
	   	$this->data['usps_domestic_16'] = $this->request->post('usps_domestic_16',$this->config->get('usps_domestic_16'));		
	
	   	$this->data['usps_domestic_17'] = $this->request->post('usps_domestic_17',$this->config->get('usps_domestic_17'));	
		 
	   	$this->data['usps_domestic_18'] = $this->request->post('usps_domestic_18',$this->config->get('usps_domestic_18'));
		  
		$this->data['usps_domestic_19'] = $this->request->post('usps_domestic_19',$this->config->get('usps_domestic_19'));
		
		$this->data['usps_domestic_22'] = $this->request->post('usps_domestic_22',$this->config->get('usps_domestic_22'));
		
		$this->data['usps_domestic_23'] = $this->request->post('usps_domestic_23',$this->config->get('usps_domestic_23'));
		
		$this->data['usps_domestic_25'] = $this->request->post('usps_domestic_25',$this->config->get('usps_domestic_25')); 
		
		$this->data['usps_domestic_27'] = $this->request->post('usps_domestic_27',$this->config->get('usps_domestic_27')); 
		
		$this->data['usps_domestic_28'] = $this->request->post('usps_domestic_28',$this->config->get('usps_domestic_28')); 
		
		$this->data['usps_international_1'] = $this->request->post('usps_international_1',$this->config->get('usps_international_1')); 
		
		$this->data['usps_international_2'] = $this->request->post('usps_international_2',$this->config->get('usps_international_2')); 
		
		$this->data['usps_international_4'] = $this->request->post('usps_international_4',$this->config->get('usps_international_4')); 
		
		$this->data['usps_international_5'] = $this->request->post('usps_international_5',$this->config->get('usps_international_5'));
		
		$this->data['usps_international_6'] = $this->request->post('usps_international_6',$this->config->get('usps_international_6'));
		
		$this->data['usps_international_7'] = $this->request->post('usps_international_7',$this->config->get('usps_international_7'));
		
		$this->data['usps_international_8'] = $this->request->post('usps_international_8',$this->config->get('usps_international_8'));
		
		$this->data['usps_international_9'] = $this->request->post('usps_international_9',$this->config->get('usps_international_9'));
		
		$this->data['usps_international_10'] = $this->request->post('usps_international_10',$this->config->get('usps_international_10'));
		
		$this->data['usps_international_11'] = $this->request->post('usps_international_11',$this->config->get('usps_international_11'));
		
		$this->data['usps_international_12'] = $this->request->post('usps_international_12',$this->config->get('usps_international_12'));
		
		$this->data['usps_international_13'] = $this->request->post('usps_international_13',$this->config->get('usps_international_13'));
		
		$this->data['usps_international_14'] = $this->request->post('usps_international_14',$this->config->get('usps_international_14'));
		
		$this->data['usps_international_15'] = $this->request->post('usps_international_15',$this->config->get('usps_international_15'));
		
		$this->data['usps_international_16'] = $this->request->post('usps_international_16',$this->config->get('usps_international_16'));
		
		$this->data['usps_international_21'] = $this->request->post('usps_international_21',$this->config->get('usps_international_21'));
		
		$this->data['usps_size'] = $this->request->post('usps_size',$this->config->get('usps_size'));

		$this->data['sizes'] = array();

		$this->data['sizes'][] = array(
			'text'  => $this->data['text_regular'],
			'value' => 'REGULAR'
		);

		$this->data['sizes'][] = array(
			'text'  => $this->data['text_large'],
			'value' => 'LARGE'
		);

		
		$this->data['usps_container'] = $this->request->post('usps_container',$this->config->get('usps_container'));
		
		$this->data['containers'] = array();

		$this->data['containers'][] = array(
			'text'  => $this->data['text_rectangular'],
			'value' => 'RECTANGULAR'
		);

		$this->data['containers'][] = array(
			'text'  => $this->data['text_non_rectangular'],
			'value' => 'NONRECTANGULAR'
		);

		$this->data['containers'][] = array(
			'text'  => $this->data['text_variable'],
			'value' => 'VARIABLE'
		);

		
		$this->data['usps_machinable'] = $this->request->post('usps_machinable',$this->config->get('usps_machinable'));
		
		$this->data['usps_length'] = $this->request->post('usps_length',$this->config->get('usps_length'));
		
		$this->data['usps_width'] = $this->request->post('usps_width',$this->config->get('usps_width'));
		
		$this->data['usps_height'] = $this->request->post('usps_height',$this->config->get('usps_height'));
		
		$this->data['usps_length'] = $this->request->post('usps_length',$this->config->get('usps_length'));
		
		$this->data['usps_display_time'] = $this->request->post('usps_display_time',$this->config->get('usps_display_time'));
		
		$this->data['usps_display_weight'] = $this->request->post('usps_display_weight',$this->config->get('usps_display_weight'));
		
		$this->data['usps_weight_class_id'] = $this->request->post('usps_weight_class_id',$this->config->get('usps_weight_class_id'));
		
		$this->load->model('localisation/weight_class');

		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->data['usps_tax_class_id'] = $this->request->post('usps_tax_class_id',$this->config->get('usps_tax_class_id'));
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->data['usps_geo_zone_id'] = $this->request->post('usps_geo_zone_id', $this->config->get('usps_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['usps_debug'] = $this->request->post('usps_debug',$this->config->get('usps_debug'));
		
		$this->data['usps_status'] = $this->request->post('usps_status',$this->config->get('usps_status'));
		
		$this->data['usps_sort_order'] = $this->request->post('usps_sort_order',$this->config->get('usps_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/usps.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/usps')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['usps_user_id']) {
			$this->error['user_id'] = $this->data['error_user_id'];
		}

		if (!$this->request->post['usps_postcode']) {
			$this->error['postcode'] = $this->data['error_postcode'];
		}

		if (!$this->request->post['usps_width']) {
			$this->error['dimension'] = $this->data['error_width'];
		}

		if (!$this->request->post['usps_height']) {
			$this->error['dimension'] = $this->data['error_height'];
		}

		if (!$this->request->post['usps_length']) {
			$this->error['dimension'] = $this->data['error_length'];
		}

		return !$this->error;
	}
}