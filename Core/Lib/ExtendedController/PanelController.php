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
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Dinamic\Model\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to edit data through the vertical panel
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
abstract class PanelController extends BaseController
{

    /**
     * Indicates if the main view has data or is empty.
     *
     * @var bool
     */
    public $hasData;

    /**
     * Tabs position in page: left, bottom.
     *
     * @var string
     */
    public $tabsPosition;

    /**
     * Starts all the objects and properties.
     *
     * @param Base\Cache      $cache
     * @param Base\Translator $i18n
     * @param Base\MiniLog    $miniLog
     * @param string          $className
     * @param string          $uri
     */
    public function __construct(Cache $cache, Translator $i18n, MiniLog $miniLog, string $className, string $uri = '')
    {
        parent::__construct($cache, $i18n, $miniLog, $className, $uri);

        $this->hasData = false;
        $this->setTabsPosition('left');
    }

    /**
     * Descriptive identifier for humans of the main data editing record.
     *
     * @return string
     */
    public function getPrimaryDescription(): string
    {
        $viewName = array_keys($this->views)[0];
        return $this->views[$viewName]->model->primaryDescription();
    }

    /**
     * Returns the url for a specified type.
     *
     * @param string $type
     *
     * @return string
     */
    public function getURL($type): string
    {
        $view = array_values($this->views)[0];
        return $view->getURL($type);
    }

    /**
     * Return the value for a field in the model of the view.
     *
     * @param string $viewName
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getViewModelValue(string $viewName, string $fieldName)
    {
        $model = $this->views[$viewName]->model;
        return isset($model->{$fieldName}) ? $model->{$fieldName} : null;
    }

    /**
     * Runs the controller's private logic.
     *
     * @param Response              $response
     * @param User                  $user
     * @param ControllerPermissions $permissions
     */
    public function privateCore(Response $response, User $user, ControllerPermissions $permissions): void
    {
        parent::privateCore($response, $user, $permissions);

        // Create the views to display
        $this->createViews();

        // Get any operations that have to be performed
        $action = $this->request->get('action', '');

        // Run operations on the data before reading it
        if (!$this->execPreviousAction($action)) {
            return;
        }

        // Load the model data for each view
        $mainViewName = array_keys($this->views)[0];
        foreach ($this->views as $viewName => $view) {
            $this->loadData($viewName, $view);

            // check if we are processing the main view
            if ($viewName == $mainViewName) {
                $this->hasData = $view->count > 0;
                continue;
            }

            // check if the view should be active
            $this->setSettings($viewName, 'active', $this->hasData);
        }

        // General operations with the loaded data
        $this->execAfterAction($action);
    }

    /**
     * Sets the tabs position, by default is setted to 'left', also supported 'bottom' and 'top'.
     *
     * @param string $position
     */
    public function setTabsPosition(string $position): void
    {
        $this->tabsPosition = $position;

        switch ($position) {
            case 'bottom':
                $this->setTemplate('Master/PanelControllerBottom');
                break;

            case 'top':
                $this->setTemplate('Master/PanelControllerTop');
                break;

            default:
                $this->tabsPosition = 'left';
                $this->setTemplate('Master/PanelController');
                break;
        }
    }

    /**
     * Returns the view class.
     *
     * @param string $view
     *
     * @return string
     */
    public function viewClass(string $view): string
    {
        $result = explode('\\', get_class($view));
        return end($result);
    }

    /**
     * Loads the data to display.
     *
     * @param string   $viewName
     * @param BaseView $view
     */
    abstract protected function loadData($viewName, $view);

    /**
     * Adds a EditList type view to the controller
     *
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addEditListView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fa-bars'): void
    {
        $view = new EditListView($viewTitle, self::MODEL_NAMESPACE . $modelName, $viewName, $this->user->nick);
        $this->addView($viewName, $view, $viewIcon);
    }

    /**
     * Adds a Edit type view to the controller.
     *
     * @param string $viewName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addEditView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fa-list-alt'): void
    {
        $view = new EditView($viewTitle, self::MODEL_NAMESPACE . $modelName, $viewName, $this->user->nick);
        $this->addView($viewName, $view, $viewIcon);
    }

    /**
     * Adds a Grid type view to the controller.
     *
     * @param        $viewName
     * @param        $parentView
     * @param        $modelName
     * @param        $viewTitle
     * @param string $viewIcon
     */
    protected function addGridView(string $viewName, $parentView, string $modelName, string $viewTitle, string $viewIcon = 'fa-list'): void
    {
        $parent = $this->views[$parentView];
        if (isset($parent)) {
            $view = new GridView($parent, $viewTitle, self::MODEL_NAMESPACE . $modelName, $viewName, $this->user->nick);
            $this->addView($viewName, $view, $viewIcon);
        }
    }

