<?php

/*
| Aplikasi seluler SIAKAD (repo randysilvio/mobilesia2.0).
| min_version = versionCode Android terendah yang masih boleh dipakai; versi di bawahnya diminta memperbarui.
*/
return [
    'min_version'  => (int) env('MOBILE_MIN_VERSION', 1),
    'download_url' => env('MOBILE_DOWNLOAD_URL', 'https://github.com/randysilvio/mobilesia2.0/releases/latest/download/sttgpi-siakad.apk'),
];
