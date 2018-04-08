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

use FacturaScripts\Core\Base;
use FacturaScripts\Core\Lib\RandomDataGenerator;
use FacturaScripts\Core\Model\Base\ModelClass;
use FacturaScripts\Core\Model\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to generate random data
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 * @author Rafael San José <info@rsanjoseo.com>
 */
class Randomizer extends Base\Controller
{

    const DINAMIC_MODEL = '\FacturaScripts\Dinamic\Model\\';
    /**
     * URL where reload.
     *
     * @var string
     */
    public $urlReload;

    /**
     * Contains the total quantity for each model.
     *
     * @var array
     */
    public $totalCounter;

    /**
     * Runs the controller's private logic.
     *
     * @param Response $response
     * @param User $user
     * @param Base\ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $option = $this->request->get('gen', '');
        if ($option !== '') {
            $this->execAction($option);
            $this->urlReload = $this->url() . '?gen=' . $option;
        }

        $this->getTotals();
    }

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'admin';
        $pageData['title'] = 'generate-test-data';
        $pageData['icon'] = 'fa-magic';

        return $pageData;
    }

    /**
     * Executes selected action.
     *
     * @param string $option
     */
    private function execAction($option)
    {
        switch ($option) {
            case 'agentes':
                $app = new RandomDataGenerator\Agentes();
                $txt = 'generated-agents';
                break;

            case 'albaranescli':
                $app = new RandomDataGenerator\AlbaranesCliente();
                $txt = 'generated-customer-delivery-notes';
                break;

            case 'albaranesprov':
                $app = new RandomDataGenerator\AlbaranesProveedor();
                $txt = 'generated-supplier-delivery-notes';
                break;

            case 'asientos':
                $app = new RandomDataGenerator\Asientos();
                $txt = 'generated-accounting-entries';
                break;

            case 'articulos':
                $app = new RandomDataGenerator\Articulos();
                $txt = 'generated-products';
                break;

            case 'articulosprov':
                $app = new RandomDataGenerator\ArticulosProveedor();
                $txt = 'generated-products';
                break;

            case 'clientes':
                $app = new RandomDataGenerator\Clientes();
                $txt = 'generated-customers';
                break;

            case 'cuentas':
                $app = new RandomDataGenerator\Cuentas();
                $txt = 'generated-accounts';
                break;

            case 'grupos':
                $app = new RandomDataGenerator\Grupos();
                $txt = 'generated-customer-groups';
                break;

            case 'fabricantes':
                $app = new RandomDataGenerator\Fabricantes();
                $txt = 'generated-manufacturers';
                break;

            case 'familias':
                $app = new RandomDataGenerator\Familias();
                $txt = 'generated-families';
                break;

            case 'pedidoscli':
                $app = new RandomDataGenerator\PedidosCliente();
                $txt = 'generated-customer-orders';
                break;

            case 'pedidosprov':
                $app = new RandomDataGenerator\PedidosProveedor();
                $txt = 'generated-supplier-orders';
                break;

            case 'presupuestoscli':
                $app = new RandomDataGenerator\PresupuestosCliente();
                $txt = 'generated-customer-estimations';
                break;

            case 'presupuestosprov':
                $app = new RandomDataGenerator\PresupuestosProveedor();
                $txt = 'generated-supplier-estimations';
                break;

            case 'proveedores':
                $app = new RandomDataGenerator\Proveedores();
                $txt = 'generated-supplier';
                break;

            case 'subcuentas':
                $app = new RandomDataGenerator\Subcuentas();
                $txt = 'generated-subaccounts';
                break;

            default:
                $app = false;
                $txt = '';
        }

        if (false !== $app) {
            $this->miniLog->info($this->i18n->trans($txt, ['%quantity%' => $app->generate()]));
        }
    }

    /**
     * Set totalCounter key for each model.
     */
    private function getTotals()
    {
        $this->totalCounter = [];

        $models = [
            'agentes' => self::DINAMIC_MODEL . 'Agente',
            'albaranescli' => self::DINAMIC_MODEL . 'AlbaranCliente',
            'albaranesprov' => self::DINAMIC_MODEL . 'AlbaranProveedor',
            'asientos' => self::DINAMIC_MODEL . 'Asiento',
            'articulos' => self::DINAMIC_MODEL . 'Articulo',
            'articulosprov' => self::DINAMIC_MODEL . 'ArticuloProveedor',
            'clientes' => self::DINAMIC_MODEL . 'Cliente',
            'cuentas' => self::DINAMIC_MODEL . 'Cuenta',
            'grupos' => self::DINAMIC_MODEL . 'GrupoClientes',
            'fabricantes' => self::DINAMIC_MODEL . 'Fabricante',
            'familias' => self::DINAMIC_MODEL . 'Familia',
            'pedidoscli' => self::DINAMIC_MODEL . 'PedidoCliente',
            'pedidosprov' => self::DINAMIC_MODEL . 'PedidoProveedor',
            'presupuestoscli' => self::DINAMIC_MODEL . 'PresupuestoCliente',
            'presupuestosprov' => self::DINAMIC_MODEL . 'PresupuestoProveedor',
            'proveedores' => self::DINAMIC_MODEL . 'Proveedor',
            'subcuentas' => self::DINAMIC_MODEL . 'Subcuenta'
        ];

        foreach ($models as $tag => $modelName) {
            $model = new $modelName();
            if ($model instanceof ModelClass) {
                $this->totalCounter[$tag] = $model->count();
            }
        }
    }
}
