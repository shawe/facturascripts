<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Base;

use FacturaScripts\Core\Lib\AssetManager;
use FacturaScripts\Core\Model;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class from which all FacturaScripts controllers must inherit.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class Controller
{
    /**
     * Constant for dinamic models.
     */
    const DIR_MODEL = '\\FacturaScripts\\Dinamic\\Model\\';

    /**
     * Contains a list of extra files to load: javascript, css, etc.
     *
     * @var array
     */
    public $assets;

    /**
     * Tools to work with currencies.
     *
     * @var DivisaTools
     */
    public $divisaTools;

    /**
     * Selected company.
     *
     * @var Model\Empresa|false
     */
    public $empresa;

    /**
     * Tools to work with numbers.
     *
     * @var NumberTools
     */
    public $numberTools;

    /**
     * User permissions on this controller.
     *
     * @var ControllerPermissions
     */
    public $permissions;

    /**
     * Request on which we can get data.
     *
     * @var Request
     */
    public $request;

    /**
     * Title of the page.
     *
     * @var string título de la página.
     */
    public $title;

    /**
     * Given uri, default is empty.
     *
     * @var string
     */
    public $uri;

    /**
     * User logged in.
     *
     * @var Model\User
     */
    public $user;

    /**
     * Cache access manager.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * It provides direct access to the database.
     *
     * @var DataBase
     */
    protected $dataBase;

    /**
     * Translator engine.
     *
     * @var Translator
     */
    protected $i18n;

    /**
     * App log manager.
     *
     * @var MiniLog
     */
    protected $miniLog;

    /**
     * HTTP Response object.
     *
     * @var Response
     */
    protected $response;

    /**
     * Name of the class of the controller (although its in inheritance from this class,
     * the name of the final class we will have here)
     *
     * @var string __CLASS__
     */
    private $className;

    /**
     * Name of the file for the template.
     *
     * @var string|false nombre_archivo.html.twig
     */
    private $template;

    /**
     * Initialize all objects and properties.
     *
     * @param Cache      $cache
     * @param Translator $i18n
     * @param MiniLog    $miniLog
     * @param string     $className
     * @param string     $uri
     */
    public function __construct(&$cache, &$i18n, &$miniLog, $className, $uri = '')
    {
        $this->assets = AssetManager::getAssetsForPage($className);
        $this->cache = &$cache;
        $this->className = $className;
        $this->dataBase = new DataBase();
        $this->divisaTools = new DivisaTools();
        $this->i18n = &$i18n;
        $this->miniLog = &$miniLog;
        $this->numberTools = new NumberTools();
        $this->request = Request::createFromGlobals();
        $this->template = $this->className . '.html.twig';
        $this->uri = $uri;

        $pageData = $this->getPageData();
        $this->title = empty($pageData) ? $this->className : $pageData['title'];
    }

    /**
     * Return the template to use for this controller.
     *
     * @return string|false
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set the template to use for this controller.
     *
     * @param string|false $template
     *
     * @return bool
     */
    public function setTemplate($template): bool
    {
        if ($template === false) {
            $this->template = false;

            return true;
        }

        $this->template = $template . '.html.twig';

        return true;
    }

    /**
     * Return the basic data for this page.
     *
     * @return array
     */
    public function getPageData(): array
    {
        return [
            'name' => $this->className,
            'title' => $this->className,
            'icon' => 'fa-circle-o',
            'menu' => 'new',
            'submenu' => null,
            'showonmenu' => true,
            'ordernum' => 100,
        ];
    }

    /**
     * Return the URL of the actual controller.
     *
     * @return string
     */
    public function url(): string
    {
        return $this->className;
    }

    /**
     * Execute the public part of the controller.
     *
     * @param Response $response
     */
    public function publicCore(&$response)
    {
        $this->response = &$response;
        $this->template = 'Login/Login.html.twig';
    }

    /**
     * Runs the controller's private logic.
     *
     * @param Response              $response
     * @param Model\User            $user
     * @param ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        $this->permissions = $permissions;
        $this->response = &$response;
        $this->user = $user;

        /// Select the default company for the user
        $empresaModel = new Model\Empresa();
        $this->empresa = $empresaModel->get($this->user->idempresa);

        /// This user have default page setted?
        $defaultPage = $this->request->query->get('defaultPage', '');
        $cookie = new Cookie('fsHomepage', $this->user->homepage, time() + FS_COOKIES_EXPIRE);
        if ($defaultPage === 'TRUE') {
            $this->user->homepage = $this->className;
            $this->response->headers->setCookie($cookie);
            $this->user->save();
        } elseif ($defaultPage === 'FALSE') {
            $this->user->homepage = null;
            $this->response->headers->setCookie($cookie);
            $this->user->save();
        }
    }

    /**
     * Return the name of the controller.
     *
     * @return string
     */
    protected function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Return array with parameters values
     *
     * @param array $keys
     *
     * @return array
     */
    protected function requestGet($keys): array
    {
        $result = [];
        foreach ($keys as $value) {
            $result[$value] = $this->request->get($value);
        }
        return $result;
    }
}
