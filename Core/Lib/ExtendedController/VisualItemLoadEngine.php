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

use FacturaScripts\Core\Model\PageOption;

/**
 * Description of VisualItemLoadEngine
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class VisualItemLoadEngine
{

    /**
     * Load the column structure from the JSON
     *
     * @param array            $columns
     * @param array            $modals
     * @param array            $rows
     * @param PageOption $model
     */
    public static function loadJSON(array $columns, array $modals, array $rows, PageOption $model): void
    {
        self::getJSONGroupsColumns($columns, $model->columns);
        self::getJSONGroupsColumns($modals, $model->modals);

        if (!empty($rows)) {
            foreach ($rows as $item) {
                $rowItem = RowItem::newFromJSON($item);
                /** @noinspection NullPointerExceptionInspection */
                $model->rows[$rowItem->type] = $rowItem;
                unset($rowItem);
            }
        }
    }

    /**
     * Add to the configuration of a controller
     *
     * @param string     $name
     * @param PageOption $model
     *
     * @return bool
     */
    public static function installXML(string $name, PageOption $model): bool
    {
        $fileName = FS_FOLDER . '/Dinamic/XMLView/' . $name . '.xml';
        if (FS_DEBUG && !file_exists($fileName)) {
            $fileName = FS_FOLDER . '/Core/XMLView/' . $name . '.xml';
        }

        $xml = simplexml_load_string(file_get_contents($fileName));
        if ($xml === false) {
            return false;
        }

        self::getXMLGroupsColumns($xml->columns, $model->columns);
        self::getXMLGroupsColumns($xml->modals, $model->modals);
        self::getXMLRows($xml->rows, $model->rows);

        return true;
    }

    /**
     * Load the list of values for a dynamic select type widget with
     * a database model or a range of values
     *
     * @param PageOption $model
     */
    public static function applyDynamicSelectValues(PageOption $model): void
    {
        // Apply values to dynamic Select widgets
        foreach ($model->columns as $group) {
            if ($group instanceof GroupItem) {
                $group->applySpecialOperations();
            }
        }

        // Apply values to dynamic Select widgets for modals forms
        if (!empty($model->modals)) {
            foreach ($model->modals as $group) {
                if ($group instanceof GroupItem) {
                    $group->applySpecialOperations();
                }
            }
        }
    }

    /**
     * Load the column structure from the JSON
     *
     * @param array $columns
     * @param array $target
     */
    private static function getJSONGroupsColumns(array $columns, array &$target): void
    {
        if (!empty($columns)) {
            foreach ($columns as $item) {
                $groupItem = GroupItem::newFromJSON($item);
                $target[$groupItem->name] = $groupItem;
            }
        }
    }

    /**
     * Load the column structure from the XML
     *
     * @param \SimpleXMLElement $columns
     * @param array             $target
     */
    private static function getXMLGroupsColumns(\SimpleXMLElement $columns, array &$target): void
    {
        // if group dont have elements
        if ($columns->count() === 0) {
            return;
        }

        // if have elements but dont have groups
        if (!isset($columns->group)) {
            $groupItem = GroupItem::newFromXML($columns);
            $target[$groupItem->name] = $groupItem;

            return;
        }

        // exists columns grouped
        foreach ($columns->group as $group) {
            $groupItem = GroupItem::newFromXML($group);
            $target[$groupItem->name] = $groupItem;
        }
    }

    /**
     * Load the special conditions for the rows from XML file
     *
     * @param \SimpleXMLElement $rows
     * @param array             $target
     */
    private static function getXMLRows(\SimpleXMLElement $rows, array &$target): void
    {
        if (!empty($rows)) {
            foreach ($rows->row as $row) {
                $rowItem = RowItem::newFromXML($row);
                /** @noinspection NullPointerExceptionInspection */
                $target[$rowItem->type] = $rowItem;
            }
        }
    }
}
