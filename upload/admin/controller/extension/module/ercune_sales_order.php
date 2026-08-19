<?php
class ControllerExtensionModuleErcuneSalesOrder extends Controller {
	private $error = array();

	private $defaults = array(
		'module_ercune_sales_order_status'                  => '1',
		'module_ercune_sales_order_api_url'                 => '',
		'module_ercune_sales_order_username'                => '',
		'module_ercune_sales_order_secret_key'              => '',
		'module_ercune_sales_order_token'                   => '',
		'module_ercune_sales_order_send_email'              => '1',
		'module_ercune_sales_order_valid_until_days'        => '7',
		'module_ercune_sales_order_default_country'         => 'HR',
		'module_ercune_sales_order_company_custom_field_id' => '1',
		'module_ercune_sales_order_tax_custom_field_id'     => '2',
		'module_ercune_sales_order_shipping_product_code'   => 'DOSTAVA',
		'module_ercune_sales_order_shipping_product_name'   => 'Dostava',
		'module_ercune_sales_order_method_cod'              => 'Cash',
		'module_ercune_sales_order_method_bank_transfer'    => 'BankTransfer',
		'module_ercune_sales_order_method_default'          => 'CreditCard',
		'module_ercune_sales_order_ensure_products'         => '1',
		'module_ercune_sales_order_debug'                   => '0'
	);

	public function index() {
		$this->load->language('extension/module/ercune_sales_order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$data = $this->normalisePostData($this->request->post);

			$this->model_setting_setting->editSetting('module_ercune_sales_order', $data);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_api_url'] = isset($this->error['api_url']) ? $this->error['api_url'] : '';
		$data['error_username'] = isset($this->error['username']) ? $this->error['username'] : '';
		$data['error_secret_key'] = isset($this->error['secret_key']) ? $this->error['secret_key'] : '';
		$data['error_token'] = isset($this->error['token']) ? $this->error['token'] : '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/ercune_sales_order', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/ercune_sales_order', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		foreach ($this->defaults as $key => $default) {
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} else {
				$value = $this->config->get($key);
				$data[$key] = ($value !== null && $value !== '') ? $value : $default;
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ercune_sales_order', $data));
	}

	public function send() {
		$this->load->language('extension/module/ercune_sales_order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order') && !$this->user->hasPermission('modify', 'extension/module/ercune_sales_order')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif (!$this->config->get('module_ercune_sales_order_status')) {
			$json['error'] = $this->language->get('error_disabled');
		} else {
			$order_id = isset($this->request->get['order_id']) ? (int)$this->request->get['order_id'] : 0;
			$type = isset($this->request->get['type']) ? $this->request->get['type'] : 'order';

			$this->load->model('extension/module/ercune_sales_order');

			try {
				$result = $this->model_extension_module_ercune_sales_order->sendOrder($order_id, $type);

				$json['success'] = $result['message'];

				if (!empty($result['number'])) {
					$json['number'] = $result['number'];
				}
			} catch (Exception $e) {
				$json['error'] = $e->getMessage();
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_ercune_sales_order', $this->defaults);

		$this->load->model('extension/module/ercune_sales_order');
		$this->model_extension_module_ercune_sales_order->install();

		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/ercune_sales_order');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/ercune_sales_order');
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_ercune_sales_order');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ercune_sales_order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$status = isset($this->request->post['module_ercune_sales_order_status']) ? (int)$this->request->post['module_ercune_sales_order_status'] : 0;

		if ($status) {
			if (empty(trim($this->request->post['module_ercune_sales_order_api_url'] ?? ''))) {
				$this->error['api_url'] = $this->language->get('error_api_url');
			}

			if (empty(trim($this->request->post['module_ercune_sales_order_username'] ?? ''))) {
				$this->error['username'] = $this->language->get('error_username');
			}

			if (empty(trim($this->request->post['module_ercune_sales_order_secret_key'] ?? ''))) {
				$this->error['secret_key'] = $this->language->get('error_secret_key');
			}

			if (empty(trim($this->request->post['module_ercune_sales_order_token'] ?? ''))) {
				$this->error['token'] = $this->language->get('error_token');
			}
		}

		return !$this->error;
	}

	private function normalisePostData(array $post): array {
		$data = array();

		foreach ($this->defaults as $key => $default) {
			$data[$key] = isset($post[$key]) ? $post[$key] : $default;
		}

		foreach ($data as $key => $value) {
			if (is_string($value)) {
				$data[$key] = trim($value);
			}
		}

		$data['module_ercune_sales_order_valid_until_days'] = max(0, (int)$data['module_ercune_sales_order_valid_until_days']);
		$data['module_ercune_sales_order_company_custom_field_id'] = max(0, (int)$data['module_ercune_sales_order_company_custom_field_id']);
		$data['module_ercune_sales_order_tax_custom_field_id'] = max(0, (int)$data['module_ercune_sales_order_tax_custom_field_id']);
		$data['module_ercune_sales_order_default_country'] = strtoupper(substr($data['module_ercune_sales_order_default_country'], 0, 2));

		return $data;
	}
}
