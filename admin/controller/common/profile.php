<?php
class ControllerCommonProfile extends Controller {
	public function index() {
		$this->data = $this->load->language('common/menu');

		$this->load->model('user/user');

		$this->load->model('tool/image');

		$user_info = $this->model_user_user->getUser($this->user->getId());

		if ($user_info) {
			$this->data['firstname'] = $user_info['firstname'];
			$this->data['lastname'] = $user_info['lastname'];
			$this->data['username'] = $user_info['username'];

			$this->data['user_group'] = $user_info['user_group'] ;

			$this->data['image'] = $this->model_tool_image->resize($user_info['image'], 45, 45);
			
		} else {
			$this->data['username'] = '';
			$this->data['image'] = '';
		}

		return $this->load->view('common/profile.tpl', $this->data);
	}
}