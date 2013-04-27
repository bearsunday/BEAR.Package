<?php
return array(
    E_ERROR => [
        'Fatal Error',
        '重大な実行時エラー。これは、メモリ確保に関する問題のように復帰で きないエラーを示します。スクリプトの実行は中断されます。'
    ],
    E_WARNING => ['Warning', '実行時の警告 (致命的なエラーではない)。スクリプトの実行は中断さ れません。	'],
    E_PARSE => ['E_PARSE', 'コンパイル時のパースエラー。パースエラーはパーサでのみ生成されます。'],
    E_NOTICE => [
        'Notice',
        '実行時の警告。エラーを発しうる状況に遭遇したことを示す。 ただし通常のスクリプト実行の場合にもこの警告を発することがありうる。'
    ],
    E_CORE_ERROR => [
        'E_CORE_ERROR',
        'PHPの初期始動時点での致命的なエラー。E_ERRORに 似ているがPHPのコアによって発行される点が違う。'
    ],
    E_CORE_WARNING => [
        'E_CORE_WARNING',
        '（致命的ではない）警告。PHPの初期始動時に発生する。 E_WARNINGに似ているがPHPのコアによって発行される 点が違う。'
    ],
    E_COMPILE_ERROR => [
        'E_COMPILE_ERROR',
        'コンパイル時の致命的なエラー。E_ERRORに 似ているがZendスクリプティングエンジンによって発行される点が違う。'
    ],
    E_COMPILE_WARNING => [
        'E_COMPILE_WARNING',
        'コンパイル時の警告（致命的ではない）。E_WARNINGに 似ているがZendスクリプティングエンジンによって発行される点が違う。'
    ],
    E_USER_ERROR => [
        'E_USER_ERROR',
        'ユーザーによって発行されるエラーメッセージ。E_ERROR に似ているがPHPコード上で trigger_error()関数を 使用した場合に発行される点が違う。'
    ],
    E_USER_WARNING => [
        'E_USER_WARNING',
        'ユーザーによって発行される警告メッセージ。E_WARNING に似ているがPHPコード上で trigger_error()関数を 使用した場合に発行される点が違う。'
    ],
    E_USER_NOTICE => [
        'E_USER_NOTICE',
        'ユーザーによって発行される注意メッセージ。E_NOTICEに に似ているがPHPコード上で trigger_error()関数を 使用した場合に発行される点が違う。'
    ],
    E_STRICT => [
        'E_STRICT',
        'コードの相互運用性や互換性を維持するために PHP がコードの変更を提案する。'
    ],
    E_RECOVERABLE_ERROR => [
        'E_RECOVERABLE_ERROR',
        'キャッチできる致命的なエラー。危険なエラーが発生したが、 エンジンが不安定な状態になるほどではないことを表す。 ユーザー定義のハンドラでエラーがキャッチされなかった場合 ( set_error_handler() も参照ください) は、 E_ERROR として異常終了する。'
    ],
    E_DEPRECATED => [
        'E_DEPRECATED',
        '実行時の注意。これを有効にすると、 将来のバージョンで動作しなくなるコードについての警告を受け取ることができる。'
    ],
    E_USER_DEPRECATED => [
        'E_USER_DEPRECATED',
        'ユーザー定義の警告メッセージ。これは E_DEPRECATED と同等だが、 PHP のコード上で関数 trigger_error() によって作成されるという点が異なる。'
    ]
);
