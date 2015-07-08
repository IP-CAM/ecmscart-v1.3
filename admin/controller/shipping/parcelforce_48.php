<?php
class ControllerShippingParcelforce48 extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('shipping/parcelforce_48');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('parcelforce_48', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']: '');

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_shipping'],
							$this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
							$this->data['heading_title'],	// Text to display link
							$this->url->link('shipping/parcelforce_48', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));
		
		$this->data['action'] = $this->url->link('shipping/parcelforce_48', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		if ($this->config->get('parcelforce_48_rate') && !$this->error) {
			$this->data['parcelforce_48_rate'] = $this->config->get('parcelforce_48_rate');
		} else {
			$this->data['parcelforce_48_rate'] = $this->request->post('parcelforce_48_rate', '10:15.99,12:19.99,14:20.99,16:21.99,18:21.99,20:21.99,22:26.99,24:30.99,26:34.99,28:38.99,30:42.99,35:52.99,40:62.99,45:72.99,50:82.99,55:92.99,60:102.99,65:112.99,70:122.99,75:132.99,80:142.99,85:152.99,90:162.99,95:172.99,100:182.99');
		}

		if ($this->config->get('parcelforce_48_insurance') && !$this->error) {
			$this->data['parcelforce_48_insurance'] = $this->config->get('parcelforce_48_insurance');
		} else {
			$this->data['parcelforce_48_insurance'] = $this->request->post('parcelforce_48_insurance', '150:0,500:12,1000:24,1500:36,2000:48,2500:60');
		}

		$this->data['parcelforce_48_display_weight'] = $this->request->post('parcelforce_48_display_weight', $this->config->get('parcelforce_48_display_weight'));
		
		$this->data['parcelforce_48_display_insurance'] = $this->request->post('parcelforce_48_display_insurance', $this->config->get('parcelforce_48_display_insurance'));
		
		$this->data['parcelforce_48_display_time'] = $this->request->post('parcelforce_48_display_time', $this->config->get('parcelforce_48_display_time'));
		
		$this->data['parcelforce_48_tax_class_id'] = $this->request->post('parcelforce_48_tax_class_id', $this->config->get('parcelforce_48_tax_class_id'));
		
		$this->load->model('localisation/tax_class');

		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->data['parcelforce_48_geo_zone_id'] = $this->request->post('parcelforce_48_geo_zone_id', $this->config->get('parcelforce_48_geo_zone_id'));
		
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->data['parcelforce_48_status'] = $this->request->post('parcelforce_48_status', $this->config->get('parcelforce_48_status'));
		
		$this->data['parcelforce_48_sort_order'] = $this->request->post('parcelforce_48_sort_order', $this->config->get('parcelforce_48_sort_order'));
		
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('shipping/parcelforce_48.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/parcelforce_48')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}