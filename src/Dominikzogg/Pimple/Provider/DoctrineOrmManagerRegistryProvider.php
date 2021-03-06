<?php

namespace Dominikzogg\Pimple\Provider;

use Dominikzogg\Doctrine\Registry\ManagerRegistry;
use Saxulum\DoctrineOrmCommands\Command\CreateDatabaseDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\DropDatabaseDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ClearMetadataCacheDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ClearQueryCacheDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ClearResultCacheDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ConvertMappingDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\CreateSchemaDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\DropSchemaDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\EnsureProductionSettingsDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\InfoDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\RunDqlDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\RunSqlDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\UpdateSchemaDoctrineCommand;
use Saxulum\DoctrineOrmCommands\Command\Proxy\ValidateSchemaCommand;
use Saxulum\DoctrineOrmCommands\Helper\ManagerRegistryHelper;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Console\Application as ConsoleApplication;

class DoctrineOrmManagerRegistryProvider
{
    public function register(\Pimple $container)
    {
        $container['doctrine'] = $container->share(function ($container) {
            return new ManagerRegistry($container);
        });

        if (isset($container['form.extensions']) && class_exists('Symfony\\Bridge\\Doctrine\\Form\\DoctrineOrmExtension')) {
            $container['form.extensions'] = $container->share(
                $container->extend('form.extensions', function ($extensions, $container) {
                    $extensions[] = new DoctrineOrmExtension($container['doctrine']);

                    return $extensions;
                })
            );
        }

        if (class_exists('Saxulum\\DoctrineOrmCommands\\Command\\CreateDatabaseDoctrineCommand')) {
            if (isset($container['console'])) {
                $container['console'] = $container->share(
                    $container->extend('console', function (ConsoleApplication $consoleApplication) use ($container) {
                        $helperSet = $consoleApplication->getHelperSet();
                        $helperSet->set(new ManagerRegistryHelper($container['doctrine']), 'doctrine');

                        return $consoleApplication;
                    })
                );
            }

            if (isset($container['console.commands'])) {
                $container['console.commands'] = $container->share(
                    $container->extend('console.commands', function ($commands) use ($container) {
                        $commands[] = new CreateDatabaseDoctrineCommand;
                        $commands[] = new DropDatabaseDoctrineCommand;
                        $commands[] = new CreateSchemaDoctrineCommand;
                        $commands[] = new UpdateSchemaDoctrineCommand;
                        $commands[] = new DropSchemaDoctrineCommand;
                        $commands[] = new RunDqlDoctrineCommand;
                        $commands[] = new RunSqlDoctrineCommand;
                        $commands[] = new ConvertMappingDoctrineCommand;
                        $commands[] = new ClearMetadataCacheDoctrineCommand;
                        $commands[] = new ClearQueryCacheDoctrineCommand;
                        $commands[] = new ClearResultCacheDoctrineCommand;
                        $commands[] = new InfoDoctrineCommand;
                        $commands[] = new ValidateSchemaCommand;
                        $commands[] = new EnsureProductionSettingsDoctrineCommand;

                        return $commands;
                    })
                );
            }
        }
    }
}
