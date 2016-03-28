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
    //public $availability;
    public $caption;
    public $display_header;
    public $options;

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
            //'availability' => array('type' => self::TYPE_INT, 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'required' => true),
            'display_header' => array('type' => self::TYPE_BOOL),
            'options' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
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

    public function add($autodate = true, $null_values = false)
    {
        $options = $this->transform_options();
        if ($options != false)
            $this->options = $options;


        if ($this->id_group) {
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'category_group` WHERE `id_group` = ' . (int) $this->id_group);
            $categories = Category::getAllCategoriesName(null, Context::getContext()->language->id);
            foreach ($categories as $category) {
                Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'category_group` 
                    (`id_category`, `id_group`)
		VALUES (' . (int) $category['id_category'] . ', ' . (int) $this->id_group . ')');
            }
        }
        parent::add($autodate, $null_values);
    }

    public function update($null_values = false)
    {
        $options = $this->transform_options();
        if ($options != false)
            $this->options = $options;
        parent::update($null_values);
    }

    public static function duplicate()
    {
        $dc = new DC(Tools::getValue(self::$definition['primary']));
        if (!is_object($dc))
            return;
        unset($dc->id);
        $dc->active = 0;
        $dc->save();
    }

    private function transform_options()
    {
        if (!Tools::getIsset('submitUpdate' . self::$definition['table']) && !Tools::getIsset('submitAdd' . self::$definition['table']))
            return false;
        $parms = array();
        foreach (self::getOptionFields() as $option)
            $parms[$option] = Tools::getValue($option);
        return Tools::jsonEncode($parms);
    }

    public static function getOptionFields()
    {
        return array('element', 'insert', 'backgroundColor', 'borderColor', 'borderWidth', 'style', 'borderStyle');
    }
}
