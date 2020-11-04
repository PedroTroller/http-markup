<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Client;
use SebastianBergmann\Diff\Differ;
use Webmozart\Assert\Assert;

final class FeatureContext implements Context
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var \GuzzleHttp\Psr7\Response|null
     */
    private $response;

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
}
