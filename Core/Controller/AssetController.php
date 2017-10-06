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
 * Date: 10/6/17
 * Time: 4:31 PM
 */

namespace Scalar\Core\Controller;


use Scalar\Core\Scalar;
use Scalar\Core\Service\CoreConfigurationService;
use Scalar\Core\Service\CoreTemplateService;
use Scalar\Http\Message\RequestInterface;
use Scalar\Http\Message\ResponseInterface;

class AssetController
{

    /**
     * CoreConfig instance
     * @var CoreConfigurationService
     */
    private $coreConfig;

    /**
     * CoreTemplate instance
     * @var CoreTemplateService
     */
    private $coreTemplate;

    public function __construct()
    {
        $this->coreConfig = Scalar::getService(Scalar::SERVICE_CORE_CONFIG);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array ...$path
     * @return ResponseInterface
     */
    public function assets
    (
        $request,
        $response,
        ...$path
    )
    {
        $this->coreTemplate = Scalar::getService(Scalar::SERVICE_CORE_TEMPLATE);
        $fullPath = $this->coreTemplate->getAssetDirectory() . '/' . join('/', $path);

        if (!file_exists($fullPath)) {
            return $response->withStatus(404);
        }

        if (strpos(realpath($fullPath), $this->coreConfig->get(CoreTemplateService::CONFIG_ASSETS_DIR)) == -1) {
            return $response->withStatus(404);
        }

        $response = $response->withAddedHeader('Content-Type', $this->mime_content_type($fullPath));
        $response = $response->withAddedHeader('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60)));
        $response->getBody()->write(file_get_contents($fullPath));
        return $response;
    }

    private function mime_content_type($filename)
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