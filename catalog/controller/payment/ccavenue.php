<?php
require_once('Crypto.php');
class Controllerpaymentccavenue extends Controller {
	protected function index() {
		$this->data = $this->load->language('payment/ccavenue');
		
		$this->data['action'] = $this->config->get('ccavenue_action');
		$this->data['access_code'] = $this->config->get('ccavenue_access_code');
		
  		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info) {
			
			$merchant_id=$this->config->get('ccavenue_Merchant_Id');  
			$order_id=$this->session->data['order_id'];
			if (strtoupper($order_info['currency_code'])=='INR'){
				$amount = intval($order_info['total']) ;   
			} else {
				$amount = intval($this->currency->convert($order_info['total'], $this->currency->getCode(), 'INR')) ;
			}
			
			$currency="INR";
			        
			/*
			$amount=$order_info['total'];            
			$currency=$order_info['currency_code'];
			*/
			$redirect_url=urlencode($this->url->link('payment/ccavenue/callback', '', 'SSL'));         
			$cancel_url=urlencode($this->url->link('checkout/checkout', '', 'SSL'));
			$language='EN';
			$billing_name=html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8').' '.html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$billing_address=html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
			$billing_city=html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$billing_state=html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
			$billing_zip=html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$billing_country= $order_info['payment_country'];
			$billing_tel=$order_info['telephone'];
			$billing_email=$order_info['email'];
			$delivery_name=html_entity_decode($order_info['shipping_firstname'], ENT_QUOTES, 'UTF-8').''.html_entity_decode($order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');
			$delivery_address=html_entity_decode($order_info['shipping_address_1'], ENT_QUOTES, 'UTF-8');
			$delivery_city=html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
			$delivery_state=html_entity_decode($order_info['shipping_zone'], ENT_QUOTES, 'UTF-8');
			$delivery_zip=html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');
			$delivery_country=$order_info['shipping_country'];
			$delivery_tel=$order_info['email'];
			$merchant_param1='';
			$merchant_param2='';
			$merchant_param3='';
			$merchant_param4='';
			$merchant_param5='';
			$promo_code='';
			$customer_identifier='';
			$working_key=$this->config->get('ccavenue_workingkey');
			$access_code=$this->config->get('ccavenue_access_code');
			
			$merchant_data=	'merchant_id='.$merchant_id.'&order_id='.$order_id.'&amount='.$amount.'&currency='.$currency.'&redirect_url='.$redirect_url.
					'&cancel_url='.$cancel_url.'&language='.$language.'&billing_name='.$billing_name.'&billing_address='.$billing_address.
					'&billing_city='.$billing_city.'&billing_state='.$billing_state.'&billing_zip='.$billing_zip.'&billing_country='.$billing_country.
					'&billing_tel='.$billing_tel.'&billing_email='.$billing_email.'&delivery_name='.$delivery_name.'&delivery_address='.$delivery_address.
					'&delivery_city='.$delivery_city.'&delivery_state='.$delivery_state.'&delivery_zip='.$delivery_zip.'&delivery_country='.$delivery_country.
					'&delivery_tel='.$delivery_tel.'&merchant_param1='.$merchant_param1.'&merchant_param2='.$merchant_param2.
					'&merchant_param3='.$merchant_param3.'&merchant_param4='.$merchant_param4.'&merchant_param5='.$merchant_param5.'&promo_code='.$promo_code.
					'&customer_identifier='.$customer_identifier;
			
			
			$this->data['encrypted_data']=encrypt($merchant_data,$working_key); 	
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/ccavenue.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/payment/ccavenue.tpl', $this->data);
			} else {
				return $this->load->view('default/template/payment/ccavenue.tpl', $this->data);
			}
	
			
		}
	}
	
	public function callback() {
		
		$this->load->model('checkout/order');
		
		$workingKey=$this->config->get('ccavenue_workingkey');
		if(isset($_POST["encResp"]))
		{
		$encResponse=$_POST["encResp"];
		
		$rcvdString=decrypt($encResponse,$workingKey);
		$order_status="";
		$decryptValues=explode('&', $rcvdString);
		$dataSize=sizeof($decryptValues);
		
		for($i = 0; $i < $dataSize; $i++) 
		{
			$information=explode('=',$decryptValues[$i]);
			if($i==3)	$order_status=$information[1];
		}
		
		$orderid=explode('=',$decryptValues[0]);
		
		$order_info = $this->model_checkout_order->getOrder($orderid['1']);
		if ($order_info) {
					if($order_status==="Success")
					{
					$order_status_id = $this->config->get('ccavenue_completed_status_id');
					}
					else if($order_status==="Failure")
					{
					$order_status_id = $this->config->get('ccavenue_failed_status_id');
					}
					else if($order_status==="Aborted")
					{
					$order_status_id = $this->config->get('ccavenue_pending_status_id');
					}
					
					$this->model_checkout_order->confirm($order_info['order_id'], $order_status_id);
				
				$this->response->redirect($this->url->link('checkout/success', '', 'SSL'));
								
			} }else
			{
				$this->response->redirect($this->url->link('checkout/checkout', '', 'SSL'));
				$this->log->write('CCAvenue :: Problem processing order. Order ID: ' . $orderid['1']);
			}
		}	
}
?>