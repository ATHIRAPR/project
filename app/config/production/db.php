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
 *  Database settings for production environment
 * -----------------------------------------------------------------------------
 *
 *  These settings get merged with the global settings.
 *
 */

return array(
	'default' => array(
		'type'       => 'mysqli',
		'connection' => array(
			//'hostname'       => '10.236.167.80',
			'hostname'       => 'db',
			'port'           => '3306',
			'database'       => 'localdb',
			'username'       => 'localdb',
			'password'       => 'localdb',
			'persistent'     => false,
			'compress'       => false,
		),
	),
);
