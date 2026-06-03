<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Cache\ResponseCache;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Header;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\DataCaster\DataCaster;
use CodeIgniter\Entity\Cast\CastInterface;
use CodeIgniter\Entity\Exceptions\CastException;
use CodeIgniter\DataConverter\DataConverter;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Entity\Cast\URICast;
use CodeIgniter\HTTP\URI;
use CodeIgniter\Log\Handlers\ChromeLoggerHandler;
use CodeIgniter\Security\CheckPhpIni;
use CodeIgniter\View\Table;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\View\Plugins;
use CodeIgniter\HTTP\ResponseTrait;
use CodeIgniter\Pager\PagerInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\Validation\Validation;
use CodeIgniter\View\RendererInterface;
use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;
use Boundwize\StructArmed\Preset\Presets\Psr4Preset;

return Architecture::define()
    ->skip([
        Psr4Preset::CLASSES_MUST_MATCH_COMPOSER => [
            __DIR__ . '/tests/system/Config/fixtures',
        ],
        __DIR__ . '/system/ThirdParty',
    ])
    ->cacheDirectory(is_dir('/tmp') ? '/tmp/structarmed' : null)
    ->withPreset(Preset::PSR4())
    // Resolve CodeIgniter layers from class names because several layers share directories.
    ->layerPattern('API', '/^CodeIgniter\\\\API\\\\.*$/')
    ->layerPattern('Cache', '/^CodeIgniter\\\\Cache\\\\.*$/')
    ->layerPattern('Controller', '/^CodeIgniter\\\\Controller$/')
    ->layerPattern('Cookie', '/^CodeIgniter\\\\Cookie\\\\.*$/')
    ->layerPattern('Database', '/^CodeIgniter\\\\Database\\\\.*$/')
    ->layerPattern('DataCaster', '/^CodeIgniter\\\\DataCaster\\\\.*$/')
    ->layerPattern('DataConverter', '/^CodeIgniter\\\\DataConverter\\\\.*$/')
    ->layerPattern('Email', '/^CodeIgniter\\\\Email\\\\.*$/')
    ->layerPattern('Encryption', '/^CodeIgniter\\\\Encryption\\\\.*$/')
    ->layerPattern('Entity', '/^CodeIgniter\\\\Entity\\\\.*$/')
    ->layerPattern('Events', '/^CodeIgniter\\\\Events\\\\.*$/')
    ->layerPattern('Files', '/^CodeIgniter\\\\Files\\\\.*$/')
    ->layerPattern('Filters', '/^CodeIgniter\\\\Filters\\\\Filter.*$/')
    ->layerPattern('Format', '/^CodeIgniter\\\\Format\\\\.*$/')
    ->layerPattern('Honeypot', '/^CodeIgniter\\\\.*Honeypot.*$/')
    ->layerPattern('URI', '/^CodeIgniter\\\\HTTP\\\\URI$/')
    ->layerPattern('HTTP', '/^CodeIgniter\\\\HTTP\\\\.*$/', '/(Exception|URI)/')
    ->layerPattern('I18n', '/^CodeIgniter\\\\I18n\\\\.*$/')
    ->layerPattern('Images', '/^CodeIgniter\\\\Images\\\\.*$/')
    ->layerPattern('Language', '/^CodeIgniter\\\\Language\\\\.*$/')
    ->layerPattern('Log', '/^CodeIgniter\\\\Log\\\\.*$/')
    ->layerPattern('Model', '/^CodeIgniter\\\\.*Model$/')
    ->layerPattern('Modules', '/^CodeIgniter\\\\Modules\\\\.*$/')
    ->layerPattern('Pager', '/^CodeIgniter\\\\Pager\\\\.*$/')
    ->layerPattern('Publisher', '/^CodeIgniter\\\\Publisher\\\\.*$/')
    ->layerPattern('RESTful', '/^CodeIgniter\\\\RESTful\\\\.*$/')
    ->layerPattern('Router', '/^CodeIgniter\\\\Router\\\\.*$/')
    ->layerPattern('Security', '/^CodeIgniter\\\\Security\\\\.*$/')
    ->layerPattern('Session', '/^CodeIgniter\\\\Session\\\\.*$/')
    ->layerPattern('Throttle', '/^CodeIgniter\\\\Throttle\\\\.*$/')
    ->layerPattern('Typography', '/^CodeIgniter\\\\Typography\\\\.*$/')
    ->layerPattern('Validation', '/^CodeIgniter\\\\Validation\\\\.*$/', '/^CodeIgniter\\\\Validation\\\\FormatRules$/')
    ->layerPattern('View', '/^CodeIgniter\\\\View\\\\.*$/')
    ->ruleset([
        'API'           => ['Format', 'HTTP', 'Database', 'Model', 'Pager', 'URI'],
        'Cache'         => ['I18n'],
        'Controller'    => ['HTTP', 'Validation'],
        'Cookie'        => ['I18n'],
        'Database'      => ['Entity', 'Events', 'I18n'],
        'DataCaster'    => ['I18n', 'URI', 'Database'],
        'DataConverter' => ['DataCaster'],
        'Email'         => ['I18n', 'Events'],
        'Entity'        => ['DataCaster', 'I18n'],
        'Files'         => ['I18n'],
        'Filters'       => ['HTTP'],
        'Honeypot'      => ['Filters', 'HTTP'],
        'HTTP'          => ['Cookie', 'Files', 'I18n', 'Security', 'URI'],
        'Images'        => ['Files', 'I18n'],
        'Model'         => ['Database', 'DataCaster', 'DataConverter', 'Entity', 'I18n', 'Pager', 'Validation'],
        'Pager'         => ['URI', 'View'],
        'Publisher'     => ['Files', 'URI'],
        // +API = API + its allowed layers; +Controller = Controller + its allowed layers
        'RESTful'       => ['+API', '+Controller'],
        'Router'        => ['HTTP', 'I18n'],
        'Security'      => ['Cookie', 'HTTP', 'I18n', 'Session'],
        'Session'       => ['Cookie', 'Database', 'HTTP', 'I18n'],
        'Throttle'      => ['Cache', 'I18n'],
        'Validation'    => ['Database', 'HTTP'],
        'View'          => ['Cache'],
    ])
    ->skipPathsForRuleset(['*test*'])
    // Skip violations for class-specific dependencies.
    ->skipClassViolation(ResponseCache::class, [
        CLIRequest::class,
        Header::class,
        IncomingRequest::class,
        ResponseInterface::class,
    ])
    ->skipClassViolation(DataCaster::class, [
        CastInterface::class,
        CastException::class,
    ])
    ->skipClassViolation(\CodeIgniter\DataCaster\Exceptions\CastException::class, [
        CastException::class,
    ])
    ->skipClassViolation(DataConverter::class, [
        Entity::class,
    ])
    ->skipClassViolation(URICast::class, [
        URI::class,
    ])
    ->skipClassViolation(ChromeLoggerHandler::class, [
        ResponseInterface::class,
    ])
    ->skipClassViolation(CheckPhpIni::class, [
        Table::class,
    ])
    ->skipClassViolation(Table::class, [
        BaseResult::class,
    ])
    ->skipClassViolation(Plugins::class, [
        URI::class,
    ])

    // BC changes that should be fixed
    ->skipClassViolation(ResponseTrait::class, [PagerInterface::class])
    ->skipClassViolation(ResponseInterface::class, [PagerInterface::class])
    ->skipClassViolation(Response::class, [PagerInterface::class])
    ->skipClassViolation(RedirectResponse::class, [PagerInterface::class])
    ->skipClassViolation(DownloadResponse::class, [PagerInterface::class])
    ->skipClassViolation(Validation::class, [RendererInterface::class]);
