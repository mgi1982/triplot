<?php

if (!$loader = @include __DIR__ . '/../vendor/.composer/autoload.php') {
    die(<<<'EOT'
You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install
EOT
    );
}

$loader->add('Doctrine\Tests\Shards', __DIR__ );
