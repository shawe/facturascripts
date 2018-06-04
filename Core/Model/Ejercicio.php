<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2018 Carlos García Gómez  <carlos@facturascripts.com>
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

use FacturaScripts\Core\Base\Utils;

/**
 * Accounting year. It is the period in which accounting entry, invoices, delivery notes are grouped ...
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
class Ejercicio extends Base\ModelClass
{

    use Base\ModelTrait;

    /**
     * Primary key. Varchar(4).
     *
     * @var string
     */
    public $codejercicio;

    /**
     * Exercise status: ABIERTO|CERRADO
     *
     * @var string
     */
    public $estado;

    /**
     * End date of the exercise.
     *
     * @var string with date format
     */
    public $fechafin;

    /**
     * Start date of the exercise.
     *
     * @var string with date format
     */
    public $fechainicio;

    /**
     * Accounting entry ID of the year end.
     *
     * @var int
     */
    public $idasientocierre;

    /**
     * Profit and loss entry ID.
     *
     * @var int
     */
    public $idasientopyg;

    /**
     * Opening accounting entry ID.
     *
     * @var int
     */
    public $idasientoapertura;

    /**
     * Length of characters of the subaccounts assigned.
     *
     * @var int
     */
    public $longsubcuenta;

    /**
     * Name of the exercise.
     *
     * @var string
     */
    public $nombre;

    /**
     * Returns the exercise for the indicated date.
     * If it does not exist, create it.
     *
     * @param string $fecha
     * @param bool   $soloAbierto
     * @param bool   $crear
     *
     * @return bool|Ejercicio
     */
    public static function getByFecha(string $fecha, bool $soloAbierto = true, bool $crear = true)
    {
        $sql = 'SELECT * FROM ' . static::tableName()
            . ' WHERE ' . self::$dataBase->var2str($fecha) . ' BETWEEN fechainicio AND fechafin;';

        $data = self::$dataBase->select($sql);
        if (empty($data)) {
            if ($crear && (strtotime($fecha) >= 1)) {
                $eje = new self();
                $eje->codejercicio = $eje->newCodigo(date('Y', strtotime($fecha)));
                $eje->nombre = date('Y', strtotime($fecha));
                $eje->fechainicio = date('1-1-Y', strtotime($fecha));
                $eje->fechafin = date('31-12-Y', strtotime($fecha));
                if ($eje->save()) {
                    return $eje;
                }
            }
            return false;
        }

        $eje = new self($data[0]);
        return ($eje->abierto() || $soloAbierto === false) ? $eje : false;
    }

    /**
     * Returns the state of the exercise ABIERTO -> true | CLOSED -> false
     *
     * @return bool
     */
    public function abierto(): bool
    {
        return $this->estado === 'ABIERTO';
    }

    /**
     * Reset the values of all model properties.
     */
    public function clear(): void
    {
        parent::clear();
        $this->nombre = '';
        $this->fechainicio = date('01-01-Y');
        $this->fechafin = date('31-12-Y');
        $this->estado = 'ABIERTO';
        $this->longsubcuenta = 10;
    }

    /**
     * This function is called when creating the model table. Returns the SQL
     * that will be executed after the creation of the table. Useful to insert values
     * default.
     *
     * @return string
     */
    public function install(): string
    {
        return 'INSERT INTO ' . static::tableName() . ' (codejercicio,nombre,fechainicio,fechafin,'
            . 'estado,longsubcuenta,idasientoapertura,idasientopyg,idasientocierre) '
            . "VALUES ('" . date('Y') . "','" . date('Y') . "'," . self::$dataBase->var2str(date('01-01-Y'))
            . ', ' . self::$dataBase->var2str(date('31-12-Y')) . ",'ABIERTO',10,NULL,NULL,NULL);";
    }

    /**
     * Returns the name of the column that is the model's primary key.
     *
     * @return string
     */
    public static function primaryColumn(): string
    {
        return 'codejercicio';
    }

    /**
     * Name of the exercise.
     *
     * @return string
     */
    public static function tableName(): string
    {
        return 'ejercicios';
    }

    /**
     * Check the exercise data, return True if they are correct
     *
     * @return bool
     */
    public function test(): bool
    {
        /// TODO: Change dates verify to $this->inRange() call
        $this->codejercicio = trim($this->codejercicio);
        $this->nombre = Utils::noHtml($this->nombre);

        if (!preg_match('/^[A-Z0-9_]{1,4}$/i', $this->codejercicio)) {
            self::$miniLog->alert(self::$i18n->trans('invalid-column-lenght', ['%column%' => 'codejercicio', '%min%' => '1', '%max%' => '4']));
        } elseif (!(strlen($this->nombre) > 1) && !(strlen($this->nombre) < 100)) {
            self::$miniLog->alert(self::$i18n->trans('invalid-column-lenght', ['%column%' => 'nombre', '%min%' => '1', '%max%' => '100']));
        } elseif (strtotime($this->fechainicio) > strtotime($this->fechafin)) {
            $params = ['%endDate%' => $this->fechainicio, '%startDate%' => $this->fechafin];
            self::$miniLog->alert(self::$i18n->trans('start-date-later-end-date', $params));
        } elseif (strtotime($this->fechainicio) < 1) {
            self::$miniLog->alert(self::$i18n->trans('date-invalid'));
        } else {
            return true;
        }

        return false;
    }

    /**
     * Returns the date closest to $date that is within the range of this exercise.
     *
     * @param string $fecha
     * @param bool   $showError
     *
     * @return string
     */
    public function getBestFecha($fecha, $showError = false): string
    {
        $fecha2 = strtotime($fecha);

        if ($fecha2 >= strtotime($this->fechainicio) && $fecha2 <= strtotime($this->fechafin)) {
            return $fecha;
        }

        if ($fecha2 > strtotime($this->fechainicio)) {
            if ($showError) {
                self::$miniLog->alert(self::$i18n->trans('date-out-of-rage-selected-better'));
            }

            return $this->fechafin;
        }

        if ($showError) {
            self::$miniLog->alert(self::$i18n->trans('date-out-of-rage-selected-better'));
        }

        return $this->fechainicio;
    }

    /**
     * Check if the indicated date is within the period of the exercise dates
     *
     * @param string $dateToCheck (string with date format)
     *
     * @return bool
     */
    public function inRange($dateToCheck): bool
    {
        $start = strtotime($this->fechainicio);
        $end = strtotime($this->fechafin);
        $date = strtotime($dateToCheck);
        return (($date >= $start) && ($date <= $end));
    }

    /**
     * Returns a new code for an exercise.
     *
     * @param string $cod
     *
     * @return string
     */
    public function newCodigo($cod = '0001'): string
    {
        $sql = 'SELECT * FROM ' . static::tableName() . ' WHERE codejercicio = ' . self::$dataBase->var2str($cod) . ';';
        if (!self::$dataBase->select($sql)) {
            return $cod;
        }

        $sql = 'SELECT MAX(' . self::$dataBase->sql2Int('codejercicio') . ') as cod FROM ' . static::tableName() . ';';
        $newCod = self::$dataBase->select($sql);
        if (!empty($newCod)) {
            return sprintf('%04s', 1 + (int) $newCod[0]['cod']);
        }

        return '0001';
    }

    /**
     * Returns the value of the year of the exercise.
     *
     * @return string en formato año
     */
    public function year(): string
    {
        return date('Y', strtotime($this->fechainicio));
    }
}