    /**
     * Adds a HTML type view to the controller.
     *
     * @param string $viewName
     * @param string $fileName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addHtmlView(string $viewName, string $fileName, string $modelName, string $viewTitle, string $viewIcon = 'fa-html5'): void
    {
        $view = new HtmlView($viewTitle, self::MODEL_NAMESPACE . $modelName, $fileName);
        $this->addView($viewName, $view, $viewIcon);
    }

    /**
     * Adds a List type view to the controller.
     *
     * @param string $viewName
     * @param string $modelName
     * @param string $viewTitle
     * @param string $viewIcon
     */
    protected function addListView(string $viewName, string $modelName, string $viewTitle, string $viewIcon = 'fa-bars'): void
    {
        $view = new ListView($viewTitle, self::MODEL_NAMESPACE . $modelName, $viewName, $this->user->nick);
        $this->addView($viewName, $view, $viewIcon);
    }

    /**
     * Adds a view to the controller and loads its data
     *
     * @param string   $keyView
     * @param BaseView $view
     * @param string   $icon
     */
    protected function addView(string $keyView, $view, string $icon): void
    {
        $this->views[$keyView] = $view;
        $this->settings[$keyView] = ['active' => true, 'icon' => $icon];

        if (empty($this->active)) {
            $this->active = $keyView;
        }
    }

    /**
     * Action to delete data
     *
     * @return bool
     */
    protected function deleteAction(): bool
    {
        if (!$this->permissions->allowDelete) {
            $this->miniLog->alert($this->i18n->trans('not-allowed-delete'));
            return false;
        }
        $model = $this->views[$this->active]->model;
        $code = $this->request->get($model->primaryColumn(), '');
        if ($model->loadFromCode($code) && $model->delete()) {
            $this->miniLog->notice($this->i18n->trans('record-deleted-correctly'));
            return true;
        }
        return false;
    }

    /**
     * Run the data edits.
     *
     * @return bool
     */
    protected function editAction(): bool
    {
        $data = $this->getFormData();
        $this->views[$this->active]->loadFromData($data);
        if (!$this->permissions->allowUpdate) {
            $this->miniLog->alert($this->i18n->trans('not-allowed-modify'));
            return false;
        }
        if ($this->views[$this->active]->model->save()) {
            $this->views[$this->active]->newCode = $this->views[$this->active]->model->primaryColumnValue();
            $this->miniLog->notice($this->i18n->trans('record-updated-correctly'));
            return true;
        }
        $this->miniLog->error($this->i18n->trans('record-save-error'));
        return false;
    }

    /**
     * Run the controller after actions.
     *
     * @param string $action
     */
    protected function execAfterAction(string $action): void
    {
        switch ($action) {
            case 'export':
                $this->setTemplate(false);
                $this->exportManager->newDoc($this->request->get('option', ''));
                foreach ($this->views as $selectedView) {
                    $selectedView->export($this->exportManager);
                }
                $this->exportManager->show($this->response);
                break;

            case 'insert':
                $this->insertAction();
                break;
        }
    }

    /**
     * Sets the tabs position, by default is setted to 'left', also supported 'bottom' and 'top'.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction(string $action): bool
    {
        switch ($action) {
            case 'autocomplete':
                $this->setTemplate(false);
                $results = $this->autocompleteAction();
                $this->response->setContent(json_encode($results));
                return false;

            case 'save':
                $this->editAction();
                break;

            case 'delete':
            case 'delete-document':
                $this->deleteAction();
                break;

            case 'save-document':
                $viewName = $this->searchGridView();
                if (!empty($viewName)) {
                    $this->setTemplate(false);
                    $data = $this->getFormData();
                    $result = $this->views[$viewName]->saveData($data);
                    $this->response->setContent(json_encode($result, JSON_FORCE_OBJECT));
                    return false;
                }
                break;
        }

        return true;
    }

    /**
     * Run the data insert action.
     */
    protected function insertAction(): void
    {
        $this->views[$this->active]->clear();
        foreach ($this->request->query->all() as $field => $value) {
            if ($field !== 'action') {
                $this->views[$this->active]->model->{$field} = $value;
            }
        }
    }

    /**
     * Returns the view class
     *
     * @param string $view
     *
     * @return string
     */
    private function searchGridView(): string
    {
        foreach ($this->views as $viewName => $view) {
            if ($view instanceof GridView) {
                return $viewName;
            }
        }

        return '';
    }
}
