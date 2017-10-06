<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaView UNA Studio Representation classes
 * @ingroup     UnaStudio
 * @{
 */

class BxBaseStudioPage extends BxDolStudioPage
{
    function __construct($mixedPageName)
    {
        parent::__construct($mixedPageName);
    }

    public function getPageIndex()
    {
        if(!is_array($this->aPage) || empty($this->aPage))
            return BX_PAGE_DEFAULT;

        if(!$this->bPageMultiple)
            return !empty($this->aPage['index']) ? (int)$this->aPage['index'] : BX_PAGE_DEFAULT;
        else
            return !empty($this->aPage[$this->sPageSelected]['index']) ? (int)$this->aPage[$this->sPageSelected]['index'] : BX_PAGE_DEFAULT;
    }

    public function getPageJs()
    {
        return array('jquery.anim.js', 'jquery.jfeed.pack.js', 'jquery.dolRSSFeed.js', 'page.js');
    }

    public function getPageJsClass()
    {
        return '';
    }

    public function getPageJsObject()
    {
        return '';
    }

    public function getPageJsCode($aOptions = array(), $bWrap = true)
    {
        $sJsClass = $this->getPageJsClass();
        $sJsObject = $this->getPageJsObject();
        if(empty($sJsClass) || empty($sJsObject))
            return '';

        $sOptions = '{}';
        if(!empty($aOptions))
            $sOptions = json_encode($aOptions);

        $sContent = 'var ' . $sJsObject . ' = new ' . $sJsClass . '(' . $sOptions . ');';
        if($bWrap)
            $sContent = BxDolStudioTemplate::getInstance()->_wrapInTagJsCode($sContent);

        return $sContent;
    }

    public function getPageCss()
    {
        $aCss = array('page.css', 'page-media-tablet.css', 'page-media-desktop.css', 'menu_top.css');
        if((int)$this->aPage['index'] == 3)
            $aCss[] = 'page_columns.css';

        return $aCss;
    }

    public function getPageHeader()
    {
        if(empty($this->aPage) || !is_array($this->aPage))
            return '';

        return _t(!$this->bPageMultiple ? $this->aPage['caption'] : $this->aPage[$this->sPageSelected]['caption']);
    }

    public function getPageBreadcrumb()
    {
        return array();
    }

    public function getPageCaption()
    {
        if(empty($this->aPage) || !is_array($this->aPage))
            return '';

        $oTemplate = BxDolStudioTemplate::getInstance();
        $oFunctions = BxTemplStudioFunctions::getInstance();

        $sHelp = $this->getPageCaptionHelp();
        if(($bHelp = strlen($sHelp)) > 0)
            $sHelp = $oFunctions->transBox('bx-std-pcap-menu-popup-help', $sHelp, true);

        $sActions = $this->getPageCaptionActions();
        if(($bActions = strlen($sActions)) > 0)
            $sActions = $oFunctions->transBox('bx-std-pcap-menu-popup-actions', $sActions, true);

        $oTemplate->addInjection('injection_header', 'text', $sHelp . $sActions);

        //--- Menu Left ---//
        $aItems = array(
            'back' => array(
                'name' => 'back',
                'icon' => 'th',
                'link' => BX_DOL_URL_STUDIO . 'launcher.php',
                'title' => '_adm_txt_back_to_launcher'
            )
        );

        $oMenu = new BxTemplStudioMenu(array('template' => 'menu_top_toolbar.html', 'menu_items' => $aItems));
        $sMenuLeft = $oMenu->getCode();

        //--- Menu Right ---//
        $aItems = array();

        if($bActions)
            $aItems['actions'] = array(
                'name' => 'actions',
                'icon' => 'cog',
                'onclick' => BX_DOL_STUDIO_PAGE_JS_OBJECT . ".togglePopup('actions', this)",
                'title' => '_adm_txt_show_help'
            );

        if($bHelp)
            $aItems['help'] = array(
                'name' => 'help',
                'icon' => 'question-circle',
                'onclick' => BX_DOL_STUDIO_PAGE_JS_OBJECT . ".togglePopup('help', this)",
                'title' => '_adm_txt_show_help'
            );

        $aItems = array_merge($aItems, array(
        	'home' => array(
                'name' => 'home',
                'icon' => 'home',
                'link' => BX_DOL_URL_ROOT,
                'onclick' => '',
                'title' => '_adm_tmi_cpt_site'
            ),
            'logout' => array(
                'name' => 'logout',
                'icon' => 'power-off',
                'link' => BX_DOL_URL_ROOT . 'logout.php',
                'onclick' => '',
                'title' => '_adm_tmi_cpt_logout'
            )
        ));

        $oMenu = new BxTemplStudioMenu(array('template' => 'menu_top_toolbar.html', 'menu_items' => $aItems));
        $sMenuRight = $oMenu->getCode();

        $aTmplVars = array(
            'menu_left' => $sMenuLeft,
            'caption' => _t($this->aPage['caption']),
            'menu_right' => $sMenuRight
        );
        return $oTemplate->parseHtmlByName('page_caption.html', $aTmplVars);
    }

    public function getPageAttributes()
    {
        return '';
    }

    public function getPageMenu($aMenu, $aMarkers = array())
    {
        $oMenu = new BxTemplStudioMenu(array('template' => 'menu_side.html', 'menu_items' => $aMenu));
        if(!empty($aMarkers))
            $oMenu->addMarkers($aMarkers);

        return $oMenu->getCode();
    }

    public function getPageCode($bHidden = false) {}

    protected function getPageCaptionHelp()
    {
    	$sContent = BxDolRss::getObjectInstance($this->sPageRssHelpObject)->getHolder($this->sPageRssHelpId, $this->iPageRssHelpLength, 0, false);

        $oTemplate = BxDolStudioTemplate::getInstance();
    	$oTemplate->addJsTranslation('_adm_txt_show_help_content_empty');
        return $oTemplate->parseHtmlByName('page_caption_help.html', array(
        	'content' => $sContent
        ));
    }

    protected function getPageCaptionActions()
    {
        if(empty($this->aActions))
            return "";

        $aForm = array(
            'form_attrs' => array(
                'id' => 'adm-page-actions',
                'name' => 'adm-page-actions',
                'action' => '',
                'method' => 'post',
            ),
            'params' => array(),
            'inputs' => array()
        );

        foreach($this->aActions as $aAction) {
            $aInput = array(
                'type' => $aAction['type'],
                'name' => $aAction['name'],
                'caption' => _t($aAction['caption'])
            );

            switch($aAction['type']) {
                case 'switcher':
                    $aInput['checked'] = $aAction['checked'];
                    $aInput['attrs']['onchange'] = $aAction['onchange'];
                    break;

            }

            $aForm['inputs'][$aInput['name']] = $aInput;
        }

        $oForm = new BxTemplStudioFormView($aForm);

        return BxDolStudioTemplate::getInstance()->parseHtmlByName('page_caption_actions.html', array('content' => $oForm->getCode()));
    }

    /**
     *
     * Block Methods
     *
     */
    public function getBlocksLine($aBlocks)
    {
        $aTmplVarsBlocks = array();
        foreach ($aBlocks as $aBlock) {
            $aTmplVarsBlocks[] = array(
                'content' => $this->getBlockCode($aBlock)
            ); 
        }

    	return BxDolStudioTemplate::getInstance()->parseHtmlByName('page_blocks_line.html', array(
    	    'count' => count($aTmplVarsBlocks),
    		'bx_repeat:blocks' => $aTmplVarsBlocks
    	));
    }
    public function getBlockCode($aBlock)
    {
    	return BxDolStudioTemplate::getInstance()->parseHtmlByName('page_block.html', array(
    		'caption' => $this->getBlockCaption($aBlock),
    		'panel_top' => $this->getBlockPanelTop($aBlock),
    		'items' => !empty($aBlock['items']) ? $aBlock['items'] : '',
    		'panel_bottom' => $this->getBlockPanelBottom($aBlock)
    	));
    }

    public function getBlockCaption($aBlock)
    {
        if(empty($aBlock) || !is_array($aBlock) || (empty($aBlock['caption']) && empty($aBlock['actions'])))
            return '';

        $aTmplActions = array();
        if(!empty($aBlock['actions']) && is_array($aBlock['actions']))
            foreach($aBlock['actions'] as $aAction) {
                $sCaption = is_array($aAction['caption']) ? call_user_func_array('_t', $aAction['caption']) : _t($aAction['caption']);

                $bOnClick = !empty($aAction['onclick']);
                $aOnClick = $bOnClick ? array('onclick' => $aAction['onclick']) : array();

                $aTmplActions[] = array(
                    'name' => $aAction['name'],
                    'url' => $aAction['url'],
                    'title' => $sCaption,
                    'bx_if:show_onclick' => array(
                        'condition' => $bOnClick,
                        'content' => $aOnClick
                    ),
                    'caption' => $sCaption
                );
            }

        return BxDolStudioTemplate::getInstance()->parseHtmlByName('block_caption.html', array(
            'caption' => is_array($aBlock['caption']) ? call_user_func_array('_t', $aBlock['caption']) : _t($aBlock['caption']),
            'bx_if:show_actions' => array(
                'condition' => !empty($aTmplActions),
                'content' => array(
                    'bx_repeat:actions' => $aTmplActions
                )
            ),
        ));
    }

    public function getBlockPanelTop($aBlock)
    {
        if(empty($aBlock) || !is_array($aBlock) || empty($aBlock['panel_top']))
            return '';

        return BxDolStudioTemplate::getInstance()->parseHtmlByName('block_panel_top.html', array(
            'content' => $aBlock['panel_top']
        ));
    }

    public function getBlockPanelBottom($aBlock)
    {
        if(empty($aBlock) || !is_array($aBlock) || empty($aBlock['panel_bottom']))
            return '';

        return BxDolStudioTemplate::getInstance()->parseHtmlByName('block_panel_bottom.html', array(
            'content' => $aBlock['panel_bottom']
        ));
    }

    protected function getJsResult($sMessage, $bTranslate = true, $bRedirect = false, $sRedirect = '', $sOnResult = '')
    {
        $aResult = array();
        $aResult['message'] = $bTranslate ? _t($sMessage) : $sMessage;
        if($bRedirect)
            $aResult['redirect'] = $sRedirect != '' ? $sRedirect : BX_DOL_URL_STUDIO;

        if(!empty($sOnResult))
            $aResult['on_result'] = $sOnResult;

        $sResult = "window.parent." . BX_DOL_STUDIO_PAGE_JS_OBJECT . ".showMessage(" . json_encode($aResult) . ");";

        return BxDolStudioTemplate::getInstance()->_wrapInTagJsCode($sResult);
    }
}

/** @} */
