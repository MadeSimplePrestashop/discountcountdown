<?php
/**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'discountcountdown` (
    `id_discountcountdown` int(11) NOT NULL AUTO_INCREMENT,
    `id_group` int(11) NOT NULL,
    `expiration` float NOT NULL,
    `countdown_format` int(3) NOT NULL,
    `date_to` DATETIME NULL,
    `availability` int(3) NOT NULL,
    `display_header` int(3) NOT NULL,
    `options` TEXT,    
    `active` int(3) NOT NULL,    
    PRIMARY KEY  (`id_discountcountdown`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = '
CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'discountcountdown_lang` (
  `id_discountcountdown` int(11),
  `id_lang` int(3) NOT NULL,
  `caption` varchar(255),
  `success_message` varchar(255),
  PRIMARY KEY (`id_discountcountdown`,id_lang)
) ENGINE = ' . _MYSQL_ENGINE_ . '  ';

$sql[] = ''
    . 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'discountcountdown_shop` (
      `id_discountcountdown` int(10)  NOT NULL,
      `id_shop` int(3) unsigned NOT NULL,
      PRIMARY KEY (`id_discountcountdown`, `id_shop`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
    . '';


foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
