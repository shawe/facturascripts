<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2018 Carlos Garcia Gomez  <carlos@facturascripts.com>
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

namespace FacturaScripts\Core\Model;

use FacturaScripts\Core\App\AppSettings;
use FacturaScripts\Core\Base\Utils;

/**
 * A country, for example Spain.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class Pais extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * Primary key. Varchar(3).
     *
     * @var string Alpha-3 code of the country.
     *             http://es.wikipedia.org/wiki/ISO_3166-1
     */
    public $codpais;

    /**
     * Alpha-2 code of the country.
     * http://es.wikipedia.org/wiki/ISO_3166-1
     *
     * @var string
     */
    public $codiso;

    /**
     * Country name.
     *
     * @var string
     */
    public $nombre;

    /**
     * Returns True if the country is the default of the company.
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->codpais === AppSettings::get('default', 'codpais');
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'codpais';
    }

    /**
     * Returns the name of the column that is the model's description.
     *
     * @return string
     */
    public function primaryDescriptionColumn(): string
    {
        return 'nombre';
    }

    /**
     * Returns True if the country is the default of the company.
     *
     * @return bool
     */
    public static function tableName(): string
    {
        return 'paises';
    }

    /**
     * Check the country's data, return True if they are correct.
     *
     * @return bool
     */
    public function test(): bool
    {
        $this->codpais = trim($this->codpais);
        $this->nombre = Utils::noHtml($this->nombre);

        if (!preg_match('/^[A-Z0-9]{1,20}$/i', $this->codpais)) {
            self::$miniLog->alert(self::$i18n->trans('invalid-column-lenght', ['%column%' => 'codpais', '%min%' => '1', '%max%' => '20']));
            return false;
        }

        if (!(\strlen($this->nombre) > 1) && !(\strlen($this->nombre) < 100)) {
            self::$miniLog->alert(self::$i18n->trans('invalid-column-lenght', ['%column%' => 'nombre', '%min%' => '1', '%max%' => '100']));
            return false;
        }

        return parent::test();
    }
}
