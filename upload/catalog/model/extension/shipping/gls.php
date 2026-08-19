<?php
class ModelExtensionShippingGls extends Model {
	public function getQuote($address) {
		$this->load->language('extension/shipping/gls');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_gls_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_gls_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if ($status && isset($address['iso_code_2']) && strtoupper($address['iso_code_2']) !== 'HR') {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$cost = (float)$this->config->get('shipping_gls_cost');
			$free_total = (float)$this->config->get('shipping_gls_free_total');

			if ($free_total > 0 && $this->cart->getSubTotal() >= $free_total) {
				$cost = 0;
			}

			$quote_data = array();

			$quote_data['gls'] = array(
				'code'         => 'gls.gls',
				'title'        => $this->language->get('text_description'),
				'cost'         => $cost,
				'tax_class_id' => $this->config->get('shipping_gls_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_gls_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'gls',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_gls_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}
