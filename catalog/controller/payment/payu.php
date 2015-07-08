<?php
class ControllerPaymentPayu extends Controller {

	public function index() {	
    	$this->data['button_confirm'] = $this->data['button_confirm'];
		$this->load->model('checkout/order');
		$this->data = $this->language->load('payment/payu');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		$this->data['merchant'] = $this->config->get('payu_merchant');
		
		 /////////////////////////////////////Start Payu Vital  Information /////////////////////////////////
		
		if($this->config->get('payu_test')=='demo')
			$this->data['action'] = 'https://test.payu.in/_payment.php';
		else
		    $this->data['action'] = 'https://secure.payu.in/_payment.php';
			
		$txnid        = 	$this->session->data['order_id'];

		             
		$this->data['key'] = $this->config->get('payu_merchant');
		$this->data['salt'] = $this->config->get('payu_salt');
		$this->data['txnid'] = $txnid;
		$this->data['amount'] = (int)$order_info['total'];
		$this->data['productinfo'] = 'opencart products information';
		$this->data['firstname'] = $order_info['payment_firstname'];
		$this->data['Lastname'] = $order_info['payment_lastname'];
		$this->data['Zipcode'] = $order_info['payment_postcode'];
		$this->data['email'] = $order_info['email'];
		$this->data['phone'] = $order_info['telephone'];
		$this->data['address1'] = $order_info['payment_address_1'];
        $this->data['address2'] = $order_info['payment_address_2'];
        $this->data['state'] = $order_info['payment_zone'];
        $this->data['city']=$order_info['payment_city'];
        $this->data['country']=$order_info['payment_country'];
		$this->data['Pg'] = 'CC';
		$this->data['surl'] = $this->url->link('payment/payu/callback');//HTTP_SERVER.'/index.php?route=payment/payu/callback';
		$this->data['Furl'] = $this->url->link('payment/payu/callback');//HTTP_SERVER.'/index.php?route=payment/payu/callback';
	  //$this->data['surl'] = $this->url->link('checkout/success');//HTTP_SERVER.'/index.php?route=payment/payu/callback';
      //$this->data['furl'] = $this->url->link('checkout/cart');//HTTP_SERVER.'/index.php?route=payment/payu/callback';
		$this->data['curl'] = $this->url->link('payment/payu/callback');
		$key          =  $this->config->get('payu_merchant');
		$amount       = (int)$order_info['total'];
		$productInfo  = $this->data['productinfo'];
	    $firstname    = $order_info['payment_firstname'];
		$email        = $order_info['email'];
		$salt         = $this->config->get('payu_salt');
		$Hash=hash('sha512', $key.'|'.$txnid.'|'.$amount.'|'.$productInfo.'|'.$firstname.'|'.$email.'|||||||||||'.$salt); 
		$this->data['user_credentials'] = $this->data['key'].':'.$this->data['email'];
		$this->data['Hash'] = $Hash;
		$service_provider = 'payu_paisa';
		$this->data['service_provider'] = $service_provider;
					/////////////////////////////////////End Payu Vital  Information /////////////////////////////////
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payu.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/payu.tpl', $this->data);
		} else {
			return $this->load->view('default/template/payment/payu.tpl', $this->data);
		}		
		
		
		
	}
	
	public function callback() {
		if (isset($this->request->post['key']) && ($this->request->post['key'] == $this->config->get('payu_merchant'))) {
			$this->data = $this->language->load('payment/payu');
			
			$this->data['title'] = sprintf($this->data['heading_title'], $this->config->get('config_name'));

			if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$this->data['base'] = HTTP_SERVER;
			} else {
				$this->data['base'] = HTTPS_SERVER;
			}
		
			$this->data['heading_title'] = sprintf($this->data['heading_title'], $this->config->get('config_name'));
			
			$this->data['text_success_wait'] = sprintf($this->data['text_success_wait'], $this->url->link('checkout/success'));
			
			$this->data['text_cancelled_wait'] = sprintf($this->data['text_cancelled_wait'], $this->url->link('checkout/cart'));
			
			$this->data['text_failure_wait'] = sprintf($this->data['text_failure_wait'], $this->url->link('checkout/cart'));
			
			 $this->load->model('checkout/order');
			 $orderid = $this->request->post['txnid'];
			 $order_info = $this->model_checkout_order->getOrder($orderid);
			 
			 
				$key          		=  	$this->request->post['key'];
				$amount      		= 	$this->request->post['amount'];
				$productInfo  		= 	$this->request->post['productinfo'];
				$firstname    		= 	$this->request->post['firstname'];
				$email        		=	$this->request->post['email'];
				$salt        		= 	$this->config->get('payu_salt');
				$txnid		 		=   $this->request->post['txnid'];
				$keyString 	  		=  	$key.'|'.$txnid.'|'.$amount.'|'.$productInfo.'|'.$firstname.'|'.$email.'||||||||||';
				$keyArray 	  		= 	explode("|",$keyString);
				$reverseKeyArray 	= 	array_reverse($keyArray);
				$reverseKeyString	=	implode("|",$reverseKeyArray);
			 
			 
			 if (isset($this->request->post['status']) && $this->request->post['status'] == 'success') {
			 	$saltString     = $salt.'|'.$this->request->post['status'].'|'.$reverseKeyString;
				$sentHashString = strtolower(hash('sha512', $saltString));
			 	$responseHashString=$this->request->post['hash'];
				
				$order_id = $this->request->post['txnid'];
				$message = '';
				$message .= 'orderId: ' . $this->request->post['txnid'] . "\n";
				$message .= 'Transaction Id: ' . $this->request->post['mihpayid'] . "\n";
				foreach($this->request->post as $k => $val){
					$message .= $k.': ' . $val . "\n";
				}
					if($sentHashString==$this->request->post['hash']){
							$this->model_checkout_order->addOrderHistory($this->request->post['txnid'], $this->config->get('payu_order_status_id'), $message, false);
							$this->data['continue'] = $this->url->link('checkout/success');
							$this->data['column_left'] = $this->load->controller('common/column_left');
				            $this->data['column_right'] = $this->load->controller('common/column_right');
				            $this->data['content_top'] = $this->load->controller('common/content_top');
				            $this->data['content_bottom'] = $this->load->controller('common/content_bottom');
				            $this->data['footer'] = $this->load->controller('common/footer');
				            $this->data['header'] = $this->load->controller('common/header');
							if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payu_success.tpl')) {
								$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/paypoint_success.tpl', $this->data));
							} else {
								$this->response->setOutput($this->load->view('default/template/payment/paypoint_success.tpl', $this->data));
							}	
							
							
								
							}
			 
			 
			 }else {
    			$this->data['continue'] = $this->url->link('checkout/cart');
				$this->data['column_left'] = $this->load->controller('common/column_left');
				$this->data['column_right'] = $this->load->controller('common/column_right');
				$this->data['content_top'] = $this->load->controller('common/content_top');
				$this->data['content_bottom'] = $this->load->controller('common/content_bottom');
				$this->data['footer'] = $this->load->controller('common/footer');
				$this->data['header'] = $this->load->controller('common/header');

		        if(isset($this->request->post['status']) && $this->request->post['unmappedstatus'] == 'userCancelled')
				{
			
				 if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payu_cancelled.tpl')) {
					$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/payu_cancelled.tpl', $this->data));
				} else {
				    $this->response->setOutput($this->load->view('default/template/payment/payu_cancelled.tpl', $this->data));
				}
				}
				else {
				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/payu_failure.tpl')) {
					$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/payment/payu_failure.tpl', $this->data));
				} else {
					$this->response->setOutput($this->load->view('default/template/payment/payu_failure.tpl', $this->data));
				}	
				
				}					
			}
		}
	}
}
