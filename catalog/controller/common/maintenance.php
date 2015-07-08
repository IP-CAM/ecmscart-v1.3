<?php
class ControllerCommonMaintenance extends Controller {
	public function index() {
		if ($this->config->get('config_maintenance')) {
			$route = '';

			if (isset($this->request->get['route'])) {
				$part = explode('/', $this->request->get['route']);

				if (isset($part[0])) {
					$route .= $part[0];
				}
			}

			// Show site if logged in as admin
			$this->load->library('user');

			$this->user = new User($this->registry);

			if (($route != 'payment') && !$this->user->isLogged()) {
				return new Action('common/maintenance/info');
			}
		}
	}

	public function info() {
		$this->data = $this->load->language('common/maintenance');

		$this->document->setTitle($this->data['heading_title']);

		if ($this->request->server['SERVER_PROTOCOL'] == 'HTTP/1.1') {
			$this->response->addHeader('HTTP/1.1 503 Service Unavailable');
		} else {
			$this->response->addHeader('HTTP/1.0 503 Service Unavailable');
		}

		$this->response->addHeader('Retry-After: 3600');

		$this->data['breadcrumbs'] = $this->config->breadcrums(
					array(
							$this->data['text_maintenance'],	// Text to display link
							$this->url->link('common/maintenance')		// Link URL
							
						)
				);
				
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['footer'] = $this->load->controller('common/footer');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/maintenance.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/maintenance.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/common/maintenance.tpl', $this->data));
		}
	}
}