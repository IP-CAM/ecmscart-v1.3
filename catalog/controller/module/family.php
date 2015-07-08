<?php
class ControllerModuleFamily extends Controller {
	public function index() {
		
		$this->data = $this->load->language('module/family');
		$this->data['heading_title'] = $this->data['heading_title'];

		$this->data['family_id'] = $this->request->get('family_id',0);
		
		$this->load->model('blog/family');

		$this->data['families'] = array();

		$families = $this->model_blog_family->getFamilies(0);
		
		foreach ($families as $family) {
			
			$children_data = array();

			if ($family['family_id'] == $this->data['family_id']) {
				$children = $this->model_blog_family->getFamilies($family['family_id']);

				foreach($children as $child) {
					
					$children_data[] = array(
						'family_id' => $child['family_id'], 
						'name' => $child['name'],
						'href' => $this->url->link('blog/family/info', 'family_id=' . $child['family_id'])
					);
				}
			}

						
			$this->data['families'][] = array(
				'family_id' => $family['family_id'],
				'name'        => $family['name'],
				'children'    => $children_data,
				'href'        => $this->url->link('blog/family/info', 'family_id=' . $family['family_id'])
			);
		}
		

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/family.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/module/family.tpl', $this->data);
		} else {
			return $this->load->view('default/template/module/family.tpl', $this->data);
		}
	}
}