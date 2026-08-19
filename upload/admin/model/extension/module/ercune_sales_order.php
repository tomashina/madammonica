<?php
class ModelExtensionModuleErcuneSalesOrder extends Model {
	public function install() {
		$this->ensureOrderColumn('number_quote');
		$this->ensureOrderColumn('number_order');
	}

	public function sendOrder($order_id, $type = 'order') {
		$order_id = (int)$order_id;
		$type = $this->normaliseType($type);

		$order = $this->getOrderData($order_id);

		if (!$order) {
			throw new Exception('Narudzba ne postoji.');
		}

		if ($this->config->get('module_ercune_sales_order_ensure_products')) {
			$this->ensureCatalogueProducts($order);
		}

		$method = ($type === 'order') ? 'SalesOrderCreate' : 'SalesQuoteCreate';

		$body = array(
			'username'   => $this->setting('username'),
			'secretKey'  => $this->setting('secret_key'),
			'token'      => $this->setting('token'),
			'method'     => $method,
			'parameters' => $this->buildSaleParameters($order, $type)
		);

		$response = $this->postApi('WebServices/API', $body);

		if ($response === false || $response === null || $response === '') {
			throw new Exception('Greska pri komunikaciji s Ercune API-jem.');
		}

		if ($this->isApiError($response)) {
			throw new Exception('API error: ' . $this->apiDescription($response));
		}

		$number = $this->extractDocumentNumber($response);

		if ($number !== '') {
			$this->saveDocumentNumber($order_id, $type, $number);
		}

		$this->addHistoryNote($order, $method, $number);

		return array(
			'message' => ($type === 'order') ? 'Narudzba je poslana u Ercune.' : 'Ponuda je poslana u Ercune.',
			'number'  => $number
		);
	}

	private function buildSaleParameters(array $order, $type) {
		$root_key = ($type === 'order') ? 'SalesOrder' : 'SalesQuote';

		return array(
			'sendIssuedInvoiceByEmail' => (bool)$this->config->get('module_ercune_sales_order_send_email'),
			'apiTransactionId'         => $this->createTransactionId($order['order_id']),
			$root_key                  => $this->buildSale($order)
		);
	}

	private function buildSale(array $order) {
		$custom_field = $this->decodeJsonArray($order['custom_field']);
		$payment_custom_field = $this->decodeJsonArray($order['payment_custom_field']);
		$company_field_id = (int)$this->config->get('module_ercune_sales_order_company_custom_field_id');
		$tax_field_id = (int)$this->config->get('module_ercune_sales_order_tax_custom_field_id');

		$company = $this->getCustomFieldValue($custom_field, $company_field_id);

		if ($company === '') {
			$company = $this->getCustomFieldValue($payment_custom_field, $company_field_id);
		}

		if ($company === '') {
			$company = $order['payment_company'];
		}

		$buyer_name = trim($company);

		if ($buyer_name === '') {
			$buyer_name = trim($order['payment_firstname'] . ' ' . $order['payment_lastname']);
		}

		if ($buyer_name === '') {
			$buyer_name = trim($order['firstname'] . ' ' . $order['lastname']);
		}

		if ($buyer_name === '') {
			$buyer_name = 'Kupac';
		}

		$tax_number = $this->getCustomFieldValue($custom_field, $tax_field_id);

		if ($tax_number === '') {
			$tax_number = $this->getCustomFieldValue($payment_custom_field, $tax_field_id);
		}

		$country = $this->resolveCountryCode($tax_number, $order['payment_country_id']);

		return array(
			'vatTransactionType' => '0',
			'buyerTaxNumber'     => $tax_number,
			'buyerName'          => $buyer_name,
			'buyerFirstName'     => $order['payment_firstname'],
			'buyerLastName'      => $order['payment_lastname'],
			'buyerStreet'        => $order['payment_address_1'],
			'buyerPostalCode'    => $order['payment_postcode'],
			'buyerCity'          => $order['payment_city'],
			'buyerCountry'       => $country,
			'buyerEMail'         => $order['email'],
			'buyerPhone'         => $order['telephone'],
			'validUntil'         => date('Y-m-d', strtotime('+' . (int)$this->config->get('module_ercune_sales_order_valid_until_days') . ' days')),
			'methodOfPayment'    => $this->resolvePaymentMethod($order['payment_code']),
			'country'            => $country,
			'Items'              => $this->buildSaleItems($order),
			'Address'            => $this->buildSaleAddress($order, $buyer_name, $country)
		);
	}

