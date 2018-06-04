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
 * Controller to edit a single item from the GrupoClientes model
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 * @author Nazca Networks <comercial@nazcanetworks.com>
 */
class EditGrupoClientes extends ExtendedController\PanelController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'customer-group';
        $pageData['menu'] = 'sales';
        $pageData['icon'] = 'fa-folder-open';
        $pageData['showonmenu'] = false;

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews(): void
    {
        $this->addEditView('EditGrupoClientes', 'GrupoClientes', 'customer-group');
        $this->addListView('ListCliente', 'Cliente', 'customers', 'fa-users');
        $this->setTabsPosition('bottom');

        /// Disable columns
        $this->views['ListCliente']->disableColumn('group', true);
    }

    /**
     * Procedure responsible for loading the data to be displayed.
     *
     * @param string                      $viewName
     * @param ExtendedController\EditView $view
     */
    protected function loadData(string $viewName, $view): void
    {
        switch ($viewName) {
            case 'EditGrupoClientes':
                $code = $this->request->get('code');
                $view->loadData($code);
                break;

            case 'ListCliente':
                $codgrupo = $this->getViewModelValue('EditGrupoClientes', 'codgrupo');
                $where = [new DataBaseWhere('codgrupo', $codgrupo)];
                $view->loadData('', $where);
                break;
        }
    }
}
