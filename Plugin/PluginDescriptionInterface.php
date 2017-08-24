<?php
/**
 * (C) 2017 by Michael Teuscher (mk.teuscher@gmail.com)
 * as part of the Scalar PHP framework
 *
 * Released under the AGPL v3.0 license
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Created by PhpStorm.
 * User: nila
 * Date: 06.06.17
 * Time: 15:46
 */

namespace Scalar\Plugin;


interface PluginDescriptionInterface
{

    /**
     * Get plugin name
     *
     * @return string
     */
    public function getName();

    /**
     * Get plugin version
     *
     * @return string
     */
    public function getVersion();

    /**
     * Get plugin author
     *
     * @return string
     */
    public function getAuthor();

    /**
     * Get plugin description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get plugin main class
     *
     * @return string
     */
    public function getMainClass();

    /**
     * Get plugin repository server
     *
     * @return mixed
     */
    public function getRepository();

}