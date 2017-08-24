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

/**
 * Created by PhpStorm.
 * User: nila
 * Date: 06.06.17
 * Time: 18:50
 */

namespace Scalar\Http\Factory;


use Scalar\Http\Message\UploadedFile;
use Scalar\Http\Message\UploadedFileInterface;
use Scalar\IO\Factory\StreamFactory;

class UploadedFileFactory implements UploadedFileFactoryInterface
{

    /**
     * Create a new uploaded file
     *
     * @param integer $error PHP error
     * @param string|resource $file
     * @param integer $size bytes
     * @param string $clientFilename
     * @param string $clientMediaType
     * @return UploadedFileInterface
     * @throws \InvalidArgumentException If file is invalid
     */
    public function createUploadedFile(
        $file,
        $size = null,
        $error = \UPLOAD_ERR_OK,
        $clientFilename = null,
        $clientMediaType = null
    )
    {
        if (is_string($file)) {
            $streamFactory = new StreamFactory();
            $file = $streamFactory->createStream($file);
        }
        return new UploadedFile
        (
            $file,
            $size,
            $error,
            $clientFilename,
            $clientMediaType
        );
    }

    /**
     * Create a new uploaded file from $_FILES
     *
     * @param array $files $_FILES or similar
     * @return array
     * @throws \InvalidArgumentException If invalid file structure is provided
     */
    public function createUploadedFileFromArray
    (
        $files
    )
    {
        $uploadedFiles = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $uploadedFiles[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $uploadedFiles[$key] = UploadedFile::createFromSpec($value);
            } elseif (is_array($value)) {
                $uploadedFiles[$key] = $this->createUploadedFileFromArray($value);
                continue;
            } else {
                throw new \InvalidArgumentException('Invalid value in file array');
            }
        }
        return $uploadedFiles;
    }
}