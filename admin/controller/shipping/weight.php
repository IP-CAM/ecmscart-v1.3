<?php
class ControllerShippingWeight extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('shipping/weight');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('weight', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_shipping'],
							$this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],	// Text to display link
							$this->url->link('shipping/weight', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));

		$this->data['action'] = $this->url->link('shipping/weight', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/geo_zone');

		$geo_zones = $this->model_localisation_geo_zone->getGeoZones();

		foreach ($geo_zones as $geo_zone) {
			$this->data['weight_' . $geo_zone['geo_zone_id'] . '_rate'] = $this->request->post('weight_' . $geo_zone['geo_zone_id'] . '_rate', $this->config->get('weight_' . $geo_zone['geo_zone_id'] . '_rate'));
			
			$this->data['weight_' . $geo_zone['geo_zone_id'] . '_status'] = $this->request->post('weight_' . $geo_zone['geo_zone_id'] . '_status', $this->config->get('weight_' . $geo_zone['geo_zone_id'] . '_status'));
			
		}

		$this->data['geo_zones'] = $geo_zones;

		$this->data['weight_tax_class_id'] = $this->request->post('weight_tax_class_id' , $this->config->get('weight_tax_class_id'));
	
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->data['weight_status'] = $this->request->post('weight_status',$this->config->get('weight_status'));
	
		$this->data['weight_sort_order'] = $this->request->post('weight_sort_order',$this->config->get('weight_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/weight.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/weight')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}