<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018 Carlos García Gómez <carlos@facturascripts.com>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Model;

/**
 * Auxiliary model to load a resume of accounting entries with VAT
 *
 * @package FacturaScripts\Core\Model
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class PartidaImpuestoResumen extends Base\ModelView
{
    /**
     * TODO: Undocummented property.
     *
     * @var float
     */
    public $baseimponible;

    /**
     * TODO: Undocummented property.
     *
     * @var float
     */
    public $iva;

    /**
     * TODO: Undocummented property.
     *
     * @var float
     */
    public $recargo;

    /**
     * TODO: Undocummented property.
     *
     * @var float
     */
    public $cuotaiva;

    /**
     * TODO: Undocummented property.
     *
     * @var float
     */
    public $cuotarecargo;

    /**
     * TODO: Undocummented property.
     *
     * @var float
     */
    public $total;

    /**
     * Return Group By clausule
     */
    protected function getGroupBy(): string
    {
        return 'GROUP BY asientos.codejercicio, subcuentas.codcuentaesp,'
                      . 'cuentasesp.descripcion, subcuentas.codimpuesto,'
                      . 'partidas.iva, partidas.recargo';
    }

    /**
     * List of fields or columns to select clausule
     */
    protected function getFields(): array
    {
        return [
            'codejercicio' => 'asientos.codejercicio',
            'codcuentaesp' => 'subcuentas.codcuentaesp',
            'descripcion' => 'cuentasesp.descripcion',
            'codimpuesto' => 'subcuentas.codimpuesto',
            'iva' => 'partidas.iva',
            'recargo' => 'partidas.recargo',
            'baseimponible' => 'SUM(partidas.baseimponible)'
        ];
    }

    /**
     * List of tables related to from clausule
     */
    protected function getSQLFrom(): string
    {
        return 'asientos'
            . ' INNER JOIN partidas ON partidas.idasiento = asientos.idasiento'
            . ' INNER JOIN subcuentas ON subcuentas.idsubcuenta = partidas.idsubcuenta'
            . ' AND subcuentas.codimpuesto IS NOT NULL'
            . ' AND subcuentas.codcuentaesp IS NOT NULL'
            . ' LEFT JOIN cuentasesp ON cuentasesp.codcuentaesp = subcuentas.codcuentaesp';
    }

    /**
     * List of tables required for the execution of the view.
     */
    protected function getTables(): array
    {
        return [
            'asientos',
            'partidas',
            'subcuentas'
        ];
    }

    /**
     * Reset the values of all model view properties.
     */
    protected function clear()
    {
        parent::clear();

        $this->baseimponible = 0.00;
        $this->iva = 0.00;
        $this->recargo = 0.00;
        $this->cuotaiva = 0.00;
        $this->cuotarecargo = 0.00;
        $this->total = 0.00;
    }

    /**
     * Assign the values of the $data array to the model view properties.
     *
     * @param array $data
     */
    protected function loadFromData($data)
    {
        parent::loadFromData($data);

        $this->cuotaiva = $this->baseimponible * ($this->iva / 100.00);
        $this->cuotarecargo = $this->baseimponible * ($this->recargo / 100.00);
        $this->total = $this->baseimponible + $this->cuotaiva + $this->cuotarecargo;
    }
}
