<?php
class AjaxSubmission extends CI_Controller {
	const UPLOAD_PATH = './uploads/ajaxsubmission/';

	public function __construct() {
		parent::__construct();
		$this->load->helper(array('url'));
	}

	public function delete()
	{
		$posted = $this->input->post();
		$is_deleted = unlink(self::UPLOAD_PATH . $posted['key']);
		if (!$is_deleted) {
			$this->output->set_content_type('application/json', 'utf-8')
			->set_output(json_encode(array(
				'error' => '削除できませんでした。',
			)));
			return;
		}
		$this->output->set_content_type('application/json', 'utf-8')
		->set_output('{}');
	}

	public function upload()
	{
		$upload_files = $_FILES['kartik-input-700'];

		$this->load->helper('security');
		$sanitized_filename = sanitize_filename($upload_files['name'][0]);
		$_FILES['target_file'] = array(
			'name' => $sanitized_filename,
			'type' => $upload_files['type'][0],
			'tmp_name' => $upload_files['tmp_name'][0],
			'error' => $upload_files['error'][0],
			'size' => $upload_files['size'][0],
		);
		$target_file = $_FILES['target_file'];

		$config = array(
			'upload_path' => self::UPLOAD_PATH,
			'allowed_types' => 'gif|jpg|png|pdf|mp4',
			'overwrite' => TRUE,
		);
		$this->load->library('upload', $config);
		if (!$this->upload->do_upload('target_file')) {
			$this->output->set_content_type('application/json', 'utf-8')
			->set_output(json_encode(array(
				'error' => $this->upload->display_errors(),
			)));
			return;
		}

		// $initial_preview = sprintf(
		// 	'<img src="%1$s%2$s%3$s" class="file-preview-image" title="%3$s" alt="%3$s" style="width:auto;height:auto;max-width:100%%;max-height:100%%;">',
		// 	base_url(), self::UPLOAD_PATH, $target_file['name']
		// );
		$initial_preview = sprintf(
			'%1$s%2$s%3$s',
			base_url(), self::UPLOAD_PATH, $target_file['name']
		);
		$output = json_encode(array(
			'initialPreview' => array($initial_preview),
			'initialPreviewConfig' => array(array(
				'filetype' => $target_file['type'],
				'caption' => $target_file['name'],
				'size' => $target_file['size'],
				'url' => sprintf('%1$s/ajaxsubmission/delete', site_url()),
				'key' => $target_file['name'], 
				'extra' => array(
					'target_file' => $target_file
				)
			)),
			'append' => true,
		));
		$this->output->set_content_type('application/json', 'utf-8')->set_output($output);
	}

	public function index()
	{
		$initial_preview_json = json_encode(array(
			sprintf('%1$s%2$s%3$s', base_url(), self::UPLOAD_PATH, 'Exif-Landscape-1.jpg'),
			sprintf('%1$s%2$s%3$s', base_url(), self::UPLOAD_PATH, 'Exif-Landscape-2.jpg'),
			sprintf('%1$s%2$s%3$s', base_url(), self::UPLOAD_PATH, 'Exif-Landscape-3.jpg')
		), JSON_UNESCAPED_SLASHES);
		$initial_preview_config_json = json_encode(array(
			array(
				'filetype' => 'image/jpeg',
				'caption' => 'Exif-Landscape-1.jpg',
				'size' => 139435,
				'url' => sprintf('%1$s/ajaxsubmission/delete', site_url()),
				'key' => 'Exif-Landscape-1.jpg', 
				'extra' => array(
					'target_file' => 'Exif-Landscape-1.jpg'
				)
			),
			array(
				'filetype' => 'image/jpeg',
				'caption' => 'Exif-Landscape-2.jpg',
				'size' => 139435,
				'url' => sprintf('%1$s/ajaxsubmission/delete', site_url()),
				'key' => 'Exif-Landscape-2.jpg', 
				'extra' => array(
					'target_file' => 'Exif-Landscape-2.jpg'
				)
			),
			array(
				'filetype' => 'image/jpeg',
				'caption' => 'Exif-Landscape-3.jpg',
				'size' => 139435,
				'url' => sprintf('%1$s/ajaxsubmission/delete', site_url()),
				'key' => 'Exif-Landscape-3.jpg', 
				'extra' => array(
					'target_file' => 'Exif-Landscape-3.jpg'
				)
			),
		), JSON_UNESCAPED_SLASHES);
		// var_dump($initial_preview_config_json);
		
		$data = array(
			// 'fileinput_init' => 'initialPreviewAsData: true,',
			// 'fileinput_init' => '',
			'fileinput_init' => array(
				// 'initialPreviewAsData' => 'initialPreviewAsData: true,',
				// 'initialPreviewAsData' => '',
				// 'initialPreview' => 'initialPreview: ["http://localhost/ci3-bootstrap-fileinput-sample/./uploads/ajaxsubmission/Exif-Landscape-1.jpg"]',
				'initialPreview' => 'initialPreview: ' . $initial_preview_json,
				'initialPreviewConfig' => 'initialPreviewConfig: ' . $initial_preview_config_json,
			),
			// 'fileinput_init' => json_encode(array(
			// 	'initialPreview' => array($initial_preview),
				// 'initialPreviewConfig' => array(array(
				// 	'filetype' => 'image/jpeg',
				// 	'caption' => 'Exif-Landscape-1.jpg',
				// 	'size' => 139435,
				// 	'url' => sprintf('%1$s/ajaxsubmission/delete', site_url()),
				// 	'key' => 'Exif-Landscape-1.jpg', 
				// 	'extra' => array(
				// 		'target_file' => 'Exif-Landscape-1.jpg'
				// 	)
				// )),
			// 	'append' => true,
			// )),
		);
		$this->load->view('ajaxsubmission/index', $data);
	}
}
