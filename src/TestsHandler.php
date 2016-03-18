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

use Contao\BackendTemplate;
use Contao\DataContainer;
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
        if ($dc === null || $dc->id) {
            return;
        }

        $this->addGlobalOperations();

        if ($this->isEnabled()) {
            $this->replaceLabelGenerator();
        }
    }

    /**
     * Return true if the tests are enabled
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return \Input::get('serp_tests') ? true : false;
    }

    /**
     * Limit the global operations
     */
    protected function addGlobalOperations()
    {
        $enabled = $this->isEnabled();

        array_insert($GLOBALS['TL_DCA']['tl_page']['list']['global_operations'], 0, [
            'serp_tests' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC'][$enabled ? 'seo_serp_tests.disable' : 'seo_serp_tests.enable'],
                'href'       => 'serp_tests=' . ($enabled ? '0' : '1'),
                'icon'       => 'system/modules/seo_serp_preview/assets/icons/tests.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ]
        ]);
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
        $GLOBALS['TL_CSS'][]        = 'system/modules/seo_serp_preview/assets/css/tests.min.css';
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
}
