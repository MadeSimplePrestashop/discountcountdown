<?php
/*
 * 2007-2014 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2014 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class Group extends GroupCore
{

    /**
     * Return current group object
     * Use context
     *
     * @return Group Group object
     */
    public static function getCurrent()
    {
        static $groups = array();
        static $ps_unidentified_group = null;
        static $ps_customer_group = null;

        if ($ps_unidentified_group === null) {
            $ps_unidentified_group = Configuration::get('PS_UNIDENTIFIED_GROUP');
        }

        if ($ps_customer_group === null) {
            $ps_customer_group = Configuration::get('PS_CUSTOMER_GROUP');
        }

        $customer = Context::getContext()->customer;
        if (Validate::isLoadedObject($customer)) {
            $id_group = (int) $customer->id_default_group;
        } else {
            $id_group = (int) $ps_unidentified_group;
        }
        // Discount with Countdown change start
        $module = Module::getInstanceByName('discountcountdown');
        if ($module) {
            if (!Context::getContext()->cookie->id_guest) {
                Guest::setNewGuest(Context::getContext()->cookie);
            }
            $is_discount_group = $module->verifyDiscount();
            if ($is_discount_group) {
                $id_group = $is_discount_group;
            }
        }
        // end

        if (!isset($groups[$id_group])) {
            $groups[$id_group] = new Group($id_group);
        }

        if (!$groups[$id_group]->isAssociatedToShop(Context::getContext()->shop->id)) {
            $id_group = (int) $ps_customer_group;
            if (!isset($groups[$id_group])) {
                $groups[$id_group] = new Group($id_group);
            }
        }
        Context::getContext()->smarty->assign(array('id_group'=>$id_group));
        return $groups[$id_group];
    }
}
