<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2016-2018  Carlos García Gómez  <carlos@facturascripts.com>
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
 * Generate random data for the products (Articulos) file
 *
 * @author Rafael San José <info@rsanjoseo.com>
 */
class Articulos extends AbstractRandom
{

    /**
     * List of warehouses.
     *
     * @var Model\Almacen[]
     */
    protected $almacenes;

    /**
     * List of manufacturers.
     *
     * @var Model\Fabricante[]
     */
    protected $fabricantes;

    /**
     * List of families.
     *
     * @var Model\Familia[]
     */
    protected $familias;

    /**
     * List of taxes.
     *
     * @var Model\Impuesto[]
     */
    protected $impuestos;

    /**
     * Articulos constructor.
     */
    public function __construct()
    {
        parent::__construct(new Model\Articulo());
        $this->shuffle($this->almacenes, new Model\Almacen());
        $this->shuffle($this->fabricantes, new Model\Fabricante());
        $this->shuffle($this->familias, new Model\Familia());
        $this->shuffle($this->impuestos, new Model\Impuesto());
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
        $art = $this->model;
        for ($generated = 0; $generated < $num; ++$generated) {
            $art->clear();
            $this->setArticuloData($art);

            if ($art->exists()) {
                continue;
            }
            if (!$art->save()) {
                break;
            }

            if (random_int(0, 2) == 0) {
                $this->sumStock($art, random_int(0, 1000));
            } else {
                $this->sumStock($art, random_int(0, 20));
            }
        }

        return $generated;
    }

    /**
     * TODO: Undocumented function.
     *
     * @param Model\Articulo $art
     */
    private function setArticuloData(Model\Articulo $art)
    {
        $art->descripcion = $this->descripcion();
        $art->codimpuesto = $this->impuestos[0]->codimpuesto;
        $art->setPvpIva($this->precio(1, 49, 699));
        $art->costemedio = $art->preciocoste = $this->cantidad(0, $art->pvp, $art->pvp + 1);

        switch (random_int(0, 2)) {
            case 0:
                $art->referencia = $art->newCode();
                break;

            case 1:
                $aux = explode(':', $art->descripcion);
                $art->referencia = empty($aux) ? $art->newCode() : $this->txt2codigo($aux[0], 25);
                break;

            default:
                $art->referencia = $this->randomString(10);
        }

        if (random_int(0, 9) > 0) {
            $art->codfabricante = $this->getOneItem($this->fabricantes)->codfabricante;
            $art->codfamilia = $this->getOneItem($this->familias)->codfamilia;
        } else {
            $art->codfabricante = null;
            $art->codfamilia = null;
        }

        $art->publico = (random_int(0, 3) === 0);
        $art->bloqueado = (random_int(0, 9) === 0);
        $art->nostock = (random_int(0, 9) === 0);
        $art->secompra = (random_int(0, 9) !== 0);
        $art->sevende = (random_int(0, 9) !== 0);
    }

    /**
     * Add more stock from this product.
     *
     * @param Model\Articulo $art
     * @param float|int      $quantity
     */
    private function sumStock(Model\Articulo $art, $quantity): void
    {
        $stock = new Model\Stock();
        $stock->referencia = $art->referencia;
        $stock->codalmacen = $this->getOneItem($this->almacenes)->codalmacen;
        $stock->cantidad = $quantity;
        $stock->save();
    }
}
