<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL);


require_once '_recaptcha_keys.php';
$siteKey = V3_SITEKEY;			// reCAPTCHA サイトキー
$secretKey = V3_SECRETKEY;	// reCAPTCHA シークレットキー

require_once './recaptcha-master/src/autoload.php';				/* google reCAPTCHAR */

//reCAPTCHA トークン
$token = isset( $_POST[ 'g-recaptcha-response' ] ) ? $_POST[ 'g-recaptcha-response' ] : NULL;
//reCAPTCHA アクション名
$action = isset( $_POST[ 'action' ] ) ? $_POST[ 'action' ] : NULL;
// 結果を表示する文字列を初期化
$result_status = '';

// トークンとアクション名が取得できれば
if ($token && $action) {
	//インスタンスを生成
	$recaptcha = new \ReCaptcha\ReCaptcha($secretKey);
	$resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME']) //ホスト名の確認
										->setExpectedAction($action) //アクション名の確認
										->setScoreThreshold(0.5) //スコアの閾値の設定（この場合 0.5 以上）
										->verify($token, $_SERVER['REMOTE_ADDR']);

	if ( $resp->isSuccess() ) {
		// 検証を通過した場合の処理（メールの送信など）
		$result_status = 'Verified（検証通過）!';
	} else {
		// 検証を通過しなかった場合の処理（エラーの表示など）
		$errors = $resp->getErrorCodes();
		$result_status = 'Failed（失敗） ';
	}
}


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

require_once '_mail_address.php';

// 他のサイトでインラインフレーム表示を禁止する（クリックジャッキング対策）
header('X-FRAME-OPTIONS: SAMEORIGIN');

