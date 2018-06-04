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

namespace FacturaScripts\Core\Controller;

use FacturaScripts\Dinamic\Lib\EmailTools;
use FacturaScripts\Dinamic\Lib\ExtendedController;
use FacturaScripts\Dinamic\Model\Settings;

/**
 * Controller to edit main settings
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class EditSettings extends ExtendedController\PanelController
{
    /**
     * Constant start of name for generic settings.
     */
    const KEY_SETTINGS = 'Settings';

    /**
     * Returns the configuration property value for a specified $field
     *
     * @param Settings $model
     * @param string   $field
     *
     * @return mixed
     */
    public function getViewModelValue($model, $field)
    {
        if (isset($model->{$field})) {
            return $model->{$field};
        }

        if (\is_array($model->properties) && array_key_exists($field, $model->properties)) {
            return $model->properties[$field];
        }

        return null;
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'app-preferences';
        $pageData['icon'] = 'fa-cogs';
        $pageData['menu'] = 'admin';
        $pageData['submenu'] = 'control-panel';

        return $pageData;
    }

    /**
     * Returns the url for a specified $type
     *
     * @param string $type
     *
     * @return string
     */
    public function getURL($type): string
    {
        switch ($type) {
            case 'list':
                return 'AdminPlugins';

            case 'edit':
                return 'EditSettings';
        }

        return FS_ROUTE;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        $modelName = 'Settings';
        $icon = $this->getPageData()['icon'];
        foreach ($this->allSettingsXMLViews() as $name) {
            $title = strtolower(substr($name, 8));
            $this->addEditView($name, $modelName, $title, $icon);
        }

        $this->testViews();
    }

    /**
     * Run the controller after actions
     *
     * @param string $action
     */
    protected function execAfterAction(string $action)
    {
        switch ($action) {
            case 'export':
                $this->setTemplate(false);
                $this->exportAction();
                break;

            case 'testmail':
                $emailTools = new EmailTools();
                if ($emailTools->test()) {
                    $this->miniLog->info($this->i18n->trans('mail-test-ok'));
                } else {
                    $this->miniLog->error($this->i18n->trans('mail-test-error'));
                }
                break;
        }
    }

    /**
     * Load view data
     *
     * @param string                      $viewName
     * @param ExtendedController\EditView $view
     */
    protected function loadData($viewName, $view)
    {
        if (empty($view->getModel())) {
            return;
        }

        $code = $this->getKeyFromViewName($viewName);
        $view->loadData($code);

        if ($view->model->name === null) {
            $view->model->description = $view->model->name = strtolower(substr($viewName, 8));
            $view->model->save();
        }
    }

    /**
     * Return a list of all XML view files on XMLView folder.
     *
     * @return array
     */
    private function allSettingsXMLViews(): array
    {
        $names = [];
        $files = array_diff(scandir(FS_FOLDER . '/Dinamic/XMLView', SCANDIR_SORT_ASCENDING), ['.', '..']);
        foreach ($files as $fileName) {
            if (0 === strpos($fileName, self::KEY_SETTINGS)) {
                $names[] = substr($fileName, 0, -4);
            }
        }

        return $names;
    }

    /**
     * Exports data from views.
     */
    private function exportAction()
    {
        $this->exportManager->newDoc($this->request->get('option'));
        foreach ($this->views as $view) {
            if ($view->model === null || !isset($view->model->properties)) {
                continue;
            }

            $headers = ['key' => 'key', 'value' => 'value'];
            $rows = [];
            foreach ($view->model->properties as $key => $value) {
                $rows[] = ['key' => $key, 'value' => $value];
            }

            if (count($rows) > 0) {
                $this->exportManager->generateTablePage($headers, $rows);
            }
        }

        $this->exportManager->show($this->response);
    }

    /**
     * Returns the view id for a specified $viewName
     *
     * @param string $viewName
     *
     * @return string
     */
    private function getKeyFromViewName($viewName): string
    {
        return strtolower(substr($viewName, \strlen(self::KEY_SETTINGS)));
    }

    /**
     * Test all view to show usefull errors.
     */
    private function testViews()
    {
        foreach ($this->views as $viewName => $view) {
            if (!$view->model) {
                continue;
            }

            $error = true;
            foreach ($view->getColumns() as $group) {
                if (!isset($group->columns)) {
                    break;
                }

                foreach ($group->columns as $col) {
                    if ($col->name === 'name') {
                        $error = false;
                        break;
                    }
                }

                break;
            }

            if ($error) {
                $this->miniLog->critical($this->i18n->trans('error-no-name-in-settings', ['%viewName%' => $viewName]));
            }
        }
    }
}
