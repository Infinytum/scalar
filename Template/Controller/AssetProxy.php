<?php
/**
 * Created by PhpStorm.
 * User: teryx
 * Date: 7/13/17
 * Time: 10:51 AM
 */

namespace Scalar\Template\Controller;


use Scalar\Core\Config\ScalarConfig;
use Scalar\Core\Scalar;
use Scalar\Http\Message\ResponseInterface;

class AssetProxy
{

    /**
     * @param $request
     * @param $response ResponseInterface
     * @param $assetPath
     * @return ResponseInterface
     */
    public function proxy
    (
        $request,
        $response,
        $assetPath
    )
    {
        $fullPath = ScalarConfig::getInstance()->get(Scalar::CONFIG_ASSETS_DIR) . '/' . $assetPath;
        if (!file_exists($fullPath)) {
            return $response->withStatus(404);
        }

        if (strpos(realpath($fullPath), ScalarConfig::getInstance()->get(Scalar::CONFIG_ASSETS_DIR)) == -1) {
            return $response->withStatus(404);
        }

        $response = $response->withAddedHeader('Content-Type', $this->mime_content_type($fullPath));
        $response = $response->withAddedHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
        $response->getBody()->write(file_get_contents($fullPath));
        return $response;
    }

    function mime_content_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $array = explode('.', $filename);
        $ext = strtolower(array_pop($array));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

}