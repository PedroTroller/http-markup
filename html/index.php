<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Process\Process;
use Slim\App;
use React\Promise\Deferred;
use React\Promise\Promise;

require_once dirname(__DIR__).'/vendor/autoload.php';

$app = new App();

$app->post('/', function (Request $request, Response $response, array $args): Response {
    $result = [
        'status' => 502,
        'body' => '',
    ];

    $deferred = new Deferred();
    $promise = $deferred->promise();

    $promise
        ->then(function (Request $request): array {
            $type = $request->getHeader('Content-Type');

            if (empty($type)) {
                throw new Exception('No Content-Type provided', 415);
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
                throw new Exception(
                    sprintf('Unsupported Content-Type. Only %s supported', implode(', ', array_keys($formats))),
                    415,
                );
            }

            return [$formats[$type], (string) $request->getBody()];
        })
        ->then(function (array $data): string {
            [$extension, $markup] = $data;
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
        })
        ->then(function (string $html): string {
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
        })
        ->otherwise(function (Exception $exception) use (&$result) {
            $result['status'] = $exception->getCode() ?: 500;
            $result['body'] = $exception->getMessage();
        })
        ->then(function (string $html) use (&$result): void {
            $result['status'] = 200;
            $result['body'] = $html;
        })
    ;

    $deferred->resolve($request);

    $response->getBody()->write($result['body']);

    return $response->withStatus($result['status']);
});

$app->get('/_ping', function (Request $request, Response $response, array $args): Response {
      return $response->withStatus(200);
});

$app->run();
