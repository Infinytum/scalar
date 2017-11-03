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

namespace Scalar\Core\Router\Hook;


use Scalar\Http\Message\ResponseInterface;
use Scalar\Http\Message\ServerRequestInterface;
use Scalar\Http\Middleware\HttpMiddlewareInterface;

class MinifierMiddleware implements HttpMiddlewareInterface
{

    const MINIFY_STRING = '"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'';
    const MINIFY_COMMENT_CSS = '/\*[\s\S]*?\*/';
    const MINIFY_COMMENT_HTML = '<!\-{2}[\s\S]*?\-{2}>';
    const MINIFY_COMMENT_JS = '//[^\n]*';
    const MINIFY_PATTERN_JS = '/[^\n]+?/[gimuy]*';
    const MINIFY_HTML = '<[!/]?[a-zA-Z\d:.-]+[\s\S]*?>';
    const MINIFY_HTML_ENT = '&(?:[a-zA-Z\d]+|\#\d+|\#x[a-fA-F\d]+);';
    const MINIFY_HTML_KEEP = '<pre(?:\s[^<>]*?)?>[\s\S]*?</pre>|<code(?:\s[^<>]*?)?>[\s\S]*?</code>|<script(?:\s[^<>]*?)?>[\s\S]*?</script>|<style(?:\s[^<>]*?)?>[\s\S]*?</style>|<textarea(?:\s[^<>]*?)?>[\s\S]*?</textarea>';
    const MINIFY_X = "\x1A";

    /**
     * Process object an then pass it to the next middleware
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param \Closure $next
     * @return object
     */
    public function process(
        $request,
        $response,
        $next
    )
    {
        /**
         * @var $response ResponseInterface
         */
        $response = $next($request, $response);
        $body = $response->getBody();
        $body->rewind();
        $content = $body->getContents();
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        $mimeType = $finfo->buffer($content);

        if ($response->hasHeader('Content-Type')) {
            $mimeType = $response->getHeader('Content-Type')[0];
        }

        if (strpos($mimeType, 'html')) {
            $response->getBody()->wipe();
            $response->getBody()->write($this->fn_minify_css_union($this->fn_minify_html_union_attr($content)));
        }

        if (strpos($mimeType, 'css')) {
            $response->getBody()->wipe();
            $response->getBody()->write($this->fn_minify_css_union($content));
        }

        if (strpos($mimeType, 'javascript')) {
            $response->getBody()->wipe();
            $response->getBody()->write($this->fn_minify_js_union($content));
        }


        return $response;
    }

    private function fn_minify_css_union($input)
    {
        if (stripos($input, 'calc(') !== false) {
            $input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i', function ($m) {
                return $m[1] . preg_replace('#\s+#', self::MINIFY_X, $m[2]) . ')';
            }, $input);
        }
        $input = preg_replace([
            '#(?<=[\w])\s+(\*|\[|:[\w-]+)#',
            '#([*\]\)])\s+(?=[\w\#.])#', '#\b\s+\(#', '#\)\s+\b#',
            '#\#([a-f\d])\1([a-f\d])\2([a-f\d])\3\b#i',
            '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
            '#\b(?<!\d\.)(?:0+\.)?0+(?:[a-z]+\b)#i',
            '#\b0+\.(\d+)#',
            '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
            '#\b(background(?:-position)?):(?:0|none)([;,\}])#i',
            '#\b(border(?:-radius)?|outline):none\b#i',
            '#(^|[\{\}])(?:[^\{\}]+)\{\}#',
            '#;+([;\}])#',
            '#\s+#'
        ], [
            self::MINIFY_X . '$1',
            '$1' . self::MINIFY_X, self::MINIFY_X . '(', ')' . self::MINIFY_X,
            '#$1$2$3',
            '$1',
            '0',
            '.$1',
            ':0',
            '$1:0 0$2',
            '$1:0',
            '$1',
            '$1',
            ' '
        ], $input);
        return trim(str_replace(self::MINIFY_X, ' ', $input));
    }

    private function t($a, $b)
    {
        if ($a && strpos($a, $b) === 0 && substr($a, -strlen($b)) === $b) {
            return substr(substr($a, strlen($b)), 0, -strlen($b));
        }
        return $a;
    }

    private function fn_minify_html_union_attr($input)
    {
        if (strpos($input, '=') === false) return $input;
        return preg_replace_callback('#=(' . self::MINIFY_STRING . ')#', function ($m) {
            $q = $m[1][0];
            if (strpos($m[1], ' ') === false && preg_match('#^' . $q . '[a-zA-Z_][\w-]*?' . $q . '$#', $m[1])) {
                return '=' . $this->t($m[1], $q);
            }
            return $m[0];
        }, $input);
    }

    private function fn_minify_js_union($input)
    {
        return preg_replace([
            '#\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#',
            '#[;,]([\]\}])#',
            '#\btrue\b#', '#\bfalse\b#', '#\b(return\s?)\s*\b#',
            '#\b(?:new\s+)?Array\((.*?)\)#', '#\b(?:new\s+)?Object\((.*?)\)#'
        ], [
            '$1',
            '$1',
            '!0', '!1', '$1',
            '[$1]', '{$1}'
        ], $input);
    }

}