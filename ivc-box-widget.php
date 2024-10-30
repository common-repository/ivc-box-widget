<?php
/*
 * Plugin Name: IvcBox Widget
 * Description: Подключение виджета IvcBox.
 * Version: 1.0
 * Author: IvcBox
 * Author URI: https://ivcbox.com/
 * License: GPLv2 or later
 */
 
require __DIR__ . '/functions.php';

register_activation_hook(__FILE__, 'ibw_create_db');


