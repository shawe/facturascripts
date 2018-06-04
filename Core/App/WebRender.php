<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018 Carlos García Gómez  <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\MiniLog;
use FacturaScripts\Core\Base\PluginManager;
use FacturaScripts\Core\Base\Translator;
use FacturaScripts\Dinamic\Lib\AssetManager;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Twig_Extension_Debug;
use Twig_Function;
use Twig_Loader_Filesystem;

/**
 * Description of WebRender
 *
 * @author Carlos García Gómez
 */
class WebRender
{

    /**
     * Translation engine.
     *
     * @var Translator
     */
    private $i18n;

    /**
     * FALSE if FacturaScripts is not installed already.
     *
     * @var bool
     */
    private $installed;

    /**
     * Loads template from the filesystem.
     *
     * @var Twig_Loader_Filesystem
     */
    private $loader;

    /**
     * App log manager.
     *
     * @var MiniLog
     */
    private $miniLog;

    /**
     * Plugin manager.
     *
     * @var PluginManager
     */
    private $pluginManager;

    /**
     * WebRender constructor.
     */
    public function __construct()
    {
        $this->installed = true;
        if (!\defined('FS_DEBUG')) {
            \define('FS_DEBUG', true);
            $this->installed = false;
        }

        $this->i18n = new Translator();
        $path = FS_DEBUG ? FS_FOLDER . '/Core/View' : FS_FOLDER . '/Dinamic/View';
        $this->loader = new Twig_Loader_Filesystem($path);
        $this->miniLog = new MiniLog();
        $this->pluginManager = new PluginManager();
    }

    /**
     * Return Twig environment with default options for Twig.
     *
     * @return Twig_Environment
     */
    public function getTwig(): Twig_Environment
    {
        $twig = new Twig_Environment($this->loader, $this->getOptions());

        /// asset functions
        $assetFunction = new Twig_Function('asset', function ($string) {
            $path = FS_ROUTE . '/';
            if (0 === strpos($string, $path)) {
                return $string;
            }
            return $path . $string;
        });
        $twig->addFunction($assetFunction);

        /// assetCombine functions
        $assetCombineFunction = new Twig_Function('assetCombine', function ($fileList) {
            return AssetManager::combine($fileList);
        });
        $twig->addFunction($assetCombineFunction);

        /// debug extension
        $twig->addExtension(new Twig_Extension_Debug());

        return $twig;
    }

    /**
     * Add all paths from Core and Plugins folders.
     */
    public function loadPluginFolders(): void
    {
        /// Core namespace
        try {
            $this->loader->addPath(FS_FOLDER . '/Core/View', 'Core');
        } catch (Twig_Error_Loader $e) {
            $this->miniLog->critical($this->i18n->trans('twig-error-loader', ['%error%' => $e->getMessage()]));
        }

        foreach ($this->pluginManager->enabledPlugins() as $pluginName) {
            $pluginPath = FS_FOLDER . '/Plugins/' . $pluginName . '/View';
            if (!file_exists($pluginPath)) {
                continue;
            }

            /// plugin namespace
            if (FS_DEBUG) {
                try {
                    $this->loader->addPath($pluginPath, 'Plugin' . $pluginName);
                    $this->loader->prependPath($pluginPath);
                } catch (Twig_Error_Loader $e) {
                    $this->miniLog->critical($this->i18n->trans('twig-error-loader', ['%error%' => $e->getMessage()]));
                }
            }
        }
    }

    /**
     * Returns the data into the standard output.
     *
     * @param string $template
     * @param array  $params
     *
     * @return string
     */
    public function render(string $template, array $params): string
    {
        $templateVars = [
            'i18n' => $this->i18n,
            'log' => $this->miniLog,
        ];
        foreach ($params as $key => $value) {
            $templateVars[$key] = $value;
        }

        $twig = $this->getTwig();
        try {
            return $twig->render($template, $templateVars);
        } catch (Twig_Error_Loader $e) {
            $this->miniLog->critical($this->i18n->trans('twig-error-loader', ['%error%' => $e->getMessage()]));
            return '';
        } catch (Twig_Error_Runtime $e) {
            $this->miniLog->critical($this->i18n->trans('twig-error-runtime', ['%error%' => $e->getMessage()]));
            return '';
        } catch (Twig_Error_Syntax $e) {
            $this->miniLog->critical($this->i18n->trans('twig-error-syntax', ['%error%' => $e->getMessage()]));
            return '';
        }
    }

    /**
     * Return default options for Twig.
     *
     * @return array
     */
    private function getOptions(): array
    {
        if ($this->installed) {
            return [
                'debug' => FS_DEBUG,
                'cache' => FS_FOLDER . '/MyFiles/Cache/Twig',
                'auto_reload' => true
            ];
        }

        return ['debug' => FS_DEBUG,];
    }
}
