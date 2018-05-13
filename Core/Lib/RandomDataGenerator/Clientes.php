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
 * Generate random data for the customers (clientes) file
 *
 * @package FacturaScripts\Core\Lib\RandomDataGenerator
 * @author Rafael San José <info@rsanjoseo.com>
 */
class Clientes extends AbstractRandomPeople
{

    /**
     * Clientes constructor.
     */
    public function __construct()
    {
        parent::__construct(new Model\Cliente());
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
        $cliente = $this->model;
        for ($i = 0; $i < $num; ++$i) {
            $cliente->clear();
            $this->fillCliPro($cliente);

            $cliente->fechaalta = date((string) random_int(1, 28) . '-' . (string) random_int(1, 12) . '-' . (string) random_int(2013, date('Y')));
            $cliente->regimeniva = (random_int(0, 9) === 0) ? 'Exento' : 'General';

            if (random_int(0, 2) > 0) {
                shuffle($this->agentes);
                $cliente->codagente = $this->agentes[0]->codagente;
            } else {
                $cliente->codagente = null;
            }

            if (!empty($this->grupos) && random_int(0, 2) > 0) {
                shuffle($this->grupos);
                $cliente->codgrupo = $this->grupos[0]->codgrupo;
            } else {
                $cliente->codgrupo = null;
            }

            $cliente->codcliente = $cliente->newCode();
            if (!$cliente->save()) {
                break;
            }

            /// añadimos direcciones
            $numDirs = random_int(0, 3);
            $this->direccionesCliente($cliente, $numDirs);

            /// Añadimos cuentas bancarias
            $numCuentas = random_int(0, 3);
            $this->cuentasBancoCliente($cliente, $numCuentas);
        }

        return $i;
    }

    /**
     * Rellena cuentas bancarias de un cliente con datos aleatorios.
     *
     * @param Model\Cliente $cliente
     * @param int           $max
     */
    protected function cuentasBancoCliente($cliente, $max = 3)
    {
        while ($max > 0) {
            $cuenta = new Model\CuentaBancoCliente();
            $cuenta->codcliente = $cliente->codcliente;
            $cuenta->descripcion = 'Banco ' . random_int(1, 999);
            $cuenta->iban = $this->iban();
            $cuenta->swift = random_int(0, 2) !== 0 ? Utils::randomString(8) : '';
            $cuenta->fmandato = (random_int(0, 1) === 0) ? date('d-m-Y', strtotime($cliente->fechaalta . ' +' . random_int(1, 30) . ' days')) : null;

            if (!$cuenta->save()) {
                break;
            }

            --$max;
        }
    }

    /**
     * Rellena direcciones de un cliente con datos aleatorios.
     *
     * @param Model\Cliente $cliente
     * @param int           $max
     */
    protected function direccionesCliente($cliente, $max = 3)
    {
        while ($max > 0) {
            $dir = new Model\DireccionCliente();
            $dir->codcliente = $cliente->codcliente;
            $dir->codpais = (random_int(0, 2) === 0) ? $this->paises[0]->codpais : AppSettings::get('default', 'codpais');

            $dir->provincia = $this->provincia();
            $dir->ciudad = $this->ciudad();
            $dir->direccion = $this->direccion();
            $dir->codpostal = (string) random_int(1234, 99999);
            $dir->apartado = (random_int(0, 3) === 0) ? (string) random_int(1234, 99999) : null;
            $dir->domenvio = (random_int(0, 1) === 1);
            $dir->domfacturacion = (random_int(0, 1) === 1);
            $dir->descripcion = 'Dirección #' . $max;
            if (!$dir->save()) {
                break;
            }

            --$max;
        }
    }
}
