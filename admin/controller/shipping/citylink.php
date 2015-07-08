<?php
class ControllerShippingCitylink extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('shipping/citylink');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('citylink', $this->request->post);

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
							$this->url->link('shipping/citylink', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));
		
		$this->data['action'] = $this->url->link('shipping/citylink', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		
		if ($this->config->get('citylink_rate') && !$this->error) {
			$this->data['citylink_rate'] = $this->config->get('citylink_rate');
		} else {
			$this->data['citylink_rate'] =  $this->request->post('citylink_rate', '10:11.6,15:14.1,20:16.60,25:19.1,30:21.6,35:24.1,40:26.6,45:29.1,50:31.6,55:34.1,60:36.6,65:39.1,70:41.6,75:44.1,80:46.6,100:56.6,125:69.1,150:81.6,200:106.6');
		}

		$this->data['citylink_tax_class_id'] = $this->request->post('citylink_tax_class_id', $this->config->get('citylink_tax_class_id'));
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->data['citylink_geo_zone_id'] = $this->request->post('citylink_geo_zone_id', $this->config->get('citylink_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['citylink_status'] = $this->request->post('citylink_status', $this->config->get('citylink_status'));
		
		$this->data['citylink_sort_order'] = $this->request->post('citylink_sort_order', $this->config->get('citylink_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/citylink.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/citylink')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}