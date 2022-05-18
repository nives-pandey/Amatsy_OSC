<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Amasty\Checkout\Model\Optimization;

use Magento\Deploy\Package\BundleInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset\Minification;

class Bundle implements BundleInterface
{
    /**
     * Root of path relative to package directory where bundle files should be created
     */
    const ROOT_BUNDLE_JS_DIR = 'amasty/bundle';

    /**
     * Additional path directory where bundle file should be created
     */
    const BUNDLE_SUB_DIR = 'checkout';

    /**
     * Bundle file name. If minification enabled, then minified sign will be added to the name.
     */
    const BUNDLE_JS_FILE = 'bundle.js';

    /**
     * Identify all JS multiline comments
     */
    const REGEXP_JS_COMMENT = '!^[ \t]*/\*.*?\*/[ \t]*[\r\n]!s';

    /**
     * Identify all html comments which doesn't contains " ko " or " /ko "
     */
    const REGEXP_HTML_COMMENT = '/<!--(?!\s*ko\s|\s*\/ko)[^>]*-->/';

    const POOL_NAME_JS = 'jsbuild';
    const POOL_NAME_HTML = 'text';

    /**
     * Helper class for static files minification related processes
     *
     * @var Minification
     */
    private $minification;

    /**
     * @var Filesystem\Directory\ReadInterface
     */
    private $rootDir;

    /**
     * Static content directory writable interface
     *
     * @var ReadInterface
     */
    private $staticDir;

    /**
     * Media content directory writable interface
     *
     * @var WriteInterface
     */
    private $mediaDir;

    /**
     * Package area
     *
     * @var string
     */
    private $area;

    /**
     * Package theme
     *
     * @var string
     */
    private $theme;

    /**
     * Package locale
     *
     * @var string
     */
    private $locale;

    /**
     * Bundle content pools
     *
     * @var string[]
     */
    private $contentPools = [
        'js' => self::POOL_NAME_JS,
        'html' => self::POOL_NAME_HTML
    ];

    /**
     * Files to be bundled
     *
     * @var array[]
     */
    private $files = [
        self::POOL_NAME_JS => [],
        self::POOL_NAME_HTML => []
    ];

    /**
     * Files content cache
     *
     * @var string[]
     */
    private $fileContent = [];

    /**
     * Relative path to directory where bundle files should be created
     *
     * @var string
     */
    private $pathToBundleDir;

    /**
     * @var \Magento\Framework\Code\Minifier\Adapter\Js\JShrink
     */
    private $JShrink;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var array
     */
    private $excludeFiles = [
        \Amasty\Checkout\Model\Optimization\BundleService::COLLECT_SCRIPT_PATH
    ];

    public function __construct(
        Filesystem $filesystem,
        Minification $minification,
        \Magento\Framework\Code\Minifier\Adapter\Js\JShrink $JShrink,
        string $area,
        string $theme,
        string $locale,
        array $contentPools = [],
        array $excludeFiles = []
    ) {
        $this->filesystem = $filesystem;
        $this->minification = $minification;
        $this->staticDir = $filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $this->mediaDir = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->rootDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->JShrink = $JShrink;
        $this->area = $area;
        $this->theme = $theme;
        $this->locale = $locale;
        $this->contentPools = array_merge($this->contentPools, $contentPools);
        $this->excludeFiles = array_merge($this->excludeFiles, $excludeFiles);
        $this->pathToBundleDir = static::ROOT_BUNDLE_JS_DIR . '/'
            . $this->area . '/' . $this->theme . '/' . $this->locale . '/' . static::BUNDLE_JS_DIR . '/'
            . static::BUNDLE_SUB_DIR ;
    }

    /**
     * Add file that can be bundled
     *
     * @param string $filePath
     * @param string $sourcePath
     * @param string $contentType
     * @return bool true on success
     */
    public function addFile($filePath, $sourcePath, $contentType)
    {
        foreach ($this->excludeFiles as $excludeName) {
            if (strpos($filePath, $excludeName) !== false) {
                return false;
            }
        }
        // all unknown content types designated to "text" pool
        $contentPoolName = isset($this->contentPools[$contentType]) ? $this->contentPools[$contentType] : 'text';

        $this->files[$contentPoolName][$filePath] = $sourcePath;

        return true;
    }

