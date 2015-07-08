<?php
class ControllerToolUpload extends Controller {
	private $error = array();
	private $url_data = array(//array for filter and paging
				'filter_name =>encode' ,
				'filter_date_added',
				'sort',
				'order',
				'page',
			);

	public function index() {
		$this->data = $this->load->language('tool/upload');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('tool/upload');

		$this->getList();
	}

	public function delete() {
		$this->data = $this->load->language('tool/upload');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('tool/upload');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $upload_id) {
				// Remove file before deleting DB record.
				$upload_info = $this->model_tool_upload->getUpload($upload_id);

				if ($upload_info && is_file(DIR_DOWNLOAD . $upload_info['filename'])) {
					unlink(DIR_UPLOAD . $upload_info['filename']);
				}

				$this->model_tool_upload->deleteUpload($upload_id);
			}

			$this->session->data['success'] = $this->data['text_success'];

			$url = $this->request->getUrl($this->url_data);
			
			$this->response->redirect($this->url->link('tool/upload', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_name = $this->request->get('filter_name', null);
		
		$filter_date_added = $this->request->get('filter_date_added', null);
		
		$sort = $this->request->get('sort', 'date_added');
		
		$order = $this->request->get('order', 'DESC');
		
		$page = $this->request->get('page', 1);

		$url = $this->request->getUrl($this->url_data);

		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('tool/upload', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->data['delete'] = $this->url->link('tool/upload/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['uploads'] = array();

		$filter_data = array(
			'filter_name'	    => $filter_name,
			'filter_date_added'	=> $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$upload_total = $this->model_tool_upload->getTotalUploads($filter_data);

		$results = $this->model_tool_upload->getUploads($filter_data);

		foreach ($results as $result) {
			$this->data['uploads'][] = array(
				'upload_id'  => $result['upload_id'],
				'name'       => $result['name'],
				'filename'   => $result['filename'],
				'date_added' => date($this->$this->data['date_format_short'], strtotime($result['date_added'])),
				'download'   => $this->url->link('tool/upload/download', 'token=' . $this->session->data['token'] . '&code=' . $result['code'] . $url, 'SSL')
			);
		}

		$this->data['token'] = $this->session->data['token'];
		
		$this->data['error_warning'] =(isset($this->session->data['warning'])) ? $this->session->data['warning'] : '';
		
		$this->data['success'] =(isset($this->session->data['success'])) ? $this->session->data['success'] : '';
		
		if (isset($this->session->data['success'])) 
			unset($this->session->data['success']);
		

		$this->data['selected'] = (array)$this->request->post('selected', array());
		
		// for sorting
		$url_data = array(
				'filter_name' => 'encode', 
				'filter_date_added',
			);
		
		$url = $this->request->getUrl($url_data);
		$url .= ($order == 'ASC')? '&order=DESC' : '&order=ASC' ;

		if (isset($this->request->get['page'])) 
			$url .= '&page=' . $this->request->get['page'];

		$this->data['sort_name'] = $this->url->link('tool/upload', 'token=' . $this->session->data['token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_filename'] = $this->url->link('tool/upload', 'token=' . $this->session->data['token'] . '&sort=filename' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('tool/upload', 'token=' . $this->session->data['token'] . '&sort=date_added' . $url, 'SSL');

		$url_data = array(
				'filter_name' => 'encode', 
				'filter_date_added',
				'sort',
				'order',
			);
	
		$url = $this->request->getUrl($url_data);
		
		$pagination = new Pagination();
		$pagination->total = $upload_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('tool/upload', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($upload_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($upload_total - $this->config->get('config_limit_admin'))) ? $upload_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $upload_total, ceil($upload_total / $this->config->get('config_limit_admin')));

		$this->data['filter_name'] = $filter_name;
		$this->data['filter_date_added'] = $filter_date_added;

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('tool/upload.tpl', $this->data));
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'tool/upload')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}

	public function download() {
		 $this->load->model('tool/upload');

		if (isset($this->request->get['code'])) {
			$code = $this->request->get['code'];
		} else {
			$code = 0;
		}

		$upload_info = $this->model_tool_upload->getUploadByCode($code);

		if ($upload_info) {
			$file = DIR_UPLOAD . $upload_info['filename'];
			$mask = basename($upload_info['name']);

			if (!headers_sent()) {
				if (is_file($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					readfile($file, 'rb');
					exit;
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		} else {
			$this->data = $this->load->language('error/not_found');

			$this->document->setTitle($this->data['heading_title']);

				$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL')	// Link URL
						));
			
			$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('error/not_found.tpl', $this->data));
		}
	}

	public function upload() {
		$this->data = $this->load->language('sale/order');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'tool/upload')) {
			$json['error'] = $this->data['error_permission'];
		}

		if (!$json) {
			if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
				// Sanitize the filename
				$filename = html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8');

				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->data['error_filename'];
				}

				// Allowed file extension types
				$allowed = array();

				$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

				$filetypes = explode("\n", $extension_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
					$json['error'] = $this->data['error_filetype'];
				}

				// Allowed file mime types
				$allowed = array();

				$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

				$filetypes = explode("\n", $mime_allowed);

				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}

				if (!in_array($this->request->files['file']['type'], $allowed)) {
					$json['error'] = $this->data['error_filetype'];
				}

				// Check to see if any PHP files are trying to be uploaded
				$content = file_get_contents($this->request->files['file']['tmp_name']);

				if (preg_match('/\<\?php/i', $content)) {
					$json['error'] =$this->data['error_filetype'];
				}

				// Return any upload error
				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->data['error_upload_' . $this->request->files['file']['error']];
				}
			} else {
				$json['error'] = $this->data['error_upload'];
			}
		}

		if (!$json) {
			$file = $filename . '.' . md5(mt_rand());

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			// Hide the uploaded file name so people can not link to it directly.
			$this->load->model('tool/upload');

			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);

			$json['success'] =$this->data['text_upload'];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}