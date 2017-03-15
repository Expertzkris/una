<?php defined('BX_DOL') or die('hack attempt');
/**
 * Copyright (c) UNA, Inc - https://una.io
 * MIT License - https://opensource.org/licenses/MIT
 *
 * @defgroup    UnaCore UNA Core
 * @{
 */

require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

class BxDolGridSubscriptions extends BxTemplGrid
{
    protected $_bOwner = false;
    protected $_sObjectConnections = 'sys_profiles_subscriptions';
    protected $_oConnection;

    public function __construct ($aOptions, $oTemplate = false)
    {
        parent::__construct ($aOptions, $oTemplate);
        $this->_sDefaultSortingOrder = 'DESC';

        $this->_oConnection = BxDolConnection::getObjectInstance($this->_sObjectConnections);
        if (!$this->_oConnection)
            return;

        $iProfileId = bx_process_input(bx_get('profile_id'), BX_DATA_INT);
        if (!$iProfileId)
            return;

        $oProfile = BxDolProfile::getInstance($iProfileId);
        if (!$oProfile)
            return;

        if ($oProfile->id() == bx_get_logged_profile_id())
            $this->_bOwner = true;

        $aSQLParts = $this->_oConnection->getConnectedContentAsSQLParts('p', 'id', $oProfile->id());

        $this->addMarkers(array(
            'profile_id' => $oProfile->id(),
            'join_connections' => $aSQLParts['join']
        ));
    }

    protected function _delete ($mixedId)
    {
        list($iId, $iViewedId) = $this->_prepareIds();

        if(!$this->_oConnection->isConnected($iViewedId, $iId))
            return true;

        return $this->_oConnection->removeConnection($iViewedId, $iId);
    }

    protected function _getCellName ($mixedValue, $sKey, $aField, $aRow)
    {
        $oProfile = BxDolProfile::getInstance($aRow['id']);
        if (!$oProfile)
            return _t('_sys_txt_error_occured');

        return parent::_getCellDefault ($oProfile->getUnit(), $sKey, $aField, $aRow);
    }

    protected function _getActionDelete ($sType, $sKey, $a, $isSmall = false, $isDisabled = false, $aRow = array())
    {
        if (!isLogged() || !$this->_bOwner)
            return '';

        return parent::_getActionDefault ($sType, $sKey, $a, $isSmall, $isDisabled, $aRow);
    }

    protected function _prepareIds ()
    {
        $iViewedId = bx_get_logged_profile_id();

        $iId = 0;
        $aIds = bx_get('ids');
        if($aIds && is_array($aIds))
            $mixedId = array_pop($aIds);

        if(strpos($mixedId, ':') !== false) {
            list($iId, $iViewedId) = explode (':', $mixedId);

            $iId = (int)$iId;
            $iViewedId = (int)$iViewedId;
        }
        else 
            $iId = (int)$mixedId;

        return array($iId, $iViewedId);
    }
}

/** @} */
