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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Controller;

use FacturaScripts\Core\Lib\ExtendedController;

/**
 * Controller to list the items in the Asiento model
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class ListAsiento extends ExtendedController\ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'accounting-entries';
        $pageData['icon'] = 'fa-balance-scale';
        $pageData['menu'] = 'accounting';

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews()
    {
        /// accounting entries
        $this->addView('ListAsiento', 'Asiento', 'accounting-entries', 'fa-balance-scale');
        $this->addSearchFields('ListAsiento', ['CAST(numero AS CHAR(10))', 'concepto']);

        $this->addFilterDatePicker('ListAsiento', 'fecha', 'date', 'fecha');
        $this->addFilterNumber('ListAsiento', 'importe', 'amount', 'importe');

        $selectValues = $this->codeModel::all('ejercicios', 'codejercicio', 'nombre');
        $this->addFilterSelect('ListAsiento', 'codejercicio', 'exercise', 'codejercicio', $selectValues);

        $this->addOrderBy('ListAsiento', 'numero', 'number');
        $this->addOrderBy('ListAsiento', 'fecha', 'date', 2);

        /// concepts
        $this->addView('ListConceptoPartida', 'ConceptoPartida', 'predefined-concepts', 'fa-indent');
        $this->addSearchFields('ListConceptoPartida', ['codconcepto', 'descripcion']);

        $this->addOrderBy('ListConceptoPartida', 'codconcepto', 'code');
        $this->addOrderBy('ListConceptoPartida', 'descripcion', 'description');
    }

    /**
     * Run the actions that alter data before reading it.
     *
     * @param string $action
     *
     * @return bool
     */
    protected function execPreviousAction($action): bool
    {
        switch ($action) {
            case 'renumber':
                if ($this->views['ListAsiento']->model->renumber()) {
                    $this->miniLog->notice($this->i18n->trans('renumber-accounting-ok'));
                }
                return true;

            default:
                return parent::execPreviousAction($action);
        }
    }
}
