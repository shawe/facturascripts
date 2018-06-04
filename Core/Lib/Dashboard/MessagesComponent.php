<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos García Gómez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Lib\Dashboard;

use FacturaScripts\Dinamic\Model;

/**
 * Description of ComponentMessages
 *
 * @author Francesc Pineda Segarra <francesc.pineda.segarra@gmail.com>
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
class MessagesComponent extends BaseComponent implements ComponentInterface
{

    /**
     * List of dashboard cards.
     *
     * @var Model\DashboardData[]
     */
    public $messages;

    /**
     * MessagesComponent constructor.
     *
     * @param Model\DashboardData $data
     * @param string              $userNick
     */
    public function __construct(Model\DashboardData $data, string $userNick)
    {
        parent::__construct($data, $userNick);
        $this->messages = [];
    }

    /**
     * Sets the special fields for the component and their initial values
     *
     * @return array
     */
    public static function getPropertiesFields(): array
    {
        return [
            'description' => '',
            'color' => 'info',
            'link' => '',
        ];
    }

    /**
     * Load data of component for user to put into dashboard
     */
    public function loadData(): void
    {
        $where = $this->getDataFilter();
        $orderBy = $this->getDataOrderBy();

        $model = new Model\DashboardData();
        $this->messages = $model->all($where, $orderBy);

        if (empty($this->messages)) {
            $this->generateRandomData(15, 15);
            $this->messages = $model->all($where, $orderBy);
        }
    }

    /**
     * Return the URL to this component.
     *
     * @param string $id
     *
     * @return string
     */
    public function url(string $id): string
    {
        return 'EditDashboardData?code=' . $id;
    }

    /**
     * Data persists in the database, modifying if the record existed or inserting
     * in case the primary key does not exist.
     *
     * @param array $data
     */
    public function saveData(array $data): void
    {
        $newItem = new Model\DashboardData();
        $newItem->component = 'Messages';
        $newItem->nick = $this->nick;

        if (isset($data['id'])) {
            $newItem->id = $data['id'];
            $newItem->displaydate = $data['displaydate'];
            $newItem->nick = $data['nick'];
        }

        if ($this->randomData) {
            $data['link'] = (random_int(0, 3) === 0) ? 'https://www.' . random_int(999, 99999) . '.com' : '';
        }

        $newItem->properties = [
            'color' => $data['color'],
            'description' => $data['description'],
            'link' => $data['link'],
        ];

        $newItem->save();
    }

    /**
     * Return the number of columns to display width this component.
     *
     * @return string
     */
    public function getNumColumns(): string
    {
        return 'col-5';
    }
}
