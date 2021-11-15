<?php
ini_set('file_uploads', 'On');
ini_set('max_file_uploads', '5');           // 一度にアップロード出来るファイル数
ini_set('memory_limit', '128M');            // 一回の実行で使う最大のメモリサイズ
ini_set('post_max_size', '512M');           // 送信できるPOSTデータのサイズ
ini_set('upload_max_filesize', '512M');     // アップロードできる合計ファイルサイズ
