<?php

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Console;

use Cake\Utility\Security;
use Composer\Script\Event;
use Exception;

/**
 * Provides installation hooks for when this application is installed via
 * composer. Customize this class to suit your needs.
 */
class Installer
{
    /**
     * @throws Exception
     */
    public static function setConfig(Event $event)
    {
        $io = $event->getIO();
        $args = $event->getArguments();
        $promptUser = true;
        $rootDir = dirname(dirname(__DIR__));
        $configFiles = static::getListOfAppConfigs($rootDir);
        $configToUse = -1;
        $optionsStr = '';
        $configIdxOptions = range(0, count($configFiles) - 1);

        if (empty($args)) {
            if (!$io->isInteractive()) {
                throw new Exception('Not in interactive mode, but no config specified');
            }
        } else {
            $specifiedConfig = $rootDir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $args[0];
            $configToUse = array_search($specifiedConfig, $configFiles);
            if ($configToUse == false && $configToUse != 0) {
                throw new Exception('Config arg ' . $args[0] . ' is invalid');
            } else {
                $promptUser = false;
            }
        }

        if ($io->isInteractive() && $configToUse < 0) {
            // create config options string to present to user
            if (empty($configFiles)) {
                throw new Exception('No config files found');
            }
            for ($i = 0; $i < count($configFiles); ++$i) {
                $optionsStr .= "  [" . $i . "] => " . $configFiles[$i] . "\n";
            }
            // present options to user
            $io->write($optionsStr);
            // validate user's selection
            $validator = function ($arg) {
                $rootDir = dirname(dirname(__DIR__));
                $configFiles = static::getListOfAppConfigs($rootDir);
                $configIdxOptions = range(0, count($configFiles) - 1);
                if (in_array((int)$arg, $configIdxOptions)) {
                    return $arg;
                }
                throw new Exception(
                    'This is not a valid answer. Please choose one of the '
                    . 'numeric options from 0 to ' . (count($configFiles) - 1)
                );
            };
            // ask user to select config
            $configToUse = $io->askAndValidate(
                '<info>Choose desired config (0 through ' . (count($configFiles) - 1) . '): </info>',
                $validator,
                3, // max attempts
                -1 // default answer if none given by user
            );
        }

        // Ensure configToUse is an integer in the valid range of options
        if (!preg_match("/^\d{1,2}$/", $configToUse) || !in_array($configToUse, $configIdxOptions)) {
            $io->write("'" . $configToUse . "' isn't a valid option chosen. Cancelling...");
            throw new Exception('No valid config selected');
        }

        static::setAppPhp($io, $rootDir, $configFiles[$configToUse], $promptUser);
    }

    /**
     */
    public static function getListOfAppConfigs($dir)
    {
        $appConfigsRegex = $dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.*.php';
        $appConfigFiles = glob($appConfigsRegex);
        return $appConfigFiles;
    }

    public static function setAppPhp($io, $dir, $config, $promptUser)
    {
        $appConfig = $dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
        $defaultConfig = $dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.default.php';
        if (!file_exists($config)) {
            $io->write($config . " doesn't exists");
            return;
        }
        // ask if the permissions should be changed
        if ($io->isInteractive() && $promptUser == true) {
            $validator = function ($arg) {
                if (in_array($arg, ['Y', 'y', 'N', 'n'])) {
                    return $arg;
                }
                throw new Exception('This is not a valid answer. Please choose Y or n.');
            };
            $setFolderPermissions = $io->askAndValidate(
                '<info>Copy ' . $config . ' to ' . $appConfig
                . "?\n  (Defaults to Y)</info> [<comment>Y,n</comment>]? ",
                $validator,
                10,
                'Y'
            );

            if (in_array($setFolderPermissions, ['Y', 'y'])) {
                copy($config, $appConfig);
                $io->write('Copied ' . $config . ' to ' . $appConfig . '.');
            } else {
                $io->write('Not copying files. Exiting...');
            }
        } else {
            copy($config, $appConfig);
            $io->write('Copied ' . $config . ' to ' . $appConfig . '.');
        }
    }

    /**
     * Does some routine installation tasks so people don't have to.
     *
     * @param \Composer\Script\Event $event The composer event object.
     * @return void
     * @throws \Exception Exception raised by validator.
     */
    public static function postInstall(Event $event)
    {
        $io = $event->getIO();

        $rootDir = dirname(dirname(__DIR__));

        static::createAppConfig($rootDir, $io);
        static::createWritableDirectories($rootDir, $io);

        // ask if the permissions should be changed
        if ($io->isInteractive()) {
            $validator = function ($arg) {
                if (in_array($arg, ['Y', 'y', 'N', 'n'])) {
                    return $arg;
                }
                throw new Exception('This is not a valid answer. Please choose Y or n.');
            };
            $setFolderPermissions = $io->askAndValidate(
                '<info>Set Folder Permissions on tmp and cache directories ? '
                . '(Default to Y)</info> [<comment>Y,n</comment>]? ',
                $validator,
                10,
                'Y'
            );

            if (in_array($setFolderPermissions, ['Y', 'y'])) {
                static::setFolderPermissions($rootDir, $io);
            }
        } else {
            static::setFolderPermissions($rootDir, $io);
        }

        static::setSecuritySalt($rootDir, $io);

        if (class_exists('\Cake\Codeception\Console\Installer')) {
            \Cake\Codeception\Console\Installer::customizeCodeceptionBinary($event);
        }
    }

