<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2014-2018 Carlos García Gómez <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\LineaPedidoCliente as DinamicLineaPedidoCliente;

/**
 * Customer order.
 *
 * @package FacturaScripts\Core\Model
 * @author Francesc Pineda Segarra <shawe.ewahs@gmail.com>
 */
class PedidoCliente extends Base\SalesDocument
{

    use Base\ModelTrait;

    /**
     * Related delivery note ID.
     *
     * @var integer
     */
    public $idalbaran;

    /**
     * Primary key.
     *
     * @var integer
     */
    public $idpedido;

    /**
     * Expected date of departure of the material.
     *
     * @var string
     */
    public $fechasalida;

    /**
     * Returns the lines associated with the order.
     *
     * @return DinamicLineaPedidoCliente[]
     */
    public function getLines(): array
    {
        $lineaModel = new DinamicLineaPedidoCliente();
        $where = [new DataBaseWhere('idpedido', $this->idpedido)];
        $order = ['orden' => 'DESC', 'idlinea' => 'ASC'];

        return $lineaModel->all($where, $order, 0, 0);
    }

    /**
     * Returns a new line for the document.
     *
     * @param array $data
     *
     * @return DinamicLineaPedidoCliente
     */
    public function getNewLine(array $data = []): DinamicLineaPedidoCliente
    {
        $newLine = new DinamicLineaPedidoCliente($data);
        $newLine->idpedido = $this->idpedido;

        $state = $this->getState();
        $newLine->actualizastock = $state->actualizastock;

        return $newLine;
    }

    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install(): string
    {
        parent::install();
        new AlbaranCliente();

        return '';
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'idpedido';
    }

    /**
     * Returns the name of the table that uses this model.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'pedidoscli';
    }
}
