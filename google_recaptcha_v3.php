<?php

require_once './_recaptcha_keys.php';
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

