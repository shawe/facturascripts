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

use FacturaScripts\Core\Base;
use FacturaScripts\Dinamic\Lib\ExportManager;
use FacturaScripts\Dinamic\Model;

/**
 * Base definition for the views used in ExtendedControllers
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
abstract class BaseView
{

    /**
     * Contains the translator
     *
     * @var Base\Translator
     */
    protected static $i18n;
    /**
     * Total count of read rows.
     *
     * @var int
     */
    public $count;
    /**
     * Needed model to for the model method calls.
     * In the scope of EditController it contains the view data.
     *
     * @var Model\Base\ModelClass|Model\Base\BusinessDocument|Model\Base\PurchaseDocument|Model\Base\SalesDocument
     */
    public $model;

    /**
     * Stores the new code from the save() procedure, to use in loadData().
     *
     * @var string
     */
    public $newCode;
    /**
     * View title
     *
     * @var string
     */
    public $title;
    /**
     * Columns and filters configuration
     *
     * @var Model\PageOption
     */
    protected $pageOption;

    /**
     * Construct and initialize the class
     *
     * @param string $title
     * @param string $modelName
     */
    public function __construct(string $title, string $modelName)
    {
        static::$i18n = new Base\Translator();

        $this->count = 0;
        $this->title = static::$i18n->trans($title);
        $this->model = class_exists($modelName) ? new $modelName() : null;
        $this->pageOption = new Model\PageOption();
    }

    /**
     * Method to export the view data.
     *
     * @param ExportManager $exportManager
     *
     * @return mixed
     */
    abstract public function export(ExportManager $exportManager);

    /**
     * Clears the model and set new code for the PK.
     */
    public function clear(): void
    {
        $this->model->clear();
        $this->model->{$this->model::primaryColumn()} = $this->model->newCode();
    }

    /**
     * Gets the column by the given field name
     *
     * @param string $fieldName
     *
     * @return ColumnItem
     */
    public function columnForField(string $fieldName): ColumnItem
    {
        $result = null;
        foreach ($this->pageOption->columns as $group) {
            foreach ($group->columns as $column) {
                if ($column->widget->fieldName === $fieldName) {
                    $result = $column;
                    break;
                }
            }
            if (!empty($result)) {
                break;
            }
        }

        return $result;
    }

    /**
     * Gets the column by the column name
     *
     * @param string $columnName
     *
     * @return ColumnItem
     */
    public function columnForName(string $columnName): ColumnItem
    {
        $result = null;
        foreach ($this->pageOption->columns as $group) {
            foreach ($group->columns as $key => $column) {
                if ($key === $columnName) {
                    $result = $column;
                    break;
                }
            }
            if (!empty($result)) {
                break;
            }
        }

        return $result;
    }

    /**
     * Returns the list of modal forms
     *
     * @return array
     */
    public function getModals(): array
    {
        return $this->pageOption->modals;
    }

    /**
     * If it exists, return the specified row type
     *
     * @param string $key
     *
     * @return RowItem|null
     */
    public function getRow(string $key): ?RowItem
    {
        return $this->pageOption->rows[$key] ?? null;
    }

    /**
     * Returns the url for the requested model type
     *
     * @param string $type (edit / list / auto)
     *
     * @return string
     */
    public function getURL(string $type): string
    {
        return empty($this->model) ? '' : $this->model->url($type);
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getViewName(): string
    {
        return $this->pageOption->name;
    }

    /**
     * Verifies the structure and loads into the model the given data array
     *
     * @param array $data
     */
    public function loadFromData(array &$data): void
    {
        $fieldKey = $this->model::primaryColumn();
        $fieldValue = $data[$fieldKey];
        if ($fieldValue !== $this->model->primaryColumnValue() && $fieldValue !== '') {
            $this->model->loadFromCode($fieldValue);
        }

        $this->model->checkArrayData($data);
        $this->model->loadFromData($data, ['action', 'active']);
    }
}
