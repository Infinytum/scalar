<?php

namespace Scaly\Http\Factory;

use Scaly\Http\Message\UploadedFileInterface;

interface UploadedFileFactoryInterface
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
    );

}