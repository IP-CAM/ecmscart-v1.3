<?php
class ControllerModuleStore extends Controller {
	public function index() {
		$status = true;

		if ($this->config->get('store_admin')) {
			$this->load->library('user');

			$this->user = new User($this->registry);

			$status = $this->user->isLogged();
		}

		if ($status) {
			$this->data = $this->load->language('module/store');

			$this->data['store_id'] = $this->config->get('config_store_id');

			$this->data['stores'] = array();

			$this->data['stores'][] = array(
				'store_id' => 0,
				'name'     => $this->data['text_default'],
				'url'      => HTTP_SERVER . 'index.php?route=common/home&session_id=' . $this->session->getId()
			);

			$this->load->model('setting/store');

			$results = $this->model_setting_store->getStores();

			foreach ($results as $result) {
				$this->data['stores'][] = array(
					'store_id' => $result['store_id'],
					'name'     => $result['name'],
					'url'      => $result['url'] . 'index.php?route=common/home&session_id=' . $this->session->getId()
				);
			}

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/store.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/module/store.tpl', $this->data);
			} else {
				return $this->load->view('default/template/module/store.tpl', $this->data);
			}
		}
	}
}