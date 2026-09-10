<?php 
class ModelExtensionfbecommevnt extends Controller { 
   	private $modname = 'fbecommevnt';
   	private $langid = 0;
	private $storeid = 0;
	private $custgrpid = 0;
	
	public function __construct($registry) {
		parent::__construct($registry);
		
		$this->langid = (int)$this->config->get('config_language_id');
		$this->storeid = (int)$this->config->get('config_store_id');
		$this->custgrpid = (int)$this->config->get('config_customer_group_id');
		
		if(substr(VERSION,0,3)>='3.0') { 
			$this->modname = 'module_fbecommevnt';
 		}
 	}
	
	public function index() {
  		$data[$this->modname.'_status'] = $this->config->get($this->modname.'_status');
	}
	public function getmodstatus() {
		return ($this->config->get($this->modname.'_sts'.$this->storeid) && $this->config->get($this->modname.'_status')) ? true : false;
	}
	public function getFBID() {
  		return $this->getmodstatus() ? $this->config->get($this->modname.'_fb_pixel_id'.$this->storeid) : false;
	}
	public function getFBCATALOGID() {
  		return $this->getmodstatus() ? $this->config->get($this->modname.'_fb_product_catalog_id'.$this->storeid) : false;
	}
	public function getlang() {	
		$setting_lang = $this->config->get($this->modname.'_lang');
 		$lang = array('text_atc', 'text_wishlist', 'text_compare', 'text_removecart', 'text_loginevent', 'text_regevent', 'text_chkstp_onename', 'text_chkstp_twoname', 'text_chkstp_threename', 'text_chkstp_fourname', 'text_chkstp_fivename','text_chkstp_sixname');
 		foreach($lang as $lng) {
			$module_lang[$lng] = isset($setting_lang[$lng][$this->langid]) ? $setting_lang[$lng][$this->langid] : $lng;
		} 
 		return $module_lang;
	}
	
	public function getordertax($order_id) {
 		$tax_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'tax'");
		if (isset($tax_query->row['value']) && $tax_query->row['value']) {
			return $tax_query->row['value'];
		} 
		return 0;
	}	
	public function getordershipping($order_id) {
 		$tax_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = 'shipping'");
		if (isset($tax_query->row['value']) && $tax_query->row['value']) {
			return $tax_query->row['value'];
		} 
		return 0;
	} 	
	public function getOrderProduct($order_id) {
		$query = $this->db->query("SELECT op.*,p.sku,p.tax_class_id, (select m.name from " . DB_PREFIX . "manufacturer m where m.manufacturer_id = p.manufacturer_id) as brandname, (select cd.name from " . DB_PREFIX . "category_description cd inner join " . DB_PREFIX . "product_to_category pc on pc.category_id = cd.category_id where pc.product_id = p.product_id limit 1) AS category FROM " . DB_PREFIX . "order_product op INNER JOIN " . DB_PREFIX . "product p ON p.product_id = op.product_id LEFT JOIN " . DB_PREFIX . "order_option oo ON (oo.order_product_id = op.order_product_id) WHERE op.order_id = '" . (int)$order_id . "' AND oo.order_id IS NULL GROUP BY op.order_product_id");
 		
 		if($query->num_rows) {
			return $query->rows;
		}
		
		return array();
 	}	              
	public function getOrderProductOptions($order_id) {
		$query = $this->db->query("SELECT op.*,p.sku,p.tax_class_id, (select m.name from " . DB_PREFIX . "manufacturer m where m.manufacturer_id = p.manufacturer_id) as brandname, (select cd.name from " . DB_PREFIX . "category_description cd inner join " . DB_PREFIX . "product_to_category pc on pc.category_id = cd.category_id where pc.product_id = p.product_id limit 1) AS category, oo.name as option_name, oo.value,oo.order_product_id,GROUP_CONCAT(DISTINCT oo.name, ': ', oo.value SEPARATOR ' - ') as options_data FROM " . DB_PREFIX . "order_product op INNER JOIN " . DB_PREFIX . "product p ON p.product_id = op.product_id INNER JOIN " . DB_PREFIX . "order_option oo ON op.order_product_id = oo.order_product_id WHERE op.order_id = '" . (int)$order_id . "' AND op.order_product_id = oo.order_product_id GROUP BY oo.order_product_id");
		
 		if($query->num_rows) {
			return $query->rows;
		}
		
		return array();
	}
	public function getProdCatName($product_id) {
		$query = $this->db->query("SELECT name FROM " . DB_PREFIX . "category_description cd INNER JOIN " . DB_PREFIX . "product_to_category pc ON pc.category_id = cd.category_id WHERE 1 AND pc.product_id = '".$product_id."' limit 1");
		return (! empty($query->row['name'])) ? $query->row['name'] : '';
	}
	public function getProdBrandName($product_id) {
		$query = $this->db->query("SELECT name from " . DB_PREFIX . "manufacturer m INNER JOIN " . DB_PREFIX . "product p on m.manufacturer_id = p.manufacturer_id WHERE 1 AND p.product_id = ".$product_id);
		return (! empty($query->row['name'])) ? $query->row['name'] : '';
	}
	
