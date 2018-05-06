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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Lib\ExtendedController;

/**
 * Controller to edit a single item from the Fabricante model
 *
 * @package FacturaScripts\Core\Controller
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class EditFabricante extends ExtendedController\PanelController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'manufacturer';
        $pageData['menu'] = 'warehouse';
        $pageData['icon'] = 'fa-folder-open';
        $pageData['showonmenu'] = false;

        return $pageData;
    }

    /**
     * Load views.
     */
    protected function createViews()
    {
        $this->addEditView('EditFabricante', 'Fabricante', 'manufacturer');
        $this->addListView('EditFabricanteListArticulos', 'Articulo', 'products');
    }

    /**
     * Load data view procedure
     *
     * @param string                                                  $viewName
     * @param ExtendedController\BaseView|ExtendedController\EditView $view
     */
    protected function loadData($viewName, $view)
    {
        switch ($viewName) {
            case 'EditFabricante':
                $code = $this->request->get('code');
                $view->loadData($code);
                break;

            case 'EditFabricanteListArticulos':
                $codfabricante = $this->getViewModelValue('EditFabricante', 'codfabricante');
                $where = [new DataBaseWhere('codfabricante', $codfabricante)];
                $view->loadData('', $where);
                break;
        }
    }
}
