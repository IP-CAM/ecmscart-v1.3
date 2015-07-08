<?php
class ControllerDashboardProductViewed extends Controller {
	public function index() {
		$this->data = $this->load->language('dashboard/product_viewed');
		
		$this->data['token'] = $this->session->data['token'];		

		$this->load->model('report/product');
		
		$this->data['views'] = $this->model_report_product->getTotalProductsViewed();
		
		$this->load->model('catalog/product');

		$total = $this->model_catalog_product->getTotalProducts();
		
		if (($this->data['views']>0)&&($total)) {
			$this->data['percentage']	 = round($this->data['views'] / $total * 100, 2);
		} else {
			$this->data['percentage']	 = 0;
		}
		
		
		$this->data['product_viewed'] = $this->url->link('report/product_viewed', 'token=' . $this->session->data['token'], 'SSL');

		return $this->load->view('dashboard/product_viewed.tpl', $this->data);
	}
}
