<?php

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