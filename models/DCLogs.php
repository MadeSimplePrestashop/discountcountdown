<?php

/**
 * Module Discount with Countdown
 * 
 * @author 	kuzmany.biz
 * @copyright 	kuzmany.biz/prestashop
 * @license 	kuzmany.biz/prestashop
 * Reminder: You own a single production license. It would only be installed on one online store (or multistore)
 */
class DCLogs extends ObjectModel {

    public $id_discountcountdown_logs;
    public $id_guest;
    public $id_discountcountdown;
    public $dat;
    public static $definition = array(
        'table' => 'discountcountdown_logs',
        'primary' => 'id_discountcountdown_logs',
        'fields' => array(
            'id_guest' => array('type' => self::TYPE_INT),
            'id_discountcountdown' => array('type' => self::TYPE_INT),
            'date' => array('type' => self::TYPE_DATE)
        )
    );

    public static function getAll($parms = array()) {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'c');
        if (empty($parms) == false)
            foreach ($parms as $k => $p)
                $sql->where('' . $k . ' =\'' . $p . '\'');
        return Db::getInstance()->executeS($sql);
    }

    public static function exist() {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'c');
        $sql->where('id_guest=\'' . Context::getContext()->cookie->id_guest . '\'');
        return Db::getInstance()->executeS($sql);
    }

}

?>