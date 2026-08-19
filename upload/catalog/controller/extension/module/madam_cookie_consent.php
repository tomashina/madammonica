<?php
class ControllerExtensionModuleMadamCookieConsent extends Controller {
	public function index() {
		$this->load->language('extension/module/madam_cookie_consent');

		$this->document->addStyle('catalog/view/theme/basel/stylesheet/madam-cookie-consent.css?v=1.2.1');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_description'] = $this->language->get('text_description');
		$data['text_privacy'] = $this->language->get('text_privacy');
		$data['text_settings'] = $this->language->get('text_settings');
		$data['text_necessary'] = $this->language->get('text_necessary');
		$data['text_necessary_description'] = $this->language->get('text_necessary_description');
		$data['text_analytics'] = $this->language->get('text_analytics');
		$data['text_analytics_description'] = $this->language->get('text_analytics_description');
		$data['text_marketing'] = $this->language->get('text_marketing');
		$data['text_marketing_description'] = $this->language->get('text_marketing_description');
		$data['button_accept_all'] = $this->language->get('button_accept_all');
		$data['button_necessary'] = $this->language->get('button_necessary');
		$data['button_preferences'] = $this->language->get('button_preferences');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_close'] = $this->language->get('button_close');
		$data['privacy_url'] = $this->url->link('information/information', 'information_id=6');

		return $this->load->view('extension/module/madam_cookie_consent', $data);
	}
}