    /**
     * Flushes all files added to appropriate bundle
     *
     * @return bool true on success
     */
    public function flush()
    {
        $bundleFile = $this->startNewBundleFile();

        foreach ($this->files as $contentPoolName => $files) {
            if (empty($files)) {
                continue;
            }

            $content = [];
            $bundleFile->write("        \"{$contentPoolName}\":");
            foreach ($files as $filePath => $sourcePath) {
                try {
                    $fileContent = $this->getFileContent($sourcePath, $contentPoolName);
                } catch (\Exception $e) {
                    continue;
                }

                $content[$this->minification->addMinifiedSign($filePath)] = $fileContent;
            }

            $content = json_encode($content, JSON_UNESCAPED_SLASHES);

            $bundleFile->write("{$content},\n");
        }

        if ($bundleFile) {
            $bundleFile->write("}});\n");
            $bundleFile->write($this->getInitJs());
        }

        $this->files = [];

        return true;
    }

    /**
     * Delete folder with merged checkout js
     *
     * @return bool true on success
     */
    public function clear()
    {
        return $this->mediaDir->delete($this->pathToBundleDir);
    }

    /**
     * @return bool
     */
    public function isBundleExist()
    {
        return $this->mediaDir->isExist($this->getFileName());
    }

    /**
     * Create new bundle file and write beginning content to it
     *
     * @param string $contentPoolName
     *
     * @return WriteInterface
     */
    private function startNewBundleFile()
    {
        $bundleFile = $this->mediaDir->openFile(
            $this->getFileName()
        );
        $bundleFile->write("require.config({\"config\": {\n");

        return $bundleFile;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->minification->addMinifiedSign($this->pathToBundleDir . '/' . self::BUNDLE_JS_FILE);
    }

    /**
     * Get content of static file
     *
     * @param string $sourcePath
     * @param string $contentPoolName
     *
     * @return string
     */
    private function getFileContent($sourcePath, $contentPoolName)
    {
        if (!isset($this->fileContent[$sourcePath])) {
            $sourcePath = $this->minification->addMinifiedSign($sourcePath);
            if ($this->minification->isMinifiedFilename($sourcePath) && !$this->staticDir->isExist($sourcePath)) {
                $sourcePath = $this->minification->removeMinifiedSign($sourcePath);
            }

            $content = $this->staticDir->readFile($sourcePath);

            if (mb_detect_encoding($content) !== "UTF-8") {
                $content = mb_convert_encoding($content, "UTF-8");
            }

            $content = $this->minifyContent($content, $contentPoolName);
            $this->fileContent[$sourcePath] = $content;
        }

        return $this->fileContent[$sourcePath];
    }

    /**
     * @param string $content
     * @param string $contentPoolName
     *
     * @return string
     */
    private function minifyContent($content, $contentPoolName)
    {
        if ($contentPoolName == static::POOL_NAME_JS) {
            //remove js comments (shrink don't removing them)
            $content = preg_replace(static::REGEXP_JS_COMMENT, '', $content);
            if (!$this->minification->isEnabled('js')) {
                //force minify if minification disabled
                $content = $this->JShrink->minify($content);
            }
        } else {
            $content =  preg_replace(
                '#(?ix)(?>[^\S ]\s*|\s{2,})(?=(?:(?:[^<]++|<(?!/?(?:textarea|pre|script)\b))*+)'
                . '(?:<(?>textarea|pre|script)\b|\z))#', //remove break lines
                ' ',
                preg_replace(
                    '#(?<!]]>)\s+</#', //remove whitespace before closing tag
                    '</',
                    preg_replace(static::REGEXP_HTML_COMMENT, '', $content) //remove html comments
                )
            );
        }

        return $content;
    }

    /**
     * Bundle initialization script content (this must be added to the latest bundle file at the very end)
     *
     * @return string
     */
    private function getInitJs()
    {
        return "require.config({\n" .
            "    bundles: {\n" .
            "        'mage/requirejs/static': [\n" .
            "            'jsbuild',\n" .
            "            'buildTools',\n" .
            "            'text',\n" .
            "            'statistician'\n" .
            "        ]\n" .
            "    },\n" .
            "    deps: [\n" .
            "        'jsbuild'\n" .
            "    ]\n" .
            "});\n";
    }
}
