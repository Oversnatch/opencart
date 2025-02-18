<?php
namespace Opencart\Admin\Controller\Marketplace;
class Vendor extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('marketplace/vendor');

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketplace/vendor', 'user_token=' . $this->session->data['user_token'] . $url)
		];

		$data['delete'] = $this->url->link('marketplace/vendor|delete', 'user_token=' . $this->session->data['user_token']);

		// Example vendor URL
		$data['vendor'] = $this->url->link('common/vendor');

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketplace/vendor', $data));
	}

	public function list(): void {
		$this->load->language('marketplace/vendor');

		$this->response->setOutput($this->getList());
	}

	public function getList(): string {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link('marketplace/vendor|list', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['vendors'] = [];

		$filter_data = [
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit' => $this->config->get('config_pagination_admin')
		];

		$this->load->model('setting/vendor');

		$vendor_total = $this->model_setting_vendor->getTotalVendors();

		$results = $this->model_setting_vendor->getVendors($filter_data);

		foreach ($results as $result) {
			$data['vendors'][] = [
				'vendor_id'  => $result['vendor_id'],
				'name'       => $result['name'],
				'code'       => $result['code'],
				'version'    => $result['version'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
			];
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('marketplace/vendor|list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_code'] = $this->url->link('marketplace/vendor|list', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url);
		$data['sort_version'] = $this->url->link('marketplace/vendor|list', 'user_token=' . $this->session->data['user_token'] . '&sort=version' . $url);
		$data['sort_date_added'] = $this->url->link('marketplace/vendor|list', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['pagination'] = $this->load->controller('common/pagination', [
			'total' => $vendor_total,
			'page'  => $page,
			'limit' => $this->config->get('config_pagination_admin'),
			'url'   => $this->url->link('marketplace/vendor|list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}')
		]);

		$data['results'] = sprintf($this->language->get('text_pagination'), ($vendor_total) ? (($page - 1) * $this->config->get('config_pagination_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_pagination_admin')) > ($vendor_total - $this->config->get('config_pagination_admin'))) ? $vendor_total : ((($page - 1) * $this->config->get('config_pagination_admin')) + $this->config->get('config_pagination_admin')), $vendor_total, ceil($vendor_total / $this->config->get('config_pagination_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('marketplace/vendor_list', $data);
	}

	public function refresh(): void {
		$this->load->language('marketplace/vendor');

		$json = [];

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = (int)$this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/installer')) {
			$json['error'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/extension');

		$extension_install_info = $this->model_setting_extension->getInstall($extension_install_id);

		if (!$extension_install_info) {
			$json['error'] = $this->language->get('error_install');
		}

		if (!$json) {

			$directories = glob(DIR_STORAGE . 'vendor/');


			$results = $this->model_setting_extension->getPathsByPath($extension_install_id);

			foreach ($results as $result) {
				$output = file_get_contents($result['path']);

				json_decode($output, true);
			}



			rsort($results);

			foreach ($results as $result) {
				$path = '';

				// Remove extension directory and files
				if (substr($result['path'], 0, strlen($extension_install_info['code'])) == $extension_install_info['code']) {
					$path = DIR_EXTENSION . $result['path'];
				}

				// Remove images
				if (substr($result['path'], 0, 6) == 'image/') {
					$path = DIR_IMAGE . substr($result['path'], 6);
				}

				// Remove vendor files
				if (substr($result['path'], 0, 7) == 'vendor/') {
					$path = DIR_STORAGE . $result['path'];
				}

				// Check if the location exists or not
				if (is_file($path)) {
					unlink($path);
				} elseif (is_dir($path)) {
					rmdir($path);
				}

				$this->model_setting_extension->deletePath($result['extension_path_id']);
			}

			// Remove extension directory
			$this->model_setting_extension->editStatus($extension_install_id, 0);

			$json['success'] = $this->language->get('text_uninstall');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
