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

$markupToHtml = function (string $markup, string $extension): string {
    $temporaryFile = sprintf('%s/%s.%s', sys_get_temp_dir(), uniqid(), $extension);
    $process = new Process(
        [
            'github-markup',
            $temporaryFile,
        ]
    );

    file_put_contents($temporaryFile, $markup);
    $process->run();
    unlink($temporaryFile);

    if ($process->isSuccessful()) {
        return $process->getOutput();
    }

    throw new Exception($process->getErrorOutput());
};

$prettier = function (string $html): string {
    $temporaryFile = sprintf('%s/%s.%s', sys_get_temp_dir(), uniqid(), 'html');
    $process = new Process(
        [
            'prettier',
            '--write',
            '--ignore-unknown',
            '--parser',
            'html',
            '--tab-width',
            2,
            '--print-width',
            1000,
            $temporaryFile,
        ]
    );

    file_put_contents($temporaryFile, $html);
    $process->run();
    $html = (string) file_get_contents($temporaryFile);
    unlink($temporaryFile);

    if ($process->isSuccessful()) {
        return $html;
    }

    throw new Exception($process->getErrorOutput());
};

$app = new \Slim\App();

$app->post('/', function (Request $request, Response $response, array $args) use ($getExtensionFromRequest, $markupToHtml, $prettier): void {
    $extension = $getExtensionFromRequest($request);
    $markup = (string) $request->getBody();

    try {
        $html = $markupToHtml($markup, $extension);
        $html = $prettier($html);
        $response->getBody()->write($html);
        $response->withStatus(200);
    } catch (Exception $exception) {
        $response->withStatus(500);
    }
});

$app->get('/_ping', function (Request $request, Response $response, array $args): void {
  $response->withStatus(200);
});

$app->run();