	private function buildSaleItems(array $order) {
		$items = array();

		foreach ($order['products'] as $product) {
			$product_code = trim($product['model']);

			if ($product_code === '') {
				$product_code = 'OC-' . (int)$product['product_id'];
			}

			$items[] = array(
				'productCode' => $product_code,
				'productName' => $product['name'],
				'quantity'    => max(1, (int)$product['quantity']),
				'netPrice'    => $this->formatDecimal($product['price'], 6)
			);
		}

		$shipping = $this->getOrderTotalValue($order, 'shipping');

		if ($shipping > 0) {
			$items[] = array(
				'productCode' => $this->setting('shipping_product_code', 'DOSTAVA'),
				'productName' => $order['shipping_method'] ? $order['shipping_method'] : $this->setting('shipping_product_name', 'Dostava'),
				'quantity'    => 1,
				'netPrice'    => $this->formatDecimal($shipping, 2)
			);
		}

		return $items;
	}

	private function buildSaleAddress(array $order, $buyer_name, $country) {
		$street = $order['shipping_address_1'] ? $order['shipping_address_1'] : $order['payment_address_1'];
		$postcode = $order['shipping_postcode'] ? $order['shipping_postcode'] : $order['payment_postcode'];
		$city = $order['shipping_city'] ? $order['shipping_city'] : $order['payment_city'];

		return array(
			'firstAddressLine' => $buyer_name,
			'street'           => $street,
			'postalCode'       => $postcode,
			'city'             => $city,
			'country'          => $country,
			'type'             => 'Delivery'
		);
	}

	private function ensureCatalogueProducts(array $order) {
		$seen = array();

		foreach ($order['products'] as $product) {
			$product_code = trim($product['model']);

			if ($product_code === '') {
				$product_code = 'OC-' . (int)$product['product_id'];
			}

			if (isset($seen[$product_code])) {
				continue;
			}

			$seen[$product_code] = true;

			$response = $this->postApi('WebServices/API', array(
				'username'   => $this->setting('username'),
				'secretKey'  => $this->setting('secret_key'),
				'token'      => $this->setting('token'),
				'method'     => 'ProductImport',
				'parameters' => array(
					'importType' => 'createOrUpdate',
					'product'    => array(
						'productCode' => $product_code,
						'name'        => $product['name'],
						'status'      => 'active',
						'type'        => 'goodsWithoutStockManagement',
						'unit'        => 'piece',
						'grossPrice'  => $this->formatDecimal(0, 2),
						'description' => $product['name'],
						'allowChangeOfPriceOnTheInvoice'              => true,
						'allowChangeOfProductDescriptionOnTheInvoice' => true
					)
				)
			));

			if ($this->isApiError($response)) {
				$description = $this->apiDescription($response);

				if ($this->isPermissionError($description)) {
					$this->log('ProductImport SKIP', 'API user nema ProductImport prava. Preskacem upis kataloga. Zadnji artikl: ' . $product_code . '. Odgovor: ' . $description);
					return;
				}

				throw new Exception('Ne mogu upisati artikl u Ercune katalog (' . $product_code . '): ' . $description);
			}
		}

		if ($this->getOrderTotalValue($order, 'shipping') > 0) {
			$this->ensureShippingProduct($order);
		}
	}

