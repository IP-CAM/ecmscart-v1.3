<?php
class ControllerPaymentKlarnaInvoice extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('payment/klarna_invoice');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$status = false;

			foreach ($this->request->post['klarna_invoice'] as $klarna_invoice) {
				if ($klarna_invoice['status']) {
					$status = true;

					break;
				}
			}

			$klarna_data = array(
				'klarna_invoice_pclasses' => $this->pclasses,
				'klarna_invoice_status'   => $status
			);

			$this->model_setting_setting->editSetting('klarna_invoice', array_merge($this->request->post, $klarna_data));

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['text_payment'],	// Text to display link
							$this->url->link('extension/payment', 'token=' . $this->session->data['token'] , 'SSL'),	// Link URL
							$this->data['heading_title'],
							$this->url->link('payment/klarna_invoice', 'token=' . $this->session->data['token'], 'SSL')
						));	
		
		$this->data['action'] = $this->url->link('payment/klarna_invoice', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['countries'] = array();

		$this->data['countries'][] = array(
			'name' => $this->data['text_germany'],
			'code' => 'DEU'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_netherlands'],
			'code' => 'NLD'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_denmark'],
			'code' => 'DNK'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_sweden'],
			'code' => 'SWE'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_norway'],
			'code' => 'NOR'
		);

		$this->data['countries'][] = array(
			'name' => $this->data['text_finland'],
			'code' => 'FIN'
		);

		
		$this->data['klarna_invoice'] = $this->request->post('klarna_invoice', $this->config->get('klarna_invoice'));
		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$file = DIR_LOGS . 'klarna_invoice.log';

		if (file_exists($file)) {
			$this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
		} else {
			$this->data['log'] = '';
		}

		$this->data['clear'] = $this->url->link('payment/klarna_invoice/clear', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/klarna_invoice.tpl', $this->data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/klarna_invoice')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	private function parseResponse($node, $document) {
		$child = $node;

		switch ($child->nodeName) {
			case 'string':
				$value = $child->nodeValue;
				break;

			case 'boolean':
				$value = (string)$child->nodeValue;

				if ($value == '0') {
					$value = false;
				} elseif ($value == '1') {
					$value = true;
				} else {
					$value = null;
				}

				break;

			case 'integer':
			case 'int':
			case 'i4':
			case 'i8':
				$value = (int)$child->nodeValue;
				break;

			case 'array':
				$value = array();

				$xpath = new DOMXPath($document);
				$entries = $xpath->query('.//array/data/value', $child);

				for ($i = 0; $i < $entries->length; $i++) {
					$value[] = $this->parseResponse($entries->item($i)->firstChild, $document);
				}

				break;

			default:
				$value = null;
		}

		return $value;
	}

	public function clear() {
		$this->data = $this->load->language('payment/klarna_invoice');

		$file = DIR_LOGS . 'klarna_invoice.log';

		$handle = fopen($file, 'w+');

		fclose($handle);

		$this->session->data['success'] = $this->data['text_success'];

		$this->response->redirect($this->url->link('payment/klarna_invoice', 'token=' . $this->session->data['token'], 'SSL'));
	}
}