<?php
class ControllerExtensionfbecommevnt extends Controller {
	private $modpath = 'extension/fbecommevnt'; 
 	private $modname = 'fbecommevnt';
  	private $modssl = 'SSL';
   	private $langid = 0;
	private $storeid = 0;
	private $custgrpid = 0;
	
	public function __construct($registry) {
		parent::__construct($registry);
 		$this->langid = (int)$this->config->get('config_language_id');
		$this->storeid = (int)$this->config->get('config_store_id');
		$this->custgrpid = (int)$this->config->get('config_customer_group_id');
		
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') { 
  			$this->modssl = true;
 		} 
 		if(substr(VERSION,0,3)>='3.0') { 
			$this->modname = 'module_fbecommevnt';
		} 
  	}	
	public function getcache() {
		$this->load->model($this->modpath);
		if($this->model_extension_fbecommevnt->getmodstatus()) {
 			$json['langdata'] = $this->model_extension_fbecommevnt->getlang();
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
	public function trackevent() {
		$this->load->model($this->modpath);
		$this->load->model('catalog/product');
 		if(isset($this->request->post['product_id'])) { 
			$product_id = (int)$this->request->post['product_id'];
			$quantity = isset($this->request->post['quantity']) ? max(1, (int)$this->request->post['quantity']) : 1;
			$product_info = $this->model_catalog_product->getProduct($product_id);
 			
			if ($product_info) { 
 				$price = $product_info['special'] ? $product_info['special'] : $product_info['price'];
 				$price = $this->tax->calculate($price , $product_info['tax_class_id'], $this->config->get('config_tax')); 
				$price = (float)$this->model_extension_fbecommevnt->getcurval($price);
 			
				$product_data = array(
					"content_ids" => array((string)$product_info['product_id']),
					"content_type" => 'product',
					"content_name" => htmlspecialchars_decode($product_info['name']),
					"content_category" => $this->model_extension_fbecommevnt->getProdCatName($product_info['product_id']),
					"contents" => array(array(
						"id" => (string)$product_info['product_id'],
						"quantity" => $quantity,
						"item_price" => $price
					)),
					"value" => $price * $quantity,
					"currency" => $this->session->data['currency']
				); 

				$catalog_id = $this->model_extension_fbecommevnt->getFBCATALOGID();
				if ($catalog_id) {
					$product_data['product_catalog_id'] = $catalog_id;
				}
				
				$event_data['items'] = $product_data;
 				
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($event_data));
			} 
		} 
	}
	public function cartevent() {
		$this->load->model($this->modpath);
		$items = $this->model_extension_fbecommevnt->getCartEventData();
		$json = $items ? array('items' => $items) : array();

		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate');
		$this->response->setOutput(json_encode($json));
	}
	public function purchaseevent() {
		$this->load->model($this->modpath);
		$json = array();
		$order_id = isset($this->session->data['order_id']) ? (int)$this->session->data['order_id'] : 0;

		if ($order_id) {
			$items = $this->model_extension_fbecommevnt->getPurchaseEventData($order_id);
			if ($items) {
				$json = array(
					'order_id' => (string)$order_id,
					'event_id' => 'purchase_' . $order_id,
					'items' => $items
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Cache-Control: no-store, no-cache, must-revalidate');
		$this->response->setOutput(json_encode($json));
	}
}
