<?php
class ControllerShippingFedex extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('shipping/fedex');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('fedex', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_key'] =  (isset($this->error['key'])?$this->error['key']:'');
		
		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');

		$this->data['error_account'] =  (isset($this->error['account'])?$this->error['account']:'');
		
		$this->data['error_meter'] =  (isset($this->error['meter'])?$this->error['meter']:'');
		
		$this->data['error_postcode'] =  (isset($this->error['postcode'])?$this->error['postcode']:'');
		
		$this->data['error_dimension'] =  (isset($this->error['dimension'])?$this->error['dimension']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_shipping'],
							$this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],	// Text to display link
							$this->url->link('shipping/fedex', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));

		$this->data['action'] = $this->url->link('shipping/fedex', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['fedex_key'] = $this->request->post('fedex_key',$this->config->get('fedex_key'));
		
		$this->data['fedex_password'] = $this->request->post('fedex_password', $this->config->get('fedex_password'));
		
		$this->data['fedex_account'] = $this->request->post('fedex_account',$this->config->get('fedex_account'));
		
		$this->data['fedex_meter'] = $this->request->post('fedex_meter',$this->config->get('fedex_meter'));
		
		$this->data['fedex_postcode'] = $this->request->post('fedex_postcode',$this->config->get('fedex_postcode'));
		
		$this->data['fedex_test'] = $this->request->post('fedex_test',$this->config->get('fedex_test'));
		
			
		if ($this->config->get('fedex_service') && !$this->error) {
			$this->data['fedex_service'] = $this->config->get('fedex_service');
		} else {
			$this->data['fedex_service'] = $this->request->post('fedex_service', array());
		}

		$this->data['services'] = array();

		$this->data['services'][] = array(
			'text'  => $this->data['text_europe_first_international_priority'],
			'value' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_1_day_freight'],
			'value' => 'FEDEX_1_DAY_FREIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_2_day'],
			'value' => 'FEDEX_2_DAY'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_2_day_am'],
			'value' => 'FEDEX_2_DAY_AM'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_2_day_freight'],
			'value' => 'FEDEX_2_DAY_FREIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_3_day_freight'],
			'value' => 'FEDEX_3_DAY_FREIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_express_saver'],
			'value' => 'FEDEX_EXPRESS_SAVER'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_first_freight'],
			'value' => 'FEDEX_FIRST_FREIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_freight_economy'],
			'value' => 'FEDEX_FREIGHT_ECONOMY'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_freight_priority'],
			'value' => 'FEDEX_FREIGHT_PRIORITY'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_fedex_ground'],
			'value' => 'FEDEX_GROUND'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_first_overnight'],
			'value' => 'FIRST_OVERNIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_ground_home_delivery'],
			'value' => 'GROUND_HOME_DELIVERY'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_international_economy'],
			'value' => 'INTERNATIONAL_ECONOMY'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_international_economy_freight'],
			'value' => 'INTERNATIONAL_ECONOMY_FREIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_international_first'],
			'value' => 'INTERNATIONAL_FIRST'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_international_priority'],
			'value' => 'INTERNATIONAL_PRIORITY'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_international_priority_freight'],
			'value' => 'INTERNATIONAL_PRIORITY_FREIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_priority_overnight'],
			'value' => 'PRIORITY_OVERNIGHT'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_smart_post'],
			'value' => 'SMART_POST'
		);

		$this->data['services'][] = array(
			'text'  => $this->data['text_standard_overnight'],
			'value' => 'STANDARD_OVERNIGHT'
		);
				
		$this->data['fedex_length'] = $this->request->post('fedex_length',$this->config->get('fedex_length'));
		
		$this->data['fedex_width'] = $this->request->post('fedex_width',$this->config->get('fedex_width'));
		
		$this->data['fedex_height'] = $this->request->post('fedex_height',$this->config->get('fedex_height'));
			
		$this->data['fedex_length_class_id'] = $this->request->post('fedex_length_class_id',$this->config->get('fedex_length_class_id'));	
		
		$this->load->model('localisation/length_class');

		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
		
		$this->data['fedex_dropoff_type'] = $this->request->post('fedex_dropoff_type',$this->config->get('fedex_dropoff_type'));
		
		$this->data['fedex_packaging_type'] = $this->request->post('fedex_packaging_type',$this->config->get('fedex_packaging_type'));
		
		$this->data['fedex_rate_type'] = $this->request->post('fedex_rate_type',$this->config->get('fedex_rate_type'));
		
		$this->data['fedex_destination_type'] = $this->request->post('fedex_destination_type',$this->config->get('fedex_destination_type'));
		
		$this->data['fedex_display_time'] = $this->request->post('fedex_display_time',$this->config->get('fedex_display_time'));
		
		$this->data['fedex_display_weight'] = $this->request->post('fedex_display_weight',$this->config->get('fedex_display_weight'));
		
		$this->data['fedex_weight_class_id'] = $this->request->post('fedex_weight_class_id',$this->config->get('fedex_weight_class_id'));
		
		$this->load->model('localisation/weight_class');

		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->data['fedex_tax_class_id'] = $this->request->post('fedex_tax_class_id',$this->config->get('fedex_tax_class_id'));
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		$this->data['fedex_geo_zone_id'] = $this->request->post('fedex_geo_zone_id',$this->config->get('fedex_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['fedex_status'] = $this->request->post('fedex_status',$this->config->get('fedex_status'));
		
		$this->data['fedex_sort_order'] = $this->request->post('fedex_sort_order',$this->config->get('fedex_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/fedex.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/fedex')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['fedex_key']) {
			$this->error['key'] = $this->data['error_key'];
		}

		if (!$this->request->post['fedex_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		if (!$this->request->post['fedex_account']) {
			$this->error['account'] = $this->data['error_account'];
		}

		if (!$this->request->post['fedex_meter']) {
			$this->error['meter'] = $this->data['error_meter'];
		}

		if (!$this->request->post['fedex_postcode']) {
			$this->error['postcode'] = $this->data['error_postcode'];
		}
		
		if (!$this->request->post['fedex_length'] || !$this->request->post['fedex_width'] || !$this->request->post['fedex_width']) {
			$this->error['dimension'] = $this->data['error_dimension'];
		}		

		return !$this->error;
	}
}