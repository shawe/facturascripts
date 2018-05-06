<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2016-2018 Carlos García Gómez <carlos@facturascripts.com>
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

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\Utils;
use FacturaScripts\Core\Model;

/**
 * Generate random data for the suppliers (proveedores) file
 *
 * @package FacturaScripts\Core\Lib\RandomDataGenerator
 * @author Rafael San José <info@rsanjoseo.com>
 */
class Proveedores extends AbstractRandomPeople
{

    /**
     * Proveedores constructor.
     */
    public function __construct()
    {
        parent::__construct(new Model\Proveedor());
    }

    /**
     * Generate random data.
     *
     * @param int $num
     *
     * @return int
     */
    public function generate($num = 50): int
    {
        $proveedor = $this->model;
        for ($generated = 0; $generated < $num; ++$generated) {
            $proveedor->clear();
            $this->fillCliPro($proveedor);

            if (random_int(0, 9) === 0) {
                $proveedor->regimeniva = 'Exento';
            }

            $proveedor->codproveedor = $proveedor->newCode();
            if ($proveedor->save()) {
                /// añadimos direcciones
                $numDirs = random_int(0, 3);
                $this->direccionesProveedor($proveedor, $numDirs);

                /// Añadimos cuentas bancarias
                $numCuentas = random_int(0, 3);
                $this->cuentasBancoProveedor($proveedor, $numCuentas);
            } else {
                break;
            }
        }

        return $generated;
    }

    /**
     * Rellena cuentas bancarias de un proveedor con datos aleatorios.
     *
     * @param Model\Proveedor $proveedor
     * @param int             $max
     */
    protected function cuentasBancoProveedor($proveedor, $max = 3)
    {
        while ($max > 0) {
            $cuenta = new Model\CuentaBancoProveedor();
            $cuenta->codproveedor = $proveedor->codproveedor;
            $cuenta->descripcion = 'Banco ' . random_int(1, 999);
            $cuenta->iban = $this->iban();
            $cuenta->swift = Utils::randomString(8);

            $opcion = random_int(0, 2);
            if ($opcion === 0) {
                $cuenta->swift = '';
            } elseif ($opcion === 1) {
                $cuenta->iban = '';
            }

            $cuenta->save();
            --$max;
        }
    }

    /**
     * Rellena direcciones de un proveedor con datos aleatorios.
     *
     * @param Model\Proveedor $proveedor
     * @param int             $max
     */
    protected function direccionesProveedor($proveedor, $max = 3)
    {
        while ($max) {
            $dir = new Model\DireccionProveedor();
            $dir->codproveedor = $proveedor->codproveedor;
            $dir->codpais = AppSettings::get('default', 'codpais');

            if (random_int(0, 2) === 0) {
                $dir->codpais = $this->paises[0]->codpais;
            }

            $dir->provincia = $this->provincia();
            $dir->ciudad = $this->ciudad();
            $dir->direccion = $this->direccion();
            $dir->codpostal = (string) random_int(1234, 99999);

            if (random_int(0, 3) === 0) {
                $dir->apartado = (string) random_int(1234, 99999);
            }

            if (random_int(0, 1) === 0) {
                $dir->direccionppal = false;
            }

            $dir->descripcion = 'Dirección #' . $max;
            $dir->save();
            --$max;
        }
    }
}
