<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Optimization;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Command class.
 * Delete bundle file from all themes and languages in static directory.
 * @api
 * @since 3.0.0
 */
class DeleteCheckoutBundles
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\View\Asset\Minification
     */
    private $minification;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Minification $minification
    ) {
        $this->filesystem = $filesystem;
        $this->minification = $minification;
    }

    /**
     * Delete all bundle files created by amasty checkout.
     *
     * @return void
     */
    public function execute()
    {
        $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if (!$mediaDir->isDirectory(Bundle::ROOT_BUNDLE_JS_DIR . '/frontend')) {
            return;
        }
        foreach ($mediaDir->read(Bundle::ROOT_BUNDLE_JS_DIR . '/frontend') as $vendorDir) {
            if (!$mediaDir->isDirectory($vendorDir)) {
                continue;
            }
            foreach ($mediaDir->read($vendorDir) as $themeDir) {
                if (!$mediaDir->isDirectory($themeDir)) {
                    continue;
                }
                foreach ($mediaDir->read($themeDir) as $languageDir) {
                    if (!$mediaDir->isDirectory($languageDir)) {
                        continue;
                    }

                    $bundleFile = $languageDir . '/' . Bundle::BUNDLE_JS_DIR . '/' . Bundle::BUNDLE_SUB_DIR . '/'
                        . Bundle::BUNDLE_JS_FILE;
                    $fileToDelete = $this->minification->addMinifiedSign($bundleFile);

                    if ($bundleFile !== $fileToDelete) {
                        $mediaDir->delete($fileToDelete);
                    }

                    $mediaDir->delete($bundleFile);
                }
            }
        }
    }
}
