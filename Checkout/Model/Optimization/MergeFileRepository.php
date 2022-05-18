<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Optimization;

use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

class MergeFileRepository implements \Amasty\Checkout\Api\MergeJsInterface
{
    /**
     * @var BundleFactory
     */
    private $bundleFactory;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * @var ThemeProviderInterface
     */
    private $themeProvider;

    public function __construct(
        BundleFactory $bundleFactory,
        Resolver $localeResolver,
        ThemeProviderInterface $themeProvider
    ) {
        $this->bundleFactory = $bundleFactory;
        $this->localeResolver = $localeResolver;
        $this->themeProvider = $themeProvider;
    }

    /**
     * @param string[] $fileNames ['/frontend/Magento/luma/en_US/mage/template.js']
     * @return boolean
     */
    public function createBundle(array $fileNames) : bool
    {
        $locale = $this->localeResolver->getLocale();

        $theme = $this->getThemePath(current($fileNames));

        if (!$theme) {
            return false;
        }

        /** @var \Amasty\Checkout\Model\Optimization\Bundle $bundleBuilder */
        $bundleBuilder = $this->bundleFactory->create(
            [
                'area' => 'frontend',
                'theme' => $theme,
                'locale' => $locale
            ]
        );

        if ($bundleBuilder->isBundleExist()) {
            return false;
        }

        $pathPrefix =  'frontend/'
            . $theme . '/'
            . $locale . '/';

        foreach ($this->parseFileNames($fileNames) as $fileProperty) {
            $bundleBuilder->addFile($fileProperty['name'], $pathPrefix . $fileProperty['name'], $fileProperty['type']);
        }

        $bundleBuilder->flush();

        return true;
    }

    /**
     * @param array $fileNames
     *
     * @return array
     */
    public function parseFileNames(array $fileNames)
    {
        $filesProperties = [];
        foreach ($fileNames as $fileUrl) {
            if (preg_match('/.*?(?>frontend|base)\/[^\/]+\/[^\/]+\/[^\/]+\/(.*)$/i', $fileUrl, $matches)) {
                $filesProperties[] = [
                    'name' => $matches[1],
                    'type' =>  substr($matches[1], -2, 2) === 'js' ? 'js' : 'text'
                ];
            }
        }

        return $filesProperties;
    }

    /**
     * @param string $fileUrl
     *
     * @return bool|string
     */
    private function extractTheme(string $fileUrl)
    {
        if (preg_match('/.*?(?>frontend|base)\/([^\/]+\/[^\/]+)\/.*$/i', $fileUrl, $matches)) {
            return $matches[1];
        }

        return false;
    }

    /**
     * @param string $fileUrl
     * @return bool|string
     */
    private function getThemePath(string $fileUrl)
    {
        $themePath = $this->extractTheme($fileUrl);
        $theme = $this->themeProvider->getThemeByFullPath('frontend/' . $themePath);

        return $theme->getId() ? $theme->getThemePath() : false;
    }
}
