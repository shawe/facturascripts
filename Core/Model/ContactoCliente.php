<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2015-2018  Carlos García Gómez  <carlos@facturascripts.com>
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
 * Description of ContactoCliente
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class ContactoCliente extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * Customer code.
     *
     * @var string
     */
    public $codcliente;

    /**
     * Primary key.
     *
     * @var int
     */
    public $id;

    /**
     * Contact code.
     *
     * @var int
     */
    public $idcontacto;

    /**
     * @return string
     */
    public function install(): string
    {
        new Contacto();

        return parent::install();
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'id';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'contactosclientes';
    }
}
