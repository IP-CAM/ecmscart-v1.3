<?php
class ControllerToolErrorLog extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('tool/error_log');
		$this->document->setTitle($this->data['heading_title']);
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('tool/error_log', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
						));
				
		$this->data['clear'] = $this->url->link('tool/error_log/clear', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['log'] = '';
		
		$file = DIR_LOGS . $this->config->get('config_error_filename');
		if (file_exists($file)) {
			$size = filesize($file);

			if ($size >= 5242880) {
				$suffix = array(
					'B',
					'KB',
					'MB',
					'GB',
					'TB',
					'PB',
					'EB',
					'ZB',
					'YB'
				);

				$i = 0;

				while (($size / 1024) > 1) {
					$size = $size / 1024;
					$i++;
				}

				$this->data['error_warning'] = sprintf($this->data['error_warning'], basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
			} else {
				$this->data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			}
		}
		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('tool/error_log.tpl', $this->data));
	}

	public function clear() {
		$this->data = $this->load->language('tool/error_log');

		if (!$this->user->hasPermission('modify', 'tool/error_log')) {
			$this->session->data['error'] = $this->data['error_permission'];
		} else {
			$file = DIR_LOGS . $this->config->get('config_error_filename');

			$handle = fopen($file, 'w+');

			fclose($handle);

			$this->session->data['success'] = $this->data['text_success'];
		}

		$this->response->redirect($this->url->link('tool/error_log', 'token=' . $this->session->data['token'], 'SSL'));
	}
}