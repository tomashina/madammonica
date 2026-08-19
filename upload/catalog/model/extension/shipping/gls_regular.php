<?php
class ModelExtensionShippingGlsRegular extends Model {
	public function getQuote($address) {
		$this->load->language('extension/shipping/gls_regular');

		if (!$this->isGeoZoneAllowed($address)) {
			return array();
		}

		$shipping_zone = $this->getShippingZone($address);

		if (!$shipping_zone) {
			return array();
		}

		$cost = $this->getCost($shipping_zone['cost_key'], $shipping_zone['default_cost']);

		$quote_data = array();

		$quote_data['gls_regular'] = array(
			'code'         => 'gls_regular.gls_regular',
			'title'        => $this->language->get('text_description') . ' - ' . $shipping_zone['title'],
			'cost'         => $cost,
			'tax_class_id' => $this->config->get('shipping_gls_regular_tax_class_id'),
			'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_gls_regular_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
		);

		return array(
			'code'       => 'gls_regular',
			'title'      => $this->language->get('text_title'),
			'quote'      => $quote_data,
			'sort_order' => $this->config->get('shipping_gls_regular_sort_order'),
			'error'      => false
		);
	}

	private function isGeoZoneAllowed($address) {
		$geo_zone_id = (int)$this->config->get('shipping_gls_regular_geo_zone_id');

		if (!$geo_zone_id) {
			return true;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . $geo_zone_id . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		return (bool)$query->num_rows;
	}

	private function getShippingZone($address) {
		$iso_code = isset($address['iso_code_2']) ? strtoupper($address['iso_code_2']) : '';

		if ($iso_code === 'HR') {
			if ($this->isSpecialCroatianArea($address)) {
				return array(
					'cost_key'     => 'cost_hr_special',
					'default_cost' => 15,
					'title'        => $this->language->get('text_zone_hr_special')
				);
			}

			return array(
				'cost_key'     => 'cost_hr',
				'default_cost' => 0,
				'title'        => $this->language->get('text_zone_hr')
			);
		}

		$zones = array(
			'SI' => 1, 'HU' => 1, 'SK' => 1, 'AT' => 1, 'CZ' => 1,
			'PL' => 2, 'DE' => 2, 'BE' => 2, 'NL' => 2, 'LU' => 2,
			'RO' => 3, 'IT' => 3, 'BG' => 3, 'DK' => 3, 'IE' => 3,
			'LT' => 4, 'LV' => 4, 'EE' => 4, 'SE' => 4, 'GR' => 4, 'FI' => 4, 'FR' => 4,
			'ES' => 5, 'PT' => 5, 'MT' => 5, 'CY' => 5
		);

		if (!isset($zones[$iso_code])) {
			return false;
		}

		$zone_number = $zones[$iso_code];
		$defaults = array(1 => 15, 2 => 18, 3 => 25, 4 => 30, 5 => 40);

		return array(
			'cost_key'     => 'cost_eu_zone_' . $zone_number,
			'default_cost' => $defaults[$zone_number],
			'title'        => $this->language->get('text_zone_eu_' . $zone_number)
		);
	}

	private function getCost($key, $default) {
		$value = $this->config->get('shipping_gls_regular_' . $key);

		if ($value === null || $value === '') {
			$value = $default;
		}

		return (float)str_replace(',', '.', $value);
	}

	private function isSpecialCroatianArea($address) {
		$postcode = isset($address['postcode']) ? preg_replace('/[^0-9]/', '', (string)$address['postcode']) : '';
		$city = isset($address['city']) ? $this->normalizeAreaName($address['city']) : '';

		if ($postcode === '' || $city === '') {
			return false;
		}

		$areas = $this->getSpecialCroatianAreas();

		if (!isset($areas[$postcode])) {
			return false;
		}

		foreach ($areas[$postcode] as $area) {
			$area = $this->normalizeAreaName($area);

			if ($city === $area || strpos($city, $area) !== false || strpos($area, $city) !== false) {
				return true;
			}
		}

		return false;
	}

	private function normalizeAreaName($value) {
		$value = trim((string)$value);

		if (function_exists('iconv')) {
			$converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

			if ($converted !== false) {
				$value = $converted;
			}
		}

		$value = strtolower($value);
		$value = preg_replace('/[^a-z0-9]+/', ' ', $value);
		$value = preg_replace('/\s+/', ' ', $value);

		return trim($value);
	}

	private function getSpecialCroatianAreas() {
		return array(
			'20221' => array('Kolocep'),
			'20222' => array('Lopud'),
			'20223' => array('Sudurad', 'Sipanska Luka'),
			'20224' => array('Korita', 'Maranovici', 'Okuklje', 'Prozura', 'Prozurska Luka', 'Saplunara'),
			'20225' => array('Babino Polje', 'Blato', 'Kozarica', 'Ropa', 'Sobra'),
			'20226' => array('Babine Kuce', 'Govedari', 'Njivice', 'Polace', 'Pomena', 'Pristaniste', 'Soline', 'Tatinica', 'Velika Loza'),
			'20290' => array('Glavat', 'Lastovo', 'Pasadur', 'Skrivena Luka', 'Susac', 'Uble', 'Zaklopatica'),
			'21225' => array('Drvenik Mali', 'Drvenik Veliki'),
			'21430' => array('Donje Selo', 'Grohote', 'Maslinica', 'Rogac', 'Srednje Selo'),
			'21432' => array('Gornje Selo', 'Necujam', 'Stomorska'),
			'22232' => array('Zlarin'),
			'22233' => array('Prvic Luka'),
			'22234' => array('Prvic Sepurine'),
			'22235' => array('Kaprije'),
			'22236' => array('Zirje'),
			'23281' => array('Sali', 'Zaglav'),
			'23282' => array('Luka', 'Zman'),
			'23283' => array('Rava'),
			'23284' => array('Mali Iz', 'Veli Iz'),
			'23285' => array('Brbinj', 'Savar'),
			'23286' => array('Bozava', 'Dragove', 'Zverinac'),
			'23287' => array('Polje', 'Soline', 'Veli Rat', 'Verunic'),
			'23291' => array('Rivanj', 'Sestrunj', 'Zverinac'),
			'23292' => array('Brgulje', 'Molat', 'Zapuntel'),
			'23293' => array('Ist'),
			'23294' => array('Premuda'),
			'23295' => array('Silba'),
			'23296' => array('Olib'),
			'51552' => array('Ilovik'),
			'51561' => array('Susak'),
			'51562' => array('Unije')
		);
	}
}
