<?php
class ControllerShippingAusPost extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('shipping/auspost');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('auspost', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_postcode'] =  (isset($this->error['postcode'])?$this->error['postcode']:'');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_shipping'],
							$this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'), 
							$this->data['heading_title'],	// Text to display link
							$this->url->link('shipping/auspost', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));
		
		$this->data['action'] = $this->url->link('shipping/auspost', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['auspost_postcode'] = $this->request->post('auspost_postcode',$this->config->get('auspost_postcode'));
		
		$this->data['auspost_standard'] = $this->request->post('auspost_standard',$this->config->get('auspost_standard'));
		
		$this->data['auspost_express'] = $this->request->post('auspost_express',$this->config->get('auspost_express'));
		
		$this->data['auspost_display_time'] = $this->request->post('auspost_display_time',$this->config->get('auspost_display_time'));
		
		$this->data['auspost_weight_class_id'] = $this->request->post('auspost_weight_class_id',$this->config->get('auspost_weight_class_id'));
		
		$this->load->model('localisation/weight_class');

		$this->data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->data['auspost_tax_class_id'] = $this->request->post('auspost_tax_class_id',$this->config->get('auspost_tax_class_id'));
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->data['auspost_geo_zone_id'] = $this->request->post('auspost_geo_zone_id',$this->config->get('auspost_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['auspost_status'] = $this->request->post('auspost_status',$this->config->get('auspost_status'));
		
		$this->data['auspost_sort_order'] = $this->request->post('auspost_sort_order',$this->config->get('auspost_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/auspost.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/auspost')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if (!preg_match('/^[0-9]{4}$/', $this->request->post['auspost_postcode'])) {
			$this->error['postcode'] = $this->data['error_postcode'];
		}

		return !$this->error;
	}
}