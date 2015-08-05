<?php
/**
* ページング用URLの作成
* @param:max_count 最大件数
* @param:max_count 現表示ページ数
* @param:limit 最大表示件数
* @return:array
*/
function getPagerUrls($max_count, $curent_page = 1, $limit = 20){
	$page_num = (int)($max_count / $limit);

	$res = array();
	$base_url = parse_url('http://' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	$base_url = (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . 
					$base_url['host'] . $base_url['path'] . '?';

	foreach ($_GET as $key => $value) {
		if($key !== 'page')$base_url .= "{$key}={$value}&";
	}

	$res['all'] = array();
	if($curent_page > 1)
		$res['prev'] = $base_url . "page=" . ($curent_page - 1);
	if($curent_page < $page_num)
		$res['next'] = $base_url . "page=" . ($curent_page + 1);
	for ($i=1; $i <= $page_num; $i++) {
		$res['all'][$i] = ($i != $curent_page) ? $base_url . "page=" . $i:'';
	}

	return $res;
}

/**
* 画像アップロード保存
* @param:file_data アップロード情報($_FILES)
* @param:path 保存先のパス(拡張子抜き)
* @return:void
*/
function uploadImg($file_data, $name){
    // エラーチェック
    if(!isset($file_data['error'])){
        throw new RuntimeException('ファイルが選択されていません');
        exit;
    }
    foreach (is_array($file_data['error'])?
        $file_data['error']:array($file_data['error']) as $value) {
        switch ($value) {
            case 0: // OK
            case UPLOAD_ERR_OK: // OK
                break;
            case UPLOAD_ERR_NO_FILE:   // ファイル未選択
                throw new RuntimeException('ファイルが選択されていません');
            case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズ超過
            case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過
                throw new RuntimeException('ファイルサイズが大きすぎます');
            default:
                throw new RuntimeException('その他のエラーが発生しました');
        }
    }
    // アップロード
    $res = array();
    $base_path = dirname(dirname(__FILE__)).'/images/'.$name;
    foreach (is_array($file_data['tmp_name'])?
        $file_data['tmp_name']:array($file_data['tmp_name']) as $key => $value) {
        $type = @exif_imagetype($value);
        if (!in_array($type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) {
            throw new RuntimeException('画像形式が未対応です');
        }
        $types = array(IMAGETYPE_GIF=>'gif', IMAGETYPE_JPEG=>'jpg', IMAGETYPE_PNG=>'png');
        $path = "{$base_path}-{$key}.{$types[$type]}";
        if(file_exists($path))unlink($path);
        if (!move_uploaded_file($value, $path)) {
            throw new RuntimeException('ファイル保存時にエラーが発生しました');
        }
        chmod($path, 0644);
        $res[] = '/images/'.basename($path);
    }
    return $res;
}



