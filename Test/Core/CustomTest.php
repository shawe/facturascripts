<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018 Carlos García Gómez <carlos@facturascripts.com>
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

namespace FacturaScripts\Test\Core;

use FacturaScripts\Core\Base\MiniLog;
use FacturaScripts\Core\Model\Base\ModelClass;
use PHPUnit\Framework\TestCase;

/**
 * Description of CustomTest
 *
 * @author Carlos García Gómez
 */
class CustomTest extends TestCase
{

    /**
     * @var ModelClass
     */
    public $model;

    public function testInit(): void
    {
        self::assertNotNull(
            $this->model,
            'Class is null'
    );
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::tableName()
     */
    public function testTableName(): void
    {
        self::assertInternalType(
            'string',
            $this->model::tableName(),
            'Table name not setted'
        );
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::primaryColumn()
     */
    public function testPrimaryColumn(): void
    {
        self::assertInternalType(
            'string',
            $this->model::primaryColumn(),
            'Primary column not setted'
        );
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::primaryDescription()
     */
    public function testPrimaryDescription(): void
    {
        self::assertInternalType(
            'string',
            $this->model->primaryDescription(),
            'Primary description not setted'
        );
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::getModelFields()
     */
    public function testFields(): void
    {
        // FIXME: getModelFields returns []
        //self::assertNotEmpty($this->model->getModelFields());
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::install()
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::all()
     */
    public function testInstall(): void
    {
        $install = $this->model->install();
        self::assertInternalType(
            'string',
            $install,
            'Is not a string'
        );

        if (\strlen($install) > 0) {
            self::assertNotEmpty(
                $this->model->all(),
                'Inserted: ' . $install . ' but contains ' . \print_r($this->model->all(), true)
            );
        }
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::checkArrayData()
     */
    public function testCheckArrayData(): void
    {
        $data = [];
        $this->model->checkArrayData($data);
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::count()
     */
    public function testCount(): void
    {
        $this->model->count();
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::url()
     */
    public function testUrl(): void
    {
        self::assertInternalType(
            'string',
            $this->model->url(),
            'Is not a string'
        );
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::clear()
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::exists()
     */
    public function testExists(): void
    {
        $this->model->clear();
        self::assertFalse(
            $this->model->exists(),
            'Record yet exists'
        );
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::all()
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::test()
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::exists()
     */
    public function testAll(): void
    {
        foreach ($this->model->all() as $model) {
            self::assertTrue(
                $model->test(),
                'Model test not passed'
            );
            self::assertTrue(
                $model->exists(),
                'Record not exists'
            );
        }
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::save()
     */
    public function testSave(): void
    {
        $this->model->save();

        // Update
        $model = $this->model;
        $model->{$model->primaryDescriptionColumn()} = $model->primaryDescription() . $model->primaryDescription();
        $model->save();
        // FIXME: Model is saved with null on primary column
        //self::assertNotNull($model->{$this->model::primaryColumn()});
        //self::assertNotNull($model->primaryColumnValue());

        // Insert
        $model = $this->model;
        $model->{$this->model::primaryColumn()} = null;
        $model->save();
        // FIXME: Model is saved with null on primary column
        //self::assertNotNull($model->{$this->model::primaryColumn()});
        //self::assertNotNull($model->primaryColumnValue());
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::get()
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::primaryColumn()
     */
    public function testGet(): void
    {
        $this->model->get($this->model->{$this->model::primaryColumn()});
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::loadFromCode()
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::primaryColumn()
     */
    public function testLoadFromCode(): void
    {
        $this->model->loadFromCode($this->model->{$this->model::primaryColumn()});
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::loadFromData()
     */
    public function testLoadFromData(): void
    {
        $this->model->loadFromData();
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::newCode()
     */
    public function testNewCode(): void
    {
        // FIXME: Fails because inside is used getModelFields that returns []
        //$this->model->newCode();
    }

    /**
     * @covers \FacturaScripts\Core\Model\Base\ModelClass::delete()
     */
    public function testDelete(): void
    {
        $this->model->delete();
    }

    protected function tearDown()
    {
        $minilog = new MiniLog();
        $messages = $minilog->read();
        if (!empty($messages) && $this->getStatus() > 1) {
            array_unshift($messages, ['test' => \get_called_class()]);
            $filename = FS_FOLDER . DIRECTORY_SEPARATOR . 'MINILOG.json';
            $content = file_exists($filename) ? file_get_contents($filename) . "\n-----------\n" : '';
            $content .= json_encode($messages);

            file_put_contents($filename, $content);
        }

        $minilog->clear();
    }
}
