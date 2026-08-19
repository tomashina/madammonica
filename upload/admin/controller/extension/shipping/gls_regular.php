<?php
class ControllerExtensionShippingGlsRegular extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/shipping/gls_regular');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/shipping/gls_regular');

		$this->model_extension_shipping_gls_regular->installSchema();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('shipping_gls_regular', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/shipping/gls_regular', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/shipping/gls_regular', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

		$defaults = array(
			'shipping_gls_regular_api_url'                  => 'https://api.mygls.hr',
			'shipping_gls_regular_username'                 => '',
			'shipping_gls_regular_password'                 => '',
			'shipping_gls_regular_client_number'            => '',
			'shipping_gls_regular_pickup_name'              => $this->config->get('config_name'),
			'shipping_gls_regular_pickup_street'            => '',
			'shipping_gls_regular_pickup_house_number'      => '',
			'shipping_gls_regular_pickup_house_number_info' => '',
			'shipping_gls_regular_pickup_city'              => '',
			'shipping_gls_regular_pickup_postcode'          => '',
			'shipping_gls_regular_pickup_country'           => 'HR',
			'shipping_gls_regular_pickup_email'             => $this->config->get('config_email'),
			'shipping_gls_regular_pickup_phone'             => $this->config->get('config_telephone'),
			'shipping_gls_regular_order_prefix'             => 'DRYZEN-',
			'shipping_gls_regular_content'                  => 'Dryzen narudzba',
			'shipping_gls_regular_printer_type'             => 'A4_2x2',
			'shipping_gls_regular_print_position'           => '1',
			'shipping_gls_regular_hide_phone'               => 0,
			'shipping_gls_regular_pickup_days'              => '1',
			'shipping_gls_regular_cost_hr'                  => '0.00',
			'shipping_gls_regular_cost_hr_special'          => '15.00',
			'shipping_gls_regular_cost_eu_zone_1'           => '15.00',
			'shipping_gls_regular_cost_eu_zone_2'           => '18.00',
			'shipping_gls_regular_cost_eu_zone_3'           => '25.00',
			'shipping_gls_regular_cost_eu_zone_4'           => '30.00',
			'shipping_gls_regular_cost_eu_zone_5'           => '40.00',
			'shipping_gls_regular_tax_class_id'             => 0,
			'shipping_gls_regular_geo_zone_id'              => 0,
			'shipping_gls_regular_status'                   => 0,
			'shipping_gls_regular_sort_order'               => 0
		);

		foreach ($defaults as $key => $default) {
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$data[$key] = $this->getConfigValue($key, $default);
			}
		}

		$data['printer_types'] = array('A4_2x2', 'A4_4x1', 'Connect', 'Thermo', 'ThermoZPL', 'ThermoZPL_300DPI', 'ShipItThermoPdf', 'ShipItThermoZpl');

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		$data['api_help'] = sprintf($this->language->get('help_api_url'), $data['shipping_gls_regular_api_url']);
		$data['price_help'] = $this->language->get('help_prices');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/shipping/gls_regular', $data));
	}

	public function install() {
		$this->load->model('extension/shipping/gls_regular');
		$this->model_extension_shipping_gls_regular->installSchema();
	}

	public function createShipment() {
		$this->load->language('extension/shipping/gls_regular');

		$json = array();

		if (!$this->user->hasPermission('modify', 'extension/shipping/gls_regular')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif (empty($this->request->get['order_id'])) {
			$json['error'] = $this->language->get('error_not_gls_order');
		} else {
			try {
				$this->load->model('extension/shipping/gls_regular');
				$shipment = $this->model_extension_shipping_gls_regular->createShipment((int)$this->request->get['order_id']);

				$json['success'] = !empty($shipment['existing']) ? $this->language->get('text_shipment_exists') : $this->language->get('text_shipment_created');
				$json['parcel_id'] = isset($shipment['parcel_id']) ? $shipment['parcel_id'] : '';
				$json['parcel_number'] = isset($shipment['parcel_number']) ? $shipment['parcel_number'] : '';
				$json['display_id'] = !empty($shipment['parcel_number']) ? $shipment['parcel_number'] : (isset($shipment['parcel_id']) ? $shipment['parcel_id'] : '');
				$json['label'] = str_replace('&amp;', '&', $this->url->link('extension/shipping/gls_regular/label', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true));
			} catch (Exception $e) {
				$json['error'] = $e->getMessage();
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function label() {
		$this->load->language('extension/shipping/gls_regular');

		if (!$this->user->hasPermission('access', 'extension/shipping/gls_regular')) {
			$this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token'], true));
		}

		$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;

		try {
			$this->load->model('extension/shipping/gls_regular');
			$pdf = $this->model_extension_shipping_gls_regular->getLabel($order_id);

			$this->response->addHeader('Content-Type: application/pdf');
			$this->response->addHeader('Content-Disposition: inline; filename="gls-regular-' . $order_id . '.pdf"');
			$this->response->setOutput($pdf);
		} catch (Exception $e) {
			$this->session->data['error_warning'] = $e->getMessage();
			$this->response->redirect($this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id, true));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/shipping/gls_regular')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	private function getConfigValue($key, $default = '') {
		$value = $this->config->get($key);

		if (($value === null || $value === '') && strpos($key, 'shipping_gls_regular_') === 0) {
			$shared_key = substr($key, strlen('shipping_gls_regular_'));

			if (in_array($shared_key, $this->getSharedConfigKeys())) {
				$value = $this->config->get('shipping_gls_' . $shared_key);
			}
		}

		return ($value !== null && $value !== '') ? $value : $default;
	}

	private function getSharedConfigKeys() {
		return array(
			'api_url',
			'username',
			'password',
			'client_number',
			'pickup_name',
			'pickup_street',
			'pickup_house_number',
			'pickup_house_number_info',
			'pickup_city',
			'pickup_postcode',
			'pickup_country',
			'pickup_email',
			'pickup_phone',
			'pickup_days',
			'order_prefix',
			'content',
			'printer_type',
			'print_position',
			'hide_phone'
		);
	}
}
