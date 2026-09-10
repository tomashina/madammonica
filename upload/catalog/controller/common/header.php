<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		// Non-essential analytics markup is injected only after explicit consent.
		$data['analytics_encoded'] = $data['analytics'] ? base64_encode(implode("\n", $data['analytics'])) : '';
		$data['analytics'] = array();
		$data['meta_pixel_id'] = '1465776258750301';

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$route = isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home';
		$noindex_prefixes = array('account/', 'affiliate/', 'api/', 'checkout/', 'tool/', 'common/language', 'error/');
		$noindex_routes = array('product/search', 'product/compare', 'account/return/add');
		$noindex = in_array($route, $noindex_routes, true);

		foreach ($noindex_prefixes as $prefix) {
			if (strpos($route, $prefix) === 0) {
				$noindex = true;
				break;
			}
		}

		if ($route === 'product/category' && (isset($this->request->get['filter']) || isset($this->request->get['sort']) || isset($this->request->get['order']) || isset($this->request->get['limit']) || isset($this->request->get['page']))) {
			$noindex = true;
		}

		// Controllers render their 404 template through the original route, so the
		// route name alone cannot identify a missing product/category/page.
		$public_entity = array(
			'product/product' => array('parameter' => 'product_id', 'table' => 'product', 'column' => 'product_id', 'status' => true),
			'product/category' => array('parameter' => 'path', 'table' => 'category', 'column' => 'category_id', 'status' => true),
			'information/information' => array('parameter' => 'information_id', 'table' => 'information', 'column' => 'information_id', 'status' => true),
			'product/manufacturer/info' => array('parameter' => 'manufacturer_id', 'table' => 'manufacturer', 'column' => 'manufacturer_id', 'status' => false)
		);

		if (isset($public_entity[$route])) {
			$entity = $public_entity[$route];
			$value = isset($this->request->get[$entity['parameter']]) ? $this->request->get[$entity['parameter']] : 0;
			if ($entity['parameter'] === 'path') {
				$parts = explode('_', (string)$value);
				$value = array_pop($parts);
			}

			$sql = "SELECT " . $entity['column'] . " FROM " . DB_PREFIX . $entity['table'] . " WHERE " . $entity['column'] . " = '" . (int)$value . "'";
			if ($entity['status']) {
				$sql .= " AND status = '1'";
			}
			if (!$value || !$this->db->query($sql . " LIMIT 1")->num_rows) {
				$noindex = true;
			}
		}

		$data['robots'] = $noindex ? 'noindex,follow' : 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
		if ($noindex) {
			$this->response->addHeader('X-Robots-Tag: noindex, follow');
		}

		$data['canonical_url'] = '';
		foreach ($data['links'] as $link) {
			if ($link['rel'] === 'canonical') {
				$data['canonical_url'] = html_entity_decode($link['href'], ENT_QUOTES, 'UTF-8');
				break;
			}
		}

		$data['seo_alternates'] = $noindex ? array() : $this->getLocalizedAlternates($route, $server);
		$data['og_type'] = ($route === 'product/product') ? 'product' : 'website';
		$data['og_locale'] = $this->getOgLocale($this->session->data['language']);
		$data['og_url'] = $data['canonical_url'] ? $data['canonical_url'] : $this->currentUrl();

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$data['og_image'] = $data['logo'];
		foreach ($data['links'] as $link) {
			if ($link['rel'] === 'image') {
				$data['og_image'] = html_entity_decode($link['href'], ENT_QUOTES, 'UTF-8');
				break;
			}
		}

		if (isset($data['opengraphs']) && is_array($data['opengraphs'])) {
			$data['opengraphs'] = $this->mergeSocialMetadata($data['opengraphs'], 'meta', array(
				'og:title' => $data['title'],
				'og:description' => $data['description'],
				'og:type' => $data['og_type'],
				'og:url' => $data['og_url'],
				'og:site_name' => $this->config->get('config_name'),
				'og:locale' => $data['og_locale'],
				'og:image' => $data['og_image']
			));
		}
		if (isset($data['twittercards']) && is_array($data['twittercards'])) {
			$data['twittercards'] = $this->mergeSocialMetadata($data['twittercards'], 'name', array(
				'twitter:card' => 'summary_large_image',
				'twitter:title' => $data['title'],
				'twitter:description' => $data['description'],
				'twitter:image' => $data['og_image']
			));
		}

		$home_url = rtrim($server, '/') . '/';
		$organization = array(
			'@type' => 'OnlineStore',
			'@id' => $home_url . '#organization',
			'name' => $this->config->get('config_name'),
			'legalName' => 'Madam Monica d.o.o.',
			'url' => $home_url,
			'email' => $this->config->get('config_email'),
			'telephone' => $this->config->get('config_telephone')
		);
		if ($data['logo']) {
			$organization['logo'] = array('@type' => 'ImageObject', 'url' => $data['logo']);
		}
		if ($this->config->get('config_address')) {
			$organization['address'] = array(
				'@type' => 'PostalAddress',
				'streetAddress' => trim(preg_replace('/\s+/', ' ', $this->config->get('config_address'))),
				'addressCountry' => 'HR'
			);
		}
		$data['site_schema'] = json_encode(array(
			'@context' => 'https://schema.org',
			'@graph' => array(
				$organization,
				array('@type' => 'WebSite', '@id' => $home_url . '#website', 'url' => $home_url, 'name' => $this->config->get('config_name'), 'publisher' => array('@id' => $home_url . '#organization'))
			)
		), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');

		$menu_side_image_status = $this->config->get('module_menu_side_image_status');
		$menu_side_image = $this->config->get('module_menu_side_image_image');
		$menu_side_image_category_id = (int)$this->config->get('module_menu_side_image_category_id');

		if (!$menu_side_image) {
			$menu_side_image = 'catalog/banneri/bnr1.jpeg';
		}

		if (!$menu_side_image_category_id) {
			$menu_side_image_category_id = 258;
		}

		$this->load->model('catalog/category');
		$menu_side_image_category = $this->model_catalog_category->getCategory($menu_side_image_category_id);

		$data['menu_side_image_status'] = ($menu_side_image_status === null) ? true : (bool)$menu_side_image_status;
		$data['menu_side_image'] = is_file(DIR_IMAGE . $menu_side_image) ? $server . 'image/' . $menu_side_image : '';
		$data['menu_side_image_link'] = $menu_side_image_category ? $this->url->link('product/category', 'path=' . $menu_side_image_category_id) : $this->url->link('common/home');
		$data['menu_side_image_alt'] = $menu_side_image_category ? $menu_side_image_category['name'] . ' – ' . $this->config->get('config_name') : $this->config->get('config_name');

		if (!empty($data['top_promo_text'])) {
			$collection_url = $this->url->link('product/category', 'path=259');
			$data['top_promo_text'] = preg_replace_callback('/(href=("|\'))[^"\']+(\2)/i', function($matches) use ($collection_url) {
				return $matches[1] . $collection_url . $matches[3];
			}, $data['top_promo_text']);
		}
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');

		return $this->load->view('common/header', $data);
	}

	private function getLocalizedAlternates($route, $server) {
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$alternates = array();
		$default_href = '';

		foreach ($languages as $language) {
			if (!$language['status']) {
				continue;
			}

			$href = $this->localizedPageUrl($route, $server, $language);
			if (!$href) {
				continue;
			}

			$hreflang = $language['code'] === 'hr-hr' ? 'hr-HR' : ($language['code'] === 'de-de' ? 'de-DE' : 'en');
			$alternates[] = array('hreflang' => $hreflang, 'href' => $href);
			if ($language['code'] === $this->config->get('config_language')) {
				$default_href = $href;
			}
		}

		if ($default_href) {
			$alternates[] = array('hreflang' => 'x-default', 'href' => $default_href);
		}

		return $alternates;
	}

	private function mergeSocialMetadata($items, $key, $fallbacks) {
		$existing = array();
		foreach ($items as $item) {
			if (!empty($item[$key])) {
				$existing[$item[$key]] = true;
			}
		}

		foreach ($fallbacks as $name => $content) {
			if ($content !== '' && !isset($existing[$name])) {
				$items[] = array($key => $name, 'content' => $content);
			}
		}

		return $items;
	}

	private function localizedPageUrl($route, $server, $language) {
		$base = rtrim($server, '/') . '/';
		$language_query = ($language['code'] === $this->config->get('config_language')) ? '' : '?language=' . rawurlencode($language['code']);

		if ($route === 'common/home') {
			return $base . ltrim($language_query, '/');
		}

		$query_key = '';
		if ($route === 'product/product' && isset($this->request->get['product_id'])) {
			$query_key = 'product_id=' . (int)$this->request->get['product_id'];
		} elseif ($route === 'information/information' && isset($this->request->get['information_id'])) {
			$query_key = 'information_id=' . (int)$this->request->get['information_id'];
		} elseif ($route === 'product/manufacturer/info' && isset($this->request->get['manufacturer_id'])) {
			$query_key = 'manufacturer_id=' . (int)$this->request->get['manufacturer_id'];
		} elseif ($route === 'product/category' && isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
			$query_key = 'category_id=' . (int)array_pop($parts);
		} elseif ($route === 'information/contact') {
			$query_key = 'information/contact';
		} else {
			return '';
		}

		$query = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = '" . $this->db->escape($query_key) . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "' AND language_id = '" . (int)$language['language_id'] . "' LIMIT 1");
		if (!$query->num_rows || !$query->row['keyword']) {
			return '';
		}

		return $base . ltrim($query->row['keyword'], '/') . $language_query;
	}

	private function getOgLocale($code) {
		if ($code === 'de-de') {
			return 'de_DE';
		}
		if ($code === 'en-gb') {
			return 'en_GB';
		}
		return 'hr_HR';
	}

	private function currentUrl() {
		$scheme = !empty($this->request->server['HTTPS']) ? 'https://' : 'http://';
		$host = isset($this->request->server['HTTP_HOST']) ? $this->request->server['HTTP_HOST'] : '';
		$uri = isset($this->request->server['REQUEST_URI']) ? $this->request->server['REQUEST_URI'] : '/';
		return $scheme . $host . $uri;
	}
}
