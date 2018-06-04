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

namespace FacturaScripts\Core\Lib\ExtendedController;

use FacturaScripts\Core\Base;
use FacturaScripts\Dinamic\Lib\ExportManager;
use FacturaScripts\Dinamic\Model\CodeModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Description of BaseController
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
abstract class BaseController extends Base\Controller
{

    /**
     * Indicates the active view.
     *
     * @var string
     */
    public $active;

    /**
     * Model to use with select and autocomplete filters.
     *
     * @var CodeModel
     */
    public $codeModel;

    /**
     * Object to export data.
     *
     * @var ExportManager
     */
    public $exportManager;

    /**
     * List of views displayed by the controller.
     *
     * @var EditListView[]|EditView[]|GridView[]|ListView[]
     */
    public $views;

    /**
     * List of configuration options for each of the views.
     * [
     *   'keyView1' => ['icon' => 'fa-icon1', 'active' => TRUE],
     *   'keyView2' => ['icon' => 'fa-icon2', 'active' => TRUE]
     * ]
     *
     * @var array
     */
    public $settings;

    /**
     * Initializes all the objects and properties.
     *
     * @param Base\Cache      $cache
     * @param Base\Translator $i18n
     * @param Base\MiniLog    $miniLog
     * @param string          $className
     * @param string          $uri
     */
    public function __construct(&$cache, &$i18n, &$miniLog, $className, $uri = '')
    {
        parent::__construct($cache, $i18n, $miniLog, $className, $uri);

        $this->active = $this->request->get('active', '');
        $this->codeModel = new CodeModel();
        $this->exportManager = new ExportManager();
        $this->views = [];
        $this->settings = [];
    }

    /**
     * Returns the configuration value for the indicated view.
     *
     * @param string|null $viewName
     * @param string|null $property
     *
     * @return mixed
     */
    public function getSettings($viewName, $property)
    {
        if (empty($viewName)) {
            return $this->settings;
        }

        if (empty($property)) {
            return $this->settings[$viewName];
        }

        return $this->settings[$viewName][$property];
    }

    /**
     * Set value for setting of a view
     *
     * @param string $viewName
     * @param string $property
     * @param mixed  $value
     */
    public function setSettings($viewName, $property, $value)
    {
        $this->settings[$viewName][$property] = $value;
    }

    /**
     * Returns the configuration property value for a specified $field.
     *
     * @param mixed  $model
     * @param string $field
     *
     * @return mixed
     */
    public function getFieldValue($model, $field)
    {
        return isset($model->{$field}) ? $model->{$field} : null;
    }

    /**
     * Inserts the views to display.
     */
    abstract protected function createViews();

    /**
     * Run the autocomplete action.
     * Returns a JSON string for the searched values.
     *
     * @return array
     */
    protected function autocompleteAction(): array
    {
        $results = [];
        $data = $this->requestGet(['source', 'field', 'title', 'term']);
        foreach ($this->codeModel::search($data['source'], $data['field'], $data['title'], $data['term']) as $value) {
            $results[] = ['key' => $value->code, 'value' => $value->description];
        }
        return $results;
    }

    /**
     * @return array
     */
    protected function getFormData(): array
    {
        $data = $this->request->request->all();

        /// get file uploads
        foreach ($this->request->files->all() as $key => $uploadFile) {
            if (is_null($uploadFile)) {
                continue;
            }
            if ($uploadFile instanceof UploadedFile) {
                if (!$uploadFile->isValid()) {
                    $this->miniLog->error($uploadFile->getErrorMessage());
                    continue;
                }

                /// exclude php files
                if (\in_array($uploadFile->getClientMimeType(), ['application/x-php', 'text/x-php'])) {
                    $this->miniLog->error($this->i18n->trans('php-files-blocked'));
                    continue;
                }

                if ($uploadFile->move(FS_FOLDER . DIRECTORY_SEPARATOR . 'MyFiles', $uploadFile->getClientOriginalName())) {
                    $data[$key] = $uploadFile->getClientOriginalName();
                }
            }
        }

        return $data;
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
