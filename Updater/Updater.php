<?php

namespace Scalar\Updater;

use Scalar\Http\Client\CurlHttpClient;
use Scalar\Http\Factory\HttpClientFactory;
use Scalar\IO\Factory\StreamFactory;
use Scalar\IO\Factory\UriFactory;
use Scalar\Repository\Repository;

class Updater implements UpdaterInterface
{

    const REPO_UPDATE = '/v1/update/';

    const CURRENT_PACKAGE_VERSION = '{bamboo.buildNumber}';

    /**
     * @var Repository
     */
    private $updateRepository;

    /**
     * @var string
     */
    private $channel;

    /**
     * Updater constructor.
     * @param Repository $updateRepository
     * @param string $channel
     */
    public function __construct($updateRepository, $channel)
    {
        $this->updateRepository = $updateRepository;
        $this->channel = $channel;
    }

    /**
     * Check if an update is available
     *
     * @return bool
     */
    public function hasUpdate()
    {
        $json = $this->fetchChannelInfo();

        if ($json === false) {
            return false;
        }

        if ($json['package_version'] > intval(self::CURRENT_PACKAGE_VERSION)) {
            return true;
        } else {
            return false;
        }
    }

    private function fetchChannelInfo()
    {
        $httpClientFactory = new HttpClientFactory();

        $uri = $this->updateRepository->getUri()->withPath
        (
            self::REPO_UPDATE . $this->channel
        );

        $httpClient = $httpClientFactory->createHttpClient
        (
            new CurlHttpClient(),
            $uri
        );

        $httpClient->request();

        $response = $httpClient->getResponse();

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $json = json_decode($response->getBody()->getContents(), true);
        return $json;
    }

    /**
     * Download and apply update
     *
     * @return bool
     */
    public function executeUpdate()
    {

        $json = $this->fetchChannelInfo();

        if ($json == false) {
            return false;
        }

        $uriFactory = new UriFactory();
        $uri = $uriFactory->createUri($json['uri']);

        $httpClientFactory = new HttpClientFactory();
        $httpClient = $httpClientFactory->createHttpClient
        (
            new CurlHttpClient(),
            $uri
        );

        $httpClient->request();

        $response = $httpClient->getResponse();

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStreamFromFile('/tmp/scalar.zip', 'w+');
        $stream->write($response->getBody()->getContents());
        $stream->close();

        $sha1 = sha1_file('/tmp/scalar.zip');

        if ($sha1 != $json['sha1']) {
            return false;
        }

        $zip = new \ZipArchive;
        $res = $zip->open('/tmp/scalar.zip');
        if ($res === TRUE) {
            $zip->extractTo(SCALAR_CORE . '../');
            $zip->close();
            unlink('/tmp/scalar.zip');
        } else {
            return false;
        }

        return true;
    }

    /**
     * Get update channel
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set update channel
     *
     * @param string $channel
     * @return void
     */
    public function setChannel
    (
        $channel
    )
    {
        $this->channel = $channel;
    }

}