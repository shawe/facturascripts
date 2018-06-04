<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2017-2018  Carlos García Gómez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Controller;

use FacturaScripts\Dinamic\Lib\ExtendedController;

/**
 * Controller to list the items in the FormaPago model
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class ListFormaPago extends ExtendedController\ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'payment-methods';
        $pageData['icon'] = 'fa-credit-card';
        $pageData['menu'] = 'accounting';

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews(): void
    {
        /* Payment Methods */
        $this->addView('ListFormaPago', 'FormaPago', 'payment-methods', 'fa-credit-card');
        $this->addSearchFields('ListFormaPago', ['descripcion', 'codpago', 'codcuenta']);

        $this->addOrderBy('ListFormaPago', 'codpago', 'code');
        $this->addOrderBy('ListFormaPago', 'descripcion', 'description');

        $this->addFilterCheckbox('ListFormaPago', 'domiciliado', 'domicilied', 'domiciliado');
        $this->addFilterCheckbox('ListFormaPago', 'imprimir', 'print', 'imprimir');

        /* Bank accounts */
        $this->addView('ListCuentaBanco', 'CuentaBanco', 'bank-accounts', 'fa-university');
        $this->addSearchFields('ListCuentaBanco', ['descripcion', 'codcuenta']);

        $this->addOrderBy('ListCuentaBanco', 'codcuenta', 'code');
        $this->addOrderBy('ListCuentaBanco', 'descripcion', 'description');
    }
}
