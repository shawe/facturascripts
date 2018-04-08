<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos Garcia Gomez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController;

/**
 *  Controller to list the items in the AlbaranProveedor model
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class ListAlbaranProveedor extends ExtendedController\ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'delivery-notes';
        $pageData['icon'] = 'fa-files-o';
        $pageData['menu'] = 'purchases';

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        $this->addView('ListAlbaranProveedor', 'AlbaranProveedor');
        $this->addSearchFields('ListAlbaranProveedor', ['codigo', 'numproveedor', 'observaciones']);

        $this->addFilterDatePicker('ListAlbaranProveedor', 'fecha', 'date', 'fecha');
        $this->addFilterNumber('ListAlbaranProveedor', 'total', 'total', 'total');

        $where = [new DataBaseWhere('tipodoc', 'AlbaranProveedor')];
        $stateValues = $this->codeModel::all('estados_documentos', 'idestado', 'nombre', true, $where);
        $this->addFilterSelect('ListAlbaranProveedor', 'idestado', 'state', 'idestado', $stateValues);

        $warehouseValues = $this->codeModel::all('almacenes', 'codalmacen', 'nombre');
        $this->addFilterSelect('ListAlbaranProveedor', 'codalmacen', 'warehouse', 'codalmacen', $warehouseValues);

        $serieValues = $this->codeModel::all('series', 'codserie', 'descripcion');
        $this->addFilterSelect('ListAlbaranProveedor', 'codserie', 'series', 'codserie', $serieValues);

        $paymentValues = $this->codeModel::all('formaspago', 'codpago', 'descripcion');
        $this->addFilterSelect('ListAlbaranProveedor', 'codpago', 'payment-method', 'codpago', $paymentValues);

        $this->addFilterAutocomplete('ListAlbaranProveedor', 'codproveedor', 'supplier', 'codproveedor', 'proveedores', 'codproveedor', 'nombre');

        $this->addOrderBy('ListAlbaranProveedor', 'codigo', 'code');
        $this->addOrderBy('ListAlbaranProveedor', 'fecha', 'date', 2);
        $this->addOrderBy('ListAlbaranProveedor', 'total', 'amount');
    }
}