	private function ensureShippingProduct(array $order) {
		$product_code = $this->setting('shipping_product_code', 'DOSTAVA');
		$product_name = $this->setting('shipping_product_name', 'Dostava');

		$response = $this->postApi('WebServices/API', array(
			'username'   => $this->setting('username'),
			'secretKey'  => $this->setting('secret_key'),
			'token'      => $this->setting('token'),
			'method'     => 'ProductImport',
			'parameters' => array(
				'importType' => 'createOrUpdate',
				'product'    => array(
					'productCode' => $product_code,
					'name'        => $product_name,
					'description' => $product_name,
					'status'      => 'active',
					'type'        => 'services',
					'unit'        => 'service',
					'grossPrice'  => $this->formatDecimal(0, 2),
					'currency'    => $order['currency_code'],
					'allowChangeOfPriceOnTheInvoice'              => true,
					'allowChangeOfProductDescriptionOnTheInvoice' => true
				)
			)
		));

		if ($this->isApiError($response)) {
			$description = $this->apiDescription($response);

			if ($this->isPermissionError($description)) {
				$this->log('ProductImport SKIP', 'API user nema ProductImport prava za servisni artikl ' . $product_code . '. Odgovor: ' . $description);
				return;
			}

			throw new Exception('Ne mogu upisati servisni artikl ' . $product_code . ': ' . $description);
		}
	}

	private function postApi($endpoint, array $body) {
		if (!function_exists('curl_init')) {
			throw new Exception('PHP cURL ekstenzija nije dostupna.');
		}

		$url = $this->buildApiUrl($endpoint);
		$payload = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);

