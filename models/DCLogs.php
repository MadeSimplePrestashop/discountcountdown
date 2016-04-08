<?php

/**
 * Module Discount with Countdown
 * 
 * @author 	kuzmany.biz
 * @copyright 	kuzmany.biz/prestashop
 * @license 	kuzmany.biz/prestashop
 * Reminder: You own a single production license. It would only be installed on one online store (or multistore)
 */
class DCLogs extends ObjectModel
{

    public $id_guest;
    public $id_discountcountdown;
    public $date;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        self::init();
        parent::__construct($id, $id_lang, $id_shop);
    }

    private static function init()
    {
        if (Shop::isFeatureActive()) {
            Shop::addTableAssociation(self::$definition['table'], array('type' => 'shop'));
        }
    }

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'discountcountdown_logs',
        'primary' => 'id_discountcountdown_logs',
        'multilang' => true,
        'fields' => array(
            'date' => array('type' => self::TYPE_DATE),
            'id_guest' => array('type' => self::TYPE_INT),
            'id_discountcountdown' => array('type' => self::TYPE_INT),
        )
    );

    public static function getAll($parms = array())
    {
        self::init();
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'c');
        $sql->leftJoin(self::$definition['table'] . '_lang', 'l', 'c.' . self::$definition['primary'] . ' = l.' . self::$definition['primary'] . ' AND l.id_lang = ' . (int) Context::getContext()->language->id);
        if (Shop::isFeatureActive()) {
            $sql->innerJoin(self::$definition['table'] . '_shop', 's', 'c.' . self::$definition['primary'] . ' = s.' . self::$definition['primary'] . ' AND s.id_shop = ' . (int) Context::getContext()->shop->id);
        }
        if (empty($parms) == false) {
            foreach ($parms as $k => $p) {
                $sql->where('' . $k . ' =\'' . $p . '\'');
            }
        }
        return Db::getInstance()->executeS($sql);
    }

    public static function exist()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table'], 'c');
        $sql->where('id_guest=\'' . Context::getContext()->cookie->id_guest . '\'');
        return Db::getInstance()->executeS($sql);
    }
}
