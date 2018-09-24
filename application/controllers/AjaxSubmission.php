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

		// $config['upload_path'] = self::UPLOAD_PATH;
		// $config['allowed_types'] = 'gif|jpg|png|pdf|mp4';
		// $config['overwrite'] = TRUE;
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

		$initial_preview = sprintf(
			'<img src="%1$s%2$s%3$s" class="file-preview-image" title="%3$s" alt="%3$s" style="width:auto;height:auto;max-width:100%%;max-height:100%%;">',
			base_url(), self::UPLOAD_PATH, $target_file['name']
		);
		$this->output->set_content_type('application/json', 'utf-8')
		->set_output(json_encode(array(
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
		)));
	}

	public function index()
	{
		$data = json_encode(array(
			'initialPreview' => array(''),
			'initialPreviewConfig' => array(array(
				'filetype' => '',
				'caption' => '',
				'size' => 1,
				'url' => sprintf('%1$s/ajaxsubmission/delete', site_url()),
				'key' => '', 
				'extra' => array(
					'target_file' => ''
				)
			)),
			'append' => true,
		));
		
		$this->load->view('ajaxsubmission/index', $data);
	}
}
