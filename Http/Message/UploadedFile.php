<?php

namespace Scalar\Http\Message;

use Scalar\IO\Stream\Stream;
use Scalar\IO\Stream\StreamInterface;


class UploadedFile implements UploadedFileInterface
{

    /**
     * @var string
     */
    private $clientFilename;
    /**
     * @var string
     */
    private $clientMediaType;
    /**
     * @var int
     */
    private $error;
    /**
     * @var string
     */
    private $file;
    /**
     * @var bool
     */
    private $moved = false;
    /**
     * @var int
     */
    private $size;
    /**
     * @var StreamInterface
     */
    private $stream;

    public function __construct(
        $file,
        $size,
        $error,
        $clientFilename = null,
        $clientType = null
    )
    {
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientType;
        $this->error = $error;
        $this->size = $size;

        if ($error == UPLOAD_ERR_OK) {
            if (is_string($file)) {
                $this->file = $file;
            } elseif (is_resource($file)) {
                $this->stream = new Stream($file);
            } elseif ($file instanceof StreamInterface) {
                $this->stream = $file;
            } else {
                throw new \InvalidArgumentException(
                    'Invalid file provided to UploadedFile'
                );
            }
        }
    }

    public static function createFromSpec($values)
    {

        if (is_array($values['tmp_name'])) {
            $files = [];
            foreach (array_keys($values['tmp_name']) as $key) {
                $spec = [
                    'error' => $values['error'][$key],
                    'name' => $values['name'][$key],
                    'size' => $values['size'][$key],
                    'tmp_name' => $values['tmp_name'][$key],
                    'type' => $values['type'][$key],
                ];
                $files[$key] = self::createFromSpec($spec);
            }
            return $files;
        }

        return new UploadedFile(
            $values['tmp_name'],
            $values['size'],
            $values['error'],
            $values['name'],
            $values['type']
        );
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \RuntimeException on any error during the move operation.
     * @throws \InvalidArgumentException if $targetPath is invalid.
     */
    public function move($targetPath)
    {
        $this->isIntact();

        if (empty($targetPath)) {
            throw new \RuntimeException(
                'Target path cannot be empty!'
            );
        }

        if ($this->file) {
            $this->moved = php_sapi_name() != 'cli'
                ? move_uploaded_file($this->file, $targetPath)
                : rename($this->file, $targetPath);
        } else {
            $targetStream = new Stream(fopen($targetPath, "w"));
            $targetStream->write($this->getStream()->getContents());
            $this->moved = true;
        }

        if (!$this->moved) {
            throw new \RuntimeException(
                'Could not move file to ' . $targetPath
            );
        }
    }

    private function isIntact()
    {
        if ($this->getUploadError() != UPLOAD_ERR_OK) {
            throw new \RuntimeException(
                'Cannot access file because the upload encountered an error'
            );
        }

        if ($this->isMoved()) {
            throw new \RuntimeException(
                'Cannot access file after it has been moved'
            );
        }
    }

    /**
     * Retrieve the error associated with the uploaded file
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int PHP Upload Constant
     */
    public function getUploadError()
    {
        return $this->error;
    }

    /**
     * Check if file has already been moved
     *
     * @return bool
     */
    public function isMoved()
    {
        return $this->moved;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * @return \Scalar\IO\Stream\StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available / can be created.
     */
    public function getStream()
    {
        $this->isIntact();
        return $this->stream;
    }

    /**
     * Retrieve the file size.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getFileSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the filename sent by the client
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client
     * @return string|null The media type sent by the client or null if none was provided.
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * Retrieve media type detected by mime_content_type
     *
     * @see http://php.net/manual/de/function.mime-content-type.php
     * @return mixed
     */
    public function getMediaType()
    {
        if (!is_string($this->file))
            throw new \RuntimeException(
                'Cannot determine media type if file is a stream'
            );
        return mime_content_type($this->file);
    }
}