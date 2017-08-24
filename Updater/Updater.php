<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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