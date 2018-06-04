<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2018  Carlos García Gómez      <carlos@facturascripts.com>
 * Copyright (C) 2017  Francesc Pineda Segarra  <francesc.pineda.segarra@gmail.com>
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
 * Controller to list the items in the EstadoDocumento model
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 */
class ListEstadoDocumento extends ExtendedController\ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'document-states';
        $pageData['icon'] = 'fa-tags';
        $pageData['menu'] = 'admin';

        return $pageData;
    }

    /**
     * Load views
     */
    protected function createViews(): void
    {
        $this->addView('ListEstadoDocumento', 'EstadoDocumento', 'states', 'fa-tags');
        $this->addSearchFields('ListEstadoDocumento', ['nombre']);
        $this->addOrderBy('ListEstadoDocumento', 'idestado', 'id');
        $this->addOrderBy('ListEstadoDocumento', 'nombre', 'name');

        $types = $this->codeModel::all('estados_documentos', 'tipodoc', 'tipodoc');
        $this->addFilterSelect('ListEstadoDocumento', 'tipodoc', 'doc-type', 'tipodoc', $types);

        $generateTypes = $this->codeModel::all('estados_documentos', 'generadoc', 'generadoc');
        $this->addFilterSelect('ListEstadoDocumento', 'generadoc', 'generate-doc-type', 'generadoc', $generateTypes);

        $this->addFilterSelect('ListEstadoDocumento', 'actualizastock', 'update-stock', 'actualizastock', $this->getactualizastockValues());
        $this->addFilterCheckbox('ListEstadoDocumento', 'predeterminado', 'default', 'predeterminado');
        $this->addFilterCheckbox('ListEstadoDocumento', 'editable', 'editable', 'editable');
    }

    /**
     * Return a list of values to update stock values.
     *
     * @return array
     */
    private function getactualizastockValues(): array
    {
        return [
            ['code' => null, 'description' => '------'],
            ['code' => -2, 'description' => $this->i18n->trans('book')],
            ['code' => -1, 'description' => $this->i18n->trans('subtract')],
            ['code' => 0, 'description' => $this->i18n->trans('do-nothing')],
            ['code' => 1, 'description' => $this->i18n->trans('add')],
            ['code' => 2, 'description' => $this->i18n->trans('foresee')],
        ];
    }
}
