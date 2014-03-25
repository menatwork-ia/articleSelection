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
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getArticle'][] = array('ArticleSelection', 'getArticleWithPermission');