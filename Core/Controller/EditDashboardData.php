<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos García Gómez     <carlos@facturascripts.com>
 * Copyright (C) 2017       Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
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

use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Dinamic\Lib\Dashboard as DashboardLib;
use FacturaScripts\Dinamic\Lib\ExtendedController;
use FacturaScripts\Dinamic\Model\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to edit a single item from the DashboardCard model
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
class EditDashboardData extends ExtendedController\EditController
{

    /**
     * Returns the configuration property value for a specified $field
     *
     * @param mixed  $model
     * @param string $field
     *
     * @return mixed
     */
    public function getFieldValue($model, string $field)
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
     * Returns the model name
     */
    public function getModelClassName(): string
    {
        return 'DashboardData';
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'dashboard-card';
        $pageData['menu'] = 'reports';
        $pageData['icon'] = 'fa-dashboard';
        $pageData['showonmenu'] = false;

        return $pageData;
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

        $this->validateProperties();
        $this->validateColumns();
    }

    /**
     * Run the data edits
     *
     * @return bool
     */
    protected function editAction(): bool
    {
        $model = $this->views[$this->active]->model;
        $properties = array_keys($this->getPropertiesFields());
        $fields = array_keys($model->properties);
        foreach ($fields as $key) {
            if (!\in_array($key, $properties, false)) {
                unset($model->properties[$key]);
            }
        }

        return parent::editAction();
    }

    /**
     * Return the propierties fields.
     *
     * @return mixed
     */
    private function getPropertiesFields()
    {
        $model = $this->getModel();
        $component = 'FacturaScripts\\Dinamic\\Lib\\Dashboard\\'
            . $model->component
            . DashboardLib\BaseComponent::SUFIX_COMPONENTS;

        return $component::getPropertiesFields();
    }

    /**
     * Validate propierties columns.
     */
    private function validateColumns(): void
    {
        $fields = array_keys($this->getModel()->properties);
        $group = $this->views['EditDashboardData']->getColumns()['options']->columns;
        foreach ($group as $column) {
            if (\in_array($column->widget->fieldName, $fields, false)) {
                continue;
            }

            $column->display = 'none';
        }
    }

    /**
     * Validate propierties fields.
     */
    private function validateProperties(): void
    {
        $model = $this->getModel();
        $properties = $this->getPropertiesFields();
        foreach ($properties as $key => $value) {
            if (!isset($model->properties[$key])) {
                $model->properties[$key] = $value;
            }
        }
    }
}
