<?php
class Ajaxsynchronous extends CI_Controller {
	const UPLOAD_PATH = './uploads/ajaxsynchronous/';

	public function __construct() {
		parent::__construct();
		$this->load->helper('url');
	}

	/**
	 * ページ初期表示時
	 *
	 * @return void
	 */
	public function index()
	{
		// var_dump($this->input->post());
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
				'url' => sprintf('%1$s/ajaxsynchronous/delete', site_url()),
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
		$this->load->view('ajaxsynchronous/index', $data);
	}

	/**
	 * ファイルをアップロードします。
	 * ファイルは複数受け取ります。
	 *
	 * @return void
	 */
	public function upload()
	{
		// $this->output->enable_profiler(TRUE);

		$this->load->helper('security');
		$config = array(
			'upload_path' => self::UPLOAD_PATH,
			'allowed_types' => 'gif|jpg|png|pdf|mp4',
			'overwrite' => TRUE,
		);
		$this->load->library('upload', $config);

		$upload_files = $_FILES['kartik-input-700'];
		$file_count = count($upload_files['name']);
		$errorkeys_array = [];
		$initial_preview_json_array = [];
		$initial_preview_config_json_array = [];
		for ($i = 0; $i < $file_count; $i++) {
			// 受け取ったファイルをアップロードできるように調整
			$sanitized_filename = sanitize_filename($upload_files['name'][$i]);
			$_FILES['target_file'] = array(
				'name' => $sanitized_filename,
				'type' => $upload_files['type'][$i],
				'tmp_name' => $upload_files['tmp_name'][$i],
				'error' => $upload_files['error'][$i],
				'size' => $upload_files['size'][$i],
			);
			$target_file = $_FILES['target_file'];

			// ファイルアップロード
			if (!$this->upload->do_upload('target_file')) {
				$errorkeys_array[] = $target_file['name'];
				// $this->output->set_content_type('application/json', 'utf-8')
				// ->set_output(json_encode(array(
				// 	'error' => $this->upload->display_errors(),
				// )));
				// return;
			}

			// アップロードしたファイルの情報から HTML へ渡すデータを生成
			$initial_preview_json_array[] = sprintf('%1$s%2$s%3$s', base_url(), self::UPLOAD_PATH, $target_file['name']);
			$initial_preview_config_json_array[] = [
				'filetype' => $target_file['type'],
				'caption' => $target_file['name'],
				'size' => $target_file['size'],
				'url' => sprintf('%1$s/ajaxsynchronous/delete', site_url()),
				'key' => $target_file['name'],
				'extra' => [
					'target_file_name' => $target_file['name']
				]
			];
		}
		$initial_preview_json = json_encode($initial_preview_json_array, JSON_UNESCAPED_SLASHES);
		$initial_preview_config_json = json_encode($initial_preview_config_json_array, JSON_UNESCAPED_SLASHES);

		// HTML へ渡すデータ組み立て、レスポンス
		$output_array = [
			'initialPreview' => $initial_preview_json_array,
			'initialPreviewConfig' => $initial_preview_config_json_array
		];
		if (count($errorkeys_array) > 0) {
			$output_array['error'] = 'アップロードに失敗しました';
			$output_array['errorkeys'] = $errorkeys_array;
		}
		$output = json_encode($output_array, JSON_UNESCAPED_SLASHES);
		// echo $output;
		$this->output->set_content_type('application/json', 'utf-8')->set_output($output);
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
}
