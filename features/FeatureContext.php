<?php

declare(strict_types=1);

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;
use SebastianBergmann\Diff\Differ;
use Webmozart\Assert\Assert;

final class FeatureContext implements Context
{
    private Client $client;

    private ?ResponseInterface $response;

    public function __construct(string $host)
    {
        $this->client = new Client(['base_uri' => $host]);
    }

    /**
     * @BeforeScenario
     */
    public function cleanup(): void
    {
        $this->response = null;
    }

    /**
     * @When I send a markup file with content type :mimeType containing
     */
    public function iSendAMarkupFileWithContentTypeContaining(string $mimeType, PyStringNode $body): void
    {
        try {
        $this->response = $this->client->request(
            'POST',
            '/',
            [
                'headers' => [
                    'Content-Type' => [
                        $mimeType,
                    ],
                ],
                'body' => (string) $body,
            ]
        );
        } catch (RequestException $requestException) {
            $this->response = $requestException->getResponse();
        }
    }

    /**
     * @Then I should get the following html
     */
    public function iShouldGetTheFollowingHtml(PyStringNode $html): void
    {
        if (null === $this->response) {
            throw new Exception('No request sent.');
        }

        Assert::eq($this->response->getStatusCode(), 200);

        try {
            Assert::eq(
                (string) $this->response->getBody(),
                (string) $html,
            );
        } catch (InvalidArgumentException $exception) {
            echo (new Differ())->diff(
                (string) $html,
                (string) $this->response->getBody(),
            );

            throw $exception;
        }
    }

    /**
     * @Then I should get an unexpected media type http response
     */
    public function iShouldGetAnUnexpectedMediaTypeHttpResponse(): void
    {
        if (null === $this->response) {
            throw new Exception('No request sent.');
        }

        Assert::eq($this->response->getStatusCode(), 415);
    }
}
