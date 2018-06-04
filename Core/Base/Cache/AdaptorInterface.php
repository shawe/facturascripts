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

namespace FacturaScripts\Core\Base\Cache;

/**
 * Adaptor Interface.
 *
 * @author Carlos García Gómez <carlos@facturascripts.com>
 */
interface AdaptorInterface
{
    /**
     * Gets the $key contents
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * Assign $content to the adaptor $key
     *
     * @param string $key
     * @param mixed  $content
     *
     * @return mixed
     */
    public function set(string $key, $content): bool;

    /**
     * Erases $key from the adaptor
     *
     * @param string $key
     *
     * @return mixed
     */
    public function delete(string $key);

    /**
     * Cleans the adaptor
     *
     * @return mixed
     */
    public function clear();
}
