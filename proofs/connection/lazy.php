<?php
declare(strict_types = 1);

use Formal\AccessLayer\{
    Connection,
    Connection\PDO,
    Connection\Lazy,
};
use Properties\Formal\AccessLayer\Connection as Properties;
use Innmind\Url\Url;
use Innmind\BlackBox\Set;

return static function() {
    $port = \getenv('DB_PORT') ?: '3306';
    $connection = new Lazy(
        static fn() => PDO::of(Url::of("mysql://root:root@127.0.0.1:$port/example")),
    );
    Properties::seed($connection);

    yield test(
        'Lazy interface',
        static fn($assert) => $assert
            ->object($connection)
            ->instance(Connection::class),
    );

    yield test(
        'Lazy connection must not be established at instanciation',
        static fn($assert) => $assert
            ->object(new Lazy(static fn() => PDO::of(Url::of('mysql://unknown:unknown@127.0.0.1:3306/unknown'))))
            ->instance(Connection::class),
    );

    yield properties(
        'Lazy properties',
        Properties::any(),
        Set\Elements::of($connection),
    );

    foreach (Properties::list() as $property) {
        yield property(
            $property,
            Set\Elements::of($connection),
        )->named('Lazy');
    }
};
