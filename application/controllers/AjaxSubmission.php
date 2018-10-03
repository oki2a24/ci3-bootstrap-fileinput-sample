<?php
class AjaxSubmission extends CI_Controller {
	const UPLOAD_PATH = './uploads/ajaxsubmission/';

	public function __construct() {
		parent::__construct();
		$this->load->helper('url');
	}

	/**
	 * ファイルを削除します。
	 * ファイルは複数ではなく、1つのみ受け取ります。
	 *
	 * @return void
	 */
	public function delete()
	{
		$posted = $this->input->post();
		// ファイル削除
		$is_deleted = unlink(self::UPLOAD_PATH . $posted['key']);

		// 結果のレスポンス
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

	/**
	 * ファイルをアップロードします。
	 * ファイルは複数ではなく、1つのみ受け取ります。
	 *
	 * @return void
	 */
	public function upload()
	{
		// 受け取ったファイルをアップロードできるように調整
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

		// ファイルアップロード
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

		// アップロードしたファイルの情報から HTML へ渡すデータを生成し、レスポンス
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
					'target_file_name' => $target_file['name'],
				)
			))
		), JSON_UNESCAPED_SLASHES);
		$this->output->set_content_type('application/json', 'utf-8')->set_output($output);
	}

	/**
	 * ページ初期表示時
	 *
	 * @return void
	 */
	public function index()
	{
		// サーバのファイル情報を取得
		$this->load->helper('file');
		$dir_file_info = get_dir_file_info(self::UPLOAD_PATH);

		// HTML に渡す initialPreview オプション、initialPreviewConfig オプションデータ生成
		$initial_preview_json_array = [];
		$initial_preview_config_json_array = [];
		foreach ($dir_file_info as $file_name => $v) {
			$initial_preview_json_array[] = sprintf('%1$s%2$s%3$s', base_url(), self::UPLOAD_PATH, $file_name);

			$mime_by_extension = get_mime_by_extension($file_name);
			$initial_preview_config_json_array[] = [
				'filetype' => $mime_by_extension,
				'caption' => $file_name,
				'size' => $v['size'],
				'url' => sprintf('%1$s/ajaxsubmission/delete', site_url()),
				'key' => $file_name, 
				'extra' => [
					'target_file_name' => $file_name
				]
			];
		}
		$initial_preview_json = json_encode($initial_preview_json_array, JSON_UNESCAPED_SLASHES);
		$initial_preview_config_json = json_encode($initial_preview_config_json_array, JSON_UNESCAPED_SLASHES);

		// HTML へ渡すデータ組み立て、レスポンス
		$data = [
			'fileinput_init' => [
				'initialPreview' => 'initialPreview: ' . $initial_preview_json,
				'initialPreviewConfig' => 'initialPreviewConfig: ' . $initial_preview_config_json,
			],
		];
		$this->load->view('ajaxsubmission/index', $data);
	}
}