	public function getcurval($taxprc) {
		if(substr(VERSION,0,3)>='3.0' || substr(VERSION,0,3)=='2.3' || substr(VERSION,0,3)=='2.2') { 
			$taxprc = $this->currency->format($taxprc, $this->session->data['currency'], false, false);
		} else {
			$taxprc = $this->currency->format($taxprc, '', false, false);
		}	
		return $taxprc;
	}
	public function getCartEventData() {
		if (!$this->cart->hasProducts()) {
			return array();
		}

		$contents = array();
		$content_ids = array();
		$num_items = 0;

		foreach ($this->cart->getProducts() as $product_info) {
			$quantity = max(1, (int)$product_info['quantity']);
			$price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
			$product_id = (string)$product_info['product_id'];

			$content_ids[] = $product_id;
			$num_items += $quantity;
			$contents[] = array(
				'id' => $product_id,
				'quantity' => $quantity,
				'item_price' => (float)$this->getcurval($price)
			);
		}

		$data = array(
			'value' => (float)$this->getcurval($this->cart->getTotal()),
			'currency' => isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency'),
			'content_type' => 'product',
			'content_ids' => $content_ids,
			'contents' => $contents,
			'num_items' => $num_items
		);

		$catalog_id = $this->getFBCATALOGID();
		if ($catalog_id) {
			$data['product_catalog_id'] = $catalog_id;
		}

		return $data;
	}
	public function getPurchaseEventData($order_id) {
		$this->load->model('checkout/order');
		$orderdata = $this->model_checkout_order->getOrder((int)$order_id);

		if (!$orderdata) {
			return array();
		}

		$order_products = array_merge($this->getOrderProduct($order_id), $this->getOrderProductOptions($order_id));
		$contents = array();
		$content_ids = array();
		$num_items = 0;

		foreach ($order_products as $product_info) {
			$quantity = max(1, (int)$product_info['quantity']);
			$product_id = (string)$product_info['product_id'];
			$content_ids[] = $product_id;
			$num_items += $quantity;
			$contents[] = array(
				'id' => $product_id,
				'quantity' => $quantity,
				'item_price' => round((float)$product_info['price'], 2)
			);
		}

		$data = array(
			'order_id' => (string)$order_id,
			'value' => round((float)$orderdata['total'], 2),
			'currency' => 'EUR',
			'content_type' => 'product',
			'content_ids' => $content_ids,
			'contents' => $contents,
			'num_items' => $num_items
		);

		$catalog_id = $this->getFBCATALOGID();
		if ($catalog_id) {
			$data['product_catalog_id'] = $catalog_id;
		}

		return $data;
	}
	public function getchksuccess($order_id = 0) {
		// Purchase is emitted once by meta-pixel.js using the order ID as its
		// browser-side deduplication key and Meta event_id.
		return '';
	}
    
	public function viewprod($product_id) {
		// meta-pixel.js detects product pages directly. Returning no markup avoids
		// duplicate ViewContent events from the legacy OCMOD hook.
		return '';
	}
    
    public function searchproduct() {
		// Search result pages are tracked centrally by meta-pixel.js.
		return '';
	}
    
    public function begincheckout() {
		// Checkout entry is tracked centrally by meta-pixel.js.
		return '';
	}
    
