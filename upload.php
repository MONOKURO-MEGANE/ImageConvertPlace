<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL);


ini_set('file_uploads', 'On');
ini_set('max_file_uploads', '5');						// 一度にアップロード出来るファイル数
ini_set('memory_limit', '128M');						// 一回の実行で使う最大のメモリサイズ
ini_set('post_max_size', '512M');						// 送信できるPOSTデータのサイズ
ini_set('upload_max_filesize', '512M');			// アップロードできる合計ファイルサイズ

ini_set('session.use_only_cookies', '0');
ini_set('session.use_trans_sid', '1');
//ini_set('session.save_path', './tmp/session');
ini_set('session.gc_maxlifetime', '1800');
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');
session_start();
$sess_name = session_name();
$sess_id = session_id();
$_SESSION[$sess_name] = $sess_id;

// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo "request_method_error!";
	exit();
}

if($_POST[$sess_name] !== $_SESSION[$sess_name]) {
	echo "session_error!";
	exit();
}

// サーバーのドメイン
$PROTOCOL = $_SERVER['REQUEST_SCHEME'];
$SERVER_HOST = $_SERVER['HTTP_HOST'];
$SERVER_DOMAIN = "$PROTOCOL".'://'."$SERVER_HOST";

$top_page_file = 'index.php';
// uploadするディレクトリ
$upload_dir = './upload/';
// uploadするファイルリスト
$upload_file_ary = array();

function display_error($error_str = '不明なエラーです！') {
	setcookie('conversion', 'failure', 0, '/');
	setcookie('error_notice', "$error_str", 0, '/');
}

// ファイルがあれば処理実行
if(isset($_FILES['file'])){
	// アップロードされたファイル件を処理
	for($i = 0; $i < count($_FILES['file']['name']); $i++){
		$upload_file = basename($_FILES['file']['name'][$i]);

		// アップロード数が5ファイルを超えているか否か
		if($i >= 5) {
			display_error('5ファイルを超えるファイルファイル数がアップロードされました。');
			header("Location: "."$SERVER_DOMAIN");
			exit();
		}

		// アップロードされたファイルの容量が5Mバイト未満か否か
		if(filesize($_FILES['file']['tmp_name'][$i]) >= 5*1024*1024) {
			display_error('5Mバイト以上のファイルがアップロードされました。');
			header("Location: "."$SERVER_DOMAIN");
			exit();
		}
		// JPEG画像・PNG画像などの画像ファイルか否か
		if( (exif_imagetype($_FILES['file']['tmp_name'][$i]) != IMAGETYPE_JPEG) && (exif_imagetype($_FILES['file']['tmp_name'][$i]) != IMAGETYPE_PNG) ) {
			display_error('PNGファイルでもJPEGファイルでもありません。');
			header("Location: "."$SERVER_DOMAIN");
			exit();
		}

		$upload_file_ary[] .= $upload_file;

		if(move_uploaded_file($_FILES['file']['tmp_name'][$i], "$upload_dir"."$upload_file") === false){
			// "アップロード失敗"
			display_error('アップロードしたファイルの取得に失敗しました。');
			header("Location: "."$SERVER_DOMAIN");
			exit();
		}
	}
}
// "アップロード成功"
setcookie('conversion', 'success', 0, '/');
setcookie('error_notice', '', 0, '/');

// 画像変換プログラム
foreach($upload_file_ary as $valid_file) {
	$no_extention_path = pathinfo($valid_file, PATHINFO_FILENAME);
	$image_type = mime_content_type("$upload_dir"."$valid_file");
	if($image_type === 'image/jpeg') {
		$converted_file = imagecreatefromjpeg("$upload_dir"."$valid_file");
		@imagepng($converted_file, "$upload_dir"."$no_extention_path".'.png');
		imagedestroy($converted_file);
	}
	if($image_type === 'image/png') {
		$converted_file = imagecreatefrompng("$upload_dir"."$valid_file");
		@imagejpeg($converted_file, "$upload_dir"."$no_extention_path".'.jpg');
		imagedestroy($converted_file);
	}
}

// リクエストのパス
$DIR_PATH = dirname($_SERVER['SCRIPT_NAME']);
// ここからクエリパラメータ
$file_list = implode('+', $upload_file_ary);

$respons_address = "$SERVER_DOMAIN"."$DIR_PATH"."$top_page_file".'?imagefiles='."$file_list";

header("Location: "."$respons_address");
exit();