		$this->log('POST ' . $endpoint . ' REQ', $payload);

		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_USERPWD        => $this->setting('username') . ':' . $this->setting('token') . '_' . $this->setting('secret_key'),
			CURLOPT_HTTPHEADER     => array(
				'Accept: application/json',
				'Content-Type: application/json; charset=utf-8'
			),
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_CONNECTTIMEOUT => 10
		));

		$response = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->log('POST ' . $endpoint . ' RESP', (string)$response);

		if ($errno) {
			$this->log('POST ' . $endpoint . ' ERR', $error);
			throw new Exception('cURL greska: ' . $error);
		}

		if ($code >= 400) {
			$this->log('POST ' . $endpoint . ' HTTP', 'HTTP ' . $code);
		}

		$decoded = json_decode($response, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return $response;
		}

		if (isset($decoded['response']['result'])) {
			return $decoded['response']['result'];
		}

		return $decoded;
	}

	private function getOrderData($order_id) {
		$order_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");

		if (!$order_query->num_rows) {
			return array();
		}

		$order = $order_query->row;

		$products_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		$order['products'] = $products_query->rows;

		$totals_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
		$order['totals'] = $totals_query->rows;

		return $order;
	}

	private function saveDocumentNumber($order_id, $type, $number) {
		$column = ($type === 'order') ? 'number_order' : 'number_quote';

		if (!$this->hasOrderColumn($column)) {
			return;
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET `" . $column . "` = '" . $this->db->escape($number) . "' WHERE order_id = '" . (int)$order_id . "'");
	}

	private function addHistoryNote(array $order, $method, $number) {
		$comment = $method . ' poslan u Ercune';

		if ($number !== '') {
			$comment .= '. Broj dokumenta: ' . $number;
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order['order_id'] . "', order_status_id = '" . (int)$order['order_status_id'] . "', notify = '0', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
	}

	private function getOrderTotalValue(array $order, $code) {
		foreach ($order['totals'] as $total) {
			if ($total['code'] === $code) {
				return round((float)$total['value'], 4);
			}
		}

		return 0.0;
	}

	private function normaliseType($type) {
		$type = strtolower(trim((string)$type));

		if ($type === 'quote') {
			$type = 'offer';
		}

		return ($type === 'offer') ? 'offer' : 'order';
	}

	private function resolvePaymentMethod($payment_code) {
		if ($payment_code === 'cod') {
			return $this->setting('method_cod', 'Cash');
		}

		if ($payment_code === 'bank_transfer') {
			return $this->setting('method_bank_transfer', 'BankTransfer');
		}

		return $this->setting('method_default', 'CreditCard');
	}

	private function resolveCountryCode($tax_number, $payment_country_id) {
		$vat = strtoupper(trim((string)$tax_number));

		if (strpos($vat, 'SI') === 0) {
			return 'SI';
		}

		if (preg_match('/^\d{11}$/', $vat)) {
			return 'HR';
		}

		$country_query = $this->db->query("SELECT iso_code_2 FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$payment_country_id . "' LIMIT 1");

		if ($country_query->num_rows && $country_query->row['iso_code_2']) {
			return strtoupper($country_query->row['iso_code_2']);
		}

		return $this->setting('default_country', 'HR');
	}

	private function decodeJsonArray($value) {
		if (is_array($value)) {
			return $value;
		}

		$decoded = json_decode((string)$value, true);

		return is_array($decoded) ? $decoded : array();
	}

	private function getCustomFieldValue(array $fields, $field_id) {
		if (!$field_id) {
			return '';
		}

		if (!isset($fields[$field_id])) {
			return '';
		}

		$value = $fields[$field_id];

		if (is_array($value)) {
			return trim(implode(', ', $value));
		}

		return trim((string)$value);
	}

	private function createTransactionId($order_id) {
		try {
			$suffix = substr(bin2hex(random_bytes(6)), 0, 12);
		} catch (Exception $e) {
			$suffix = substr(str_replace('.', '', uniqid('', true)), -12);
		}

		return (int)$order_id . '-' . $suffix;
	}

	private function extractDocumentNumber($response) {
		if (is_array($response)) {
			foreach (array('number', 'documentNumber', 'salesOrderNumber', 'salesQuoteNumber') as $key) {
				if (!empty($response[$key])) {
					return (string)$response[$key];
				}
			}

			if (isset($response['response']['result'])) {
				return $this->extractDocumentNumber($response['response']['result']);
			}
		}

		return '';
	}

	private function isApiError($response) {
		if (!is_array($response)) {
			return false;
		}

		if (isset($response['status']) && strtolower((string)$response['status']) === 'error') {
			return true;
		}

		if (isset($response['response']['status']) && strtolower((string)$response['response']['status']) === 'error') {
			return true;
		}

		return false;
	}

	private function apiDescription($response) {
		if (!is_array($response)) {
			return 'Nepoznata greska';
		}

		if (!empty($response['description'])) {
			return $response['description'];
		}

		if (!empty($response['message'])) {
			return $response['message'];
		}

		if (!empty($response['response']['description'])) {
			return $response['response']['description'];
		}

		if (!empty($response['response']['message'])) {
			return $response['response']['message'];
		}

		return 'Nepoznata greska';
	}

	private function isPermissionError($description) {
		$description = strtolower((string)$description);

		return strpos($description, 'access denied') !== false
			|| strpos($description, 'sufficient privileges') !== false
			|| strpos($description, 'permission') !== false
			|| strpos($description, 'privilege') !== false
			|| strpos($description, 'forbidden') !== false;
	}

	private function ensureOrderColumn($column) {
		if ($this->hasOrderColumn($column)) {
			return;
		}

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "order` ADD COLUMN `" . $this->db->escape($column) . "` VARCHAR(45) NOT NULL DEFAULT ''");
	}

	private function hasOrderColumn($column) {
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE '" . $this->db->escape($column) . "'");

		return (bool)$query->num_rows;
	}

	private function setting($key, $default = '') {
		$value = $this->config->get('module_ercune_sales_order_' . $key);

		if ($value === null || $value === '') {
			return $default;
		}

		return $value;
	}

	private function buildApiUrl($endpoint) {
		$base = rtrim($this->setting('api_url'), '/');
		$endpoint = ltrim($endpoint, '/');

		if (preg_match('#/WebServices/API$#i', $base) && strtolower($endpoint) === 'webservices/api') {
			return $base;
		}

		return $base . '/' . $endpoint;
	}

	private function formatDecimal($value, $scale = 2) {
		return number_format((float)$value, (int)$scale, '.', '');
	}

	private function log($type, $message) {
		if (!$this->config->get('module_ercune_sales_order_debug')) {
			return;
		}

		$log = new Log('ercune_sales_order.log');
		$log->write($type . ': ' . $message);
	}
}
