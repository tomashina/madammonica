<?php
class ControllerExtensionFeedGoogleSitemap extends Controller {
	public function index() {
		if (!$this->config->get('feed_google_sitemap_status')) {
			return;
		}

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/information');
		$this->load->model('localisation/language');
		$this->load->model('tool/image');

		$languages = $this->model_localisation_language->getLanguages();
		$original_language_id = $this->config->get('config_language_id');
		$original_language_code = $this->session->data['language'];
		$output = '<?xml version="1.0" encoding="UTF-8"?>';
		$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

		foreach ($languages as $language) {
			if (!$language['status']) {
				continue;
			}

			$this->config->set('config_language_id', (int)$language['language_id']);
			$this->session->data['language'] = $language['code'];

			$home = rtrim($this->config->get('config_ssl'), '/') . '/';
			if ($language['code'] !== $this->config->get('config_language')) {
				$home .= '?language=' . rawurlencode($language['code']);
			}
			$output .= $this->urlEntry($home);

			foreach ($this->model_catalog_product->getProducts() as $product) {
				$url = $this->url->link('product/product', 'product_id=' . (int)$product['product_id']);
				$image = '';
				if ($product['image']) {
					$image = $this->model_tool_image->resize(
						$product['image'],
						$this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'),
						$this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')
					);
				}
				$output .= $this->urlEntry($url, $product['date_modified'], $image, $product['name']);
			}

			$output .= $this->categoryEntries(0);

			foreach ($this->model_catalog_information->getInformations() as $information) {
				$output .= $this->urlEntry($this->url->link('information/information', 'information_id=' . (int)$information['information_id']));
			}

			$output .= $this->urlEntry($this->url->link('information/contact'));
		}

		$this->config->set('config_language_id', $original_language_id);
		$this->session->data['language'] = $original_language_code;
		$output .= '</urlset>';

		$this->response->addHeader('Content-Type: application/xml; charset=utf-8');
		$this->response->setOutput($output);
	}

	private function categoryEntries($parent_id, $current_path = '') {
		$output = '';
		$categories = $this->model_catalog_category->getCategories($parent_id);

		foreach ($categories as $category) {
			$path = $current_path ? $current_path . '_' . (int)$category['category_id'] : (int)$category['category_id'];
			$output .= $this->urlEntry($this->url->link('product/category', 'path=' . $path));
			$output .= $this->categoryEntries($category['category_id'], $path);
		}

		return $output;
	}

	private function urlEntry($url, $last_modified = '', $image = '', $image_title = '') {
		$output = '<url><loc>' . $this->xml($url) . '</loc>';

		if ($last_modified && strtotime($last_modified)) {
			$output .= '<lastmod>' . date('c', strtotime($last_modified)) . '</lastmod>';
		}

		if ($image) {
			$output .= '<image:image><image:loc>' . $this->xml($image) . '</image:loc>';
			if ($image_title) {
				$output .= '<image:caption>' . $this->xml($image_title) . '</image:caption>';
				$output .= '<image:title>' . $this->xml($image_title) . '</image:title>';
			}
			$output .= '</image:image>';
		}

		return $output . '</url>';
	}

	private function xml($value) {
		return htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_XML1 | ENT_COMPAT, 'UTF-8');
	}
}
