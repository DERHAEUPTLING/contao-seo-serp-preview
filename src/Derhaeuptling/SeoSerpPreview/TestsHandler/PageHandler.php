<?php

/**
 * seo_serp_preview extension for Contao Open Source CMS
 *
 * Copyright (C) 2016 derhaeuptling
 *
 * @author  derhaeuptling <https://derhaeuptling.com>
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace Derhaeuptling\SeoSerpPreview\TestsHandler;

use Contao\Backend;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\Session;

class PageHandler extends AbstractHandler
{
    /**
     * Get the table name
     *
     * @return string
     */
    protected function getTableName()
    {
        return 'tl_page';
    }

    /**
     * Initialize the tests handler if enabled
     */
    protected function initializeEnabled()
    {
        parent::initializeEnabled();

        $this->autoExpandTree();
    }

    /**
     * Auto expand the tree
     *
     * @throws \Exception
     */
    public function autoExpandTree()
    {
        $session = Session::getInstance()->getData();

        if ($session['seo_serp_expand_tree'] !== 'tl_page' && !Input::get('serp_tests_expand')) {
            return;
        }

        $nodes = Database::getInstance()->execute("SELECT DISTINCT pid FROM tl_page WHERE pid>0");

        // Reset the array first
        $session['tl_page_tree'] = [];

        // Expand the tree
        while ($nodes->next()) {
            $session['tl_page_tree'][$nodes->pid] = 1;
        }

        // Avoid redirect loop
        $session['seo_serp_expand_tree'] = null;

        Session::getInstance()->setData($session);
        Backend::redirect(str_replace('serp_tests_expand=1', '', Environment::get('request')));
    }

    /**
     * Generate the message filter
     *
     * @return string
     */
    public function generateMessageFilter()
    {
        if (Input::post('FORM_SUBMIT') === 'tl_filters'
            && Input::post($this->filterName, true) !== 'tl_'.$this->filterName
        ) {
            $session                         = Session::getInstance()->getData();
            $session['seo_serp_expand_tree'] = 'tl_page';
            Session::getInstance()->setData($session);
        }

        return parent::generateMessageFilter();
    }

    /**
     * Get the applicable record IDs
     *
     * @return array
     */
    protected function getRecordIds()
    {
        return Database::getInstance()->getChildRecords(Session::getInstance()->get('tl_page_node'), 'tl_page');
    }

    /**
     * Get the global operation
     *
     * @param bool $enabled
     *
     * @return array
     */
    protected function getGlobalOperation($enabled)
    {
        $operation = parent::getGlobalOperation($enabled);

        if ($enabled) {
            $operation['href'] .= '&serp_tests_expand=1';
        }

        return $operation;
    }
}