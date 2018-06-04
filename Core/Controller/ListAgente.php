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
 * Controller to list the items in the Agentes model
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class ListAgente extends ExtendedController\ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'agents';
        $pageData['icon'] = 'fa-id-badge';
        $pageData['menu'] = 'admin';

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews(): void
    {
        $this->addView('ListAgente', 'Agente');
        $this->addSearchFields('ListAgente', ['nombre', 'apellidos', 'codagente', 'email']);

        $this->addOrderBy('ListAgente', 'codagente', 'code');
        $this->addOrderBy('ListAgente', 'concat(nombre,apellidos)', 'name', 1);
        $this->addOrderBy('ListAgente', 'provincia', 'province');

        $selectValues = $this->codeModel::all('agentes', 'cargo', 'cargo');
        $this->addFilterSelect('ListAgente', 'cargo', 'position', 'cargo', $selectValues);

        $cityValues = $this->codeModel::all('agentes', 'ciudad', 'ciudad');
        $this->addFilterSelect('ListAgente', 'ciudad', 'city', 'ciudad', $cityValues);

        $this->addFilterCheckbox('ListAgente', 'debaja', 'suspended', 'debaja');
    }
}
