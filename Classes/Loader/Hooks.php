<?php

/**
 * Loading Hooks.
 */
declare(strict_types=1);

namespace HDNET\Autoloader\Loader;

use HDNET\Autoloader\Loader;
use HDNET\Autoloader\LoaderInterface;
use HDNET\Autoloader\Utility\ClassNamingUtility;
use HDNET\Autoloader\Utility\ExtendedUtility;
use HDNET\Autoloader\Utility\FileUtility;
use HDNET\Autoloader\Utility\ReflectionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Reflection\MethodReflection;

/**
 * Loading Hooks.
 */
class Hooks implements LoaderInterface
{
    /**
     * Get all the complex data for the loader.
     * This return value will be cached and stored in the database
     * There is no file monitoring for this cache.
     *
     * @param Loader $loader
     * @param int    $type
     *
     * @return array
     */
    public function prepareLoader(Loader $loader, int $type): array
    {
        $hooks = [];
        $folder = ExtensionManagementUtility::extPath($loader->getExtensionKey()) . 'Classes/Hooks/';
        $files = FileUtility::getBaseFilesInDir($folder, 'php');

        foreach ($files as $hookFile) {
            $hookClass = ClassNamingUtility::getFqnByPath(
                $loader->getVendorName(),
                $loader->getExtensionKey(),
                'Hooks/' . $hookFile
            );
            if (!$loader->isInstantiableClass($hookClass)) {
                continue;
            }

            $classReflection = ReflectionUtility::createReflectionClass($hookClass);

            // add class hook
            $tagConfiguration = ReflectionUtility::getTagConfiguration($classReflection, ['hook']);
            if (\count($tagConfiguration['hook'])) {
                $hooks[] = [
                    'locations' => $tagConfiguration['hook'],
                    'configuration' => $hookClass,
                ];
            }

            // add method hooks
            foreach ($classReflection->getMethods(MethodReflection::IS_PUBLIC) as $methodReflection) {
                /** @var $methodReflection \TYPO3\CMS\Extbase\Reflection\MethodReflection */
                $tagConfiguration = ReflectionUtility::getTagConfiguration($methodReflection, ['hook']);
                if (\count($tagConfiguration['hook']) > 0) {
                    $hookLocations = \array_map(function ($hook) {
                        return \trim($hook, " \t\n\r\0\x0B|");
                    }, $tagConfiguration['hook']);

                    $hooks[] = [
                        'locations' => $hookLocations,
                        'configuration' => $hookClass . '->' . $methodReflection->getName(),
                    ];
                }
            }
        }

        return $hooks;
    }

    /**
     * Run the loading process for the ext_tables.php file.
     *
     * @param Loader $loader
     * @param array  $loaderInformation
     */
    public function loadExtensionTables(Loader $loader, array $loaderInformation)
    {
    }

    /**
     * Run the loading process for the ext_localconf.php file.
     *
     * @param \HDNET\Autoloader\Loader $loader
     * @param array                    $loaderInformation
     *
     * @internal param \HDNET\Autoloader\Loader $autoLoader
     */
    public function loadExtensionConfiguration(Loader $loader, array $loaderInformation)
    {
        foreach ($loaderInformation as $hook) {
            ExtendedUtility::addHooks($hook['locations'], $hook['configuration']);
        }
    }
}
