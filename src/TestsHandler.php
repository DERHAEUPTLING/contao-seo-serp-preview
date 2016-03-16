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

namespace Derhaeuptling\SeoSerpPreview;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\DataContainer;
use Contao\Image;
use Contao\System;
use Derhaeuptling\SeoSerpPreview\Test\Exception\ErrorException;
use Derhaeuptling\SeoSerpPreview\Test\Exception\WarningException;
use Derhaeuptling\SeoSerpPreview\Test\TestInterface;

class TestsHandler
{
    /**
     * Initialize the tests handler
     *
     * @param DataContainer|null $dc
     */
    public function initialize(DataContainer $dc = null)
    {
        if ($dc->id !== null || \Input::get('act')) {
            Backend::redirect('contao/main.php?act=error');
        }

        $this->lockTable();
        $this->replaceLabelGenerator();
        $this->limitGlobalOperations();
        $this->limitRowOperations();
    }

    /**
     * Return the "edit" button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function editButton(array $row, $href, $label, $title, $icon, $attributes)
    {
        $url = str_replace('do=seo_serp_tests', 'do=page',
            Backend::addToUrl($href.'&amp;id='.$row['id'].'&amp;popup=1&amp;nb=1&amp;rt='.REQUEST_TOKEN));

        $attributes .= ' onclick="SeoSerpTests.openModalIframe({\'width\':768,\'title\':\''.specialchars(str_replace("'",
                "\\'",
                sprintf($GLOBALS['TL_LANG']['tl_page']['edit'][1], $row['id']))).'\',\'url\':this.href});return false"';

        return '<a href="'.$url.'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon,
            $label).'</a> ';
    }

    /**
     * Generate the label
     *
     * @param array         $row
     * @param string        $label
     * @param DataContainer $dc
     * @param string        $imageAttribute
     * @param boolean       $blnReturnImage
     * @param boolean       $blnProtected
     *
     * @return string
     */
    public function generateLabel(
        array $row,
        $label,
        DataContainer $dc = null,
        $imageAttribute = '',
        $blnReturnImage = false,
        $blnProtected = false
    ) {
        $default  = '';
        $callback = $GLOBALS['TL_DCA']['tl_page']['list']['label']['default_label_callback'];

        // Get the default label
        if (is_array($callback)) {
            $default = System::importStatic($callback[0])->{$callback[1]}(
                $row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected
            );
        } elseif (is_callable($callback)) {
            $default = $callback($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
        }

        $template = new BackendTemplate('be_seo_serp_tests');
        $template->setData($this->generateTests($row));
        $template->label = $default;

        // Add assets
        $GLOBALS['TL_CSS'][] = 'system/modules/seo_serp_preview/assets/css/tests.min.css';
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/seo_serp_preview/assets/js/tests.min.js';

        return $template->parse();
    }

    /**
     * Generate the tests for a record
     *
     * @param array $data
     *
     * @return string
     */
    protected function generateTests(array $data)
    {
        System::loadLanguageFile('seo_serp_tests');
        $result = ['errors' => [], 'warnings' => []];

        /** @var TestInterface $test */
        foreach (TestsManager::getAll() as $test) {
            try {
                $test->run($data);
            } catch (ErrorException $e) {
                $result['errors'][] = $e->getMessage();
            } catch (WarningException $e) {
                $result['warnings'][] = $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Lock the table so no records can be added
     */
    protected function lockTable()
    {
        $GLOBALS['TL_DCA']['tl_page']['config']['closed'] = true;
    }

    /**
     * Replace the default label generator
     */
    protected function replaceLabelGenerator()
    {
        // Preserve the default callback
        $GLOBALS['TL_DCA']['tl_page']['list']['label']['default_label_callback'] = $GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback'];

        $GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback'] = [
            'Derhaeuptling\SeoSerpPreview\TestsHandler',
            'generateLabel',
        ];
    }

    /**
     * Limit the global operations
     */
    protected function limitGlobalOperations()
    {
        $allowed = ['toggleNodes'];

        foreach ($GLOBALS['TL_DCA']['tl_page']['list']['global_operations'] as $k => $v) {
            if (!in_array($k, $allowed, true)) {
                unset($GLOBALS['TL_DCA']['tl_page']['list']['global_operations'][$k]);
            }
        }
    }

    /**
     * Limit the row operations
     */
    protected function limitRowOperations()
    {
        $allowed = ['edit'];

        // Remove the operations
        foreach ($GLOBALS['TL_DCA']['tl_page']['list']['operations'] as $k => $v) {
            if (!in_array($k, $allowed, true)) {
                unset($GLOBALS['TL_DCA']['tl_page']['list']['operations'][$k]);
            }
        }

        // Replace the default edit button
        $GLOBALS['TL_DCA']['tl_page']['list']['operations']['edit']['button_callback'] = [
            'Derhaeuptling\SeoSerpPreview\TestsHandler',
            'editButton',
        ];
    }
}
