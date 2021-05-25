<?php
/**
 * Copyright 2021 whojinn

 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at

 *  http://www.apache.org/licenses/LICENSE-2.0

 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
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
