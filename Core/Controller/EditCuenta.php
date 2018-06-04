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

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Lib\ExtendedController;

/**
 * Controller to edit a single item from the Cuenta model
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 * @author PC REDNET S.L. <luismi@pcrednet.com>
 */
class EditCuenta extends ExtendedController\PanelController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'accounts';
        $pageData['menu'] = 'accounting';
        $pageData['icon'] = 'fa-bar-chart';
        $pageData['showonmenu'] = false;

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews(): void
    {
        $this->addEditView('EditCuenta', 'Cuenta', 'account');
        $this->addListView('ListSubcuenta', 'Subcuenta', 'subaccounts');
        $this->addListView('ListCuenta', 'Cuenta', 'children-accounts');
        $this->setTabsPosition('bottom');
    }

    /**
     * Load view data procedure
     *
     * @param string                                                  $viewName
     * @param ExtendedController\EditView|ExtendedController\ListView $view
     */
    protected function loadData($viewName, $view): void
    {
        switch ($viewName) {
            case 'EditCuenta':
                $code = $this->request->get('code');
                $view->loadData($code);
                break;

            case 'ListSubcuenta':
                $idcuenta = $this->getViewModelValue('EditCuenta', 'idcuenta');
                $where = [new DataBaseWhere('idcuenta', $idcuenta)];
                $view->loadData('', $where);
                break;

            case 'ListCuenta':
                $idcuenta = $this->getViewModelValue('EditCuenta', 'idcuenta');
                $where = [new DataBaseWhere('parent_idcuenta', $idcuenta)];
                $view->loadData('', $where);
                break;
        }
    }
}
