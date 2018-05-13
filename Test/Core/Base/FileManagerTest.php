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

namespace FacturaScripts\Test\Core\Base\Utils;

use FacturaScripts\Core\Base\FileManager;

/**
 * Class to test common methods to manipulate files and folders.
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
class FileManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateWritableFolder()
    {
        $this::assertTrue(FileManager::mkDir(\FS_FOLDER . '/MyFiles/TestWritable1/Test1/Test2/Test3', 0777, true));
        $scan = FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable1', true, []);
        $this::assertNotEmpty($scan, '<pre>' . print_r($scan, true) . '</pre>');
    }

    /**
     * @covers \FacturaScripts\Core\Base\FileManager::scanFolder
     */
    public function testScanFolder()
    {
        FileManager::recurseCopy(\FS_FOLDER . '/MyFiles/TestWritable1', \FS_FOLDER . '/MyFiles/TestWritable2');
        $this::assertNotEmpty(FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable2'));
        $this::assertEquals(
            FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable1', true),
            FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable2', true),
            'Folder not equals'
        );
        $this::assertTrue(
            FileManager::delTree(\FS_FOLDER . '/MyFiles/TestWritable1/Test1/Test2'),
            'Recursive delete dir fails.'
        );
        $this::assertNotEquals(
            FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable1', true),
            FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable2', true),
            'Folder are equals '
            . '<pre>' . print_r(
                \array_diff(
                    FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable1', true),
                    FileManager::scanFolder(\FS_FOLDER . '/MyFiles/TestWritable2', true)
                ),
                true
            ) . '</pre>'
        );
    }

    /**
     * @covers \FacturaScripts\Core\Base\FileManager::delTree
     */
    public function testDelTreeWritableFolder()
    {
        $this::assertTrue(FileManager::delTree(\FS_FOLDER . '/MyFiles/TestWritable1'), 'Recursive delete dir fails.');
        $this::assertTrue(FileManager::delTree(\FS_FOLDER . '/MyFiles/TestWritable2'), 'Recursive delete dir fails.');
    }

    /**
     * @covers \FacturaScripts\Core\Base\FileManager::delTree
     */
    public function testDelTreeNonWritableFolder()
    {
        $this::assertTrue(
            FileManager::delTree(\FS_FOLDER . '/MyFiles/TestNonWritable'),
            'Recursive delete dir fails.'
        );
    }
}
