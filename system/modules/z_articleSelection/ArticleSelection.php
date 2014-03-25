<?php

/**
 * Contao Open Source CMS
 *
 * @copyright  MEN AT WORK 2014
 * @package    articleSelection
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Class ArticleSelection
 */
class ArticleSelection extends Backend
{

    /**
     * Check if the article has permission for current configurations
     * 
     * @param DB_Mysql_Result $objRow
     * @param string $strBuffer
     * @return string $strBuffer
     */
    public function getArticleWithPermission(&$objRow)
    {
        if ($objRow->articleSelection == '' || TL_MODE == 'BE')
        {
            return;
        }

        $arrCs = deserialize($objRow->articleSelection);

        if (!is_array($arrCs))
        {
            return;
        }

        $objUa = $this->Environment->agent;

        $blnGlobalPermisson = false;
        foreach ($arrCs as $arrSelector)
        {
            $arrSelector['as_client_os'] = ($arrSelector['as_client_os'] != '') ? array(
                'value' => $arrSelector['as_client_os'],
                'config' => $GLOBALS['TL_CONFIG']['os'][$arrSelector['as_client_os']]
                    ) : false;
            $arrSelector['as_client_browser']   = ($arrSelector['as_client_browser'] != '') ? $GLOBALS['TL_CONFIG']['browser'][$arrSelector['as_client_browser']] : false;
            $arrSelector['as_client_is_mobile'] = (($arrSelector['as_client_is_mobile'] != '') ? (($arrSelector['as_client_is_mobile'] == 1) ? true : false) : 'empty');

            $blnPermisson = true;
            foreach ($arrSelector as $strConfig => $mixedConfig)
            {
                switch ($strConfig)
                {
                    case 'as_client_os':
                        $blnPermisson = ($blnPermisson && AgentSelection::checkOsPermission($mixedConfig, $objUa));
                        break;

                    case 'as_client_browser':
                        $blnPermisson = ($blnPermisson && ($mixedConfig['browser'] == $objUa->browser || $mixedConfig['browser'] == '')) ? true : false;
                        break;

                    case 'as_client_browser_version':
                        $blnPermisson = ($blnPermisson && AgentSelection::checkBrowserVerPermission($mixedConfig, $objUa, $arrSelector['as_client_browser_operation']));
                        break;

                    case 'as_client_is_mobile':
                        if (strlen($mixedConfig) < 2)
                        {
                            $blnPermisson = ($blnPermisson && $mixedConfig == $objUa->mobile) ? true : false;
                        }
                        break;

                    case 'as_client_is_invert':
                        if ($mixedConfig)
                        {
                            $blnPermisson = ($blnPermisson) ? false : true;
                        }
                        break;
                }
            }

            if (!$blnGlobalPermisson && $blnPermisson)
            {
                $blnGlobalPermisson = true;
            }
        }

        if ($blnGlobalPermisson === false)
        {
            $objRow->published = '';
            return;
        }
        else
        {
            return;
        }
    }

}