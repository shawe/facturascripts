<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018 Carlos García Gómez <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\App;

use FacturaScripts\Core\Base;
use FacturaScripts\Dinamic\Lib\IPFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * App class is used for encapsulate common parts of code for the normal App execution.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
abstract class App
{

    /**
     * Cache access manager.
     *
     * @var Base\Cache
     */
    protected $cache;

    /**
     * Database access manager.
     *
     * @var Base\DataBase
     */
    protected $dataBase;

    /**
     * Translation engine.
     *
     * @var Base\Translator
     */
    protected $i18n;

    /**
     * IP filter.
     *
     * @var IPFilter
     */
    protected $ipFilter;

    /**
     * App log manager.
     *
     * @var Base\MiniLog
     */
    protected $miniLog;

    /**
     * Plugin manager.
     *
     * @var Base\PluginManager
     */
    protected $pluginManager;

    /**
     * Gives us access to the HTTP request parameters.
     *
     * @var Request
     */
    protected $request;

    /**
     * HTTP response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * Stored defaut configuration with the application settings.
     *
     * @var AppSettings
     */
    protected $settings;

    /**
     * Requested Uri
     *
     * @var string
     */
    protected $uri;

    /**
     * Initializes the app.
     *
     * @param string $uri
     */
    public function __construct(string $uri = '/')
    {
        $this->request = Request::createFromGlobals();

        if ($this->request->cookies->get('fsLang')) {
            $this->i18n = new Base\Translator($this->request->cookies->get('fsLang'));
        } else {
            $this->i18n = new Base\Translator();
        }

        $this->cache = new Base\Cache();
        $this->dataBase = new Base\DataBase();
        $this->ipFilter = new IPFilter();
        $this->miniLog = new Base\MiniLog();
        $this->pluginManager = new Base\PluginManager();
        $this->response = new Response();
        $this->settings = new AppSettings();
        $this->uri = $uri;

        /// timezone
        date_default_timezone_set(FS_TIMEZONE);

        $this->miniLog->debug('URI: ' . $this->uri);
    }

    /**
     * Connects to the database and loads the configuration.
     *
     * @return bool
     */
    public function connect(): bool
    {
        if ($this->dataBase->connect()) {
            $this->settings->load();
            return true;
        }

        return false;
    }

    /**
     * Save log and disconnects from the database.
     *
     * @param string $nick
     */
    public function close(string $nick = ''): void
    {
        new Base\MiniLogSave($this->request->getClientIp(), $nick);
        $this->dataBase->close();
    }

    /**
     * Selects and runs the corresponding controller.
     *
     * @return bool
     */
    abstract public function run(): bool;

    /**
     * Returns the data into the standard output.
     */
    public function render(): void
    {
        $this->response->send();
    }

    /**
     * Returns param number $num in uri.
     *
     * @param string $num
     *
     * @return string
     */
    protected function getUriParam(string $num): string
    {
        $params = explode('/', substr($this->uri, 1));
        return $params[$num] ?? '';
    }

    /**
     * Returns true if the client IP has been banned.
     *
     * @return bool
     */
    protected function isIPBanned(): bool
    {
        return $this->ipFilter->isBanned($this->request->getClientIp());
    }
}
