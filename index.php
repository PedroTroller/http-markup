<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

$getExtensionFromRequest = function (Symfony\Component\HttpFoundation\Request $request): string {
    $type = $request->headers->get('Content-Type');

    if (empty($type)) {
        throw new Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException(
            'No Content-Type provided'
        );
    }

    if (is_array($type)) {
        $type = current($type);
    }

    $formats = [
        'text/asciidoc'         => 'asc',
        'text/creole'           => 'creole',
        'text/markdown'         => 'md',
        'text/org'              => 'org',
        'text/orgmode'          => 'org',
        'text/rdoc'             => 'rdoc',
        'text/restructuredtext' => 'rst',
        'text/rst'              => 'rst',
        'text/textile'          => 'textile',
        'text/txstyle'          => 'textile',
        'text/wiki'             => 'wiki',
    ];

    if (false === array_key_exists($type, $formats)) {
        throw new Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException(
            sprintf('Unsupported Content-Type. Only %s supported', implode(', ', array_keys($formats)))
        );
    }

    return $formats[$type];
};

$app = new Silex\Application();

$app->post('/', function (Symfony\Component\HttpFoundation\Request $request) use ($getExtensionFromRequest) {
    $temporaryFile = sprintf('%s/%s.%s', sys_get_temp_dir(), uniqid(), $getExtensionFromRequest($request));
    $input = $request->getContent(true);
    $storage = fopen($temporaryFile, 'wb');

    stream_copy_to_stream($input, $storage);

    $response = new Symfony\Component\HttpFoundation\StreamedResponse(function () use ($temporaryFile): void {
        $cmd = sprintf('github-markup %s', $temporaryFile);

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        flush();

        $process = proc_open($cmd, $descriptorspec, $pipes, __DIR__, []);

        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                echo $s;
                flush();
            }
        }

        unlink($temporaryFile);
    }, 200, ['Content-Type' => 'text/html']);

    return $response;
});

$app->run();
