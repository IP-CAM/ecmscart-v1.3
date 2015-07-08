<?php
class ControllerAffiliateTransaction extends Controller {
	public function index() {
		if (!$this->affiliate->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('affiliate/transaction', '', 'SSL');

			$this->response->redirect($this->url->link('affiliate/login', '', 'SSL'));
		}

		$this->data = $this->load->language('affiliate/transaction');

		$this->document->setTitle($this->data['heading_title']);
		
		$this->data['breadcrumbs'] = $this->config->breadcrums(array(
							$this->data['text_home'],	// Text to display link
							$this->url->link('common/home'), 		// Link URL
							
							$this->data['text_account'],	// Text to display link
							$this->url->link('affiliate/account', '', 'SSL'),	// Link URL
							
							$this->data['text_transaction'],
							$this->url->link('affiliate/transaction', '', 'SSL')
						));

		$this->load->model('affiliate/transaction');

		$this->data['column_amount'] = sprintf($this->data['column_amount'], $this->config->get('config_currency'));

		$page = $this->request->get('page',1);
		
		$this->data['transactions'] = array();

		$filter_data = array(
			'sort'  => 't.date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);

		$transaction_total = $this->model_affiliate_transaction->getTotalTransactions();

		$results = $this->model_affiliate_transaction->getTransactions($filter_data);

		foreach ($results as $result) {
			$this->data['transactions'][] = array(
				'amount'      => $this->currency->format($result['amount'], $this->config->get('config_currency')),
				'description' => $result['description'],
				'date_added'  => date($this->data['date_format_short'], strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('affiliate/transaction', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->data['text_pagination'], ($transaction_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($transaction_total - 10)) ? $transaction_total : ((($page - 1) * 10) + 10), $transaction_total, ceil($transaction_total / 10));

		$this->data['balance'] = $this->currency->format($this->model_affiliate_transaction->getBalance());

		$this->data['continue'] = $this->url->link('affiliate/account', '', 'SSL');

		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->data['column_right'] = $this->load->controller('common/column_right');
		$this->data['content_top'] = $this->load->controller('common/content_top');
		$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['header'] = $this->load->controller('common/header');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/transaction.tpl')) {
			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/affiliate/transaction.tpl', $this->data));
		} else {
			$this->response->setOutput($this->load->view('default/template/affiliate/transaction.tpl', $this->data));
		}
	}
}