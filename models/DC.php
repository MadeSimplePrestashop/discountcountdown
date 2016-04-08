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
    public $success_message;
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
            'expiration' => array('type' => self::TYPE_FLOAT, 'required' => true),
            'countdown_format' => array('type' => self::TYPE_INT, 'required' => true),
            'availability' => array('type' => self::TYPE_INT, 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'required' => true),
            'display_header' => array('type' => self::TYPE_BOOL),
            'options' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_to' => array('type' => self::TYPE_DATE),
            'caption' => array('type' => self::TYPE_HTML, 'lang' => true),
            'success_message' => array('type' => self::TYPE_STRING, 'lang' => true),
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

        $this->id_group = 0;
        parent::add($autodate, $null_values);
//create group
        $group = new Group();
        $lang_array = array();
        foreach (Context::getContext()->controller->getLanguages() as $language) {
            $lang_array[(int) $language['id_lang']] = self::$definition['table'] . $this->id;
        }
        $group->name = $lang_array;
        $group->reduction = (int) Tools::getValue('discount');
        $group->price_display_method = 0;
        $group->show_prices = 1;
        $group->save();
        if ($group->id) {
            $this->id_group = $group->id;
            $this->update();
            self::updateRestrictions($group->id);
        }
    }

    private static function setGroupReduction($id_group)
    {
        if (Tools::getIsset('category')) {
            foreach (Tools::getValue('category') as $id_category => $c) {
                $id_category_group = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `id_category`
		FROM `' . _DB_PREFIX_ . 'category_group`
		WHERE `id_group` = ' . (int) $id_group . ' AND `id_category` = ' . (int) $id_category);
                if (!$id_category_group) {
                    Db::getInstance()->insert('category_group', array('id_category' => (int) $id_category, 'id_group' => (int) $id_group));
                }
            }

            $empty_category = (!empty($c) || Tools::strlen(trim($c))) != 0 ? false : true;
            $id_group_reduction = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `id_group_reduction`
		FROM `' . _DB_PREFIX_ . 'group_reduction`
		WHERE `id_group` = ' . (int) $id_group . ' AND `id_category` = ' . (int) $id_category);
            if ($id_group_reduction) {
                $groupreduction = new GroupReductionCore($id_group_reduction);
                if ($empty_category == false) {
                    $groupreduction->reduction = (float) ($c / 100);
                    $groupreduction->update();
                } else {
                    $groupreduction->delete();
                }
            } else {
                if ($empty_category == false) {
                    $groupreduction = new GroupReductionCore();
                    $groupreduction->id_group = $id_group;
                    $groupreduction->id_category = $id_category;
                    $groupreduction->reduction = (float) ($c / 100);
                    $groupreduction->save();
                }
            }
        }
    }

    public function update($null_values = false)
    {

        $options = $this->transform_options();
        if ($options != false) {
            $this->options = $options;
        }
        parent::update($null_values);

        self::setGroupReduction($this->id_group);

        $group = new Group($this->id_group);
        $group->reduction = (int) Tools::getValue('discount');
        $group->update();
    }

    public function delete()
    {
//update group
        $group = new Group($this->id_group);
        $group->delete();

        parent::delete();
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
        return array('element', 'insert', 'backgroundColor', 'borderColor', 'borderWidth', 'style', 'borderStyle', 'link');
    }

    protected static function updateRestrictions($id_group)
    {
        Group::truncateModulesRestrictions((int) $id_group);
        $shops = Shop::getShops(true, null, true);
        $auth_modules = array();
        $modules = Module::getModulesInstalled();
        foreach ($modules as $module) {
            $auth_modules[] = $module['id_module'];
        }
        if (is_array($auth_modules)) {
            return Group::addModulesRestrictions($id_group, $auth_modules, $shops);
        }
    }
}
