<?php
class ControllerSaleCustomField extends Controller {
	private $error = array();
	
	private $url_data = array(
							'sort',
							'order',
							'page',
						);
			
	public function index() {
		$this->data = $this->load->language('sale/custom_field');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/custom_field');

		$this->getList();
	}

	public function save() {
		$this->data = $this->load->language('sale/custom_field');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/custom_field');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			if(isset($this->request->get['custom_field_id'])){
				$this->model_sale_custom_field->editCustomField($this->request->get['custom_field_id'], $this->request->post);
			}else{
				$this->model_sale_custom_field->addCustomField($this->request->post);
			}
			
			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

		$this->response->redirect($this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->data = $this->load->language('sale/custom_field');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('sale/custom_field');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $custom_field_id) {
				$this->model_sale_custom_field->deleteCustomField($custom_field_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);

		$this->response->redirect($this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$sort = $this->request->get('sort', 'cfd.name');
		
		$order = $this->request->get('order', 'ASC');
		
		$page = $this->request->get('page', 1);
		
		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(
					array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						)
				);
		
		$this->data['save'] = $this->url->link('sale/custom_field/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/custom_field/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['custom_fields'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$custom_field_total = $this->model_sale_custom_field->getTotalCustomFields();

		$results = $this->model_sale_custom_field->getCustomFields($filter_data);

		foreach ($results as $result) {
			$type = '';

			switch ($result['type']) {
				case 'select':
					$type = $this->data['text_select'];
					break;
				case 'radio':
					$type = $this->data['text_radio'];
					break;
				case 'checkbox':
					$type = $this->data['text_checkbox'];
					break;
				case 'input':
					$type = $this->data['text_input'];
					break;
				case 'text':
					$type = $this->data['text_text'];
					break;
				case 'textarea':
					$type = $this->data['text_textarea'];
					break;
				case 'file':
					$type = $this->data['text_file'];
					break;
				case 'date':
					$type = $this->data['text_date'];
					break;
				case 'datetime':
					$type = $this->data['text_datetime'];
					break;
				case 'time':
					$type = $this->data['text_time'];
					break;
			}

			$this->data['custom_fields'][] = array(
				'custom_field_id' => $result['custom_field_id'],
				'name'            => $result['name'],
				'location'        => $this->data['text_' . $result['location']],
				'type'            => $type,
				'status'          => $result['status'],
				'sort_order'      => $result['sort_order'],
				'save'            => $this->url->link('sale/custom_field/save', 'token=' . $this->session->data['token'] . '&custom_field_id=' . $result['custom_field_id'] . $url, 'SSL')
			);
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');

		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['selected'] =  $this->request->post('selected', array());
		
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ;

		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . '&sort=cfd.name' . $url, 'SSL');
		$this->data['sort_location'] = $this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . '&sort=cf.location' . $url, 'SSL');
		$this->data['sort_type'] = $this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . '&sort=cf.type' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . '&sort=cf.status' . $url, 'SSL');
		$this->data['sort_sort_order'] = $this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . '&sort=cf.sort_order' . $url, 'SSL');
		
		$pagination = new Pagination();
		$pagination->total = $custom_field_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($custom_field_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($custom_field_total - $this->config->get('config_limit_admin'))) ? $custom_field_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $custom_field_total, ceil($custom_field_total / $this->config->get('config_limit_admin')));

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/custom_field_list.tpl', $this->data));
	}

	protected function getForm() {
		$this->data['text_form'] = !isset($this->request->get['custom_field_id']) ? $this->data['text_add'] : $this->data['text_edit'];
	
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_name'] =  (isset($this->error['name'])?$this->error['name']:'');

		$this->data['error_custom_field_value'] =  (isset($this->error['custom_field_value'])?$this->error['custom_field_value']:'');
		//for sorting function
		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		if (!isset($this->request->get['custom_field_id'])) {
			$this->data['action'] = $this->url->link('sale/custom_field/save', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/custom_field/save', 'token=' . $this->session->data['token'] . '&custom_field_id=' . $this->request->get['custom_field_id'] . $url, 'SSL');
		}

		$this->data['cancel'] = $this->url->link('sale/custom_field', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['custom_field_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$custom_field_info = $this->model_sale_custom_field->getCustomField($this->request->get['custom_field_id']);
		}

		$this->data['token'] = $this->session->data['token'];

		$this->load->model('localisation/language');

		$this->data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->get['custom_field_id']) && !$this->error) {
			$this->data['custom_field_description'] = $this->model_sale_custom_field->getCustomFieldDescriptions($this->request->get['custom_field_id']);
		} else {
			$this->data['custom_field_description'] = $this->request->post('custom_field_description', array());
		}

		if (!empty($custom_field_info) && !$this->error) {
			$this->data['location'] = $custom_field_info['location'];
		} else {
			$this->data['location'] = $this->request->post('location', '');
		}

		if (!empty($custom_field_info) && !$this->error) {
			$this->data['type'] = $custom_field_info['type'];
		} else {
			$this->data['type'] = $this->request->post('type', '');
		}

		if (!empty($custom_field_info) && !$this->error) {
			$this->data['value'] = $custom_field_info['value'];
		} else {
			$this->data['value'] = $this->request->post('value', '');
		}

		if (!empty($custom_field_info) && !$this->error) {
			$this->data['status'] = $custom_field_info['status'];
		} else {
			$this->data['status'] = $this->request->post('status', '');
		}

		if (!empty($custom_field_info) && !$this->error) {
			$this->data['sort_order'] = $custom_field_info['sort_order'];
		} else {
			$this->data['sort_order'] = $this->request->post('sort_order', '');
		}

		if (isset($this->request->get['custom_field_id'])) {
			$custom_field_values = $this->model_sale_custom_field->getCustomFieldValueDescriptions($this->request->get['custom_field_id']);
		} else {
			$custom_field_values = $this->request->post('custom_field_values', array());
		}

		$this->data['custom_field_values'] = array();

		foreach ($custom_field_values as $custom_field_value) {
			$this->data['custom_field_values'][] = array(
				'custom_field_value_id'          => $custom_field_value['custom_field_value_id'],
				'custom_field_value_description' => $custom_field_value['custom_field_value_description'],
				'sort_order'                     => $custom_field_value['sort_order']
			);
		}

		if (isset($this->request->get['custom_field_id'])) {
			$custom_field_customer_groups = $this->model_sale_custom_field->getCustomFieldCustomerGroups($this->request->get['custom_field_id']);
		} else {
			$custom_field_customer_groups = $this->request->post('custom_field_customer_groups', array());
		}

		$this->data['custom_field_customer_group'] = array();

		foreach ($custom_field_customer_groups as $custom_field_customer_group) {
			$this->data['custom_field_customer_group'][] = $custom_field_customer_group['customer_group_id'];
		}

		$this->data['custom_field_required'] = array();

		foreach ($custom_field_customer_groups as $custom_field_customer_group) {
			if ($custom_field_customer_group['required']) {
				$this->data['custom_field_required'][] = $custom_field_customer_group['customer_group_id'];
			}
		}

		$this->load->model('sale/customer_group');

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/custom_field_form.tpl', $this->data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'sale/custom_field')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		foreach ($this->request->post['custom_field_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 128)) {
				$this->error['name'][$language_id] = $this->data['error_name'];
			}
		}

		if (($this->request->post['type'] == 'select' || $this->request->post['type'] == 'radio' || $this->request->post['type'] == 'checkbox')) {
			if (!isset($this->request->post['custom_field_value'])) {
				$this->error['warning'] = $this->data['error_type'];
			}

			if (isset($this->request->post['custom_field_value'])) {
				foreach ($this->request->post['custom_field_value'] as $custom_field_value_id => $custom_field_value) {
					foreach ($custom_field_value['custom_field_value_description'] as $language_id => $custom_field_value_description) {
						if ((utf8_strlen($custom_field_value_description['name']) < 1) || (utf8_strlen($custom_field_value_description['name']) > 128)) {
							$this->error['custom_field_value'][$custom_field_value_id][$language_id] = $this->data['error_custom_value'];
						}
					}
				}
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'sale/custom_field')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}