// お問い合わせフォームからメール送信まで
if ( ($_SERVER['REQUEST_METHOD'] === 'POST') && ($_POST[$sess_name] === $_SESSION[$sess_name]) ) {
	$ADMIN_MAIL = MY_MAIL_ADDRESS;

	$name = htmlspecialchars($_POST['name']);
	$address = htmlspecialchars($_POST['address']);
	$subject = htmlspecialchars($_POST['subject']);
	$message = htmlspecialchars($_POST['message']);

	$message_detail = <<< EOM
送信内容は以下のとおりです。

お名前
{$name}
メールアドレス
{$address}
件名
{$subject}
メッセージ
{$message}
EOM;

	// 管理者にメールを送る。
	$result = mb_send_mail($ADMIN_MAIL, 'Webサイト【画像変換所 - Image convert Place】からお問い合わせが送信されました。', $message_detail, '-f'.'From: '."mb_encode_mimeheader({$name})"."<{$address}>");
	// 閲覧者にメールを送る。
	$result = mb_send_mail($address, 'Webサイト【画像変換所 - Image convert Place】からメッセージを受け取りました。', $message_detail, '-f'.'From: '."mb_encode_mimeheader('MONOKURO-MEGANE')"."<{$ADMIN_MAIL}>");
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>

	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-WPEMD9F6ZT"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());

		gtag('config', 'G-WPEMD9F6ZT');
	</script>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no,maximum-scale=1">
	<title>お問い合わせ - Image Convert Place</title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Cache-Control" content="no-cache">
	<link rel="canonical" href="https://image-convert-place.net/">
	<link rel="stylesheet" href="css/_normalize_.css">
	<link rel="stylesheet" href="css/_clearfix_.css">
	<link rel="stylesheet" href="css/common.css">
	<link rel="stylesheet" href="css/header.css">
	<link rel="stylesheet" href="css/footer.css">
	<link rel="stylesheet" href="css/contact.css">
	<!-- フォームの無限再送信仕様を無効化 -->
	<script>
		if ( window.history.replaceState ) {
		window.history.replaceState( null, null, window.location.href );
		}
	</script>
	<script src="https://kit.fontawesome.com/d48fe24770.js" crossorigin="anonymous"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	<!-- google reCAPTCHAR -->
	<script src="https://www.google.com/recaptcha/api.js?render=6LdQ-3AaAAAAABo7MJ8THi6zS79BQJx0Mc5wSYC4"></script>
	<script>
		$(document).ready(function() {
			$('#contact-form').submit(function(event) {
				event.preventDefault();
				//トークンを取得
				grecaptcha.ready(function() {
					grecaptcha.execute('6LdQ-3AaAAAAABo7MJ8THi6zS79BQJx0Mc5wSYC4', {action: 'POST_CONTACT'}).then(function(token) {
						//input 要素を生成して値にトークンを設定
						$('#contact-form').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
						//input 要素を生成して値にアクション名を設定
						$('#contact-form').prepend('<input type="hidden" name="action" value="POST_CONTACT">');
						//unbind で一度 submit のイベントハンドラを削除してから submit() を実行
						$('#contact-form').unbind('submit').submit();
					});
				});
			});
		});
	</script>
	<!-- /google reCAPTCHAR -->
</head>
<body>
	<header>
		<div class="top-bar">
			<h1 class="description">画像変換所"Image Convert Place"は、JPGファイルもしくはPNGファイルを相互に変換できるサイトです。</h1>
		</div>
		<h2 class="site-title"><a href="/"><span data-ruby="Image Convert Place">画像変換所</span></a></h2>
		<nav></nav>
	</header>
	<article>
		<section>
			<h3 class="contact-title">お問い合わせ</h3>
			<div class="attention-wrapper">
				<ul class="attention-list">
					<li class="attention-item">確認画面はございませんので、入力の間違いがないようにお願いします。</li>
					<li class="attention-item">送信すると、入力されたメールアドレスにメールが送信されます。</li>
					<li class="attention-item">すべての項目が必須項目となっています。</li>
				</ul>
			</div>
			<div class="form-wrapper">
				<form id="contact-form" action="contact.php" method="POST">
					<input type="hidden" name="<?php echo $sess_name; ?>" value="<?php echo $sess_id; ?>">
					<div class="input-block">
						<ul class="contact-list">
							<li class="contact-key"><label>お名前</label></li>
							<li class="contact-value"><input type="text" name="name" size="20" maxlength="30" required></li>
						</ul>
						<ul class="contact-list">
							<li class="contact-key"><label>メールアドレス</label></li>
							<li class="contact-value"><input type="email" name="address" size="30" maxlength="50" required></li>
						</ul>
						<ul class="contact-list">
							<li class="contact-key"><label>件名</label></li>
							<li class="contact-value"><input type="text" name="subject" size="30" maxlength="50" required></li>
						</ul>
						<ul class="contact-list">
							<li class="contact-key"><label>メッセージ</label></li>
							<li class="contact-value"><textarea name="message" rows="6" cols="50" maxlength="400" required></textarea></li>
						</ul>
					</div>
					<div style="text-align:center;">
						<input id="send-button" type="submit" value="送信">
						<input type="hidden" name="recaptcha_response" id="TOKEN">
						<input type="hidden" name="action" value="POST_CONTACT">
					</div>
				</form>
			</div>
		</section>
	</article>
	<footer>
		<div class="link-area">
			<ul class="link-type-list">
				<li class="link-type-item">
					<ul class="blog-list">
						<li class="blog-item"><a href="https://pcgame-a2z.com/wordpress/" target="_blank">PCゲームのＡtoＺのブログ</a></li>
						<li class="blog-item"><a href="https://hikki-reserve.com/wordpress/" target="_blank">ヒッキー予備軍のブログ</a></li>
					</ul>
				</li>
				<li class="page-contents"><a href="contact.php">お問い合わせフォーム</a></li>
			</ul>
		</div>
		<div class="copyright-area">
			<p class="copyright-text">&copy; COPYRIGHT <?php echo date("Y"); ?> MONOKURO-MEGANE All Right Reserved.</p>
		</div>
	</footer>
</body>
</html>