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

namespace FacturaScripts\Core\Model;

/**
 * Components to use into the Dashboard of FacturaScripts.
 *
 * @package FacturaScripts\Core\Model
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class Dashboard extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * Name of visual component
     *
     * @var string
     */
    public $component;

    /**
     * Position into dashboard.
     *
     * @var integer
     */
    public $location;

    /**
     * id version of component.
     *
     * @var string
     */
    public $version;

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'component';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'dashboard';
    }

    /**
     * Returns the url where to see / modify the data.
     *
     * @param string $type
     * @param string $list
     *
     * @return string
     */
    public function url(string $type = 'auto', string $list = 'List'): string
    {
        return parent::url($type, '');
    }
}
