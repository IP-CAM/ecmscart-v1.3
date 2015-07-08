<?php
class ControllerBlogFamily extends Controller {
	public function index() {
		$this->data = $this->load->language('blog/family');

		$this->load->model('blog/family');

		$this->load->model('tool/image');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							$this->data['text_family'],	// Text to display link
							$this->url->link('blog/family')	// Link URL
						));

		$this->data['families'] = array();

		$results = $this->model_blog_family->getFamilies(0);
		
		foreach ($results as $result) {
			if ($result['image']) {
				$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_thumb_family_width'), $this->config->get('config_thumb_family_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_thumb_family_width'), $this->config->get('config_thumb_family_height'));
			}
				
			$this->data['families'][] = array(
				'name' 	=> $result['name'],
				'thumb' => $image,
				'href' 	=> $this->url->link('blog/family/info', 'family_id=' . $result['family_id'])
			);
		}

		$this->data['continue'] = $this->url->link('common/home');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/family_list.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/blog/family_list.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/blog/family_list.tpl', $this->data));
		}
	}

	public function info() {
		
		$this->data = $this->load->language('blog/family');
		
		$this->data['text_no_blog'] = $this->data['text_no_blog'];

		$this->load->model('blog/family');
		
		$this->load->model('tool/image');
		
		if (isset($this->request->get['family_id'])) {
			$family_id = (int)$this->request->get['family_id'];
		} elseif (isset($this->request->get['trail'])) {			
			$parts = explode('_', (string)$this->request->get['trail']); 
			$family_id = (int)array_pop($parts);						
		} else {
			$family_id = 0;
		}
		
		$sort = $this->request->get('sort','b.sort_order');
		
		$order = $this->request->get('order','ASC');
		
		$page = $this->request->get('page', 1);
		
		$limit = $this->request->get('limit', $this->config->get('config_product_limit'));
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							$this->data['text_family'],	// Text to display link
							$this->url->link('blog/family'),
						));
		
		$family_info = $this->model_blog_family->getFamily($family_id);

		if ($family_info) {
			
			$this->document->setTitle($family_info['meta_title']);
			$this->document->setDescription($family_info['meta_description']);
			$this->document->setKeywords($family_info['meta_keyword']);			
			$this->document->addLink($this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id']), 'canonical');
			
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			
			$this->data['breadcrumbs'][] = array(
						'text' =>	$family_info['name'],	// Text to display link
						'href' =>	$this->url->link('blog/family/info', 'family_id=' .  $family_id),							
						);
						
						
			
			$this->data['heading_title'] = $family_info['name'];
			
			if ($family_info['image']) {
				$this->data['thumb'] = $this->model_tool_image->resize($family_info['image'], $this->config->get('config_image_family_width'), $this->config->get('config_image_family_height'));
			} else {
				$this->data['thumb'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_family_width'), $this->config->get('config_image_family_height'));
			}

			$this->data['description'] = html_entity_decode($family_info['description'], ENT_QUOTES, 'UTF-8');
						
			$this->data['childrens'] = array();

			$childrens = $this->model_blog_family->getFamilies($family_id);
		

		foreach ($childrens as $children) {
			if ($children['image']) {
				$image = $this->model_tool_image->resize($children['image'], $this->config->get('config_thumb_family_width'), $this->config->get('config_thumb_family_height'));
			} else {
				$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_thumb_family_width'), $this->config->get('config_thumb_family_height'));
			}
				
			$this->data['childrens'][] = array(
				'name' 	=> $children['name'],
				'thumb' => $image,
				'href' 	=> $this->url->link('blog/family/info', 'family_id=' . $children['family_id'])
			);
		}
			
			
			$this->load->model('blog/blog');
			
			$this->data['blogs'] = array();
			
			$filter_data = array(
				'filter_family_id' => $family_id,
				'sort'      => $sort,
				'order'     => $order,
				'start'     => ($page - 1) * $limit,
				'limit'     => $limit
			);
			
			
			$blog_total = $this->model_blog_blog->getTotalBlogs($filter_data);

			$results = $this->model_blog_blog->getBlogs($filter_data);	
			
				foreach ($results as $result) {
					if ($result['image']) {
						$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_thumb_blog_width'), $this->config->get('config_thumb_blog_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_thumb_blog_width'), $this->config->get('config_thumb_blog_height'));
					}
					$this->data['blogs'][] = array(
						'title' 		=> $result['title'],
						'description' 	=> utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '...',
						'name'   		=> $result['name'],
						'date_added'  	=> $result['date_added'],
						'image'      	=> $image,
						'href' 			=> $this->url->link('blog/blog', 'blog_id=' . $result['blog_id'])
					);
				
				}
			
			
			$url = '';

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$this->data['sorts'] = array();

			$this->data['sorts'][] = array(
				'text'  => $this->data['text_default'],
				'value' => 'b.sort_order-ASC',
				'href'  => $this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id'] . '&sort=b.sort_order&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->data['text_name_asc'],
				'value' => 'bd.title-ASC',
				'href'  => $this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id'] . '&sort=bd.name&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->data['text_name_desc'],
				'value' => 'bd.title-DESC',
				'href'  => $this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id'] . '&sort=bd.name&order=DESC' . $url)
			);
			
				$this->data['sorts'][] = array(
				'text'  => $this->data['text_date_asc'],
				'value' => 'bd.title-ASC',
				'href'  => $this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id'] . '&sort=bd.date&order=ASC' . $url)
			);

			$this->data['sorts'][] = array(
				'text'  => $this->data['text_date_desc'],
				'value' => 'b.price-DESC',
				'href'  => $this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id'] . '&sort=bd.date&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$this->data['limits'] = array();

			$limits = array_unique(array($this->config->get('config_product_limit'), 25, 50, 75, 100));

			sort($limits);

			foreach($limits as $value) {
				$this->data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$pagination = new Pagination();
			$pagination->total = $blog_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('blog/family/info', 'family_id=' . $this->request->get['family_id'] .  $url . '&page={page}');

			$this->data['pagination'] = $pagination->render();

			$this->data['results'] = sprintf($this->data['text_pagination'], ($blog_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($blog_total - $limit)) ? $blog_total : ((($page - 1) * $limit) + $limit), $blog_total, ceil($blog_total / $limit));

			$this->data['sort'] = $sort;
			$this->data['order'] = $order;
			$this->data['limit'] = $limit;
			
			$this->data['continue'] = $this->url->link('common/home');

			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['column_right'] = $this->load->controller('common/column_right');
			$this->data['content_top'] = $this->load->controller('common/content_top');
			$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->data['header'] = $this->load->controller('common/header');
			
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/blog/family_info.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/blog/family_info.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/blog/family_info.tpl', $this->data));
			}
		} else {
			
			$url = '';

			if (isset($this->request->get['family_id'])) {
				$url .= '&family_id=' . $this->request->get['family_id'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_error'],	// Text to display link
							 $this->url->link('blog/family/info', 'family_id=' .  $family_id)
							
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
	
	
}