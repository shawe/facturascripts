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
     * Remote URL for update FacturaScripts Core
     */
    const UPDATE_CORE_URL = 'https://s3.eu-west-2.amazonaws.com/facturascripts/2018.zip';

    /**
     * List of items to be updated.
     *
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
     * @param Response              $response
     * @param User                  $user
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
                 * FIXME: 'array_merge(...)' is used in a loop and is a resources greedy construction.
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
        foreach ($this->foldersFrom(FS_FOLDER) as $dir) {
            if (!is_writable($dir)) {
                $notWritable[] = $dir;
            }
        }

        if (count($notWritable) > 0) {
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
     * @param bool   $isFile
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
}
