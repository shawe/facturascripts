<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2016-2017  Carlos García Gómez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Lib\RandomDataGenerator;

use FacturaScripts\Dinamic\Model;

/**
 *  Generates orders to suppliers with random data.
 *
 * @author Rafael San José <info@rsanjoseo.com>
 */
class PedidosProveedor extends AbstractRandomDocuments
{

    /**
     * PedidosProveedor constructor.
     */
    public function __construct()
    {
        parent::__construct(new Model\PedidoProveedor());
    }

    /**
     * Generate random data.
     *
     * @param int $num
     *
     * @return int
     */
    public function generate(int $num = 50): int
    {
        $ped = $this->model;
        $this->shuffle($proveedores, new Model\Proveedor());

        $generated = 0;
        while ($generated < $num) {
            $ped->clear();
            $this->randomizeDocument($ped);
            $eje = $this->ejercicio::getByFecha($ped->fecha);
            if (false === $eje) {
                break;
            }

            $recargo = (random_int(0, 4) === 0);
            $regimeniva = $this->randomizeDocumentCompra($ped, $eje, $proveedores, $generated);
            if ($ped->save()) {
                $this->randomLineas($ped, 'idpedido', self::MODEL_NAMESPACE . 'LineaPedidoProveedor', $regimeniva, $recargo);
                ++$generated;
            } else {
                break;
            }
        }

        return $generated;
    }
}
