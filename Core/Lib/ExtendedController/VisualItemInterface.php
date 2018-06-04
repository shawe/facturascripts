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

namespace FacturaScripts\Core\Lib\ExtendedController;

/**
 * Visual elements interface
 *
 * @author Artex Trading sa <jcuello@artextrading.com>
 */
interface VisualItemInterface
{

    /**
     * Create and load element structure from JSON file
     *
     * @param array $item
     */
    public static function newFromJSON(array $item);

    /**
     * Create and load element structure from XML file
     *
     * @param \SimpleXMLElement $item
     */
    public static function newFromXML(\SimpleXMLElement $item);

    /**
     * Generates the HTML code to display the header for the visual element
     *
     * @param string $value
     *
     * @return string
     */
    public function getHeaderHTML(string $value): string;

    /**
     * Loads the attributes structure from a JSON file
     *
     * @param array $items
     */
    public function loadFromJSON(array $items): void;

    /**
     * Loads the attributes structure from a XML file
     *
     * @param \SimpleXMLElement $items
     */
    public function loadFromXML(\SimpleXMLElement $items): void;
}
