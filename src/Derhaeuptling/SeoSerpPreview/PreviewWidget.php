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

use Contao\Config;
use Derhaeuptling\SeoSerpPreview\Engine\EngineInterface;

class PreviewWidget extends \Widget
{
    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = false;

    /**
     * Add a for attribute
     * @var boolean
     */
    protected $blnForAttribute = false;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_seo_serp_preview';

    /**
     * Engine
     * @var EngineInterface
     */
    protected $engine;

    /**
     * Validate the engine
     *
     * @param array|null $attributes
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $attributes = null)
    {
        if (!isset($attributes['engine'])) {
            throw new \InvalidArgumentException('The engine is not specified');
        }

        $this->engine = new $attributes['engine']();

        if (!($this->engine instanceof EngineInterface)) {
            throw new \InvalidArgumentException(sprintf(
                'Engine "%s" must be an instance of Derhaeuptling\SeoSerpPreview\Engine\EngineInterface',
                $attributes['engine']
            ));
        }

        unset($attributes['engine']); // do not override $this->engine

        parent::__construct($attributes);
    }

    /**
     * Get the URL path from engine
     *
     * @return string
     */
    public function getUrlPath()
    {
        return $this->engine->getUrlPath($this->objDca->activeRecord->id);
    }

    /**
     * Get the page title
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->engine->getPageTitle($this->objDca->activeRecord->id);
    }

    /**
     * Get the URL suffix
     *
     * @return string
     */
    public function getUrlSuffix()
    {
        return Config::get('urlSuffix');
    }

    /**
     * Get the JavaScript engine name
     *
     * @return string
     */
    public function getJavaScriptEngineName()
    {
        return $this->engine->getJavaScriptEngineName();
    }

    /**
     * Get the JavaScript engine source
     *
     * @return string
     */
    public function getJavaScriptEngineSource()
    {
        return $this->engine->getJavaScriptEngineSource();
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        return '';
    }
}
