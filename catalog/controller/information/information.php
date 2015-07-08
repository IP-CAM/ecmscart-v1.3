<?php
class ControllerInformationInformation extends Controller {
	public function index() {
		$this->data = $this->load->language('information/information');

		$this->load->model('catalog/information');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							 $this->url->link('common/home'), 		// Link URL
							));
							
		$information_id = (int)$this->request->get('information_id',0);
		
		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$this->document->setTitle($information_info['meta_title']);
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$information_info['title'],	// Text to display link
							$this->url->link('information/information', 'information_id=' .  $information_id)
						));

			$this->data['heading_title'] = $information_info['title'];

			$this->data['button_continue'] = $this->data['button_continue'];

			$this->data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$this->data['continue'] = $this->url->link('common/home');

			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['column_right'] = $this->load->controller('common/column_right');
			$this->data['content_top'] = $this->load->controller('common/content_top');
			$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/information.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/information/information.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/information/information.tpl', $this->data));
			}
		} else {
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_error'],	// Text to display link
							$this->url->link('information/information', 'information_id=' . $information_id)
						));
			
			$this->document->setTitle($this->data['text_error']);

			$this->data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['column_right'] = $this->load->controller('common/column_right');
			$this->data['content_top'] = $this->load->controller('common/content_top');
			$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/error/not_found.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/error/not_found.tpl', $this->data));
			}
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		$information_id = (int)$this->request->get('information_id',0);
		
		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->setOutput($output);
	}
}