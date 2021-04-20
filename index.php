<?php
//ini_set("display_errors", "On");
//error_reporting(E_ALL);


setlocale(LC_CTYPE, 'ja_JP.UTF-8');         // basename(),pathinfo()の日本語対応のため

require_once '_recaptcha_keys.php';
$siteKey = V3_SITEKEY;                      // reCAPTCHA サイトキー
$secretKey = V3_SECRETKEY;                  // reCAPTCHA シークレットキー

require_once './recaptcha-master/src/autoload.php';       /* google reCAPTCHAR */

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


ini_set('file_uploads', 'On');
ini_set('max_file_uploads', '5');           // 一度にアップロード出来るファイル数
ini_set('memory_limit', '128M');            // 一回の実行で使う最大のメモリサイズ
ini_set('post_max_size', '512M');           // 送信できるPOSTデータのサイズ
ini_set('upload_max_filesize', '512M');     // アップロードできる合計ファイルサイズ

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  echo "request_method_error!";
  exit();
}

// uploadするディレクトリ
$upload_dir = './upload/';
// クッキーで変換結果を判断
$judge_cookie = isset($_COOKIE['conversion']) ? $_COOKIE['conversion'] : NULL;

if(!isset($judge_cookie)) {
  //$sess_name = bin2hex(random_bytes(10));
  setcookie('conversion', 'prepare', 0, '/');
} else {
  // ファイル情報を取得する
  $queries = $_SERVER['QUERY_STRING'];
  parse_str($queries, $query_ary);
  //$_SESSION = array();
  setcookie('conversion', '', 0, '/');
  session_unset();
  session_destroy();
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
  <!-- Google AdSense -->
  <!--<script data-ad-client="ca-pub-3312767436740276" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>-->

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no,maximum-scale=1">
  <title>画像変換所 - Image Convert Place</title>
  <meta name="description" content="JPG画像ファイルとPNG画像ファイルを相互変換するプログラムです！アップロードするファイルはドラッグ・アンド・ドロップで選択できます！">
  <meta name="keywords" content="">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-Control" content="no-cache">
  <link rel="canonical" href="https://image-convert-place.net/">
  <link rel="stylesheet" href="css/_normalize_.css">
  <link rel="stylesheet" href="css/_clearfix_.css">
  <link rel="stylesheet" href="css/common.css">
  <link rel="stylesheet" href="css/header.css">
  <link rel="stylesheet" href="css/footer.css">
  <link rel="stylesheet" href="css/index.css">
  <script src="https://kit.fontawesome.com/d48fe24770.js" crossorigin="anonymous"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="javascript/upload_file_master.js"></script>
  <!-- google reCAPTCHAR -->
  <script src="javascript/_recaptcha_keys.js"></script>
  <script>
    let key = getSitekey();
    RECAPTHCA_SITEKEY = key;
    RECAPTCHA_API_URL = 'https://www.google.com/recaptcha/api.js?render=' + key;

    document.write(`<script src="${RECAPTCHA_API_URL}"><\/script>`);
  </script>
  <script>
    $(document).ready(function() {
      $('#file-upload-form').submit(function(event) {
        event.preventDefault();
        //トークンを取得
        grecaptcha.ready(function() {
          grecaptcha.execute(RECAPTHCA_SITEKEY, {action: 'POST_IMAGE'}).then(function(token) {
            //input 要素を生成して値にトークンを設定
            $('#file-upload-form').prepend('<input type="hidden" name="g-recaptcha-response" value="' + token + '">');
            //input 要素を生成して値にアクション名を設定
            $('#file-upload-form').prepend('<input type="hidden" name="action" value="POST_IMAGE">');
            //unbind で一度 submit のイベントハンドラを削除してから submit() を実行
            $('#file-upload-form').unbind('submit').submit();
          });
        });
      });
    });
  </script>
  <!-- /google reCAPTCHAR -->
  <script>
    $(document).ready(function() {
      $('.show-image-button > .button-list > .button-item > label.green-button').on('click', function() {
        $(this).parents().find('div#png-text-wrapper').slideUp();
        $(this).parents().find('div#jpg-text-wrapper').slideToggle();
      });
      $('.show-image-button > .button-list > .button-item > label.blue-button').on('click', function() {
        $(this).parents().find('div#jpg-text-wrapper').slideUp();
        $(this).parents().find('div#png-text-wrapper').slideToggle();
      });
    });
  </script>
</head>
<body>

<div id="read_text" style="display:none;"></div>

  <header>
    <div class="top-bar">
      <h1 class="description">画像変換所"Image Convert Place"は、JPGファイルもしくはPNGファイルを相互に変換できるサイトです。</h1>
    </div>
    <h2 class="site-title"><a href="/"><span data-ruby="Image Convert Place">画像変換所</span></a></h2>
    <nav></nav>
  </header>
  <article>
    <section>
      <p class="summary-message">JPGファイルとPNGファイルを同時にアップロードしても大丈夫。</p>
      <form id="file-upload-form" action="./upload.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="<?php echo $sess_name; ?>" value="<?php echo $sess_id; ?>">
        <div id="file_drag_drop_area" class="drop-box">
          ここにファイルをドラッグ&amp;ドロップ<br/>
          <span>または</span><br/>
          <input id="file_input" style="text-align:center;" type="file" name="file[]" accept="image/jpeg, image/png" multiple required>
        </div>
        <div style="text-align:center;">
          <input type="submit" value="変換開始">
          <input type="hidden" name="recaptcha_response" id="TOKEN">
          <input type="hidden" name="action" value="POST_IMAGE">
        </div>
      </form>
    </section>
    <section>
      <div class="returned-wrapper">
        <ul class="confirm-result-box">
          <li class="download-area">
            <p class="result-title">実行結果</p>
            <?php
              if($judge_cookie === 'success') {
                $upload_file_ary = explode(' ', $query_ary['imagefiles']);
                echo '<ul>';
                foreach ($upload_file_ary as $upload_file) {
                  $image_type = mime_content_type("$upload_dir"."$upload_file");
                  if($image_type === 'image/jpeg') {
                    $no_extention_path = pathinfo($upload_file, PATHINFO_FILENAME);
                    $download_file_path = trim("$no_extention_path".'.png', '_');
                    $download_full_path = "$upload_dir"."$no_extention_path".'.png';
                    echo '<li>'.'<a href="'."$download_full_path".'" download='."$download_file_path".'>'."$no_extention_path".'</a></li>';
                  } else if($image_type === 'image/png') {
                    $no_extention_path = pathinfo($upload_file, PATHINFO_FILENAME);
                    $download_file_path = trim("$no_extention_path".'.jpg', '_');
                    $download_full_path = "$upload_dir"."$no_extention_path".'.jpg';
                    echo '<li>'.'<a href="'."$download_full_path".'" download='."$download_file_path".'>'."$no_extention_path".'</a></li>';
                  } else {
                    //echo "JPGかPNGの画像ファイルを指定してください！";
                  }
                }
                echo '</ul>';
              } else if($judge_cookie === 'failure') {
                echo "COOKIEエラー";
              } else {
                //echo "UNKNOWN!!";
              }
            ?>
          </li>
          <li class="notice-area">
            <p class="error-title">内容</p>
            <?php
              $catch_cookie = 0;
              if(isset($_COOKIE['error_notice'])) {
                $catch_cookie = $_COOKIE['error_notice'];
              } else {
                $catch_cookie = "問題ありません";
              }
              echo htmlspecialchars("$catch_cookie");
            ?>
          </li>
        </ul>
      </div>
    </section>
    <section>
      <div class="introduction">
        <p class="explanation">この「画像変換所（Image Convert Place）」は、アップロードされた画像ファイルが<strong>JPEG形式ならPNG形式に変換し、逆にPNG形式ならJPEG形式に変換</strong>します。</p>
        <p class="explanation">変換処理されたファイルは、緑色の<span style="color:green;">実行結果</span>の枠内に変換されたファイルへのリンクが貼られますので、自由にご利用ください。</p>
      </div>
      <div class="notes-wrapper">
        <dl class="notes-block">
          <dt class="notes-title"><i class="fas fa-exclamation-circle"></i>&nbsp;注意事項</dt>
          <dd class="notes-list">当サイトを利用したことに端する、すべての事象の責任は負いません。</dd>
          <dd class="notes-list">変換に用いたファイルは90分前後でサーバーから自動削除されます。</dd>
          <dd class="notes-list">1回あたり5ファイルまで。1ファイルあたり5MBまで。</dd>
        </dl>
      </div>
    </section>
    <section>
      <div style="border:2px dashed gray;margin:5% auto;"></div>
    </section>
    <section>
      <h3 class="image-type-caption">画像保存形式が複数あるのは、形式ごとに得意な画像イメージがあるから！</h3>
      <div class="show-image-button">
        <ul class="button-list">
          <li class="button-item"><label class="green-button">"JPG/JPEG形式"とは</label></li>
          <li class="button-item"><label class="blue-button">"PNG形式"とは</label></li>
        </ul>
        <div id="jpg-text-wrapper" class="image-category-block">
          <h4>"JPG/JPEG"形式とは</h4>
          <p class="description-text">
            <img class="example_of_jpg" src="image/jpg_category.jpg" alt="見本">
            JPG形式及びJPEG形式（以下"JPG形式"に統一）が扱うのに適している画像は、写真などで保存されるなめらかなで多くの色が使われているイメージです。多くの色を扱 うことが出来るので、微妙な色の違いを正確に画像に反映させることが出来ます。一方で、透過（透明：色が存在しない）画像を扱うことが出来ません。<br>
            <br>
            少し専門的な説明をします。JPG形式は"<span style="color:red;">不</span>可逆圧縮"という方式で圧縮された画像ファイル形式です。一般的にデジタルカメラ・スマートフォンのカメラで採用されておりますが、あれは撮影した画像ファイルを圧縮されたものであり、撮影された画像そのものではありません（圧縮する前の画像ファイル 形式はBMP形式です）。また、"<span style="color:red;">不</span>可逆圧縮"というのは、画像ファイルを圧縮する際に人の目にはわからない画像情報をそも  そも切り捨ててしまい、圧縮する前にあった画像情報が存在せず圧縮する前の画像ファイル（BMP形式）に戻すことが出来ません。"<span style="color:red;">不</span>可逆圧縮"の<span style="color:red;">不</span>可逆とはそういうことになります。
          </p>
        </div>
        <div id="png-text-wrapper" class="image-category-block">
          <h4>"PNG"形式とは</h4>
          <p class="description-text">
            <img class="example_of_png" src="image/png_category.png" alt="見本">
            PNG形式が扱うのに適している画像は、イラストなどで作成された手書き感のある人の手によって描かれたイメージです。PNG形式はJPG形式と同じように多くの色を扱うことが出来ますし、透過（透明：色が存在しない）画像も扱うことが出来ます。一方で、画像ファイルによってはファイルサイズが大きくなってしまいます（写真などで顕著です）。<br>
            <br>
            少し専門的な説明をします。PNG形式はGIF形式の次世代規格で、共に"可逆圧縮"という方式で圧縮された画像ファイル形式です。この"可逆圧縮"というのは、圧縮す る前の画像ファイル（圧縮する前の画像ファイル形式はBMP形式です）に、完全に戻すことが出来るということです。たとえば、パソコンなどを操作しているとファイルの圧縮にZIP形式のファイルがありますが、展開・解凍という操作で圧縮前のファイルを扱うことが出来ます。PNG形式の画像ファイルも、同じ様にBMP形式の画像ファイルに戻せるということです。"可逆圧縮"の可逆とはそういうことになります。
          </p>
        </div>
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

