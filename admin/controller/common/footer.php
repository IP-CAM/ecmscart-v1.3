<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->data = $this->load->language('common/footer');

		if ($this->user->isLogged() && isset($this->request->get['token']) && ($this->request->get['token'] == $this->session->data['token'])) {
			$this->data['text_version'] = sprintf($this->data['text_version'], VERSION);
		} else {
			$this->data['text_version'] = '';
		}

		return $this->load->view('common/footer.tpl', $this->data);
	}
}