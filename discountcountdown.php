<?php
/**
 * Module Discount with Countdown
 * 
 * @author 	kuzmany.biz
 * @copyright 	kuzmany.biz/prestashop
 * @license 	kuzmany.biz/prestashop
 * Reminder: You own a single production license. It would only be installed on one online store (or multistore)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
include_once dirname(__FILE__) . '/models/DC.php';
include_once dirname(__FILE__) . '/models/DCLogs.php';

class Discountcountdown extends Module
{

    protected $config_form = false;
    protected $cookie;
    public $cipherTool;

    public function __construct()
    {
        $this->name = 'discountcountdown';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'kuzmany.biz/prestashop';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '44258249c62bc824d6de016c55d5265d';

        parent::__construct();

        $this->displayName = $this->l('Discount with Countdown');
        $this->description = $this->l('Create special discount promotion with countdown.');

        $this->cookie = new Cookie(__CLASS__);
        if (!Configuration::get('PS_CIPHER_ALGORITHM') || !defined('_RIJNDAEL_KEY_')) {
            $this->cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
        } else {
            $this->cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
        }
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('displayFooter') ||
            !$this->registerHook('backOfficeHeader') ||
            !$this->registerHook('productActions') ||
            !$this->registerHook('displayTop')) {
            return false;
        }
        include_once(dirname(__FILE__) . '/sql/install.php');

        $this->context->controller->getLanguages();
        $lang_array = array();
        $id_parent = 0;
        foreach ($this->context->controller->_languages as $language) {
            $lang_array[(int) $language['id_lang']] = $this->displayName;
        }
        $this->installAdminTab($lang_array, 'AdminDC', $id_parent);
        return true;
    }

    public function uninstall()
    {
        include_once(dirname(__FILE__) . '/sql/uninstall.php');
        $this->uninstallAdminTab('AdminDC');
        return parent::uninstall();
    }

    private function installAdminTab($name, $className, $parent)
    {
        $tab = new Tab();
        $tab->name = $name;
        $tab->class_name = $className;
        $tab->id_parent = $parent;
        $tab->module = $this->name;
        $tab->add();
        return $tab;
    }

    private function uninstallAdminTab($className)
    {
        $tab = new Tab((int) Tab::getIdFromClassName($className));
        $tab->delete();
    }

    private function verifyDiscountFromDb($id_discount)
    {
        if (!$this->is_inspector()) {
            $discountdb = DC::getAll(array('c.id_discountcountdown' => $id_discount, 'c.active' => 1));
//discount doesn't exist
            if ($discountdb) {
                $discountdb = $discountdb[0];
                if ($discountdb['date_to'] && $discountdb['date_to'] != '0000-00-00 00:00:00' && time() > strtotime($discountdb['date_to'])) {
                    return;
                }
                $discountdb['options'] = Tools::jsonDecode($discountdb['options']);
                return $discountdb;
            }
        }
    }

    public function verifyDiscount()
    {
        if (Cache::retrieve(__CLASS__ . 'c')) {
            $discountdb = Cache::retrieve(__CLASS__ . 'c');
        } else {
            //if get param
            if (Tools::getIsset('dc')) {
                $discountdb = $this->verifyDiscountFromDb($this->cipherTool->decrypt((Tools::getValue('dc'))));
                if ($discountdb) {
                    $id_discount_from_url = $discountdb['id_discountcountdown'];
                }
            }
            $id_discount_from_cookie = $this->getCookie('id_discount');
            $activated = $this->getCookie('activated');
            if (!isset($id_discount_from_url) && !$id_discount_from_cookie) {
                return;
            }

            if (!isset($id_discount_from_url) && $id_discount_from_cookie) {
                $discountdb = $this->verifyDiscountFromDb($id_discount_from_cookie);
            }

            if (isset($id_discount_from_cookie) && isset($id_discount_from_url) && $id_discount_from_cookie != $id_discount_from_url) {
                // remove rom cookie
                $id_discount_from_cookie = '';
            }
            if (!$discountdb) {
                return;
            }

            if (!$id_discount_from_cookie && $id_discount_from_url) {
                $id_discount = $discountdb['id_discountcountdown'];
                $this->cookie->__set('id_discount', $id_discount);
                $activated = time();
                $this->cookie->__set('activated', $activated);

                $logs = new DCLogs();
                $logs->id_guest = $this->context->cookie->id_guest;
                $logs->id_discountcountdown = $discountdb['id_discountcountdown'];
                $logs->date = time();
                $logs->save();
            }
            //expiration
            if ($activated + ($discountdb['expiration'] * 3600) < time()) {
//                $exist = DCLogs::getAll(array('c.id_guest' => $this->context->cookie->id_guest, 'c.id_discountcountdown' => $discountdb['id_discountcountdown']));
//                $exist = DCLogs::exist();
//                $this->context->smarty->assign('dc_exist', $exist);
                return;
            }

            if (isset($id_discount_from_url)) {
                $this->context->smarty->assign('dc_message', 1);
            }

            $this->context->smarty->assign(array('dc' => $discountdb));
            $this->context->smarty->assign(array('dc_activated' => $activated + ($discountdb['expiration'] * 3600)));
            Cache::store(__CLASS__ . 'c', $discountdb);
        }
        return $discountdb['id_group'];
    }

    private function getCookie($par)
    {
        return $this->cookie->__get($par);
    }

    public function getContent()
    {
        Tools::redirectAdmin('index.php?controller=AdminDC&token=' . Tools::getAdminTokenLite('AdminDC'));
    }

    public function hookBackOfficeHeader()
    {
        if (in_array(Dispatcher::getInstance()->getController(), array('AdminDC'))) {
            $this->context->controller->addJS($this->_path . '/views/js/admin.js');
        }
    }

    private function is_inspector()
    {
        return Tools::getValue('dc_live_edit_token') && Tools::getValue('dc_live_edit_token') == $this->getLiveEditToken() && Tools::getIsset('id_employee') ? true : false;
    }

    public function hookDisplayFooter($params)
    {
        if ($this->is_inspector()) {
            $this->context->controller->addJS($this->_path . '/views/js/inspector.js');
            $this->context->controller->addCSS($this->_path . '/views/css/inspector.css');
            return $this->display(__FILE__, 'views/templates/hook/inspector.tpl');
        }
    }

    private function addMedia()
    {
        $this->context->controller->addJS($this->_path . '/views/js/jquery.countdown.min.js');
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookProductActions()
    {
        $this->addMedia();
        $discountdb = Cache::retrieve(__CLASS__ . 'c');
        if ($discountdb && isset($discountdb['display_product']) && $discountdb['display_product']) {
            return $this->display(__FILE__, 'discountcountdownproduct.tpl');
        }
    }

    public function hookDisplayTop()
    {
        $this->addMedia();
        $discountdb = Cache::retrieve(__CLASS__ . 'c');
        if ($discountdb && $discountdb['display_header']) {
            return $this->display(__FILE__, 'discountcountdowntop.tpl');
        }
    }

    public function getLiveEditToken()
    {
        return Tools::getAdminToken($this->name . (int) Tab::getIdFromClassName('sliderseverywhere')
                . (is_object(Context::getContext()->employee) ? (int) Context::getContext()->employee->id :
                    Tools::getValue('id_employee')));
    }
}
