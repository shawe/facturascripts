<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Lib;

use FacturaScripts\Core\Model\Base\BusinessDocument;

/**
 * Description of BusinessDocumentGenerator
 *
 * @author Carlos García Gómez
 */
class BusinessDocumentGenerator
{
    /**
     * Constant for dinamic models.
     */
    const DIR_MODEL = '\\FacturaScripts\\Dinamic\\Model\\';

    /**
     * Generates a new business document.
     *
     * @param BusinessDocument $prototype
     * @param string           $newClass
     *
     * @return bool
     */
    public function generate(BusinessDocument $prototype, string $newClass): bool
    {
        $exclude = ['idestado', 'fecha', 'hora'];
        $newDocClass = self::DIR_MODEL . $newClass;
        $newDoc = new $newDocClass();
        if ($newDoc instanceof BusinessDocument) {
            foreach ($prototype->getModelFields() as $field => $value) {
                if (\in_array($field, $exclude, false)) {
                    continue;
                }

                $newDoc->{$field} = $prototype->{$field};
            }
        }

        return $newDoc->save() && $this->cloneLines($prototype, $newDoc);
    }

    /**
     * Clone lines between two documents.
     *
     * @param BusinessDocument $prototype
     * @param BusinessDocument $newDoc
     *
     * @return bool
     */
    private function cloneLines(BusinessDocument $prototype, $newDoc): bool
    {
        foreach ($prototype->getLines() as $line) {
            $arrayLine = [];
            foreach ($line->getModelFields() as $field => $value) {
                $arrayLine[$field] = $line->{$field};
            }

            $newLine = $newDoc->getNewLine($arrayLine);
            if (!$newLine->save()) {
                return false;
            }

            $newLine->updateStock($newDoc->codalmacen);
        }

        return true;
    }
}
