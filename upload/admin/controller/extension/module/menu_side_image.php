<?php
class ControllerExtensionModuleMenuSideImage extends Controller {
	private $error = array();
	private $route = 'extension/module/menu_side_image';
	private $code = 'module_menu_side_image';
	private $default_image = 'catalog/banneri/bnr1.jpeg';
	private $default_link = '/haljine';

	public function index() {
		$this->load->language($this->route);

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->request->post[$this->code . '_status'] = isset($this->request->post[$this->code . '_status']) ? (int)$this->request->post[$this->code . '_status'] : 0;

			$this->model_setting_setting->editSetting($this->code, $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->route, 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

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
			'href' => $this->url->link($this->route, 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link($this->route, 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);

		$image = $this->getValue($this->code . '_image', $this->default_image);
		$link = $this->getValue($this->code . '_link', $this->default_link);
		$status = $this->getValue($this->code . '_status', 1);

		$this->load->model('tool/image');

		if ($image && is_file(DIR_IMAGE . $image)) {
			$thumb = $this->model_tool_image->resize($image, 100, 100);
		} else {
			$thumb = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_link'] = $this->language->get('entry_link');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['help_image'] = $this->language->get('help_image');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['image'] = $image;
		$data['thumb'] = $thumb;
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['link'] = $link;
		$data['status'] = $status;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->route, $data));
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting($this->code, array(
			$this->code . '_status' => 1,
			$this->code . '_image' => $this->default_image,
			$this->code . '_link' => $this->default_link
		));

		$this->load->model('user/user_group');
		$user_group_id = $this->user->getGroupId();

		$this->model_user_user_group->addPermission($user_group_id, 'access', $this->route);
		$this->model_user_user_group->addPermission($user_group_id, 'modify', $this->route);
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting($this->code);

		$this->load->model('user/user_group');
		$user_group_id = $this->user->getGroupId();

		$this->model_user_user_group->removePermission($user_group_id, 'access', $this->route);
		$this->model_user_user_group->removePermission($user_group_id, 'modify', $this->route);
	}

	private function getValue($key, $default = '') {
		if (isset($this->request->post[$key])) {
			return $this->request->post[$key];
		}

		$value = $this->config->get($key);

		return ($value === null || $value === '') ? $default : $value;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->route) && !$this->user->hasPermission('modify', 'setting/setting') && (int)$this->user->getGroupId() !== 1) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
