<?php
class ControllerCommonLanguage extends Controller {
	public function index() {
		$this->load->language('common/language');

		$data['action'] = $this->url->link('common/language/language', '', $this->request->server['HTTPS']);

		$data['code'] = $this->session->data['language'];

		$this->load->model('localisation/language');

		$data['languages'] = array();

		$results = $this->model_localisation_language->getLanguages();
		$route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';
		$url_data = $this->request->get;
		unset($url_data['_route_'], $url_data['route'], $url_data['language']);

		foreach ($results as $result) {
			if ($result['status']) {
				$data['languages'][] = array(
					'name' => $result['name'],
					'code' => $result['code'],
					'href' => $this->localizedLink($route, $url_data, $result)
				);
			}
		}

		if (!isset($this->request->get['route'])) {
			$data['redirect'] = $this->url->link('common/home');
		} else {
			$url_data = $this->request->get;

			unset($url_data['_route_']);

			$route = $url_data['route'];

			unset($url_data['route']);

			$url = '';

			if ($url_data) {
				$url = '&' . urldecode(http_build_query($url_data, '', '&'));
			}

			$data['redirect'] = $this->url->link($route, $url, $this->request->server['HTTPS']);
		}

		return $this->load->view('common/language', $data);
	}

	private function localizedLink($route, $url_data, $language) {
		$base = rtrim($this->config->get('config_ssl'), '/') . '/';
		// A switch must always carry an explicit code, including the default HR
		// language, otherwise an existing EN/DE session immediately overrides it.
		$language_query = 'language=' . rawurlencode($language['code']);
		$keyword = '';

		if ($route === 'common/home') {
			return $base . '?' . $language_query;
		}

		$query_key = '';
		if ($route === 'product/product' && isset($url_data['product_id'])) {
			$query_key = 'product_id=' . (int)$url_data['product_id'];
		} elseif ($route === 'information/information' && isset($url_data['information_id'])) {
			$query_key = 'information_id=' . (int)$url_data['information_id'];
		} elseif ($route === 'product/manufacturer/info' && isset($url_data['manufacturer_id'])) {
			$query_key = 'manufacturer_id=' . (int)$url_data['manufacturer_id'];
		} elseif ($route === 'product/category' && isset($url_data['path'])) {
			$parts = explode('_', (string)$url_data['path']);
			$query_key = 'category_id=' . (int)array_pop($parts);
		} else {
			$query_key = $route;
		}

		if ($query_key) {
			$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($query_key) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$language['language_id'] . "' LIMIT 1");
			if ($query->num_rows) {
				$keyword = $query->row['keyword'];
			}
		}

		if ($keyword) {
			return $base . ltrim($keyword, '/') . '?' . $language_query;
		}

		$url_data['route'] = $route;
		$url_data['language'] = $language['code'];

		return $base . 'index.php?' . http_build_query($url_data, '', '&');
	}

	public function language() {
		if (isset($this->request->post['code'])) {
			$this->session->data['language'] = $this->request->post['code'];
		}

		if (isset($this->request->post['redirect'])) {
			$this->response->redirect($this->request->post['redirect']);
		} else {
			$this->response->redirect($this->url->link('common/home'));
		}
	}
}
