<?php
class ControllerShippingUPS extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('shipping/ups');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ups', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		$this->data['error_key'] =  (isset($this->error['key'])?$this->error['key']: '');
		
		$this->data['error_username'] =  (isset($this->error['username'])?$this->error['username']:'');

		$this->data['error_password'] =  (isset($this->error['password'])?$this->error['password']:'');
		
		$this->data['error_city'] =  (isset($this->error['city'])?$this->error['city']:'');
		
		$this->data['error_state'] =  (isset($this->error['state'])?$this->error['state']:'');
		
		$this->data['error_country'] =  (isset($this->error['country'])?$this->error['country']:'');
		
		$this->data['error_dimension'] =  (isset($this->error['dimension'])?$this->error['dimension']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_shipping'],
							$this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],	// Text to display link
							$this->url->link('shipping/ups', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));
		
		$this->data['action'] = $this->url->link('shipping/ups', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['ups_key'] = $this->request->post('ups_key',$this->config->get('ups_key'));
		
		$this->data['ups_username'] = $this->request->post('ups_username', $this->config->get('ups_username'));
		$this->data['ups_password'] = $this->request->post('ups_password',$this->config->get('ups_password'));
		$this->data['ups_pickup'] = $this->request->post('ups_pickup',$this->config->get('ups_pickup'));
		
		$this->data['pickups'] = array();

		$this->data['pickups'][] = array(
			'value' => '01',
			'text'  => $this->data['text_daily_pickup']
		);

		$this->data['pickups'][] = array(
			'value' => '03',
			'text'  => $this->data['text_customer_counter']
		);

		$this->data['pickups'][] = array(
			'value' => '06',
			'text'  => $this->data['text_one_time_pickup']
		);

		$this->data['pickups'][] = array(
			'value' => '07',
			'text'  => $this->data['text_on_call_air_pickup']
		);

		$this->data['pickups'][] = array(
			'value' => '19',
			'text'  => $this->data['text_letter_center']
		);

		$this->data['pickups'][] = array(
			'value' => '20',
			'text'  => $this->data['text_air_service_center']
		);

		$this->data['pickups'][] = array(
			'value' => '11',
			'text'  => $this->data['text_suggested_retail_rates']
		);

		
		$this->data['ups_packaging'] = $this->request->post('ups_packaging',$this->config->get('ups_packaging'));
		
		$this->data['packages'] = array();

		$this->data['packages'][] = array(
			'value' => '02',
			'text'  => $this->data['text_package']
		);

		$this->data['packages'][] = array(
			'value' => '01',
			'text'  => $this->data['text_ups_letter']
		);

		$this->data['packages'][] = array(
			'value' => '03',
			'text'  => $this->data['text_ups_tube']
		);

		$this->data['packages'][] = array(
			'value' => '04',
			'text'  => $this->data['text_ups_pak']
		);

		$this->data['packages'][] = array(
			'value' => '21',
			'text'  => $this->data['text_ups_express_box']
		);

		$this->data['packages'][] = array(
			'value' => '24',
			'text'  => $this->data['text_ups_25kg_box']
		);

		$this->data['packages'][] = array(
			'value' => '25',
			'text'  => $this->data['text_ups_10kg_box']
		);

		$this->data['ups_classification'] = $this->request->post('ups_classification',$this->config->get('ups_classification'));
		
		$this->data['classifications'][] = array(
			'value' => '01',
			'text'  => '01'
		);

		$this->data['classifications'][] = array(
			'value' => '03',
			'text'  => '03'
		);

		$this->data['classifications'][] = array(
			'value' => '04',
			'text'  => '04'
		);

		$this->data['ups_origin'] = $this->request->post('ups_origin',$this->config->get('ups_origin'));
		
		$this->data['origins'] = array();

		$this->data['origins'][] = array(
			'value' => 'US',
			'text'  => $this->data['text_us']
		);

		$this->data['origins'][] = array(
			'value' => 'CA',
			'text'  => $this->data['text_ca']
		);

		$this->data['origins'][] = array(
			'value' => 'EU',
			'text'  => $this->data['text_eu']
		);

		$this->data['origins'][] = array(
			'value' => 'PR',
			'text'  => $this->data['text_pr']
		);

		$this->data['origins'][] = array(
			'value' => 'MX',
			'text'  => $this->data['text_mx']
		);

		$this->data['origins'][] = array(
			'value' => 'other',
			'text'  => $this->data['text_other']
		);

		
		$this->data['ups_city'] = $this->request->post('ups_city',$this->config->get('ups_city'));
		
		$this->data['ups_state'] = $this->request->post('ups_state',$this->config->get('ups_state'));	
		
		$this->data['ups_country'] = $this->request->post('ups_country',$this->config->get('ups_country'));
		
		$this->data['ups_postcode'] = $this->request->post('ups_postcode',$this->config->get('ups_postcode'));
		
		$this->data['ups_test'] = $this->request->post('ups_test',$this->config->get('ups_test'));

		$this->data['ups_quote_type'] = $this->request->post('ups_quote_type',$this->config->get('ups_quote_type'));

		$this->data['quote_types'] = array();

		$this->data['quote_types'][] = array(
			'value' => 'residential',
			'text'  => $this->data['text_residential']
		);

		$this->data['quote_types'][] = array(
			'value' => 'commercial',
			'text'  => $this->data['text_commercial']
		);

		// US
		$this->data['ups_us_01'] = $this->request->post('ups_us_01',$this->config->get('ups_us_01'));
		
		$this->data['ups_us_02'] = $this->request->post('ups_us_02',$this->config->get('ups_us_02'));
		
		$this->data['ups_us_03'] = $this->request->post('ups_us_03',$this->config->get('ups_us_03'));

		$this->data['ups_us_07'] = $this->request->post('ups_us_07',$this->config->get('ups_us_07'));
		
		$this->data['ups_us_08'] = $this->request->post('ups_us_08',$this->config->get('ups_us_08'));

		$this->data['ups_us_11'] = $this->request->post('ups_us_11',$this->config->get('ups_us_11'));

		$this->data['ups_us_12'] = $this->request->post('ups_us_12',$this->config->get('ups_us_12'));

		$this->data['ups_us_13'] = $this->request->post('ups_us_13',$this->config->get('ups_us_13'));

		$this->data['ups_us_14'] = $this->request->post('ups_us_14',$this->config->get('ups_us_14'));

		$this->data['ups_us_54'] = $this->request->post('ups_us_54',$this->config->get('ups_us_54'));

		$this->data['ups_us_59'] = $this->request->post('ups_us_59',$this->config->get('ups_us_59'));

		$this->data['ups_us_65'] = $this->request->post('ups_us_65',$this->config->get('ups_us_65'));
		
		// Puerto Rico
		$this->data['ups_pr_01'] = $this->request->post('ups_pr_01',$this->config->get('ups_pr_01'));
		
		$this->data['ups_pr_02'] = $this->request->post('ups_pr_02',$this->config->get('ups_pr_02'));
		
		$this->data['ups_pr_03'] = $this->request->post('ups_pr_03',$this->config->get('ups_pr_03'));
		
		$this->data['ups_pr_07'] = $this->request->post('ups_pr_07',$this->config->get('ups_pr_07'));

		$this->data['ups_pr_08'] = $this->request->post('ups_pr_08',$this->config->get('ups_pr_08'));

		$this->data['ups_pr_14'] = $this->request->post('ups_pr_14',$this->config->get('ups_pr_14'));

		$this->data['ups_pr_54'] = $this->request->post('ups_pr_54',$this->config->get('ups_pr_54'));

		$this->data['ups_pr_65'] = $this->request->post('ups_pr_65',$this->config->get('ups_pr_65'));

		// Canada
		$this->data['ups_ca_01'] = $this->request->post('ups_ca_01',$this->config->get('ups_ca_01'));
		
		$this->data['ups_ca_02'] = $this->request->post('ups_ca_02',$this->config->get('ups_ca_02'));
		
		$this->data['ups_ca_07'] = $this->request->post('ups_ca_07',$this->config->get('ups_ca_07'));
		
		$this->data['ups_ca_08'] = $this->request->post('ups_ca_08',$this->config->get('ups_ca_08'));
		
		$this->data['ups_ca_11'] = $this->request->post('ups_ca_11',$this->config->get('ups_ca_11'));

		$this->data['ups_ca_12'] = $this->request->post('ups_ca_12',$this->config->get('ups_ca_12'));

		$this->data['ups_ca_13'] = $this->request->post('ups_ca_13',$this->config->get('ups_ca_13'));

		$this->data['ups_ca_14'] = $this->request->post('ups_ca_14',$this->config->get('ups_ca_14'));

		$this->data['ups_ca_54'] = $this->request->post('ups_ca_54',$this->config->get('ups_ca_54'));
		
		$this->data['ups_ca_65'] = $this->request->post('ups_ca_65',$this->config->get('ups_ca_65'));
		
		// Mexico
		$this->data['ups_mx_07'] = $this->request->post('ups_mx_07',$this->config->get('ups_mx_07'));
		
		$this->data['ups_mx_08'] = $this->request->post('ups_mx_08',$this->config->get('ups_mx_08'));
		
		$this->data['ups_mx_54'] = $this->request->post('ups_mx_54',$this->config->get('ups_mx_54'));
		
		$this->data['ups_mx_65'] = $this->request->post('ups_mx_65',$this->config->get('ups_mx_65'));

		// EU
		$this->data['ups_eu_07'] = $this->request->post('ups_eu_07',$this->config->get('ups_eu_07'));
		
		$this->data['ups_eu_08'] = $this->request->post('ups_eu_08',$this->config->get('ups_eu_08'));
		
		$this->data['ups_eu_11'] = $this->request->post('ups_eu_11',$this->config->get('ups_eu_11'));

		$this->data['ups_eu_54'] = $this->request->post('ups_eu_54',$this->config->get('ups_eu_54'));

		$this->data['ups_eu_65'] = $this->request->post('ups_eu_65',$this->config->get('ups_eu_65'));

		$this->data['ups_eu_82'] = $this->request->post('ups_eu_82',$this->config->get('ups_eu_82'));

		$this->data['ups_eu_83'] = $this->request->post('ups_eu_83',$this->config->get('ups_eu_83'));

		$this->data['ups_eu_84'] = $this->request->post('ups_eu_84',$this->config->get('ups_eu_84'));

		$this->data['ups_eu_85'] = $this->request->post('ups_eu_85',$this->config->get('ups_eu_85'));
		
		$this->data['ups_eu_86'] = $this->request->post('ups_eu_86',$this->config->get('ups_eu_86'));

		// Other
		$this->data['ups_other_07'] = $this->request->post('ups_other_07',$this->config->get('ups_other_07'));
		
		$this->data['ups_other_08'] = $this->request->post('ups_other_08',$this->config->get('ups_other_08'));
		
		$this->data['ups_other_11'] = $this->request->post('ups_other_11',$this->config->get('ups_other_11'));
		
		$this->data['ups_other_54'] = $this->request->post('ups_other_54',$this->config->get('ups_other_54'));

		$this->data['ups_other_65'] = $this->request->post('ups_other_65',$this->config->get('ups_other_65'));
		
		$this->data['ups_display_weight'] = $this->request->post('ups_display_weight',$this->config->get('ups_display_weight'));
		
		$this->data['ups_insurance'] = $this->request->post('ups_insurance',$this->config->get('ups_insurance'));
		
		$this->data['ups_weight_class_id'] = $this->request->post('ups_weight_class_id',$this->config->get('ups_weight_class_id'));

		$this->load->model('localisation/weight_class');

		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();
		
		$this->data['ups_length_code'] = $this->request->post('ups_length_code',$this->config->get('ups_length_code'));

		$this->data['ups_length_class_id'] = $this->request->post('ups_length_class_id',$this->config->get('ups_length_class_id'));

		$this->load->model('localisation/length_class');

		$this->data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();
		
		$this->data['ups_length'] = $this->request->post('ups_length',$this->config->get('ups_length'));

		$this->data['ups_width'] = $this->request->post('ups_width',$this->config->get('ups_width'));

		$this->data['ups_height'] = $this->request->post('ups_height',$this->config->get('ups_height'));

		$this->data['ups_tax_class_id'] = $this->request->post('ups_tax_class_id',$this->config->get('ups_tax_class_id'));

		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
		
		$this->data['ups_geo_zone_id'] = $this->request->post('ups_geo_zone_id',$this->config->get('ups_geo_zone_id'));

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		$this->data['ups_status'] = $this->request->post('ups_status',$this->config->get('ups_status'));

		$this->data['ups_sort_order'] = $this->request->post('ups_sort_order',$this->config->get('ups_sort_order'));

		$this->data['ups_debug'] = $this->request->post('ups_debug',$this->config->get('ups_debug'));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/ups.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/ups')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!$this->request->post['ups_key']) {
			$this->error['key'] = $this->data['error_key'];
		}

		if (!$this->request->post['ups_username']) {
			$this->error['username'] = $this->data['error_username'];
		}

		if (!$this->request->post['ups_password']) {
			$this->error['password'] = $this->data['error_password'];
		}

		if (!$this->request->post['ups_city']) {
			$this->error['city'] = $this->data['error_city'];
		}

		if (!$this->request->post['ups_state']) {
			$this->error['state'] = $this->data['error_state'];
		}

		if (!$this->request->post['ups_country']) {
			$this->error['country'] = $this->data['error_country'];
		}

		if (empty($this->request->post['ups_length'])) {
			$this->error['dimension'] = $this->data['error_dimension'];
		}

		if (empty($this->request->post['ups_width'])) {
			$this->error['dimension'] = $this->data['error_dimension'];
		}

		if (empty($this->request->post['ups_height'])) {
			$this->error['dimension'] = $this->data['error_dimension'];
		}

		return !$this->error;
	}
}