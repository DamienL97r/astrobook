<?php

declare(strict_types=1);

/*
 * This file is part of the AstroBook project.
 * (c) David Pelletier-Ulrich <d@mztrix.me>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/parameters.php');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    $services->load('Dogstronauts\AstroBook\\', __DIR__ . '/../src/')
        ->exclude([
            __DIR__ . '/../src/DependencyInjection',
            __DIR__ . '/../src/Entity',
            __DIR__ . '/../src/Kernel.php',
            __DIR__ . '/../src/Security/DependencyInjection',
            __DIR__ . '/../src/Security/Model',
        ])
    ;

    $services->set(Dogstronauts\AstroBook\ApiPlatform\State\SoftDeleteProcessor::class)
        ->decorate('api_platform.doctrine.orm.state.remove_processor')
        ->args([
            new Reference(Dogstronauts\AstroBook\ApiPlatform\State\SoftDeleteProcessor::class . '.inner'),
            new Reference('doctrine.orm.entity_manager'),
        ])
    ;

    $services->set(Dogstronauts\AstroBook\ApiPlatform\Doctrine\Orm\Filter\SoftDeleteFilter::class)
        ->tag('api_platform.doctrine.orm.query_extension.collection')
        ->tag('api_platform.doctrine.orm.query_extension.item')
    ;

    if (in_array($containerConfigurator->env(), ['dev', 'test'], true)) {
        $services
            ->load('Dogstronauts\AstroBook\Fixtures\\', __DIR__ . '/../fixtures/')
            ->autowire()
            ->autoconfigure()
        ;
    }
};
