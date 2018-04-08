<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
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

/**
 * Description of PluginDeploy
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class PluginDeploy
{
    /**
     * The directory separator.
     */
    const DS = \DIRECTORY_SEPARATOR;

    /**
     * System translator.
     *
     * @var Translator
     */
    private $i18n;

    /**
     * Manage the log of the entire application.
     *
     * @var Minilog
     */
    private $minilog;

    /**
     * PluginDeploy constructor.
     */
    public function __construct()
    {
        $this->i18n = new Translator();
        $this->minilog = new MiniLog();
    }

    /**
     * Deploy all the necessary files in the Dinamic folder to be able to use plugins
     * with the autoloader, but following the priority system of FacturaScripts.
     *
     * @param string $pluginPath
     * @param array $enabledPlugins
     * @param bool $clean
     */
    public function deploy($pluginPath, $enabledPlugins, $clean = true)
    {
        $folders = ['Assets', 'Controller', 'Model', 'Lib', 'Table', 'View', 'XMLView'];
        foreach ($folders as $folder) {
            if ($clean) {
                $this->cleanFolder(FS_FOLDER . self::DS . 'Dinamic' . self::DS . $folder);
            }

            $this->createFolder(FS_FOLDER . self::DS . 'Dinamic' . self::DS . $folder);

            /// examine the plugins
            foreach ($enabledPlugins as $pluginName) {
                if (file_exists($pluginPath . $pluginName . self::DS . $folder)) {
                    $this->linkFiles($folder, 'Plugins', $pluginName);
                }
            }

            /// examine the core
            if (file_exists(FS_FOLDER . self::DS . 'Core' . self::DS . $folder)) {
                $this->linkFiles($folder);
            }
        }
    }

    /**
     * Delete the $folder and its files.
     *
     * @param string $folder
     *
     * @return bool
     */
    private function cleanFolder($folder): bool
    {
        $done = true;

        if (file_exists($folder)) {
            /// Comprobamos los archivos que no son '.' ni '..'
            $items = array_diff(scandir($folder, SCANDIR_SORT_ASCENDING), ['.', '..']);

            /// Ahora recorremos y eliminamos lo que encontramos
            foreach ($items as $item) {
                if (is_dir($folder . self::DS . $item)) {
                    $done = $this->cleanFolder($folder . self::DS . $item . self::DS);
                } else {
                    $done = unlink($folder . self::DS . $item);
                }
            }
        }

        return $done;
    }

    /**
     * Create the folder.
     *
     * @param string $folder
     *
     * @return bool
     */
    private function createFolder($folder): bool
    {
        if (!file_exists($folder) && !@mkdir($folder, 0775, true) && !is_dir($folder)) {
            $this->minilog->critical($this->i18n->trans('cant-create-folder', ['%folderName%' => $folder]));

            return false;
        }

        return true;
    }

    /**
     * Link the files.
     *
     * @param string $folder
     * @param string $place
     * @param string $pluginName
     */
    private function linkFiles($folder, $place = 'Core', $pluginName = '')
    {
        if (empty($pluginName)) {
            $path = FS_FOLDER . self::DS . $place . self::DS . $folder;
        } else {
            $path = FS_FOLDER . self::DS . 'Plugins' . self::DS . $pluginName . self::DS . $folder;
        }

        foreach ($this->scanFolders($path) as $fileName) {
            $infoFile = pathinfo($fileName);
            if (is_dir($path . self::DS . $fileName)) {
                $this->createFolder(FS_FOLDER . self::DS . 'Dinamic' . self::DS . $folder . self::DS . $fileName);
            } elseif ($infoFile['filename'] !== '' && is_file($path . self::DS . $fileName)) {
                if ($infoFile['extension'] === 'php') {
                    $this->linkClassFile($fileName, $folder, $place, $pluginName);
                } else {
                    $filePath = $path . self::DS . $fileName;
                    $this->linkFile($fileName, $folder, $filePath);
                }
            }
        }
    }

    /**
     * Link classes dynamically.
     *
     * @param string $fileName
     * @param string $folder
     * @param string $place
     * @param string $pluginName
     */
    private function linkClassFile($fileName, $folder, $place, $pluginName)
    {
        if (!file_exists(FS_FOLDER . self::DS . 'Dinamic' . self::DS . $folder . self::DS . $fileName)) {
            if (empty($pluginName)) {
                $namespace = 'FacturaScripts\\' . $place . '\\' . $folder;
                $newNamespace = 'FacturaScripts\\Dinamic\\' . $folder;
            } else {
                $namespace = "FacturaScripts\Plugins\\" . $pluginName . '\\' . $folder;
                $newNamespace = "FacturaScripts\Dinamic\\" . $folder;
            }

            $paths = explode(self::DS, $fileName);
            for ($key = 0; $key < count($paths) - 1; ++$key) {
                $namespace .= '\\' . $paths[$key];
                $newNamespace .= '\\' . $paths[$key];
            }

            $className = basename($fileName, '.php');
            $txt = '<?php namespace ' . $newNamespace . ";" . \PHP_EOL . \PHP_EOL
                . '/**' . \PHP_EOL
                . ' * Class created by Core/Base/PluginManager' . \PHP_EOL
                . ' * @package ' . $newNamespace . \PHP_EOL
                . ' * @author Carlos García Gómez <carlos@facturascripts.com>' . \PHP_EOL
                . ' */' . \PHP_EOL
                . 'class ' . $className . ' extends \\' . $namespace . '\\' . $className . \PHP_EOL . '{' . \PHP_EOL . '}' . \PHP_EOL;

            file_put_contents(FS_FOLDER . self::DS . 'Dinamic' . self::DS . $folder . self::DS . $fileName, $txt);
        }
    }

    /**
     * Link other static files.
     *
     * @param string $fileName
     * @param string $folder
     * @param string $filePath
     */
    private function linkFile($fileName, $folder, $filePath)
    {
        if (!file_exists(FS_FOLDER . self::DS . 'Dinamic' . self::DS . $folder . self::DS . $fileName)) {
            @copy($filePath, FS_FOLDER . self::DS . 'Dinamic' . self::DS . $folder . self::DS . $fileName);
        }
    }

    /**
     * Makes a recursive scan in folders inside a root folder and extracts the list of files
     * and pass its to an array as result.
     *
     * @param string $folder
     *
     * @return array $result
     */
    private function scanFolders($folder): array
    {
        $result = [];
        $rootFolder = array_diff(scandir($folder, SCANDIR_SORT_ASCENDING), ['.', '..']);
        foreach ($rootFolder as $item) {
            $newItem = $folder . self::DS . $item;
            if (is_file($newItem)) {
                $result[] = $item;
                continue;
            }
            $result[] = $item;
            foreach ($this->scanFolders($newItem) as $item2) {
                $result[] = $item . self::DS . $item2;
            }
        }

        return $result;
    }
}
