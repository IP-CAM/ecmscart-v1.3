<?php
class ControllerFeedGoogleSitemap extends Controller {
	private $error = array();

	public function index() {
		$this->data = $this->load->language('feed/google_sitemap');

		$this->document->setTitle($this->data['heading_title']);

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('google_sitemap', $this->request->post);

			$this->session->data['success'] = $this->data['text_success'];

			$this->response->redirect($this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'));
		}

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), // Link URL
							$this->data['text_feed'],
							$this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL'),		
							$this->data['heading_title'],	// Text to display link
							$this->url->link('feed/google_sitemap', 'token=' . $this->session->data['token'], 'SSL'),	// Link URL
						));

		$this->data['action'] = $this->url->link('feed/google_sitemap', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/feed', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['google_sitemap_status'] = $this->request->post('google_sitemap_status', $this->config->get('google_sitemap_status'), false);

		$this->data['data_feed'] = HTTP_CATALOG . 'index.php?route=feed/google_sitemap';

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('feed/google_sitemap.tpl', $this->data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'feed/google_sitemap')) {
			$this->error['warning'] = $this->data['error_permission'];
		}

		return !$this->error;
	}
}