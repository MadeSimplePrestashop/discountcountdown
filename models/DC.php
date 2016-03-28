<?php

/**
 * Module Discount with Countdown
 * 
 * @author 	kuzmany.biz
 * @copyright 	kuzmany.biz/prestashop
 * @license 	kuzmany.biz/prestashop
 * Reminder: You own a single production license. It would only be installed on one online store (or multistore)
 */
class DC extends ObjectModel
{

    public $id_discountcountdown;
    public $id_group;
    public $expiration;
    public $date_to;
    public $countdown_format;
    public $active;
    public $availability;
    public $caption;
    public $display_header;
    public $element;
    public $insert;

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
        'table' => 'discountcountdown',
        'primary' => 'id_discountcountdown',
        'multilang' => true,
        'fields' => array(
            'id_group' => array('type' => self::TYPE_INT, 'required' => true),
            'expiration' => array('type' => self::TYPE_INT, 'required' => true),
            'countdown_format' => array('type' => self::TYPE_INT, 'required' => true),
            'availability' => array('type' => self::TYPE_INT, 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'required' => true),
            'display_header' => array('type' => self::TYPE_BOOL),
            'element' => array('type' => self::TYPE_STRING),
            'insert' => array('type' => self::TYPE_STRING),
            'date_to' => array('type' => self::TYPE_DATE),
            'caption' => array('type' => self::TYPE_HTML, 'lang' => true),
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

    public static function duplicate()
    {
        
    }
}
