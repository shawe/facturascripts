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

use FacturaScripts\Core\Model\User;
use FacturaScripts\Test\Core\CustomTest;

/**
 * @covers \FacturaScripts\Core\Model\User
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
final class UserTest extends CustomTest
{

    /**
     * @var User
     */
    public $model;

    /**
     * @covers \FacturaScripts\Core\Model\User::newLogkey()
     * @covers \FacturaScripts\Core\Model\User::verifyLogkey()
     */
    public function testNewLogkey(): void
    {
        $logKey = $this->model->newLogkey('192.168.1.1');
        self::assertNotEmpty($logKey);
        self::assertTrue($this->model->verifyLogkey($logKey));
    }

    /**
     * @covers \FacturaScripts\Core\Model\User::setPassword()
     */
    public function testSetPassword(): void
    {
        $this->model = $this->model->get('admin');
        if ($this->model !== null) {
            $this->model->setPassword('admin');
        }
    }

    protected function setUp()
    {
        $this->model = new User();
    }
}
