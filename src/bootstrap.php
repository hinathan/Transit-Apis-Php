<?php

/**
 * Simple autoload utility
 *
 * Pretty trivial
 *
 * PHP version 5.3
 *
 * @category  Default
 * @package   Transit
 * @author    Nathan Schmidt <nschmidt@gmail.com>
 * @copyright 2011 Nathan Schmidt
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   CVS: unused
 * @link      None
 *
 */

$autoload = function($class) {
    $path = __DIR__ . '/' . strtr($class, '\\_', '//') . '.php';
    if (file_exists($path)) {
        include $path;
        return true;
    }
    return false;
};

spl_autoload_register($autoload);

