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
use FacturaScripts\Core\Lib\IDFiscal;
use FacturaScripts\Core\Lib\RegimenIVA;
use FacturaScripts\Core\Model;

/**
 * Controller to edit a single item from the Cliente model
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 * @author Fco. Antonio Moreno Pérez <famphuelva@gmail.com>
 */
class EditCliente extends ExtendedController\PanelController
{

    /**
     * Returns the sum of the customer's total delivery notes.
     *
     * @param ExtendedController\EditView $view
     *
     * @return string
     */
    public function calcClientDeliveryNotes($view): string
    {
        $where = [];
        $where[] = new DataBaseWhere('codcliente', $this->getViewModelValue('EditCliente', 'codcliente'));
        $where[] = new DataBaseWhere('ptefactura', true);

        $totalModel = Model\TotalModel::all('albaranescli', $where, ['total' => 'SUM(total)'], '')[0];

        return $this->divisaTools::format($totalModel->totals['total'], 2);
    }

    /**
     * Returns the sum of the client's total outstanding invoices.
     *
     * @param ExtendedController\EditView $view
     *
     * @return string
     */
    public function calcClientInvoicePending($view): string
    {
        $where = [];
        $where[] = new DataBaseWhere('codcliente', $this->getViewModelValue('EditCliente', 'codcliente'));
        $where[] = new DataBaseWhere('estado', 'Pagado', '<>');

        $totalModel = Model\TotalModel::all('reciboscli', $where, ['total' => 'SUM(importe)'], '')[0];

        return $this->divisaTools::format($totalModel->totals['total'], 2);
    }

    /**
     * Create views
     */
    protected function createViews()
    {
        $this->addMainView();

        $this->addEditListView('DireccionCliente', 'EditDireccionCliente', 'addresses', 'fa-road');
        $this->addEditListView('CuentaBancoCliente', 'EditCuentaBancoCliente', 'customer-banking-accounts', 'fa-bank');
        $this->addListView('Cliente', 'ListCliente', 'same-group', 'fa-users');
        $this->addListView('FacturaCliente', 'ListFacturaCliente', 'invoices', 'fa-files-o');
        $this->addListView('AlbaranCliente', 'ListAlbaranCliente', 'delivery-notes', 'fa-files-o');
        $this->addListView('PedidoCliente', 'ListPedidoCliente', 'orders', 'fa-files-o');
        $this->addListView('PresupuestoCliente', 'ListPresupuestoCliente', 'estimations', 'fa-files-o');

        /// Disable columns
        $this->views['ListFacturaCliente']->disableColumn('customer', true);
        $this->views['ListAlbaranCliente']->disableColumn('customer', true);
        $this->views['ListPedidoCliente']->disableColumn('customer', true);
        $this->views['ListPresupuestoCliente']->disableColumn('customer', true);
    }

    /**
     * Load view data procedure
     *
     * @param string $keyView
     * @param ExtendedController\EditView $view
     */
    protected function loadData($keyView, $view)
    {
        $limit = FS_ITEM_LIMIT;
        switch ($keyView) {
            case 'EditCliente':
                $code = $this->request->get('code');
                $view->loadData($code);
                break;

            case 'ListCliente':
                $codgrupo = $this->getViewModelValue('EditCliente', 'codgrupo');
                if (!empty($codgrupo)) {
                    $where = [new DataBaseWhere('codgrupo', $codgrupo)];
                    $view->loadData(false, $where);
                }
                break;

            case 'EditDireccionCliente':
            case 'EditCuentaBancoCliente':
                $limit = 0;
                // no break
            case 'ListFacturaCliente':
            case 'ListAlbaranCliente':
            case 'ListPedidoCliente':
            case 'ListPresupuestoCliente':
                $codcliente = $this->getViewModelValue('EditCliente', 'codcliente');
                $where = [new DataBaseWhere('codcliente', $codcliente)];
                $view->loadData(false, $where, [], 0, $limit);
                break;
        }
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'customer';
        $pageData['icon'] = 'fa-users';
        $pageData['menu'] = 'sales';
        $pageData['showonmenu'] = false;

        return $pageData;
    }

    /**
     * Create and configure main view
     */
    private function addMainView()
    {
        $this->addEditView('Cliente', 'EditCliente', 'customer');

        /// Load values option to Fiscal ID select input
        $columnFiscalID = $this->views['EditCliente']->columnForName('fiscal-id');
        $columnFiscalID->widget->setValuesFromArray(IDFiscal::all());

        /// Load values option to VAT Type select input
        $columnVATType = $this->views['EditCliente']->columnForName('vat-regime');
        $columnVATType->widget->setValuesFromArray(RegimenIVA::all());
    }
}
