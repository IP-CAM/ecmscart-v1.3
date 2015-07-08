<?php
class ControllerAccountReturn extends Controller {
	private $error = array();

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return', '', 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->data = $this->load->language('account/return');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL')	// Link URL
						));

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['heading_title'],	// Text to display link
							$this->url->link('account/return', $url, 'SSL') 		// Link URL
							
						));
		
		$this->load->model('account/return');

		$page = $this->request->get('page',1);
		
		$this->data['returns'] = array();

		$return_total = $this->model_account_return->getTotalReturns();

		$results = $this->model_account_return->getReturns(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['returns'][] = array(
				'return_id'  => $result['return_id'],
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
				'href'       => $this->url->link('account/return/info', 'return_id=' . $result['return_id'] . $url, 'SSL')
			);
		}

		$pagination = new Pagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_product_limit');
		$pagination->url = $this->url->link('account/return', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($return_total) ? (($page - 1) * $this->config->get('config_product_limit')) + 1 : 0, ((($page - 1) * $this->config->get('config_product_limit')) > ($return_total - $this->config->get('config_product_limit'))) ? $return_total : ((($page - 1) * $this->config->get('config_product_limit')) + $this->config->get('config_product_limit')), $return_total, ceil($return_total / $this->config->get('config_product_limit')));

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/return_list.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/return_list.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/return_list.tpl', $this->data));
		}
	}

	public function info() {
		$this->data = $this->load->language('account/return');

		$return_id = $this->request->get('return_id',0);
		
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');

			$this->response->redirect($this->url->link('account/login', '', 'SSL'));
		}

		$this->load->model('account/return');

		$return_info = $this->model_account_return->getReturn($return_id);

		if ($return_info) {
			$this->document->setTitle($this->data['text_return']);
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home', '', 'SSL'),		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL')
						));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['heading_title'],	// Text to display link
							$this->url->link('account/return', $url, 'SSL'), 		// Link URL
							$this->data['text_return'],	// Text to display link
							$this->url->link('account/return/info', 'return_id=' . $this->request->get['return_id'] . $url, 'SSL')
						));
			
			$this->data['return_id'] = $return_info['return_id'];
			$this->data['order_id'] = $return_info['order_id'];
			$this->data['date_ordered'] = date($this->data['date_format_short'], strtotime($return_info['date_ordered']));
			$this->data['date_added'] = date($this->data['date_format_short'], strtotime($return_info['date_added']));
			$this->data['firstname'] = $return_info['firstname'];
			$this->data['lastname'] = $return_info['lastname'];
			$this->data['email'] = $return_info['email'];
			$this->data['telephone'] = $return_info['telephone'];
			$this->data['product'] = $return_info['product'];
			$this->data['model'] = $return_info['model'];
			$this->data['quantity'] = $return_info['quantity'];
			$this->data['reason'] = $return_info['reason'];
			$this->data['opened'] = $return_info['opened'] ? $this->data['text_yes'] : $this->data['text_no'];
			$this->data['comment'] = nl2br($return_info['comment']);
			$this->data['action'] = $return_info['action'];

			$this->data['histories'] = array();

			$results = $this->model_account_return->getReturnHistories($this->request->get['return_id']);

			foreach ($results as $result) {
				$this->data['histories'][] = array(
					'date_added' => date($this->data['date_format_short'], strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => nl2br($result['comment'])
				);
			}

			$this->data['continue'] = $this->url->link('account/return', $url, 'SSL');

			$this->data['column_left'] = $this->load->controller('common/column_left');
			$this->data['column_right'] = $this->load->controller('common/column_right');
			$this->data['content_top'] = $this->load->controller('common/content_top');
			$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
			$this->data['footer'] = $this->load->controller('common/footer');
			$this->data['header'] = $this->load->controller('common/header');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/return_info.tpl')) {
				$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/return_info.tpl', $this->data));
			} else {
				$this->response->setOutput($this->load->view('default/template/account/return_info.tpl', $this->data));
			}
		} else {
			$this->document->setTitle($this->data['text_return']);
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['text_account'],	// Text to display link
							$this->url->link('account/account', '', 'SSL'),
							$this->data['heading_title'],
							$this->url->link('account/return', '', 'SSL')
						));

			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_return'],	// Text to display link
							 $this->url->link('account/return/info', 'return_id=' . $return_id . $url, 'SSL')
							
						));
			
			$this->data['continue'] = $this->url->link('account/return', '', 'SSL');

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

	public function add() {
		$this->data = $this->load->language('account/return');

		$this->load->model('account/return');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			unset($this->session->data['captcha']);

			$return_id = $this->model_account_return->addReturn($this->request->post);

			// Add to activity log
			$this->load->model('account/activity');

			if ($this->customer->isLogged()) {
				$activity_data = array(
					'customer_id' => $this->customer->getId(),
					'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
					'return_id'   => $return_id
				);

				$this->model_account_activity->addActivity('return_account', $activity_data);
			} else {
				$activity_data = array(
					'name'      => $this->request->post['firstname'] . ' ' . $this->request->post['lastname'],
					'return_id' => $return_id
				);

				$this->model_account_activity->addActivity('return_guest', $activity_data);
			}

			$this->response->redirect($this->url->link('account/return/success', '', 'SSL'));
		}

		$this->document->setTitle($this->data['heading_title']);
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'),
							$this->data['text_account'], // Text to display link
							$this->url->link('account/account', '', 'SSL'),
							 $this->data['heading_title'],
							 $this->url->link('account/return/add', '', 'SSL')
						));

		$this->data['error_warning'] =  (isset($this->error['warning'])?$this->error['warning']:'');
		
		$this->data['error_order_id'] =  (isset($this->error['order_id'])?$this->error['order_id']:'');

		$this->data['error_firstname'] =  (isset($this->error['firstname'])?$this->error['firstname']:'');

		$this->data['error_lastname'] =  (isset($this->error['lastname'])?$this->error['lastname']:'');

		$this->data['error_email'] =  (isset($this->error['email'])?$this->error['email']:'');

		$this->data['error_telephone'] =  (isset($this->error['telephone'])?$this->error['telephone']:'');

		$this->data['error_product'] =  (isset($this->error['product'])?$this->error['product']:'');

		$this->data['error_model'] =  (isset($this->error['model'])?$this->error['model']:'');

		$this->data['error_reason'] =  (isset($this->error['reason'])?$this->error['reason']:'');

		$this->data['error_captcha'] =  (isset($this->error['captcha'])?$this->error['captcha']:'');
		
		$this->data['action'] = $this->url->link('account/return/add', '', 'SSL');

		$this->load->model('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_account_order->getOrder($this->request->get['order_id']);
		}

		$this->load->model('catalog/product');

		if (isset($this->request->get['product_id'])) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		}

		if (!empty($order_info) && !$this->error) {
			$this->data['order_id'] = $order_info['order_id'];
		} else {
			$this->data['order_id'] = $this->request->post('order_id','');
		}

		if (!empty($order_info) && !$this->error) {
			$this->data['date_ordered'] = date('Y-m-d', strtotime($order_info['date_added']));
		} else {
			$this->data['date_ordered'] = $this->request->post('date_ordered','');
		}

		if (!empty($order_info) && !$this->error) {
			$this->data['firstname'] = $order_info['firstname'];
		} else {
			$this->data['firstname'] = $this->request->post('firstname',$this->customer->getFirstName());
		}

		if (!empty($order_info) && !$this->error) {
			$this->data['lastname'] = $order_info['lastname'];
		} else {
			$this->data['lastname'] = $this->request->post('lastname',$this->customer->getLastName());
		}

		if (!empty($order_info) && !$this->error) {
			$this->data['email'] = $order_info['email'];
		} else {
			$this->data['email'] = $this->request->post('email',$this->customer->getEmail());
		}

		if (!empty($order_info) && !$this->error) {
			$this->data['telephone'] = $order_info['telephone'];
		} else {
			$this->data['telephone'] = $this->request->post('telephone',$this->customer->getTelephone());
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['product'] = $product_info['name'];
		} else {
			$this->data['product'] =  $this->request->post('product','');
		}

		if (!empty($product_info) && !$this->error) {
			$this->data['model'] = $product_info['model'];
		} else {
			$this->data['model'] = $this->request->post('model','');
		}

		$this->data['quantity'] = $this->request->post('quantity',1);
		
		$this->data['opened'] = $this->request->post('opened',false);
		
		$this->data['return_reason_id'] = $this->request->post('return_reason_id','');

		$this->load->model('localisation/return_reason');

		$this->data['return_reasons'] = $this->model_localisation_return_reason->getReturnReasons();
		
		$this->data['comment'] = $this->request->post('comment','');

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->data['text_agree'], $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_return_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}

		$this->data['agree'] = $this->request->post('agree',false);
		
		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/return_form.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/account/return_form.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/account/return_form.tpl', $this->data));
		}
	}

	public function success() {
		$this->data = $this->load->language('account/return');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							$this->data['heading_title'],	// Text to display link
							$this->url->link('account/return', '', 'SSL')	// Link URL
						));

		$this->data['continue'] = $this->url->link('common/home');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/success.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/common/success.tpl', $this->data));
		}
	}

	protected function validate() {
		if (!$this->request->post['order_id']) {
			$this->error['order_id'] = $this->data['error_order_id'];
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->data['error_firstname'];
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->data['error_lastname'];
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->data['error_email'];
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->data['error_telephone'];
		}

		if ((utf8_strlen($this->request->post['product']) < 1) || (utf8_strlen($this->request->post['product']) > 255)) {
			$this->error['product'] = $this->data['error_product'];
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->data['error_model'];
		}

		if (empty($this->request->post['return_reason_id'])) {
			$this->error['reason'] = $this->data['error_reason'];
		}

		if (empty($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
			$this->error['captcha'] = $this->data['error_captcha'];
		}

		if ($this->config->get('config_return_id')) {
			$this->load->model('catalog/information');

			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_return_id'));

			if ($information_info && !isset($this->request->post['agree'])) {
				$this->error['warning'] = sprintf($this->data['error_agree'], $information_info['title']);
			}
		}

		return !$this->error;
	}
}
