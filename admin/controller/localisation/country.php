<?php
class ControllerLocalisationCountry extends Controller {
	private $error = array();
	// sorting and filter array
	private $url_data = array(
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('localisation/country');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/country');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('localisation/country');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/country');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['country_id'])){
				$this->model_localisation_country->editCountry($this->request->get['country_id'], $this->request->post);
			} else{
				$this->model_localisation_country->addCountry($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('localisation/country');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('localisation/country');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $country_id) {
				$this->model_localisation_country->deleteCountry($country_id);
			}

			$this->session->data['success'] = $this->data['text_success'];
			// Sorting and Filter Function
			$url = $this->request->getUrl($this->url_data);

			$this->response->redirect($this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort','name');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page',1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);
		
		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		$this->data['save'] = $this->url->link('localisation/country/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/country/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['countries'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$country_total = $this->model_localisation_country->getTotalCountries();

		$results = $this->model_localisation_country->getCountries($filter_data);

		foreach ($results as $result) {
			$this->data['countries'][] = array(
				'country_id' => $result['country_id'],
				'name'       => $result['name'] . (($result['country_id'] == $this->config->get('config_country_id')) ? $this->data['text_default'] : null),
				'iso_code_2' => $result['iso_code_2'],
				'iso_code_3' => $result['iso_code_3'],
				'save'       => $this->url->link('localisation/country/save', 'token=' . $this->session->data['token'] . '&country_id=' . $result['country_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['selected'] = $this->request->post('selected',array());
		
		$url = ($order == 'ASC')? '&order=DESC' : '&order=ASC' ; // for sorting
		
		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_iso_code_2'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . '&sort=iso_code_2' . $url, 'SSL');
		$this->data['sort_iso_code_3'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . '&sort=iso_code_3' . $url, 'SSL');

		// Sorting and Filter Function for paging
		$url_data = array(
				'sort',
				'order',
			);
		
		$url = $this->request->getUrl($url_data);

		$pagination = new Pagination();
		$pagination->total = $country_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($country_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($country_total - $this->config->get('config_limit_admin'))) ? $country_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $country_total, ceil($country_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/country_list.tpl', $this->data));
	}

	protected function getForm() {		
		$this->data['text_form'] = !isset($this->request->get['country_id']) ? $this->data['text_add'] : $this->data['text_edit'];
		
		$this->data['error_warning'] =  (isset($this->error['warning'])? $this->error['warning']: '');
		
		$this->data['error_name'] =  (isset($this->error['name'])? $this->error['name']: '');

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));
		
		if (!isset($this->request->get['country_id'])) {
			$this->data['action'] = $this->url->link('localisation/country/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/country/save', 'token=' . $this->session->data['token'] . '&country_id=' . $this->request->get['country_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('localisation/country', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['country_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);
		}

		if (!empty($country_info) && !$this->error) {
			$this->data['name'] = $country_info['name'];
		} else {
			$this->data['name'] = $this->request->post('name', '');
		}

		if (!empty($country_info) && !$this->error) {
			$this->data['iso_code_2'] = $country_info['iso_code_2'];
		} else {
			$this->data['iso_code_2'] = $this->request->post('iso_code_2', '');
		}

		if (isset($this->request->post['iso_code_3'])) {
			$this->data['iso_code_3'] = $this->request->post['iso_code_3'];
		} elseif (!empty($country_info) && !$this->error) {
			$this->data['iso_code_3'] = $country_info['iso_code_3'];
		} else {
			$this->data['iso_code_3'] = $this->request->post('iso_code_3', '');
		}

		if (!empty($country_info) && !$this->error) {
			$this->data['address_format'] = $country_info['address_format'];
		} else {
			$this->data['address_format'] = $this->request->post('address_format', '');
		}

		if (!empty($country_info) && !$this->error) {
			$this->data['postcode_required'] = $country_info['postcode_required'];
		} else {
			$this->data['postcode_required'] = $this->request->post('postcode_required', 0);
		}

		if (!empty($country_info) && !$this->error) {
			$this->data['status'] = $country_info['status'];
		} else {
			$this->data['status'] = '1';$this->request->post('status', '1');
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/country_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/country')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 128)) {
			$this->error['name'] = $this->data['error_name'];
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/country')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		$this->load->model('setting/store');
		$this->load->model('sale/customer');
		$this->load->model('marketing/affiliate');
		$this->load->model('localisation/zone');
		$this->load->model('localisation/geo_zone');

		foreach ($this->request->post['selected'] as $country_id) {
			if ($this->config->get('config_country_id') == $country_id) {
				$this->error['warning'] = $this->data['error_default'];
			}

			$store_total = $this->model_setting_store->getTotalStoresByCountryId($country_id);

			if ($store_total) {
				$this->error['warning'] = sprintf($this->data['error_store'], $store_total);
			}

			$address_total = $this->model_sale_customer->getTotalAddressesByCountryId($country_id);

			if ($address_total) {
				$this->error['warning'] = sprintf($this->data['error_address'], $address_total);
			}

			$affiliate_total = $this->model_marketing_affiliate->getTotalAffiliatesByCountryId($country_id);

			if ($affiliate_total) {
				$this->error['warning'] = sprintf($this->data['error_affiliate'], $affiliate_total);
			}

			$zone_total = $this->model_localisation_zone->getTotalZonesByCountryId($country_id);

			if ($zone_total) {
				$this->error['warning'] = sprintf($this->data['error_zone'], $zone_total);
			}

			$zone_to_geo_zone_total = $this->model_localisation_geo_zone->getTotalZoneToGeoZoneByCountryId($country_id);

			if ($zone_to_geo_zone_total) {
				$this->error['warning'] = sprintf($this->data['error_zone_to_geo_zone'], $zone_to_geo_zone_total);
			}
		}

		return !$this->error;
	}
}