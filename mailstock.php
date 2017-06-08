<?php

if (!defined('_PS_VERSION_'))
    exit;

class MailStock extends Module
{
    public function __construct($name = null, Context $context = null)
    {
        $this->name = 'mailstock';
        $this->tab = 'others';
        $this->version = '1.0';
        $this->author = 'Vivien Marnier';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct($name, $context);
        $this->displayName = $this->l('MailStock');
        $this->description = $this->l('A simple module to send emails when a product stock is updated.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall MailStock ?');

        if (!Configuration::get('MYMODULE_NAME'))
            $this->warning = $this->l('No name provided');
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() ||
            !$this->registerHook('actionUpdateQuantity') ||
            !Configuration::updateValue('MYMODULE_NAME', 'MailStock')
        )
            return false;

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('MYMODULE_NAME')
        )
            return false;

        return true;
    }
    public function hookActionUpdateQuantity($params)
    {
        if((isset($params['id_product'])) && (isset($params['quantity'])))
        {
            $this->sendMail($params['id_product'],$params['quantity']);
        }
    }

    /**
     * @param $productId
     * @param $quantity
     * setup and send an email to a specific recipient which notify the new quantity of specific product
     */
    private function sendMail($productId,$quantity)
    {
        $mailSubject = 'Product quantity updated';
        $productName = $this->getProductName($productId);
        $template = "product_quantity_updated";
        $recipient = "admin@prestashop.com";
        $data = [
            'quantity' => $quantity,
            'productName' =>$productName,
        ];

        Mail::Send($this->context->language->id,$template,$mailSubject,$data,$recipient,null,null,null,null,null,'mails/');
    }
    /**
     * @param $productId
     * @return string
     * get the product name from the product id
     */
    private function getProductName($productId)
    {
        $product = new Product($productId, false, $this->context->language->id);
        return $product->name;
    }
}
