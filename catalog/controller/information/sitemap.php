<?php
class ControllerInformationSitemap extends Controller {
	public function index() {
		$this->data = $this->load->language('information/sitemap');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							
							$this->data['heading_title'],	// Text to display link
							$this->url->link('information/sitemap')	// Link URL
						));

		$this->load->model('catalog/category');
		$this->load->model('catalog/product');

		$this->data['categories'] = array();

		$categories_1 = $this->model_catalog_category->getCategories(0);

		foreach ($categories_1 as $category_1) {
			$level_2_data = array();

			$categories_2 = $this->model_catalog_category->getCategories($category_1['category_id']);

			foreach ($categories_2 as $category_2) {
				$level_3_data = array();

				$categories_3 = $this->model_catalog_category->getCategories($category_2['category_id']);

				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'name' => $category_3['name'],
						'href' => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'] . '_' . $category_3['category_id'])
					);
				}

				$level_2_data[] = array(
					'name'     => $category_2['name'],
					'children' => $level_3_data,
					'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'])
				);
			}

			$this->data['categories'][] = array(
				'name'     => $category_1['name'],
				'children' => $level_2_data,
				'href'     => $this->url->link('product/category', 'path=' . $category_1['category_id'])
			);
		}

		$this->data['special'] = $this->url->link('product/special');
		$this->data['account'] = $this->url->link('account/account', '', 'SSL');
		$this->data['edit'] = $this->url->link('account/edit', '', 'SSL');
		$this->data['password'] = $this->url->link('account/password', '', 'SSL');
		$this->data['address'] = $this->url->link('account/address', '', 'SSL');
		$this->data['history'] = $this->url->link('account/order', '', 'SSL');
		$this->data['download'] = $this->url->link('account/download', '', 'SSL');
		$this->data['cart'] = $this->url->link('checkout/cart');
		$this->data['checkout'] = $this->url->link('checkout/checkout', '', 'SSL');
		$this->data['search'] = $this->url->link('product/search');
		$this->data['contact'] = $this->url->link('information/contact');

		$this->load->model('catalog/information');

		$this->data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			$this->data['informations'][] = array(
				'title' => $result['title'],
				'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
			);
		}
		// Blog Families
		$this->load->model('blog/family');
		
		$this->data['families'] = array();

		$families_1 = $this->model_blog_family->getFamilies(0);
		
		foreach ($families_1 as $faimly_1) {
			$level_2_data = array();

			$faimlies_2 = $this->model_blog_family->getFamilies($faimly_1['family_id']); 
		
			foreach ($faimlies_2 as $faimly_2) {
				$level_3_data = array();

				$faimlies_3 = $this->model_blog_family->getFamilies($faimly_2['family_id']);

				foreach ($faimlies_3 as $faimly_3) {
					$level_3_data[] = array(
						'name' => $faimly_3['name'],
						'href' => $this->url->link('blog/family/info', 'family_id=' . $faimly_3['family_id'])
					);
				}

				$level_2_data[] = array(
					'name'     => $faimly_2['name'],
					'children' => $level_3_data,
					'href'     => $this->url->link('blog/family/info', 'family_id=' . $faimly_2['family_id'])
				);
			}

			$this->data['families'][] = array(
				'name'     => $faimly_1['name'],
				'children' => $level_2_data,
				'href'     => $this->url->link('blog/family/info', 'family_id=' . $faimly_1['family_id'])
			);
		}


		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/information/sitemap.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/information/sitemap.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/information/sitemap.tpl', $this->data));
		}
	}
}