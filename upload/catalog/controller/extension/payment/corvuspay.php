<?php
class ControllerExtensionPaymentCorvusPay extends Controller {
    const CORVUSPAY_VERSION = '1.6';

    public function index() {
        $this->load->language('extension/payment/corvuspay');
        $this->load->model('checkout/order');

        if (empty($this->session->data['order_id'])) {
            return '';
        }

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (!$order_info) {
            return '';
        }

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['action'] = $this->config->get('payment_corvuspay_test')
            ? 'https://wallet.test.corvuspay.com/checkout/'
            : 'https://wallet.corvuspay.com/checkout/';

        $authorisation_type = (string)$this->config->get('payment_corvuspay_authorisationtype');
        $language = isset($this->session->data['language'])
            ? substr((string)$this->session->data['language'], 0, 2)
            : 'hr';
        $supported_languages = array('hr', 'en', 'it', 'de', 'rs', 'sl', 'mk', 'sq');

        if (!in_array($language, $supported_languages, true)) {
            $language = 'hr';
        }

        $country_code = strtoupper(substr((string)$order_info['payment_iso_code_2'], 0, 2));

        if (!preg_match('/^[A-Z]{2}$/', $country_code)) {
            $country_code = 'HR';
        }

        $parameters = array(
            'version'                 => self::CORVUSPAY_VERSION,
            'store_id'                => trim((string)$this->config->get('payment_corvuspay_merchant')),
            'order_number'            => (string)(int)$order_info['order_id'],
            'language'                => $language,
            'currency'                => strtoupper((string)$order_info['currency_code']),
            'amount'                  => number_format((float)$order_info['total'], 2, '.', ''),
            'cart'                    => $this->limitUtf8('Web shop kupnja - ' . (int)$order_info['order_id'], 255),
            'require_complete'        => $authorisation_type === '0' ? 'true' : 'false',
            'cardholder_country_code' => $country_code,
            'success_url'             => $this->url->link('extension/payment/corvuspay/callback', '', true),
            'cancel_url'              => $this->url->link('checkout/checkout', '', true)
        );

        $optional_parameters = array(
            'cardholder_name'     => $this->limitUtf8((string)$order_info['payment_firstname'], 40),
            'cardholder_surname'  => $this->limitUtf8((string)$order_info['payment_lastname'], 40),
            'cardholder_address'  => $this->limitUtf8((string)$order_info['payment_address_1'], 100),
            'cardholder_city'     => $this->limitUtf8((string)$order_info['payment_city'], 20),
            'cardholder_zip_code' => $this->limitUtf8((string)$order_info['payment_postcode'], 9),
            'cardholder_email'    => $this->limitUtf8((string)$order_info['email'], 100)
        );

        foreach ($optional_parameters as $name => $value) {
            if ($value !== '') {
                $parameters[$name] = $value;
            }
        }

        ksort($parameters, SORT_STRING);

        $data['parameters'] = $parameters;
        $data['signature'] = $this->calculateSignature(
            $parameters,
            trim((string)$this->config->get('payment_corvuspay_password'))
        );

        return $this->load->view('extension/payment/corvuspay', $data);
    }

    public function callback() {
        $this->load->model('checkout/order');

        $callback_data = $this->request->get;
        $received_signature = isset($callback_data['signature'])
            ? strtolower(trim((string)$callback_data['signature']))
            : '';

        unset($callback_data['route'], $callback_data['signature']);

        if ($received_signature === '') {
            $this->rejectCallback('403 Forbidden', 'Missing CorvusPay signature.');
            return;
        }

        $expected_signature = $this->calculateSignature(
            $callback_data,
            trim((string)$this->config->get('payment_corvuspay_password'))
        );

        if (!hash_equals(strtolower($expected_signature), $received_signature)) {
            $this->rejectCallback('403 Forbidden', 'Invalid CorvusPay signature.');
            return;
        }

        $order_number = isset($callback_data['order_number'])
            ? (string)$callback_data['order_number']
            : '';
        $approval_code = isset($callback_data['approval_code'])
            ? trim((string)$callback_data['approval_code'])
            : '';

        if ($order_number === '' || !ctype_digit($order_number) || $approval_code === '') {
            $this->rejectCallback('400 Bad Request', 'Invalid CorvusPay success response.');
            return;
        }

        $order_id = (int)$order_number;
        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!$order_info) {
            $this->rejectCallback('404 Not Found', 'Order not found.');
            return;
        }

        if (isset($order_info['payment_code']) && $order_info['payment_code'] !== 'corvuspay') {
            $this->rejectCallback('400 Bad Request', 'Invalid payment method.');
            return;
        }

        $paid_status_id = (int)$this->config->get('payment_corvuspay_order_status_id');

        if ($paid_status_id < 1) {
            $this->rejectCallback('500 Internal Server Error', 'CorvusPay order status is not configured.');
            return;
        }

        if ((int)$order_info['order_status_id'] !== $paid_status_id) {
            $this->model_checkout_order->addOrderHistory($order_id, $paid_status_id, '', true);
        }

        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    private function calculateSignature(array $parameters, $secret_key) {
        ksort($parameters, SORT_STRING);
        $message = '';

        foreach ($parameters as $name => $value) {
            $message .= $name . (string)$value;
        }

        return hash_hmac('sha256', $message, $secret_key);
    }

    private function limitUtf8($value, $length) {
        $value = trim((string)$value);

        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $length, 'UTF-8');
        }

        return substr($value, 0, $length);
    }

    private function rejectCallback($status, $message) {
        $protocol = isset($this->request->server['SERVER_PROTOCOL'])
            ? $this->request->server['SERVER_PROTOCOL']
            : 'HTTP/1.1';

        $this->response->addHeader($protocol . ' ' . $status);
        $this->response->setOutput($message);
    }
}
