<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018 Carlos Garcia Gomez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Test\Core\App;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\Utils;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2017-07-19 at 11:58:07.
 */
class AppSettingsTest extends TestCase
{

    /**
     * @var AppSettings
     */
    protected $object;

    /**
     * @covers \FacturaScripts\Core\App\AppSettings::load()
     */
    public function testLoad()
    {
        $this->object->load();
    }

    /**
     * @covers \FacturaScripts\Core\App\AppSettings::get()
     */
    public function testGet()
    {
        // FIXME: Must return 'ESP' because is previously call to load()
        //self::assertNotNull($this->object::get('default', 'codpais'));
        self::assertNull($this->object::get('default', Utils::randomString()));
        self::assertEmpty($this->object::get('default', 'null', ''));
    }

    /**
     * @covers \FacturaScripts\Core\App\AppSettings::set()
     */
    public function testSet()
    {
        $this->object->set('default', 'prop1', 'val1');
        $this->object->set('not-default', 'prop2', 'val2');
    }

    /**
     * @covers \FacturaScripts\Core\App\AppSettings::save()
     */
    public function testSave()
    {
        $this->object->save();
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new AppSettings();
    }
}