<?php
class ControllerToolBackup extends Controller {
	private $error = array();

	public function index() {
		 $this->data = $this->load->language('tool/backup');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('tool/backup');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'tool/backup')) {
			if (is_uploaded_file($this->request->files['import']['tmp_name'])) {
				$content = file_get_contents($this->request->files['import']['tmp_name']);
			} else {
				$content = false;
			}

			if ($content) {
				$this->model_tool_backup->restore($content);

				$this->session->data['success'] = $this->data['text_success'];

				$this->response->redirect($this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->error['warning'] = $this->data['error_empty'];
			}
		}
		
		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} 
		
		$this->data['error_warning'] = (isset($this->error['warning'])) ? $this->session->data['error'] : '';

		$this->data['success'] = (isset($this->session->data['success'])) ? $this->session->data['success'] : '';
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		} 
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
						$this->data['text_home'],	// Text to display link
						$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
						$this->data['heading_title'],	// Text to display link
						$this->url->link('tool/backup', 'token=' . $this->session->data['token'] , 'SSL')	// Link URL
					));
		
		$this->data['restore'] = $this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['backup'] = $this->url->link('tool/backup/backup', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['tables'] = $this->model_tool_backup->getTables();

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('tool/backup.tpl', $this->data));
	}

	public function backup() {
		$this->data = $this->load->language('tool/backup');

		if (!isset($this->request->post['backup'])) {
			$this->session->data['error'] = $this->data['error_backup'];

			$this->response->redirect($this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'));
		} elseif ($this->user->hasPermission('modify', 'tool/backup')) {
			$this->response->addheader('Pragma: public');
			$this->response->addheader('Expires: 0');
			$this->response->addheader('Content-Description: File Transfer');
			$this->response->addheader('Content-Type: application/octet-stream');
			$this->response->addheader('Content-Disposition: attachment; filename=' . date('Y-m-d_H-i-s', time()) . '_backup.sql');
			$this->response->addheader('Content-Transfer-Encoding: binary');

			$this->load->model('tool/backup');

			$this->response->setOutput($this->model_tool_backup->backup($this->request->post['backup']));
		} else {
			$this->session->data['error'] = $this->data['error_permission'];

			$this->response->redirect($this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}
}