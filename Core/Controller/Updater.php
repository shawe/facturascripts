<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\Base\DownloadTools;
use FacturaScripts\Core\Base\PluginManager;
use FacturaScripts\Core\Model\User;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

/**
 * Description of Updater
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class Updater extends Controller
{

    /**
     * TODO: Uncomplete documentation.
     */
    const UPDATE_CORE_URL = 'https://s3.eu-west-2.amazonaws.com/facturascripts/2018.zip';

    /**
     * TODO: Uncomplete documentation.
     * @var array
     */
    public $updaterItems = [];

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'admin';
        $pageData['submenu'] = 'control-panel';
        $pageData['title'] = 'updater';
        $pageData['icon'] = 'fa-cloud-download';

        return $pageData;
    }

    /**
     * Runs the controller's private logic.
     *
     * @param Response $response
     * @param User $user
     * @param ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        /// Folders writables?
        $folders = $this->notWritablefolders();
        if (!empty($folders)) {
            $this->miniLog->alert($this->i18n->trans('folder-not-writable'));
            foreach ($folders as $folder) {
                $this->miniLog->alert($folder);
            }
            return;
        }

        $this->updaterItems[] = [
            'id' => 'CORE',
            'description' => 'Core component',
            'downloaded' => file_exists(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip')
        ];

        $action = $this->request->get('action', '');
        $this->execAction($action);
    }

    /**
     * Erase $dir folder and all its subfolders.
     *
     * @param string $dir
     *
     * @return bool
     */
    private function delTree(string $dir): bool
    {
        $files = array_diff(scandir($dir, SCANDIR_SORT_ASCENDING), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$dir/$file") ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }

    /**
     * Downloads core zip.
     */
    private function download()
    {
        if (file_exists(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip')) {
            unlink(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip');
        }

        $downloader = new DownloadTools();
        if ($downloader->download(self::UPDATE_CORE_URL, FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip')) {
            $this->miniLog->info('download-completed');
            $this->updaterItems[0]['downloaded'] = true;
        }
    }

    /**
     * Execute selected action.
     *
     * @param string $action
     */
    private function execAction(string $action)
    {
        switch ($action) {
            case 'download':
                $this->download();
                break;

            case 'update':
                $this->update();
                $pluginManager = new PluginManager();
                $pluginManager->initControllers();
                break;
        }
    }

    /**
     * Returns an array with all subforder of $baseDir folder.
     *
     * @param string $baseDir
     *
     * @return array
     */
    private function foldersFrom(string $baseDir): array
    {
        $directories = [];
        $files = array_diff(scandir($baseDir, SCANDIR_SORT_ASCENDING), ['.', '..']);
        foreach ($files as $file) {
            $dir = $baseDir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($dir)) {
                $directories[] = $dir;
                /**
                 * 'array_merge(...)' is used in a loop and is a resources greedy construction.
                 * https://github.com/kalessil/phpinspectionsea/blob/master/docs/performance.md#slow-array-function-used-in-loop
                 */
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $directories = array_merge($directories, $this->foldersFrom($dir));
            }
        }

        return $directories;
    }

    /**
     * Returns an array with all not writable folders.
     *
     * @return array
     */
    private function notWritablefolders(): array
    {
        $notWritable = [];
        $count = 0;
        foreach ($this->foldersFrom(FS_FOLDER) as $dir) {
            if (!is_writable($dir) && !$this->fixWritable($dir)) {
                $notWritable[] = $dir;
                $count++;
            }
        }

        if ($count > 0) {
            $this->showSolution();
        }

        return $notWritable;
    }

    /**
     * Show command to solve this problem.
     */
    private function showSolution()
    {
        $correctOwner = \posix_getpwuid(\posix_geteuid())['name'];
        $correctGroup = \posix_getgrgid(\posix_getgid())['name'];

        $chmodDir = $this->getDefaultPerms(false);
        $chmodFile = $this->getDefaultPerms(true);

        $this->miniLog->critical(
            'Can\'t be auto fixed. You need to execute from shell: ' . '<br/>' .
            'find ' . \FS_FOLDER . ' -exec chown ' . $correctOwner . ':' . $correctGroup . ' {} \;' . '<br/>' .
            'find ' . \FS_FOLDER . ' -type d -exec chmod ' . $chmodDir . ' {} \;' . '<br/>' .
            'find ' . \FS_FOLDER . ' -type f -exec chmod ' . $chmodFile . ' {} \;'
        );
    }

    /**
     * Returns default permissions for file or folder.
     * If not correctOwner or realFileOwner received, readed from execution.
     *
     * @param bool $isFile
     * @param string $correctOwner
     * @param string $realFileOwner
     *
     * @return string
     */
    private function getDefaultPerms($isFile, $correctOwner = '', $realFileOwner = ''): string
    {
        if ($correctOwner === '') {
            $correctOwner = \posix_getpwuid(\posix_geteuid())['name'];
        }
        if ($realFileOwner === '') {
            $realFileOwner = \posix_getpwuid(\fileowner(\FS_FOLDER))['name'];
        }

        /// Needed in common hostings accounts
        $string = $isFile ? '0644' : '0755';
        if ($correctOwner !== $realFileOwner) {
            /// Needed with Apache userdir and some virtualhost configurations
            $string = $isFile ? '0664' : '0775';
        }
        return $string;
    }

    /**
     * Try to fix not writable folder.
     *
     * @param string $dir
     *
     * @return bool
     */
    private function fixWritable(string $dir): bool
    {
        // Apache user/group setted
        $correctOwner = \posix_getpwuid(\posix_geteuid())['name'];
        $correctGroup = \posix_getgrgid(\posix_getgid())['name'];
        // Owner user/group
        $realFileOwner = \posix_getpwuid(\fileowner($dir))['name'];
        $realFileGroup = \posix_getgrgid(\filegroup($dir))['name'];

        $allowedOwners = ['root', 'www-data', 'http', $correctOwner];
        $allowedGroups = ['root', 'www-data', 'http', $correctGroup];

        if (!$this->chModR($dir)) {
            if ($realFileGroup !== $correctGroup && !\in_array($realFileGroup, $allowedGroups, true)) {
                if ($this->chGrpR($dir, $correctGroup)) {
                    return is_writable($dir);
                }
                if ($this->chOwnR($dir, $correctOwner)) {
                    return is_writable($dir);
                }
                return false;
            }
            if ($realFileOwner !== $correctOwner && !\in_array($realFileOwner, $allowedOwners, true)) {
                if ($this->chOwnR($dir, $correctOwner)) {
                    return is_writable($dir);
                }
                if ($this->chOwnR($dir, $correctOwner)) {
                    return is_writable($dir);
                }
                return false;
            }
        }

        return is_writable($dir);
    }

    /**
     * Return a list of disabled php functions.
     *
     * @return array
     */
    private function getPhpDisabledFunctions(): array
    {
        return explode(',', ini_get('disable_functions'));
    }

    /**
     * Copy all files and folders from $src to $dst
     *
     * @param string $src
     * @param string $dst
     */
    private function recurseCopy(string $src, string $dst)
    {
        $dir = opendir($src);
        if (!mkdir($dst) && !is_dir($dst)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dst));
        }
        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir($src . '/' . $file)) {
                $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
        closedir($dir);
    }

    /**
     * Extract zip file and update all files.
     *
     * @return bool
     */
    private function update(): bool
    {
        $zip = new ZipArchive();
        $zipStatus = $zip->open(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip', ZipArchive::CHECKCONS);
        if ($zipStatus !== true) {
            $this->miniLog->critical('ZIP ERROR: ' . $zipStatus);
            return false;
        }

        $zip->extractTo(FS_FOLDER);
        $zip->close();
        unlink(FS_FOLDER . DIRECTORY_SEPARATOR . 'update-core.zip');

        foreach (['Core', 'node_modules', 'vendor'] as $folder) {
            $origin = FS_FOLDER . DIRECTORY_SEPARATOR . 'facturascripts' . DIRECTORY_SEPARATOR . $folder;
            $dest = FS_FOLDER . DIRECTORY_SEPARATOR . $folder;
            if (!file_exists($origin)) {
                $this->miniLog->critical('COPY ERROR: ' . $origin);
                break;
            }

            $this->delTree($dest);
            $this->recurseCopy($origin, $dest);
        }

        $this->delTree(FS_FOLDER . DIRECTORY_SEPARATOR . 'facturascripts');
        return true;
    }

    /**
     * Calls to chgrp recursively.
     *
     * @param string $path
     * @param string $group
     *
     * @return bool
     */
    private function chGrpR(string $path, string $group): bool
    {
        if (\in_array('chgrp', $this->getPhpDisabledFunctions(), true)) {
            $this->miniLog->critical(
                'chgrp is a disabled function.'
            );
            return false;
        }

        if (!is_dir($path)) {
            return @chgrp($path, $group);
        }

        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $path . '/' . $file;
                if (is_link($fullPath)) {
                    return false;
                }
                if (!is_dir($fullPath) && !@chgrp($fullPath, $group)) {
                    return false;
                }
                if (!$this->chGrpR($fullPath, $group)) {
                    return false;
                }
            }
        }

        closedir($dh);

        return @chgrp($path, $group);
    }

    /**
     * Calls to chmod recursively.
     *
     * @param string $path
     * @param string $fileMode
     *
     * @return bool
     */
    private function chModR(string $path, string $fileMode = ''): bool
    {
        if (\in_array('chmod', $this->getPhpDisabledFunctions(), true)) {
            $this->miniLog->critical(
                'chmod is a disabled function.'
            );
            return false;
        }

        if ($fileMode === '') {
            $fileMode = $this->getDefaultPerms(is_file($path));
        }

        if ($this->isOctal($fileMode)) {
            if (!is_dir($path)) {
                return @chmod($path, $fileMode);
            }

            $dh = opendir($path);
            while (($file = readdir($dh)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $fullPath = $path . '/' . $file;
                    if (is_link($fullPath)) {
                        return false;
                    }
                    if (!is_dir($fullPath) && !@chmod($fullPath, $fileMode)) {
                        return false;
                    }
                    if (!$this->chModR($fullPath, $fileMode)) {
                        return false;
                    }
                }
            }

            closedir($dh);

            return @chmod($path, $fileMode);
        }

        $this->miniLog->critical(
            '"' . $fileMode . '" : Is not an octal file mode.'
        );
        return false;
    }

    /**
     * Returns if is octal file mode.
     *
     * @param string $fileMode
     *
     * @return bool
     */
    private function isOctal($fileMode): bool
    {
        $formatted = \str_pad(
            decoct(octdec($fileMode)),
            4,
            0,
            \STR_PAD_LEFT
        );
        return $formatted === $fileMode;
    }

    /**
     * Calls to chown recursively.
     *
     * @param string $path
     * @param string $owner
     *
     * @return bool
     */
    private function chOwnR(string $path, string $owner): bool
    {
        if (\in_array('chown', $this->getPhpDisabledFunctions(), true)) {
            $this->miniLog->critical(
                'chmod is a disabled function.'
            );
            return false;
        }

        if (!is_dir($path)) {
            return @chown($path, $owner);
        }

        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $fullPath = $path . '/' . $file;
                if (is_link($fullPath)) {
                    return false;
                }
                if (!is_dir($fullPath) && !@chown($fullPath, $owner)) {
                    return false;
                }
                if (!$this->chOwnR($fullPath, $owner)) {
                    return false;
                }
            }
        }

        closedir($dh);

        return @chown($path, $owner);
    }
}
