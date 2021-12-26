<?php
declare(strict_types=1);

return [
    'verify_timestamp' => env('VERIFY_TIMESTAMP', false),
    'verify_sign' => env('VERIFY_SIGN', false),
    'sign_secret_salt' => env('SIGN_SECRET_SALT', ''),
    'doc_auth_user' => env('DOC_AUTH_USER', 'admin'),
    'doc_auth_pass' => env('DOC_AUTH_PASS', '111111'),
    'doc_navbar_color' => env('DOC_NAVBAR_COLOR', 'F7931E'),
];