    /**
     * Create the config/app.php file if it does not exist.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function createAppConfig($dir, $io)
    {
        $appConfig = $dir . '/config/app.php';
        $defaultConfig = $dir . '/config/app.default.php';
        if (!file_exists($appConfig)) {
            copy($defaultConfig, $appConfig);
            $io->write('Created `config/app.php` file');
        }
    }

    /**
     * Create the `logs` and `tmp` directories.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function createWritableDirectories($dir, $io)
    {
        $paths = [
            'logs',
            'tmp',
            'tmp/cache',
            'tmp/cache/models',
            'tmp/cache/persistent',
            'tmp/cache/views',
            'tmp/sessions',
            'tmp/tests'
        ];

        foreach ($paths as $path) {
            $path = $dir . '/' . $path;
            if (!file_exists($path)) {
                mkdir($path);
                $io->write('Created `' . $path . '` directory');
            }
        }
    }

    /**
     * Set globally writable permissions on the "tmp" and "logs" directory.
     *
     * This is not the most secure default, but it gets people up and running quickly.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function setFolderPermissions($dir, $io)
    {
        // Change the permissions on a path and output the results.
        $changePerms = function ($path, $perms, $io) {
            // Get permission bits from stat(2) result.
            $currentPerms = fileperms($path) & 0777;
            if (($currentPerms & $perms) == $perms) {
                $io->write('Permissions on ' . $path . ' are already world writable ('
                    . decoct($perms) . '). Doing nothing.');
                return;
            }

            $res = chmod($path, $currentPerms | $perms);
            if ($res) {
                $io->write('Permissions set to ' . decoct($currentPerms) . ' on ' . $path);
            } else {
                $io->write('Failed to set permissions to ' . decoct($currentPerms) . '  on ' . $path);
            }
        };

        $walker = function ($dir, $perms, $io) use (&$walker, $changePerms) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir . '/' . $file;

                if (!is_dir($path)) {
                    continue;
                }

                $changePerms($path, $perms, $io);
                $walker($path, $perms, $io);
            }
        };

        $worldWritable = bindec('0000000111');
        $walker($dir . '/tmp', $worldWritable, $io);
        $changePerms($dir . '/tmp', $worldWritable, $io);
        $changePerms($dir . '/logs', $worldWritable, $io);
    }

    /**
     * Set the security.salt value in the application's config file.
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @return void
     */
    public static function setSecuritySalt($dir, $io)
    {
        $newKey = hash('sha256', Security::randomBytes(64));
        static::setSecuritySaltInFile($dir, $io, $newKey, 'app.php');
    }

    /**
     * Set the security.salt value in a given file
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @param string $newKey key to set in the file
     * @param string $file A path to a file relative to the application's root
     * @return void
     */
    public static function setSecuritySaltInFile($dir, $io, $newKey, $file)
    {
        $config = $dir . '/config/' . $file;
        $content = file_get_contents($config);

        $content = str_replace('__SALT__', $newKey, $content, $count);

        if ($count == 0) {
            $io->write('No Security.salt placeholder to replace.');

            return;
        }

        $result = file_put_contents($config, $content);
        if ($result) {
            $io->write('Updated Security.salt value in config/' . $file);

            return;
        }
        $io->write('Unable to update Security.salt value.');
    }

    /**
     * Set the APP_NAME value in a given file
     *
     * @param string $dir The application's root directory.
     * @param \Composer\IO\IOInterface $io IO interface to write to console.
     * @param string $appName app name to set in the file
     * @param string $file A path to a file relative to the application's root
     * @return void
     */
    public static function setAppNameInFile($dir, $io, $appName, $file)
    {
        $config = $dir . '/config/' . $file;
        $content = file_get_contents($config);
        $content = str_replace('__APP_NAME__', $appName, $content, $count);

        if ($count == 0) {
            $io->write('No __APP_NAME__ placeholder to replace.');

            return;
        }

        $result = file_put_contents($config, $content);
        if ($result) {
            $io->write('Updated __APP_NAME__ value in config/' . $file);

            return;
        }
        $io->write('Unable to update __APP_NAME__ value.');
    }
}
