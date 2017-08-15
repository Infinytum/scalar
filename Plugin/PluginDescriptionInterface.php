<?php
/**
 * Created by PhpStorm.
 * User: nila
 * Date: 06.06.17
 * Time: 15:46
 */

namespace Scaly\Plugin;


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