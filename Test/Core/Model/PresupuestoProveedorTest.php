<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017       Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 * Copyright (C) 2017-2018  Carlos Garcia Gomez     <carlos@facturascripts.com>
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

namespace FacturaScripts\Test\Core\Model;

use FacturaScripts\Core\Model\PresupuestoProveedor;
use FacturaScripts\Test\Core\CustomTest;

/**
 * @covers \FacturaScripts\Core\Model\PresupuestoProveedor
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
final class PresupuestoProveedorTest extends CustomTest
{

    /**
     * @var PresupuestoProveedor
     */
    public $model;

    /**
     * @covers \FacturaScripts\Core\Model\PresupuestoProveedor::getLines()
     */
    public function testGetLines()
    {
        self::assertEmpty($this->model->getLines());
    }

    /**
     * @covers \FacturaScripts\Core\Model\PresupuestoProveedor::getNewLine()
     */
    public function testGetNewLine()
    {
        self::assertNotEmpty($this->model->getNewLine());
    }

    protected function setUp()
    {
        $this->model = new PresupuestoProveedor();
    }
}