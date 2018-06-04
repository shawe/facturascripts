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
use FacturaScripts\Core\Base\DivisaTools;
use FacturaScripts\Core\Lib\ExtendedController;
use FacturaScripts\Core\Lib\IDFiscal;
use FacturaScripts\Core\Lib\RegimenIVA;
use FacturaScripts\Core\Model;

/**
 * Controller to edit a single item from the Proveedor model
 *
 * @author Nazca Networks <comercial@nazcanetworks.com>
 * @author Fco. Antonio Moreno Pérez <famphuelva@gmail.com>
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class EditProveedor extends ExtendedController\PanelController
{

    /**
     * Returns the sum of the customer's total delivery notes.
     *
     * @param ExtendedController\EditView $view
     *
     * @return string
     */
    public function calcSupplierDeliveryNotes($view)
    {
        $where = [
            new DataBaseWhere('codproveedor', $view->model->codproveedor),
            new DataBaseWhere('editable', true)
        ];

        $totalModel = Model\TotalModel::all('albaranesprov', $where, ['total' => 'SUM(total)'], '')[0];

        $divisaTools = new DivisaTools();
        return $divisaTools::format($totalModel->totals['total'], 2);
    }

    /**
     * Returns the sum of the client's total outstanding invoices.
     *
     * @param ExtendedController\EditView $view
     *
     * @return string
     */
    public function calcSupplierInvoicePending($view)
    {
        $where = [];
        $where[] = new DataBaseWhere('codproveedor', $view->model->codproveedor);
        $where[] = new DataBaseWhere('estado', 'Pagado', '<>');

        $totalModel = Model\TotalModel::all('recibosprov', $where, ['total' => 'SUM(importe)'], '')[0];

        $divisaTools = new DivisaTools();
        return $divisaTools::format($totalModel->totals['total'], 2);
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'supplier';
        $pageData['icon'] = 'fa-users';
        $pageData['menu'] = 'purchases';
        $pageData['showonmenu'] = false;

        return $pageData;
    }

    /**
     * Create views
     */
    protected function createViews()
    {
        $this->addMainView();

        $this->addEditListView('EditDireccionProveedor', 'DireccionProveedor', 'addresses', 'fa-road');
        $this->addEditListView('EditCuentaBancoProveedor', 'CuentaBancoProveedor', 'bank-accounts', 'fa-university');
        $this->addListView('ListArticuloProveedor', 'ArticuloProveedor', 'products', 'fa-cubes');
        $this->addListView('ListFacturaProveedor', 'FacturaProveedor', 'invoices', 'fa-files-o');
        $this->addListView('ListAlbaranProveedor', 'AlbaranProveedor', 'delivery-notes', 'fa-files-o');
        $this->addListView('ListPedidoProveedor', 'PedidoProveedor', 'orders', 'fa-files-o');
        $this->addListView('ListPresupuestoProveedor', 'PresupuestoProveedor', 'estimations', 'fa-files-o');

        /// Disable columns
        $this->views['ListArticuloProveedor']->disableColumn('supplier', true);
        $this->views['ListFacturaProveedor']->disableColumn('supplier', true);
        $this->views['ListAlbaranProveedor']->disableColumn('supplier', true);
        $this->views['ListPedidoProveedor']->disableColumn('supplier', true);
        $this->views['ListPresupuestoProveedor']->disableColumn('supplier', true);
    }

    /**
     * Load view data
     *
     * @param string                      $viewName
     * @param ExtendedController\EditView $view
     */
    protected function loadData($viewName, $view)
    {
        $limit = FS_ITEM_LIMIT;
        switch ($viewName) {
            case 'EditProveedor':
                $code = $this->request->get('code');
                $view->loadData($code);
                break;

            case 'EditDireccionProveedor':
            case 'EditCuentaBancoProveedor':
                $limit = 0;
            /// no break
            case 'ListArticuloProveedor':
            case 'ListFacturaProveedor':
            case 'ListAlbaranProveedor':
            case 'ListPedidoProveedor':
            case 'ListPresupuestoProveedor':
                $codproveedor = $this->getViewModelValue('EditProveedor', 'codproveedor');
                $where = [new DataBaseWhere('codproveedor', $codproveedor)];
                $view->loadData('', $where, [], 0, $limit);
                break;
        }
    }

    /**
     * Create and configure main view
     */
    private function addMainView()
    {
        $this->addEditView('EditProveedor', 'Proveedor', 'supplier');

        /// Load values option to Fiscal ID select input
        $columnFiscalID = $this->views['EditProveedor']->columnForName('fiscal-id');
        $columnFiscalID->widget->setValuesFromArray(IDFiscal::all());

        /// Load values option to VAT Type select input
        $columnVATType = $this->views['EditProveedor']->columnForName('vat-regime');
        $columnVATType->widget->setValuesFromArray(RegimenIVA::all());
    }
}
