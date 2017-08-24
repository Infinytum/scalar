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

namespace Scalar\Http\Message;

/**
 * Interface UploadedFileInterface
 *
 * Object representing an uploaded file.
 * Instances of this class are immutable
 *
 * @package Scalar\Http\Message
 */
interface UploadedFileInterface
{

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \RuntimeException on any error during the move operation.
     * @throws \InvalidArgumentException if $targetPath is invalid.
     */
    public function move($targetPath);

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * @return \Scalar\IO\Stream\StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available / can be created.
     */
    public function getStream();

    /**
     * Retrieve the file size.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getFileSize();

    /**
     * Retrieve the error associated with the uploaded file
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int PHP Upload Constant
     */
    public function getUploadError();

    /**
     * Retrieve the filename sent by the client
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename();

    /**
     * Retrieve the media type sent by the client
     * @return string|null The media type sent by the client or null if none was provided.
     */
    public function getClientMediaType();

    /**
     * Retrieve media type detected by mime_content_type
     *
     * @see http://php.net/manual/de/function.mime-content-type.php
     * @return mixed
     */
    public function getMediaType();
}