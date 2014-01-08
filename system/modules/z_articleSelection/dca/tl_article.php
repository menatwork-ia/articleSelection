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
 * Lists 
 */
$GLOBALS['TL_DCA']['tl_article']['list']['label']['label_callback'] = array('tl_article_articleSelection', 'addIcon');

/**
 * Palettes
 */
foreach ($GLOBALS['TL_DCA']['tl_article']['palettes'] as $key => $row)
{
    if ($key == '__selector__')
    {
        continue;
    }

    $arrPalettes = explode(";", $row);    
    $arrPalettes[] = '{articleSelection_legend},articleSelection';

    $GLOBALS['TL_DCA']['tl_article']['palettes'][$key] = implode(";", $arrPalettes);
}

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_article']['fields']['articleSelection'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_article']['articleSelection'],
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'eval' => array
        (
        'columnFields' => array
            (
            'as_client_os' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_article']['as_client_os'],
                'exclude' => true,
                'inputType' => 'select',
                'options_callback' => array('AgentSelection', 'getClientOs'),
                'eval' => array(
                    'style' => 'width:158px',
                    'chosen' => true,
                    'includeBlankOption' => true
                )
            ),
            'as_client_browser' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_article']['as_client_browser'],
                'exclude' => true,
                'inputType' => 'select',
                'options_callback' => array('AgentSelection', 'getClientBrowser'),
                'eval' => array(
                    'style' => 'width:158px',
                    'chosen' => true,
                    'includeBlankOption' => true
                )
            ),
            'as_client_browser_operation' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_article']['as_client_browser_operation'],
                'inputType' => 'select',
                'options' => array(
                    'lt' => '<',
                    'lte' => '<=',
                    'gte' => '>=',
                    'gt' => '>'
                ),
                'eval' => array(
                    'style' => 'width:70px',
                    'chosen' => true,
                    'includeBlankOption' => true
                )
            ),
            'as_client_browser_version' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_article']['as_client_browser_version'],
                'inputType' => 'text',
                'eval' => array(
                    'style' => 'width:70px'
                )
            ),
            'as_client_is_mobile' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_article']['as_client_is_mobile'],
                'exclude' => true,
                'inputType' => 'select',
                'options' => array(
                    '1' => $GLOBALS['TL_LANG']['MSC']['yes'],
                    '2' => $GLOBALS['TL_LANG']['MSC']['no']
                ),
                'eval' => array(
                    'includeBlankOption' => true,
                    'style' => 'width:50px',
                    'chosen' => true
                )
            ),
            'as_client_is_invert' => array
                (
                'label' => &$GLOBALS['TL_LANG']['tl_article']['as_client_is_invert'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array(
                    'style' => 'min-width:30px'
                )
            )
        )
    )
);

/**
 * Class tl_article_articleSelection
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  MEN AT WORK 2014
 * @package    articleSelection
 * @license    GNU/GPL 2
 * @filesource
 */
class tl_article_articleSelection extends Controller
{

    /**
     * Return the new label for the article
     * 
     * @param array $arrRow
     * @param string $label 
     * @return string
     */
    public function addIcon($arrRow, $label)
    {
        $arrContentSelection = array();
        if ($arrRow['articleSelection'])
        {
            $arrCs = deserialize($arrRow['articleSelection']);
            if (is_array($arrCs))
            {
                $arrSelector = $arrCs[0];
                $strInvert   = (($arrSelector['as_client_is_invert']) ? ucfirst($GLOBALS['TL_LANG']['MSC']['hiddenHide']) : ucfirst($GLOBALS['TL_LANG']['MSC']['hiddenShow'])) . ':';
                foreach ($arrSelector as $strConfig => $mixedConfig)
                {
                    switch ($strConfig)
                    {
                        case 'as_client_os':
                            if ($mixedConfig)
                            {
                                $arrContentSelection[] = ' ' . $mixedConfig;
                            }
                            break;

                        case 'as_client_browser':
                            if ($mixedConfig)
                            {
                                $arrContentSelection[] = ' ' . $mixedConfig;
                            }
                            break;

                        case 'as_client_browser_version':
                            if ($mixedConfig)
                            {
                                switch ($arrSelector['as_client_browser_operation'])
                                {
                                    case 'lt':
                                        $strOperator           = '<';
                                        break;
                                    case 'lte':
                                        $strOperator           = '<=';
                                        break;
                                    case 'gte':
                                        $strOperator           = '>=';
                                        break;
                                    case 'gt':
                                        $strOperator           = '>';
                                        break;
                                    default:
                                        $strOperator           = '';
                                        break;
                                }
                                $arrContentSelection[] = ' ' . $strOperator . ' ' . $mixedConfig;
                            }
                            break;

                        case 'as_client_is_mobile':
                            if ($mixedConfig != '')
                            {
                                $arrContentSelection[] = ' ' . $GLOBALS['TL_LANG']['tl_article']['as_client_is_mobile'][0] . ': ' . (($mixedConfig == 1) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no']);
                            }
                            break;
                    }
                }

                if (count($arrContentSelection) > 0)
                {
                    array_unshift($arrContentSelection, $strInvert);
                    array_unshift($arrContentSelection, '(');
                    if (count($arrCs) > 1)
                    {
                        $arrContentSelection[] = ' /... ';
                    }
                    $arrContentSelection[] = ')';
                }
            }
        }
         $label = str_replace('</span>', implode('', $arrContentSelection).'</span>', $label);

        $time = time();
		$published = ($row['published'] && ($row['start'] == '' || $row['start'] < $time) && ($row['stop'] == '' || $row['stop'] > $time));

		return '<a href="contao/main.php?do=feRedirect&amp;page='.$row['pid'].'&amp;article='.(($row['alias'] != '' && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $row['alias'] : $row['id']).'" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['view']).'" target="_blank">'.Image::getHtml('articles'.($published ? '' : '_').'.gif').'</a> '.$label;
    }

}

?>