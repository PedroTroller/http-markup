<?php

require_once __DIR__ . '/vendor/autoload.php';

function getExtensionFromRequest (Symfony\Component\HttpFoundation\Request $request) {
    if (null === $type = $request->headers->get('Content-Type')) {
        throw new Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException(
            'No Content-Type provided'
        );
    }

    $formats = [
        'text/markdown' => 'md',
    ];

    if (false === array_key_exists($type, $formats)) {
        throw new Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException(
            sprintf('Unsupported Content-Type. Only %s supported', implode(', ', array_keys($formats)))
        );
    }

    return $formats[$type];
};

$app = new Silex\Application();

$app->post('/', function (Symfony\Component\HttpFoundation\Request $request) {
    $file = sprintf('%s/%s.%s', sys_get_temp_dir(), uniqid(), getExtensionFromRequest($request));

    file_put_contents($file, $request->getContent());

    $response = new Symfony\Component\HttpFoundation\StreamedResponse(function() use ($file) {
        $cmd = sprintf('github-markup %s', $file);

        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w"),
        );

        flush();

        $process = proc_open($cmd, $descriptorspec, $pipes, __DIR__, array());

        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                print $s;
                flush();
            }
        }

        unlink($file);
    }, 200, ['Content-Type' => 'text/html']);

    return $response;
});

$app->run();
