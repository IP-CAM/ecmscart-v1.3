<?php
class ControllerReportProductViewed extends Controller {
	// sorting and filter array
	private $url_data = array(
				'page',
			);
	public function index() {
		$this->data = $this->load->language('report/product_viewed');

		$this->document->setTitle($this->data['heading_title']);

		$page = $this->request->get('page', 1);
		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		// Breadcrumb array with common function of Text and URL 
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('report/product_viewed', 'token=' . $this->session->data['token'] . $url, 'SSL')	// Link URL
						));

		$this->load->model('report/product');

		$filter_data = array(
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$this->data['products'] = array();

		$product_viewed_total = $this->model_report_product->getTotalProductsViewed();

		$results = $this->model_report_product->getProductsViewed($filter_data);

		foreach ($results as $result) {
			if ($result['viewed']) {
				$percent = round($result['viewed'] / $product_viewed_total * 100, 2);
			} else {
				$percent = 0;
			}

			$this->data['products'][] = array(
				'name'    => $result['name'],
				'model'   => $result['model'],
				'viewed'  => $result['viewed'],
				'percent' => $percent . '%'
			);
		}

		// Sorting and Filter Function
		$url = $this->request->getUrl($this->url_data);

		$this->data['reset'] = $this->url->link('report/product_viewed/reset', 'token=' . $this->session->data['token'] . $url, 'SSL');
		
		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		}
		
		$this->data['success'] =  (isset($this->session->data['success'])? $this->session->data['success']: '');
		
		if (isset($this->session->data['success']))  // To unset success session variable.
			unset($this->session->data['success']);

		$pagination = new Pagination();
		$pagination->total = $product_viewed_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/product_viewed', 'token=' . $this->session->data['token'] . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();
		$limit = $this->config->get('config_limit_admin');

		$this->data['results'] = sprintf($this->data['text_pagination'], ($pagination->total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($pagination->total - $limit)) ? $pagination->total : ((($page - 1) * $limit) + $limit), $pagination->total, ceil($pagination->total / $limit));

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/product_viewed.tpl', $this->data));
	}

	public function reset() {
		$this->data = $this->load->language('report/product_viewed');

		if (!$this->user->hasPermission('modify', 'report/product_viewed')) {
			$this->session->data['error'] = $this->data['error_permission'];
		} else {
			$this->load->model('report/product');

			$this->model_report_product->reset();

			$this->session->data['success'] = $this->data['text_success'];
		}

		$this->response->redirect($this->url->link('report/product_viewed', 'token=' . $this->session->data['token'], 'SSL'));
	}
}