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

use Derhaeuptling\SeoSerpPreview\Test\TestInterface;

class TestsManager
{
    /**
     * Tests
     * @var array
     */
    protected static $tests = [];

    /**
     * Add the test
     *
     * @param string $key
     * @param string $class
     */
    public static function add($key, $class)
    {
        static::$tests[$key] = $class;
    }

    /**
     * Remove the test
     *
     * @param string $key
     */
    public static function remove($key)
    {
        if (!isset(static::$tests[$key])) {
            throw new \InvalidArgumentException(sprintf('The test "%s" has been not found', $key));
        }

        unset(static::$tests[$key]);
    }

    /**
     * Get all tests
     *
     * @return array
     */
    public static function getAll()
    {
        $tests = [];
        $types = array_keys(static::$tests);

        foreach ($types as $key) {
            $tests[] = static::get($key);
        }

        return $tests;
    }

    /**
     * Get the test
     *
     * @param string $key
     *
     * @return TestInterface
     *
     * @throws \InvalidArgumentException
     */
    public static function get($key)
    {
        if (!isset(static::$tests[$key])) {
            throw new \InvalidArgumentException(sprintf('The test "%s" has been not found', $key));
        }

        $class = static::$tests[$key];

        return new $class();
    }
}
