<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2015-2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\Utils;

/**
 * Class to manage the data of retenciones table
 *
 * @author Cristo M. Estévez Hernández <cristom.estevez@gmail.com>
 */
class Retencion extends Base\ModelClass
{
    use Base\ModelTrait;

    /**
     * Primary key. varchar(10).
     *
     * @var string
     */
    public $codretencion;

    /**
     * Description of the tax.
     *
     * @var string
     */
    public $descripcion;

    /**
     * Percent of the retention
     *
     * @var int
     */
    public $porcentaje;

    /**
     * Returns the name of the column that is the primary key of the model.
     *
     * @return string
     */
    public static function primaryColumn() :string
    {
        return 'codretencion';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName() : string
    {
        return 'retenciones';
    }

    /**
     * Reset the values of all model properties.
     *
     * @return void
     */
    public function clear() : void
    {
        parent::clear();
        $this->descripcion = '';
        $this->porcentaje = 0;
    }

    /**
     * Returns True if there is no erros on properties values.
     *
     * @return boolean
     */
    public function test() : bool
    {
        $this->descripcion = Utils::noHtml($this->descripcion);
        if (empty($this->descripcion) || strlen($this->descripcion) > 50) {
            self::$miniLog->alert(self::$i18n->trans('not-valid-description-retention'));
            return false;
        }

        if (empty($this->porcentaje) || intval($this->porcentaje) <= 0) {
            self::$miniLog->alert(self::$i18n->trans('not-valid-percentage-retention'));
            return false;
        }

        return parent::test();
    }
}
