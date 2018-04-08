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

use FacturaScripts\Core\Lib\ExtendedController;

/**
 * Controller to list the items in the Balance model
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Fco. Antonio Moreno Pérez <famphuelva@gmail.com>
 */
class ListBalance extends ExtendedController\ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'balances';
        $pageData['icon'] = 'fa-clipboard';
        $pageData['menu'] = 'accounting';

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        $this->addView('ListBalance', 'Balance');
        $fields = [
            'codbalance', 'naturaleza', 'descripcion1', 'descripcion2', 'descripcion3', 'descripcion4', 'descripcion4ba',
        ];
        $this->addSearchFields('ListBalance', $fields);

        $this->addOrderBy('ListBalance', 'codbalance', 'code');
        $this->addOrderBy('ListBalance', 'descripcion1', 'description-1', 2);
        $this->addOrderBy('ListBalance', 'descripcion2', 'description-2');
        $this->addOrderBy('ListBalance', 'descripcion3', 'description-3');
        $this->addOrderBy('ListBalance', 'descripcion4', 'description-4');
        $this->addOrderBy('ListBalance', 'descripcion4ba', 'description-4ba');
    }
}
