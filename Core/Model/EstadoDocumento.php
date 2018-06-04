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

namespace FacturaScripts\Core\Model;

use FacturaScripts\Core\Base\Utils;

/**
 * A state associated with documents to distinguish them by groups.
 * For example: Earrings, Approved, ...
 *
 * @author Francesc Pineda Segarra <francesc.pìneda.segarra@gmail.com>
 * @author Carlos García Gómez     <carlos@facturascripts.com>
 */
class EstadoDocumento extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * True if this states must update product stock.
     *
     * @var int
     */
    public $actualizastock;

    /**
     * If the state is blocked or not.
     *
     * @var bool
     */
    public $bloquear;

    /**
     * If the state is editable or not.
     *
     * @var bool
     */
    public $editable;

    /**
     * Name of the document to generate when this state is selected.
     *
     * @var string
     */
    public $generadoc;

    /**
     * Primary key.
     *
     * @var int
     */
    public $idestado;

    /**
     * Name of the state to show the user.
     *
     * @var string
     */
    public $nombre;

    /**
     * Sets this state as default for tipodoc.
     *
     * @var bool
     */
    public $predeterminado;

    /**
     * Document type: custommer invoice, supplier order, etc...
     * @var string
     */
    public $tipodoc;

    /**
     * Reset the values of all model properties.
     */
    public function clear(): void
    {
        parent::clear();
        $this->actualizastock = 0;
        $this->bloquear = false;
        $this->editable = true;
        $this->predeterminado = false;
        $this->tipodoc = 'PedidoProveedor';
    }

    /**
     * Remove the model data from the database.
     *
     * @return boolean
     */
    public function delete(): bool
    {
        if ($this->bloquear) {
            self::$miniLog->alert(self::$i18n->trans('locked'));
            return false;
        }

        return parent::delete();
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'idestado';
    }

    /**
     * Stores the model data in the database.
     *
     * @return boolean
     */
    public function save(): bool
    {
        if ($this->bloquear) {
            self::$miniLog->alert(self::$i18n->trans('locked'));
            return false;
        }

        if (parent::save()) {
            if ($this->predeterminado) {
                $sql = 'UPDATE ' . static::tableName() . ' SET predeterminado = false'
                    . ' WHERE predeterminado = true'
                    . ' AND tipodoc = ' . self::$dataBase->var2str($this->tipodoc)
                    . ' AND idestado != ' . self::$dataBase->var2str($this->idestado) . ';';
                return self::$dataBase->exec($sql);
            }

            return true;
        }

        return false;
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'estados_documentos';
    }

    /**
     * Returns True if there is no erros on properties values.
     *
     * @return bool
     */
    public function test(): bool
    {
        $this->nombre = Utils::noHtml($this->nombre);

        if (empty($this->tipodoc) || empty($this->nombre)) {
            return false;
        }

        return parent::test();
    }
}
