<?php

declare(strict_types=1);

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Dotenv\Dotenv;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;


require_once \dirname(__DIR__) . '/vendor/autoload.php';

// бутстрап аналогичен cli
if (!class_exists(Dotenv::class)) {
    throw new LogicException('You need to add "symfony/dotenv" as Composer dependencies.');
}

$input = new ArgvInput();
if (null !== $env = $input->getParameterOption(['--env', '-e'], null, true)) {
    putenv('APP_ENV=' . $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
}

if ($input->hasParameterOption('--no-debug', true)) {
    putenv('APP_DEBUG=' . $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
}

(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

$paths = [
    realpath(__DIR__ . '/src')
];
$isDevMode = true;

// the connection configuration
$dbParams = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite'
);

$cache = new \Symfony\Component\Cache\Adapter\FilesystemAdapter();
$wrapper = new \Symfony\Component\Cache\DoctrineProvider($cache);

$config = Setup::createAnnotationMetadataConfiguration(
    $paths,
    $isDevMode,
    null,
    $wrapper,
    false
);
return EntityManager::create($dbParams, $config);
