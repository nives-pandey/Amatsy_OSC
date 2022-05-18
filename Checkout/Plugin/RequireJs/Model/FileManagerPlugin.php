<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Plugin\RequireJs\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State as AppState;
use Magento\Framework\RequireJs\Config;

class FileManagerPlugin
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Amasty\Checkout\Model\Optimization\BundleService
     */
    private $bundleService;

    /**
     * @var \Magento\Framework\View\Asset\Minification
     */
    private $minification;

    public function __construct(
        \Magento\Framework\Filesystem $appFilesystem,
        AppState $appState,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Amasty\Checkout\Model\Optimization\BundleService $bundleService,
        \Magento\Framework\View\Asset\Minification $minification
    ) {
        $this->filesystem = $appFilesystem;
        $this->appState = $appState;
        $this->assetRepo = $assetRepo;
        $this->bundleService = $bundleService;
        $this->minification = $minification;
    }

    /**
     * Replace bundle functionality on checkout page.
     *
     * @param \Magento\RequireJs\Model\FileManager $subject
     * @param callable $proceed
     *
     * @return \Magento\Framework\View\Asset\File[]
     */
    public function aroundCreateBundleJsPool(\Magento\RequireJs\Model\FileManager $subject, callable $proceed)
    {
        if (!$this->canLoadCheckoutBundle()) {
            return $proceed();
        }

        $bundles = [];
        $mediaDir = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        /** @var $context \Magento\Framework\View\Asset\File\FallbackContext */
        $context = $this->assetRepo->getStaticViewFileContext();

        $bundleFile = \Amasty\Checkout\Model\Optimization\Bundle::ROOT_BUNDLE_JS_DIR . '/'
            . $context->getPath() . '/'
            . \Amasty\Checkout\Model\Optimization\Bundle::BUNDLE_JS_DIR . '/'
            . \Amasty\Checkout\Model\Optimization\Bundle::BUNDLE_SUB_DIR . '/'
            . \Amasty\Checkout\Model\Optimization\Bundle::BUNDLE_JS_FILE;

        $bundleFile = $this->minification->addMinifiedSign($bundleFile);

        if (!$mediaDir->isExist($bundleFile) || !$mediaDir->isFile($bundleFile)) {
            return [];
        }

        $this->bundleService->setBundleLoaded();

        $relPath = $mediaDir->getRelativePath($bundleFile);
        $bundles[] = $this->assetRepo->createArbitrary(
            $relPath,
            '',
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );

        return $bundles;
    }

    /**
     * By default, Magento can load bundle only in Production mode
     *
     * @param \Magento\RequireJs\Model\FileManager $subject
     * @param \Magento\Framework\View\Asset\File|false $result
     *
     * @return \Magento\Framework\View\Asset\File
     */
    public function afterCreateStaticJsAsset(\Magento\RequireJs\Model\FileManager $subject, $result)
    {
        if (!$result && $this->canLoadCheckoutBundle()) {
            $result = $this->assetRepo->createAsset(Config::STATIC_FILE_NAME);
        }

        return $result;
    }

    private function canLoadCheckoutBundle()
    {
        return $this->bundleService->canLoadBundle();
    }
}
