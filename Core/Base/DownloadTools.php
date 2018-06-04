<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2018  Carlos García Gómez  <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Base;

use Exception;

/**
 * Description of DownloadTools
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class DownloadTools
{

    const USERAGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';

    /**
     * Downloads and returns url content with curl or file_get_contents.
     *
     * @param string $url
     * @param int    $timeOut
     *
     * @return string
     */
    public function getContents(string $url, int $timeOut = 30): string
    {
        if (\function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
            curl_setopt($ch, CURLOPT_USERAGENT, self::USERAGENT);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            if (ini_get('open_basedir') === null) {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            }

            $data = curl_exec($ch);
            $info = curl_getinfo($ch);
            if ($info['http_code'] === 200) {
                curl_close($ch);
                return $data;
            }

            if ($info['http_code'] === 301 || $info['http_code'] === 302) {
                $redirs = 0;
                return $this->curlRedirectExec($ch, $redirs);
            }

            /// guardamos en el log
            if ($info['http_code'] !== 404) {
                $error = (curl_error($ch) === '') ? 'ERROR ' . $info['http_code'] : curl_error($ch);
                $miniLog = new MiniLog();
                $miniLog->alert($error);
            }

            curl_close($ch);
            return 'ERROR';
        }

        return file_get_contents($url);
    }

    /**
     * Downloads file from selected url.
     *
     * @param string $url
     * @param string $fileName
     * @param int    $timeOut
     *
     * @return bool
     */
    public function download(string $url, string $fileName, int $timeOut = 30): bool
    {
        try {
            $data = $this->getContents($url, $timeOut);
            if ($data && $data !== 'ERROR' && file_put_contents($fileName, $data) !== false) {
                return true;
            }
        } catch (Exception $exc) {
            /// nothing to do
        }

        return false;
    }

    /**
     * Alternative function when followlocation fails.
     *
     * @param resource $ch
     * @param int      $redirects
     * @param bool     $curloptHeader
     *
     * @return string
     */
    private function curlRedirectExec($ch, &$redirects, $curloptHeader = false): string
    {
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code === 301 || $http_code === 302) {
            list($header) = explode("\r\n\r\n", $data, 2);
            $matches = [];
            preg_match("/(Location:|URI:)[^(\n)]*/", $header, $matches);
            $url = trim(str_replace($matches[1], '', $matches[0]));
            $urlParsed = parse_url($url);
            if (isset($urlParsed)) {
                curl_setopt($ch, CURLOPT_URL, $url);
                $redirects++;
                return $this->curlRedirectExec($ch, $redirects, $curloptHeader);
            }
        }

        if ($curloptHeader) {
            curl_close($ch);
            return $data;
        }

        list(, $body) = explode("\r\n\r\n", $data, 2);
        curl_close($ch);
        return $body;
    }
}
