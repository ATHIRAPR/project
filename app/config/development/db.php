<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

/**
 * -----------------------------------------------------------------------------
 *  Database settings for development environment
 * -----------------------------------------------------------------------------
 *
 *  These settings get merged with the global settings.
 *
 */

return array(
	'default' => array(
		'type'       => 'mysqli',
		'connection' => array(
			'hostname'       => '127.0.0.1',
			'port'           => '3306',
			'database'       => 'cums',
			'username'       => 'root',
			'password'       => 'oL8eS6hz',
			'persistent'     => false,
			'compress'       => false,
		),
	),
);
