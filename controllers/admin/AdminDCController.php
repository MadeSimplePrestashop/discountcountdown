<?php
/**
 * Module Discount with Countdown
 * 
 * @author 	kuzmany.biz
 * @copyright 	kuzmany.biz/prestashop
 * @license 	kuzmany.biz/prestashop
 * Reminder: You own a single production license. It would only be installed on one online store (or multistore)
 */
require_once(_PS_MODULE_DIR_ . 'discountcountdown/models/DC.php');

class AdminDCController extends ModuleAdminController
{

    public function __construct()
    {

        $this->bootstrap = true;
        $this->show_toolbar = true;
        $this->show_toolbar_options = true;
        $this->show_page_header_toolbar = true;

        $this->table = DC::$definition['table'];
        $this->className = 'DC';

        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');

        Shop::addTableAssociation($this->table, array('type' => 'shop'));
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            )
        );

        parent::__construct();
    }

    public function initContent()
    {
        if (Tools::getIsset('duplicate' . $this->table)) {
            DC::duplicate();
        }
        parent::initContent();
    }

    public function postProcess()
    {
        parent::postProcess();
        if (Tools::getIsset('delete' . $this->table)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminDC'));
        } elseif (Tools::getIsset('delete' . $this->table)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminDC'));
        } elseif (Tools::getIsset('submitStay')) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminDC') . '&' . DC::$definition['primary'] . '=' . $this->object->id . '&update' . $this->table);
        } elseif (Tools::isSubmit('submitAdd' . $this->table)) {
            Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminDC'));
        } elseif (Tools::getIsset('status' . $this->table)) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminDC'));
        }
    }

    public function renderForm()
    {

        $obj = $this->loadObject(true);
        if (!$obj) {
            return;
        }
        if (is_object($obj)) {
            $options = Tools::jsonDecode($obj->options);
        } else {
            $options = '';
        }
        if ($obj->id) {
            $group = new Group($obj->id_group);
            $activation_html = '<p><input type="text" readonly="readonly" value="' . $this->context->shop->getBaseUrl() . '?dc=' . urlencode($this->module->cipherTool->encrypt($obj->id)) . '" /></p>'
                . '<p>' . $this->l('Use this link for your customers in your campaign') . '</p>';
        } else {
            $activation_html = '<p>' . $this->l('Please save before we\re able to generate link') . '</p>';
        }

        $this->fields_form = array(
            'legend' => array(
                'tinymce' => true,
                'title' => $this->l('Discount countdowns'),
                'icon' => 'icon-cogs'
            ),
            'tabs' => array(
                'discount' => $this->l('Discount'),
                'countdown' => $this->l('Countdown'),
                'display' => $this->l('Display'),
                'customization' => $this->l('Customization'),
                'activation' => $this->l('Link to activate discount'),
            ),
            'input' => array(
                array(
                    'tab' => 'discount',
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'default_value' => isset($obj->active) ? $obj->active : 1
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Expiration after'),
                    'name' => 'expiration',
                    'class' => 'input fixed-width-md',
                    'suffix' => 'hours',
                    'default_value' => isset($obj->expiration) ? $obj->expiration : 24,
                    'tab' => 'discount'
                ),
                array(
                    'type' => 'hidden',
                    'name' => DC::$definition['primary'],
                    'tab' => 'options'
                ),
                array(
                    'tab' => 'countdown',
                    'type' => 'switch',
                    'label' => $this->l('Display'),
                    'name' => 'display_header',
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled')
                        )
                    ),
                    'default_value' => isset($obj->display_header) ? $obj->display_header : 1
                ),
                array(
                    'tab' => 'countdown',
                    'type' => 'select',
                    'label' => $this->l('Countdown format'),
                    'name' => 'countdown_format',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => '1',
                                'name' => $this->l('Days/Hours/Minutes/Seconds')
                            ),
                            array(
                                'id' => '2',
                                'name' => $this->l('Hours/Minutes/Seconds')
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'tab' => 'countdown',
                    'type' => 'text',
                    'label' => $this->l('Text to countdown'),
                    'lang' => true,
                    'name' => 'caption',
                    'desc' => $this->l('For example: Your discount 10% expire until')
                ),
                array(
                    'tab' => 'countdown',
                    'type' => 'text',
                    'label' => $this->l('Countdown link'),
                    'name' => 'link',
                    'default_value' => isset($options->link) ? $options->link : '',
                ),
                array(
                    'tab' => 'discount',
                    'type' => 'select',
                    'label' => $this->l('Availability'),
                    'name' => 'availability',
                    'options' => array(
                        'query' => array(
                            array(
                                'id' => '1',
                                'name' => $this->l('Only once for the user')
                            ),
                            array(
                                'id' => '2',
                                'name' => $this->l('Multiple times for the user')
                            ),
                        ),
                        'id' => 'id',
                        'name' => 'name',
                    ),
                ),
                array(
                    'tab' => 'discount',
                    'type' => 'datetime',
                    'label' => $this->l('Active until'),
                    'name' => 'date_to',
                    'desc' => $this->l('Leave empty, If discount never expire'),
                ),
                array(
                    'tab' => 'discount',
                    'type' => 'text',
                    'label' => $this->l('Success alert activation messsage'),
                    'lang' => true,
                    'name' => 'success_message',
                    'desc' => $this->l('For example: Discount will be activated')
                ),
                array(
                    'tab' => 'discount',
                    'type' => 'text',
                    'label' => $this->l('Already activated alert messsage'),
                    'lang' => true,
                    'name' => 'already_message',
                    'hint' => $this->l('Availability has to be  set to Only once for the user'),
                    'desc' => $this->l('For example: Discount has been activated')
                ),
                array(
                    'tab' => 'activation',
                    'type' => 'html',
                    'name' => 'activation_html',
                    'html_content' => $activation_html
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
                'name' => 'submit',
            )
        );

        $positions = array();
        $href = $this->context->shop->getBaseUrl() . '?dc_live_edit_token=' . $this->module->getLiveEditToken() . '&id_employee=' . $this->context->employee->id;
        $positions[] = '<div class="col-sm-4">
            <input  type="hidden" value="' . isset($obj->position) . '" name="positions" id="positions">';

        $positions[] = '<a onclick="if(!confirm(\'' . $this->l('Web page opens in a mode for direct selection position through the web site element picker. Do you want continue?') . '\')) return false"  target="_blank" href="' . $href . '" id="select_position"><button   type="button" class="btn btn-default" >' . $this->l('select element from website') . '</button></a>';
        $positions[] = '</div>';
        $this->fields_value['position'] = implode('', $positions);
        $this->fields_form['input'][] = array(
            'tab' => 'display',
            'type' => 'free',
            'name' => 'position',
            'label' => $this->l('Website position picker')
        );
        $this->fields_value['element'] = '<input value="' . (isset($options->element) ? $options->element : '' ) . '" name="element" id="element" type="text">';
        $this->fields_form['input'][] = array(
            'class' => 'element',
            'tab' => 'display',
            'type' => 'free',
            'name' => 'element',
            'desc' => $this->l('If leave empty, countdown will not displayed'),
            'label' => $this->l('Selected element'),
        );

        $this->fields_form['input'][] = array(
            'tab' => 'display',
            'type' => 'radio',
            'label' => $this->l('Insert slider'),
            'name' => 'insert',
            'required' => true,
            'values' => array(
                array(
                    'id' => 'after',
                    'value' => 'after',
                    'label' => $this->l('After selected element')
                ),
                array(
                    'id' => 'before',
                    'value' => 'before',
                    'label' => $this->l('Before selected element')
                ),
                array(
                    'id' => 'prepend',
                    'value' => 'prepend',
                    'label' => $this->l('Prepend to selected element')
                ),
                array(
                    'id' => 'append',
                    'value' => 'append',
                    'label' => $this->l('Append to selected element')
                ),
                array(
                    'id' => 'replace',
                    'value' => 'replace',
                    'label' => $this->l('Replace selected element')
                )
            ),
            'default_value' => isset($options->insert) ? $options->insert : 'prepend',
        );


        $this->fields_form['input'][] = array(
            'tab' => 'customization',
            'type' => 'color',
            'label' => $this->l('Background color'),
            'name' => 'backgroundColor',
            'default_value' => isset($options->backgroundColor) ? $options->backgroundColor : '#f6f6f6',
        );
        $this->fields_form['input'][] = array(
            'tab' => 'customization',
            'type' => 'text',
            'label' => 'Border width',
            'name' => 'borderWidth',
            'class' => 'input fixed-width-sm',
            'default_value' => isset($options->borderWidth) ? $options->borderWidth : '3px',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'customization',
            'type' => 'select',
            'label' => $this->l('Border Style'),
            'name' => 'borderStyle',
            'options' => array(
                'query' => array(
                    array(
                        'value' => 'none',
                        'label' => $this->l('None')
                    ),
                    array(
                        'value' => 'hidden',
                        'label' => $this->l('Hidden')
                    ),
                    array(
                        'value' => 'dotted',
                        'label' => $this->l('Dotted')
                    ),
                    array(
                        'value' => 'solid',
                        'label' => $this->l('Solid')
                    ),
                    array(
                        'value' => 'double',
                        'label' => $this->l('double')
                    ),
                    array(
                        'value' => 'groove',
                        'label' => $this->l('Groove')
                    ),
                    array(
                        'value' => 'ridge',
                        'label' => $this->l('Ridge')
                    ),
                    array(
                        'value' => 'inset',
                        'label' => $this->l('Inset')
                    ),
                    array(
                        'value' => 'outset',
                        'label' => $this->l('Outset')
                    )
                ),
                'id' => 'value',
                'name' => 'label'
            ),
            'default_value' => isset($options->borderStyle) ? $options->borderStyle : 'solid',
        );


        $this->fields_form['input'][] = array(
            'tab' => 'customization',
            'type' => 'color',
            'label' => $this->l('Border color'),
            'name' => 'borderColor',
            'default_value' => isset($options->borderColor) ? $options->borderColor : '#e9e9e9',
        );

        $this->fields_form['input'][] = array(
            'tab' => 'customization',
            'type' => 'text',
            'label' => 'Inline CSS style',
            'name' => 'style',
            'desc' => $this->l('For advanced user'),
            'default_value' => isset($options->style) ? $options->style : 'text-align: center;  margin:0px 0px 0px 0px; padding: 10px 10px 20px 10px;',
        );

        $globalpreduction = isset($group->reduction) ? $group->reduction : number_format(10, 2);
        $this->fields_form['input'][] = array(
            'type' => 'text',
            'label' => $this->l('Global discount'),
            'name' => 'discount',
            'id' => 'globaldiscount',
            'suffix' => '%',
            'class' => 'input fixed-width-sm',
            'tab' => 'discount',
            'default_value' => $globalpreduction
        );

        $categories = Category::getSimpleCategories(Context::getContext()->language->id);
        if ($obj->id) {
            $groupreductions = GroupReductionCore::getGroupReductions($obj->id_group, Context::getContext()->language->id);
        }

        foreach ($categories as $category) {
            if (Category::getTopCategory()->id == $category['id_category']) {
                continue;
            }
            $default_value = '';
            if (isset($groupreductions)) {
                foreach ($groupreductions as $groupreduction) {
                    if ($category['id_category'] == $groupreduction['id_category']) {
                        $default_value = $groupreduction['reduction'];
                    }
                }
            }
            $this->fields_form['input'][] = array(
                'type' => 'text',
                'label' => $category['name'],
                'name' => 'category[' . $category ['id_category'] . ']',
                'suffix' => '%',
                'class' => 'input fixed-width-sm discountcategory',
                'tab' => 'discount',
                'placeholder' => $globalpreduction,
                'default_value' => (empty($default_value)) ? '' : number_format(( $default_value * 100), 2)
            );
        }


        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'tab' => 'display',
                'type' => 'shop',
                'label' => $this->l('Shop association:'),
                'name' => 'checkBoxShopAsso',
                'tab' => 'discount'
            );
        }


        $this->page_header_toolbar_btn['save'] = array(
            'href' => 'javascript:$("#' . $this->table . '_form button:submit").click();',
            'desc' => $this->l('Save')
        );
        $this->page_header_toolbar_btn['save-and-stay'] = array(
            'short' => 'SaveAndStay',
            'href' => 'javascript:$("#' . $this->table . '_form").attr("action", $("#' . $this->table . '_form").attr("action")+"&submitStay");$("#' . $this->table . '_form button:submit").click();',
            'desc' => $this->l('Save and stay'),
            'force_desc' => true,
        );

        $this->page_header_toolbar_btn['edit'] = array(
            'href' => self::$currentIndex . '&token=' . $this->token,
            'desc' => $this->l('Return'),
            'icon' => 'process-icon-cancel'
        );


        return parent::

            renderForm();
    }

    public function renderList()
    {

        $this->fields_list = array(
            'id_discountcountdown' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25,
                'orderby' => false,
                'search' => false,
            ),
            'id_group' => array(
                'title' => $this->l('Discount'),
                'type' => 'text',
                'orderby' => false,
                'search' => false,
                'callback' => 'getCustomerGroup'
            ),
            'date_to' => array(
                'title' => $this->l('Active until'),
                'type' => 'text',
                'orderby' => false,
                'search' => false,
            ),
            'active' => array(
                'title' => $this->l('Active'),
                'active' => 'status',
                'type' => 'bool',
                'orderby' => false,
                'search' => false
            )
        );


        $this->page_header_toolbar_btn['new'] = array(
            'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
            'desc' => $this->l('Add new'),
            'icon' => 'process-icon-new'
        );

        return parent::

            renderList();
    }

    public function getCustomerGroup($echo)
    {
        $group = new Group($echo, $this->context->language->id);
        return $group->reduction . '%';
    }
}
