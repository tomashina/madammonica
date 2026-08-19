<?php
class ControllerCommonHome extends Controller {
	public function index() {
		$language_code = $this->session->data['language'];
		$seo = array(
			'hr-hr' => array(
				'title' => 'Madam Monica | Dizajnerske haljine i sako-kaputići',
				'description' => 'Otkrijte ženske haljine, sako-kaputiće, bluze i statement komade Madam Monica. Profinjeni krojevi, posebni materijali i sigurna online kupnja.',
				'heading' => 'Dizajnerska ženska moda Madam Monica',
				'intro' => 'U kolekciji Madam Monica pronađite haljine, sako-kaputiće, bluze i statement modele naglašenih silueta, posebnih uzoraka i pažljivo odabranih materijala. Istražite poslovne, svečane, retro i unikatne komade te uz vodič za veličine odaberite model koji vam najbolje pristaje.'
			),
			'en-gb' => array(
				'title' => 'Madam Monica | Designer Dresses and Blazer Coats',
				'description' => 'Discover Madam Monica women’s dresses, blazer coats, blouses and statement pieces with refined cuts, distinctive fabrics and secure online shopping.',
				'heading' => 'Madam Monica Designer Womenswear',
				'intro' => 'Discover Madam Monica dresses, blazer coats, blouses and statement pieces defined by feminine silhouettes, distinctive patterns and carefully selected fabrics. Explore business, occasion, retro and one-of-a-kind designs, then use our size guide to find the right fit.'
			),
			'de-de' => array(
				'title' => 'Madam Monica | Designerkleider und Blazermäntel',
				'description' => 'Entdecken Sie Damenkleider, Blazermäntel, Blusen und Statement-Pieces von Madam Monica mit raffinierten Schnitten und besonderen Stoffen.',
				'heading' => 'Designer-Damenmode von Madam Monica',
				'intro' => 'Entdecken Sie Kleider, Blazermäntel, Blusen und Statement-Pieces von Madam Monica mit femininen Silhouetten, besonderen Mustern und sorgfältig ausgewählten Stoffen. Finden Sie Business-, Anlass-, Retro- und Einzelstücke und wählen Sie mithilfe unseres Größenratgebers die passende Größe.'
			)
		);

		$current_seo = isset($seo[$language_code]) ? $seo[$language_code] : $seo['hr-hr'];
		$this->document->setTitle($current_seo['title']);
		$this->document->setDescription($current_seo['description']);
		$this->document->setKeywords('');

		$canonical = rtrim($this->config->get('config_ssl'), '/') . '/';
		if ($language_code !== $this->config->get('config_language')) {
			$canonical .= '?language=' . rawurlencode($language_code);
		}
		$this->document->addLink($canonical, 'canonical');

		$data['seo_heading'] = $current_seo['heading'];
		$data['seo_intro'] = $current_seo['intro'];

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/home', $data));
	}
}
