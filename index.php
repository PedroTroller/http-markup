<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Process\Process;

require_once __DIR__.'/vendor/autoload.php';

$getExtensionFromRequest = function (Request $request): string {
    $type = $request->getHeader('Content-Type');

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

$app = new \Slim\App();

$app->post('/', function (Request $request, Response $response, array $args) use ($getExtensionFromRequest): void {
    $temporaryFile = sprintf('%s/%s.%s', sys_get_temp_dir(), uniqid(), $getExtensionFromRequest($request));
    $input = (string) $request->getBody();

    file_put_contents($temporaryFile, $input);

    $process = new Process(['github-markup', $temporaryFile]);

    $process->run(function (string $type, string $message) use ($response): void {
        $response->getBody()->write($message);
    });

    $response->withStatus($process->isSuccessful() ? 200 : 500);
});

$app->run();
