<?php
class ControllerFileManagerFileManager extends Controller {
	public function getUrl(){
		$this->data = $this->load->language('filemanager/filemanager');
		
		$json['heading_title'] = $this->data['heading_title'];
		
		$json['url'] = HTTP_FILE_MANAGER.'dialog.php?type=0&lang='.$this->config->get('config_filemanager_language').'&field_id='.$this->request->get['field_id'];
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	public function getImage(){
		//filemanger library for file type error
		include 'include/mime_type_lib.php';
		$this->data = $this->load->language('filemanager/filemanager');
		
		$this->load->model('tool/image');
		
		$json =  array();
		
		if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}
		$filename = basename(html_entity_decode($this->request->get['image_url'], ENT_QUOTES, 'UTF-8'));
		
		// Validate the filename length
		if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 255)) {
			$json['error'] = $this->data['error_filename'];
		}

		// Allowed file extension types
		$allowed = array(
				'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'svg'
			);

		if (!in_array(utf8_strtolower(utf8_substr(strrchr($filename, '.'), 1)), $allowed)) {
			$json['error'] = $this->data['error_file_type'];
		}
		$filetype = get_file_mime_type($filename);
		// Allowed file mime types
				$allowed = array(
					'image/jpeg',
					'image/pjpeg',
					'image/png',
					'image/x-png',
					'image/gif'
				);

				if (!in_array($filetype, $allowed)) {
					$json['error'] = $this->data['error_file_type'];
				}
		
		if(!$json && $this->request->get['image_url']){
			$json = array(
					'thumb' => $this->model_tool_image->resize(utf8_substr($this->request->get['image_url'], utf8_strlen($server.'image/')), 100, 100),
					'path'  => utf8_substr($this->request->get['image_url'], utf8_strlen($server.'image/')),
					'href'  => $this->request->get['image_url']
				);
			
			}
	
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
}