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

namespace FacturaScripts\Core\Lib\ExtendedController;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\DivisaTools;
use FacturaScripts\Core\Lib\ExportManager;
use FacturaScripts\Dinamic\Model\Base\ModelClass;

/**
 * View definition for its use in ListController
 *
 * @package FacturaScripts\Core\Lib\ExtendedController
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class ListView extends BaseView implements DataViewInterface
{

    /**
     * Order constants
     */
    const ICON_ASC = 'fa-sort-amount-asc';
    const ICON_DESC = 'fa-sort-amount-desc';

    /**
     * Cursor with data from the model display
     *
     * @var ModelClass[]
     */
    private $cursor;

    /**
     * Tools to work with currencies.
     *
     * @var DivisaTools
     */
    public $divisaTools;

    /**
     * Filter configuration preset by the user
     *
     * @var ListFilter[]
     */
    private $filters;

    /**
     * Stores the offset for the cursor
     *
     * @var int
     */
    private $offset;

    /**
     * Stores the order for the cursor
     *
     * @var array
     */
    private $order;

    /**
     * List of fields available to order by
     * Example: orderBy[key] = ["label" => "Etiqueta", "icon" => ICON_ASC]
     *          key = field_asc | field_desc
     *
     * @var array
     */
    private $orderBy;

    /**
     * List of fields where to search in when a search is made
     *
     * @var array
     */
    private $searchIn;

    /**
     * Selected element in the Order By list
     *
     * @var string
     */
    public $selectedOrderBy;

    /**
     * Stores the where parameters for the cursor
     *
     * @var DataBaseWhere[]
     */
    private $where;

    /**
     * ListView constructor and initialization.
     *
     * @param string $title
     * @param string $modelName
     * @param string $viewName
     * @param string $userNick
     */
    public function __construct($title, $modelName, $viewName, $userNick)
    {
        parent::__construct($title, $modelName);

        $this->cursor = [];
        $this->divisaTools = new DivisaTools();
        $this->filters = [];
        $this->orderBy = [];
        $this->selectedOrderBy = '';
        $this->searchIn = [];

        // Load configuration view for user
        $this->pageOption->getForUser($viewName, $userNick);
    }

    /**
     * Defines a new option to filter the data with
     *
     * @param string     $key
     * @param ListFilter $filter
     */
    public function addFilter(string $key, ListFilter $filter)
    {
        $this->filters[$key] = $filter;
    }

    /**
     * Adds a field to the Order By list
     *
     * @param string $field
     * @param string $label
     * @param int    $default (0 = None, 1 = ASC, 2 = DESC)
     */
    public function addOrderBy($field, $label = '', $default = 0)
    {
        $key1 = strtolower($field) . '_asc';
        $key2 = strtolower($field) . '_desc';
        if (empty($label)) {
            $label = $field;
        }

        $this->orderBy[$key1] = ['icon' => self::ICON_ASC, 'label' => static::$i18n->trans($label)];
        $this->orderBy[$key2] = ['icon' => self::ICON_DESC, 'label' => static::$i18n->trans($label)];

        switch ($default) {
            case 1:
                $this->setSelectedOrderBy($key1);
                break;

            case 2:
                $this->setSelectedOrderBy($key2);
                break;

            default:
                break;
        }
    }

    /**
     * Adds the given fields to the list of fields to search in
     *
     * @param array $fields
     */
    public function addSearchIn($fields)
    {
        if (\is_array($fields)) {
            /**
             * Perhaps array_merge/array_replace can be used instead.
             * Documentation can be found here: https://github.com/kalessil/phpinspectionsea/blob/master/docs/probable-bugs.md#addition-operator-applied-to-arrays
             */
            /** @noinspection AdditionOperationOnArraysInspection */
            $this->searchIn += $fields;
        }
    }

    /**
     * Establishes a column's display state
     *
     * @param string $columnName
     * @param bool   $disabled
     */
    public function disableColumn($columnName, $disabled)
    {
        $column = $this->columnForName($columnName);
        if (!empty($column)) {
            $column->display = $disabled ? 'none' : 'left';
        }
    }

    /**
     * Method to export the view data.
     *
     * @param ExportManager $exportManager
     */
    public function export(&$exportManager)
    {
        if ($this->count > 0) {
            $exportManager->generateListModelPage(
                $this->model,
                $this->where,
                $this->order,
                $this->offset,
                $this->getColumns(),
                $this->title
            );
        }
    }

    /**
     * Returns the link text for a given model
     *
     * @param mixed $data
     *
     * @return string
     */
    public function getClickEvent($data): string
    {
        foreach ($this->getColumns() as $col) {
            if ($col->widget->onClick !== null && $col->widget->onClick !== '') {
                return $col->widget->onClick . '?code=' . $data->{$col->widget->fieldName};
            }
        }

        return '';
    }

    /**
     * List of columns and its configuration
     *
     * @return ColumnItem[]
     */
    public function getColumns(): array
    {
        $keys = array_keys($this->pageOption->columns);
        if (empty($keys)) {
            return [];
        }

        $key = $keys[0];
        return $this->pageOption->columns[$key]->columns;
    }

    /**
     * Returns the read data list in Model format
     *
     * @return ModelClass[]
     */
    public function getCursor(): array
    {
        return $this->cursor;
    }

    /**
     * Returns the list of defined filters
     *
     * @return ListFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns the list of defined Order By
     *
     * @return array
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    /**
     * Returns the field list for the search, in WhereDatabase format
     *
     * @return string
     */
    public function getSearchIn(): string
    {
        return implode('|', $this->searchIn);
    }

    /**
     * Returns the indicated Order By in array format
     *
     * @param string $orderKey
     *
     * @return array
     */
    public function getSQLOrderBy($orderKey = ''): array
    {
        if (empty($this->orderBy)) {
            return [];
        }

        if ($orderKey === '') {
            $orderKey = array_keys($this->orderBy)[0];
        }

        $orderBy = explode('_', $orderKey);
        return [$orderBy[0] => $orderBy[1]];
    }

    /**
     * Returns the url for the requested model type
     *
     * @param string $type (list / new)
     *
     * @return string
     */
    public function getURL(string $type): string
    {
        if (empty($this->where)) {
            return parent::getURL($type);
        }

        $extra = '';
        foreach (DataBaseWhere::getFieldsFilter($this->where) as $field => $value) {
            $extra .= ('' === $extra) ? '?' : '&';
            $extra .= $field . '=' . $value;
        }

        switch ($type) {
            case 'list':
                return parent::getURL($type) . $extra;

            case 'new':
                $extra .= ('' === $extra) ? '?action=insert' : '&action=insert';
                return parent::getURL($type) . $extra;

            default:
                return parent::getURL($type);
        }
    }

    /**
     * Load the data in the cursor property, according to the where filter specified.
     *
     * @param mixed           $code
     * @param DataBaseWhere[] $where
     * @param array           $order
     * @param int             $offset
     * @param int             $limit
     */
    public function loadData($code = false, array $where = [], array $order = [], $offset = 0, $limit = \FS_ITEM_LIMIT)
    {
        $this->order = empty($order) ? $this->getSQLOrderBy($this->selectedOrderBy) : $order;
        $this->count = $this->model->count($where);
        /// needed when megasearch force data reload
        $this->cursor = [];
        if ($this->count > 0) {
            $this->cursor = $this->model->all($where, $this->order, $offset, $limit);
        }

        /// store values where & offset for exportation
        $this->offset = $offset;
        $this->where = $where;
    }

    /**
     * Checks and establishes the selected value in the Order By
     *
     * @param string $orderKey
     */
    public function setSelectedOrderBy($orderKey)
    {
        $keys = array_keys($this->orderBy);
        if (empty($orderKey) || !\in_array($orderKey, $keys, false)) {
            if (empty($this->selectedOrderBy)) {
                $this->selectedOrderBy = (string) $keys[0]; // We force the first element when there is no default
            }
        } else {
            $this->selectedOrderBy = $orderKey;
        }
    }
}
