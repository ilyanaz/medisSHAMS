<?php

declare(strict_types=1);

$projectRoot = __DIR__;
$publicRoot = $projectRoot.'/public';
$runtimeStorage = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'medis-shams';

$directories = [
    $runtimeStorage,
    $runtimeStorage.'/app',
    $runtimeStorage.'/app/public',
    $runtimeStorage.'/framework',
    $runtimeStorage.'/framework/cache',
    $runtimeStorage.'/framework/cache/data',
    $runtimeStorage.'/framework/sessions',
    $runtimeStorage.'/framework/testing',
    $runtimeStorage.'/framework/views',
    $runtimeStorage$runtimeStorage.'/logs',
    $runtimeStorage.'/bootstrap',
    $runtimeStorage.'/bootstrap/cache',
];

foreach ($directories as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

$defaults = [
    'APP_STORAGE' => $runtimeStorage,
    'VIEW_COMPILED_PATH' => $runtimeStorage.'/framework/views',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'LOG_CHANNEL' => 'stderr',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
];

foreach ($defaults as $key => $value) {
    $current = getenv($key);
    if ($current === false || $current === '') {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if ($uri !== '/') {
    $candidate = realpath($publicRoot.$uri);
    $publicReal = realpath($publicRoot);
    if ($candidate && $publicReal && str_starts_with($candidate, $publicReal) && is_file($candidate)) {
        return false;
    }
}

chdir($publicRoot);
$_SERVER['SCRIPT_FILENAME'] = $publicRoot.'/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require $publicRoot.'/index.php';

