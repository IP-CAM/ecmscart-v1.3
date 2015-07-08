<?php
class ControllerLocalisationZone extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('localisation/zone');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/zone');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/zone');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/zone');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['zone_id'])){
				$this->model_localisation_zone->editZone($this->request->get['zone_id'], $this->request->post);
			} else{
				$this->model_localisation_zone->addZone($this->request->post);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('localisation/zone');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/zone');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $zone_id) {
				$this->model_localisation_zone->deleteZone($zone_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','c.name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['save'] = $this->url->link('localisation/zone/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/zone/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['zones'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$zone_total = $this->model_localisation_zone->getTotalZones();

		$results = $this->model_localisation_zone->getZones($filter_data);

		foreach ($results as $result) {
			$this->data['zones'][] = array(
				'zone_id' => $result['zone_id'],
				'country' => $result['country'],
				'name'    => $result['name'] . (($result['zone_id'] == $this->config->get('config_zone_id')) ? $this->data['text_default'] : null),
				'code'    => $result['code'],
				'save'    => $this->url->link('localisation/zone/save', 'token=' . $this->session->data['token'] . '&zone_id=' . $result['zone_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');

		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');

		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());
		
		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // for sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_country'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . '&sort=c.name' . $url, 'SSL');
		$this->data['sort_name'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . '&sort=z.name' . $url, 'SSL');
		$this->data['sort_code'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . '&sort=z.code' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $zone_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($zone_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($zone_total - $this->config->get('config_limit_admin'))) ? $zone_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $zone_total, ceil($zone_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/zone_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['zone_id']) ? $this->data['text_add'] : $this->data['text_edit'];

		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: '');

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
						
		if (!isset($this->request->get['zone_id'])) {
			$this->data['action'] = $this->url->link('localisation/zone/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/zone/save', 'token=' . $this->session->data['token'] . '&zone_id=' . $this->request->get['zone_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/zone', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['zone_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$zone_info = $this->model_localisation_zone->getZone($this->request->get['zone_id']);
		}

		if (!empty($zone_info) && !$this->error) {
			$this->data['status'] = $zone_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', '1');
		}

		if (!empty($zone_info) && !$this->error) {
			$this->data['name'] = $zone_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($zone_info) && !$this->error) {
			$this->data['code'] = $zone_info['code'];
		} else {
			$this->data['code'] = $this->request->post('code', '');
		}

		if (!empty($zone_info) && !$this->error) {
			$this->data['country_id'] = $zone_info['country_id'];
		} else {
			$this->data['country_id'] = $this->request->post('country_id', '');
		}

		$this->load->model('localisation/country');

		$this->data['countries'] = $this->model_localisation_country->getCountries();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/zone_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/zone')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->data['error_name'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/zone')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('setting/store');
		$this->load->model('sale/customer');
		$this->load->model('marketing/affiliate');
		$this->load->model('localisation/geo_zone');

		foreach ($this->request->post['selected'] as $zone_id) {
			if ($this->config->get('config_zone_id') == $zone_id) {
				$this->error['warning'] = $this->data['error_default'];
			}

			$store_total = $this->model_setting_store->getTotalStoresByZoneId($zone_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->data['error_store'], $store_total);
			}

			$address_total = $this->model_sale_customer->getTotalAddressesByZoneId($zone_id);

			if ($address_total) {
				$this->error['warning'] = sprintf($this->data['error_address'], $address_total);
			}

			$affiliate_total = $this->model_marketing_affiliate->getTotalAffiliatesByZoneId($zone_id);

			if ($affiliate_total) {
				$this->error['warning'] = sprintf($this->data['error_affiliate'], $affiliate_total);
			}

			$zone_to_geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByZoneId($zone_id);

			if ($zone_to_geo_zone_total) {
				$this->error['warning'] = sprintf($this->data['error_zone_to_geo_zone'], $zone_to_geo_zone_total);
			}
		}

		return !$this->error;
	}
}