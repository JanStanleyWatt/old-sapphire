<?php

require_once __DIR__.'/vendor/autoload.php';

use League\CommonMark\Environment;
use League\CommonMark\Extension\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Whojinn\Sapphire\SapphireExtension;

$environment = new Environment();

$environment->addExtension(new CommonMarkCoreExtension());
$environment->addExtension(new SapphireExtension());
$environment->mergeConfig([
    'sapphire' => [
        'sutegana' => false,    // trueにすると、特定の小文字が大文字になる
        'rp_tag' => false,      // trueにすると、<rp>タグがルビにつく
    ],
]);

$converter = new MarkdownConverter($environment);

$markdown = 'この拡張機能《エクステンション》は｜素晴らしい《イカしてる》';

echo $converter->convertToHtml($markdown);