    public function checkoutfunnel() {
		if($this->getFBID()) {
        	$langdata = $this->getlang();
 			$product_data = array();
             
            if ($this->cart->hasProducts()) {
                foreach ($this->cart->getProducts() as $product_info) { 
                    $price = $this->tax->calculate($product_info['price'] , $product_info['tax_class_id'], $this->config->get('config_tax')); 
                    
                    $product_data[] = array(
                        "product_catalog_id" => $this->getFBCATALOGID(),
                        "id" => $product_info['product_id'],
                        "quantity" => $product_info['quantity'],
                        "item_price" => $this->getcurval($price),
                    );
                }
                
                $cartdata = array(
                    "value" => $this->getcurval($this->cart->getTotal()),
                    "currency" => $this->session->data['currency'],
                    "content_type" => 'product', 
                    "contents" => $product_data
                ); 
                
$jsoncart_data = json_encode($cartdata);
$fbecommevnt_text_chkstp_onename = $langdata["text_chkstp_onename"];
$fbecommevnt_text_chkstp_twoname = $langdata["text_chkstp_twoname"];
$fbecommevnt_text_chkstp_threename = $langdata["text_chkstp_threename"];
$fbecommevnt_text_chkstp_fourname = $langdata["text_chkstp_fourname"];
$fbecommevnt_text_chkstp_fivename = $langdata["text_chkstp_fivename"];
$fbecommevnt_text_chkstp_sixname = $langdata["text_chkstp_sixname"];
$returndata = <<<EOF
<script type="text/javascript">
$(document).delegate('#button-register', 'click', function() {    
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_twoname', $jsoncart_data, 0);
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_threename', $jsoncart_data, 0);			
});
$(document).delegate('#button-guest', 'click', function() {    
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_twoname', $jsoncart_data, 0);
    fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_threename', $jsoncart_data, 0);		
});
$(document).delegate('#button-payment-address', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_twoname', $jsoncart_data, 0);
});
$(document).delegate('#button-shipping-address', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_threename', $jsoncart_data, 0);
});
$(document).delegate('#button-shipping-method', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_fourname', $jsoncart_data, 0);
});
$(document).delegate('#button-payment-method', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_fivename', $jsoncart_data, 1);
});
$(document).delegate('#button-confirm', 'click', function() {
	fbecommevnt.checkoutfunnel('$fbecommevnt_text_chkstp_sixname', $jsoncart_data, 0);
});
</script>
EOF;
return $returndata;
}
 		} 
	}
    
    public function contactus() {
		if($this->getFBID()) {
$returndata = <<<EOF
<script type="text/javascript">
if (window.mmMetaPixel) window.mmMetaPixel.track('Contact');
</script>
EOF;
return $returndata;
 		} 
	}
    
    public function loginevent() {
		if($this->getFBID()) {
        $langdata = $this->getlang();
$loginevent = $langdata['text_loginevent'];
$returndata = <<<EOF
<script type="text/javascript">
if (window.mmMetaPixel) window.mmMetaPixel.trackCustom('$loginevent');
</script>
EOF;
return $returndata;
 		} 
	}
    
    public function registerevent() {
		if($this->getFBID()) {
        $langdata = $this->getlang();
        $leaddata = array(
            "product_catalog_id" => $this->getFBCATALOGID(),
            "content_category" => 'completesignup',
            "content_name" => 'leadtracking',
            "value" => 1,
            "currency" => $this->session->data['currency'], 
        );
$jsonlead_data = json_encode($leaddata);
$regevent = $langdata['text_regevent'];
$returndata = <<<EOF
<script type="text/javascript">
if (window.mmMetaPixel) {
	window.mmMetaPixel.trackCustom('$regevent');
	window.mmMetaPixel.track('CompleteRegistration');
	window.mmMetaPixel.track('Lead', $jsonlead_data);
}
</script>
EOF;
return $returndata;
 		} 
	}
    
	public function gettrackcode() {
		// The maintained, consent-aware integration is loaded by the theme header.
		// Keep this hook empty so an enabled legacy module cannot initialize the
		// same Pixel twice or bypass the visitor's marketing-cookie choice.
		return '';
	}    
}
