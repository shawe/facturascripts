<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos García Gómez  <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Lib\ExportManager;

/**
 * View definition for its use in ExtendedControllers
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class EditView extends BaseView implements DataViewInterface
{

    /**
     * EditView constructor and initialization.
     *
     * @param string $title
     * @param string $modelName
     * @param string $viewName
     * @param string $userNick
     */
    public function __construct(string $title, string $modelName, string $viewName, string $userNick)
    {
        parent::__construct($title, $modelName);

        // Loads the view configuration for the user
        $this->pageOption->getForUser($viewName, $userNick);
    }

    /**
     * Establishes the column edit state
     *
     * @param string $columnName
     * @param bool   $disabled
     */
    public function disableColumn(string $columnName, bool $disabled): void
    {
        $column = $this->columnForName($columnName);
        if (!empty($column)) {
            $column->widget->readOnly = $disabled;
        }
    }

    /**
     * Returns the column configuration
     *
     * @return GroupItem[]
     */
    public function getColumns(): array
    {
        return $this->pageOption->columns;
    }

    /**
     * Load the data in the model property, according to the code specified.
     *
     * @param string|array    $code
     * @param DataBaseWhere[] $where
     * @param array           $order
     * @param int             $offset
     * @param int             $limit
     */
    public function loadData($code = '', array $where = [], array $order = [], int $offset = 0, int $limit = FS_ITEM_LIMIT): void
    {
        if ($this->newCode !== null) {
            $code = $this->newCode;
        }

        if (\is_array($code)) {
            $where = [];
            foreach ($code as $fieldName => $value) {
                $where[] = new DataBaseWhere($fieldName, $value);
            }
            $this->model->loadFromCode('', $where);
        } else {
            $this->model->loadFromCode($code);
        }

        $fieldName = $this->model::primaryColumn();
        $this->count = empty($this->model->{$fieldName}) ? 0 : 1;

        /// if not a new reg. we lock primary key
        $column = $this->columnForField($fieldName);
        if (!empty($column)) {
            $column->widget->readOnly = ($this->count > 0);
        }
    }

    /**
     * Method to export the view data.
     *
     * @param ExportManager $exportManager
     */
    public function export(ExportManager $exportManager): void
    {
        $exportManager->generateModelPage($this->model, $this->getColumns(), $this->title);
    }

    /**
     * Returns the text for the data panel header
     *
     * @return string
     */
    public function getPanelHeader(): string
    {
        return $this->title;
    }

    /**
     * Returns the text for the data panel footer
     *
     * @return string
     */
    public function getPanelFooter(): string
    {
        return '';
    }
}
