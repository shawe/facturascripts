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

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Model\User;
use FacturaScripts\Test\Core\CustomTest;

/**
 * @covers \User
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
final class UserTest extends CustomTest
{
    public function testNewUser()
    {
        $model = new User();

        $this::assertInstanceOf(User::class, $model);
        $this::assertEquals(\FS_LANG, $model->langcode);
        $this::assertEquals(AppSettings::get('default', 'idempresa', 1), $model->idempresa);
        $this::assertTrue($model->enabled);
        $this::assertEquals(1, $model->level);
        $this::assertFalse($model->test());
    }

    public function testTable()
    {
        $model = new User();

        $this::assertInternalType('string', $model::tableName());
    }

    public function testPrimaryColumn()
    {
        $model = new User();

        $this::assertInternalType('string', $model::primaryColumn());
    }

    public function testInstall()
    {
        $model = new User();

        $this::assertInternalType('string', $model->install());
    }

    public function testAll()
    {
        $model = new User();
        $list = $model->all();

        if (!empty($list)) {
            $this::assertInternalType('array', $list);
        } else {
            $this::assertSame([], $list);
        }
    }
}
