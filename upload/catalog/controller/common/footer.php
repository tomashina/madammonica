<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');
		$footer_labels = array(
			'hr-hr' => array('email' => 'Pošaljite email', 'top' => 'Na vrh'),
			'en-gb' => array('email' => 'Send an email', 'top' => 'Back to top'),
			'de-de' => array('email' => 'E-Mail senden', 'top' => 'Nach oben')
		);
		$language_code = isset($this->session->data['language']) ? $this->session->data['language'] : 'hr-hr';
		$data['floating_labels'] = isset($footer_labels[$language_code]) ? $footer_labels[$language_code] : $footer_labels['hr-hr'];

		// Repair legacy custom footer links that were saved without a URI scheme.
		if (!empty($data['basel_footer_columns'])) {
			foreach ($data['basel_footer_columns'] as &$column) {
				if (empty($column['links'])) {
					continue;
				}

				foreach ($column['links'] as &$link) {
					$target = isset($link['target']) ? trim($link['target']) : '';
					if ($target && strpos($target, '@') !== false && strpos($target, ':') === false) {
						$link['target'] = 'mailto:' . $target;
					} elseif (stripos($target, 'tel:') === 0) {
						$link['target'] = 'tel:' . preg_replace('/[^0-9+]/', '', substr($target, 4));
					} elseif ($target === '#') {
						$link['target'] = '';
					} elseif ($target) {
						$link['target'] = $this->localizeInternalUrl($target);
					}
				}
				unset($link);
			}
			unset($column);
		}

		$data['cookie_consent'] = $this->load->controller('extension/module/madam_cookie_consent');
		
		return $this->load->view('common/footer', $data);
	}

	private function localizeInternalUrl($target) {
		$keyword = trim(rawurldecode((string)parse_url($target, PHP_URL_PATH)), '/');
		$query = $this->db->query("SELECT localized.keyword FROM " . DB_PREFIX . "seo_url source JOIN " . DB_PREFIX . "seo_url localized ON localized.`query` = source.`query` AND localized.store_id = source.store_id WHERE source.keyword = '" . $this->db->escape($keyword) . "' AND source.store_id = '" . (int)$this->config->get('config_store_id') . "' AND localized.language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");

		if (!$query->num_rows || !$query->row['keyword']) {
			return $target;
		}

		$url = rtrim($this->config->get('config_ssl'), '/') . '/' . ltrim($query->row['keyword'], '/');
		if ($this->session->data['language'] !== $this->config->get('config_language')) {
			$url .= '?language=' . rawurlencode($this->session->data['language']);
		}

		return $url;
	}
}
