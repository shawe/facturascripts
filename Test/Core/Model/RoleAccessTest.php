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

use FacturaScripts\Core\Model\Page;
use FacturaScripts\Core\Model\RoleAccess;
use FacturaScripts\Test\Core\CustomTest;

/**
 * @covers \FacturaScripts\Core\Model\RoleAccess
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
final class RoleAccessTest extends CustomTest
{

    /**
     * @var RoleAccess
     */
    public $model;

    /**
     * @covers \FacturaScripts\Core\Model\RoleAccess::addPagesToRole()
     */
    public function testAddPagesToRole(): void
    {
        $pages = new Page();
        self::assertEmpty($this->model::addPagesToRole('admin', $pages->all()));
    }

    protected function setUp()
    {
        $this->model = new RoleAccess();
    }
}
