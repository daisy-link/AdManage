<?php

namespace Plugin\AdManage\Twig\Extension;

use Eccube\Application;

class AdManageExtension extends \Twig_Extension
{
    /** @var Application */
    protected $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ad_manage';
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('ad_manage_csrf_token_for_anchor', array($this, 'getCsrfTokenForAnchor'),
                array('is_safe' => array('all'))),
        );
    }

    /**
     * csrf_token_for_anchorを取得する。
     *
     * @return string
     */
    public function getCsrfTokenForAnchor()
    {
        /** @var \Eccube\Twig\Extension\EccubeExtension $extension */
        $extension = $this->app['twig']->getExtension('eccube');
        $result = method_exists($extension, 'getCsrfTokenForAnchor') ?
            $extension->getCsrfTokenForAnchor() :
            '';
        return $result;
    }
}