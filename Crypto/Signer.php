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

namespace Scalar\Crypto;


use Scalar\IO\Exception\IOException;
use Scalar\IO\File;

class Signer
{

    /**
     * @var string|File|null
     */
    private $privateKey;

    /**
     * @var string
     */
    private $passphrase;

    /**
     * @var string|File|null
     */
    private $publicKey;

    /**
     * Signer constructor.
     * @param string|File|null $privateKey
     * @param string $passphrase
     * @param string|File|null $publicKey
     */
    public function __construct
    (
        $privateKey = null,
        $passphrase = null,
        $publicKey = null
    )
    {
        $this->privateKey = $privateKey;
        $this->passphrase = $passphrase;
        $this->publicKey = $publicKey;
    }

    public function sign
    (
        $data,
        $passphrase = null,
        $privateKey = null,
        $algorithm = 'sha512'
    )
    {
        if ($passphrase === null) {
            $passphrase = $this->passphrase;
        }

        if ($privateKey === null) {
            $privateKey = $this->getPrivateKey($passphrase);
        }

        if (openssl_sign($data, $signature, $privateKey, $algorithm)) {
            return $signature;
        }

        throw new \Exception('Exception while signing');
    }

    /**
     * Shorthand for getKey(privateKey)
     * @param string $passphrase
     * @return string
     */
    private function getPrivateKey
    (
        $passphrase = null
    )
    {
        $privateKey = $this->getKey($this->privateKey);
        return openssl_get_privatekey($privateKey, $passphrase);
    }

    /**
     * Get declared key
     *
     * @param string|file $key
     * @return string Plain-text key
     * @throws IOException When key is not readable
     * @throws \Exception When key is invalid or none was defined
     */
    private function getKey
    (
        $key
    )
    {
        if ($key instanceof File) {
            if ($key->isReadable()) {
                return $key->toStream('r')->getContents();
            } else {
                throw new IOException('Public Key not readable');
            }
        } else if (is_string($key)) {
            return $key;
        } else {
            throw new \Exception('None or invalid instance-wide public key declared!');
        }
    }

    public function verify
    (
        $data,
        $signature,
        $publicKey = null,
        $algorithm = 'sha512'
    )
    {
        if ($publicKey === null) {
            $publicKey = $this->getPublicKey();
        }

        return openssl_verify($data, $signature, $publicKey, $algorithm) === 1;
    }

    /**
     * Shorthand for getKey(publicKey)
     * @return string
     */
    private function getPublicKey()
    {
        $publicKey = $this->getKey($this->publicKey);
        return openssl_get_publickey($publicKey);
    }


}