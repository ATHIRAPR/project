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

return array(
	/**
	 * -------------------------------------------------------------------------
	 *  Logging threshold.
	 * -------------------------------------------------------------------------
	 *
	 *  Can be set to any of the following:
	 *
	 *      Fuel::L_NONE
	 *      Fuel::L_ERROR
	 *      Fuel::L_WARNING
	 *      Fuel::L_DEBUG
	 *      Fuel::L_INFO
	 *      Fuel::L_ALL
	 *
	 */

	'log_threshold'   => Fuel::L_ALL,
	'log_path'        => '/www/testserver5.oly.jp/stat/app/',
	'log_file'        => 'application_log.'.date('Ymd'),
);
