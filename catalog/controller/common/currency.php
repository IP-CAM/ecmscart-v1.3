<?php
class ControllerCommonCurrency extends Controller {
	public function index() {
		$this->data = $this->load->language('common/currency');

		$this->data['text_currency'] = $this->data['text_currency'];

		$this->data['action'] = $this->url->link('common/currency/currency', '', $this->request->server['HTTPS']);

		$this->data['code'] = $this->currency->getCode();

		$this->load->model('localisation/currency');

		$this->data['currencies'] = array();

		$results = $this->model_localisation_currency->getCurrencies();

		foreach ($results as $result) {
			if ($result['status']) {
				$this->data['currencies'][] = array(
					'title'        => $result['title'],
					'code'         => $result['code'],
					'symbol_left'  => $result['symbol_left'],
					'symbol_right' => $result['symbol_right']
				);
			}
		}

		if (!isset($this->request->get['route'])) {
			$this->data['redirect'] = $this->url->link('common/home');
		} else {
			$url_data = $this->request->get;

			unset($url_data['_route_']);

			$route = $url_data['route'];

			unset($url_data['route']);

			$url = '';

			if ($url_data) {
				$url = '&' . urldecode(http_build_query($url_data, '', '&'));
			}

			$this->data['redirect'] = $this->url->link($route, $url, $this->request->server['HTTPS']);
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/currency.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/common/currency.tpl', $this->data);
		} else {
			return $this->load->view('default/template/common/currency.tpl', $this->data);
		}
	}

	public function currency() {
		if (isset($this->request->post['code'])) {
			$this->currency->set($this->request->post['code']);

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
		}

		if (isset($this->request->post['redirect'])) {
			$this->response->redirect($this->request->post['redirect']);
		} else {
			$this->response->redirect($this->url->link('common/home'));
		}
	}
}