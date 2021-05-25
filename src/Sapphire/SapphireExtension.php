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

namespace Whojinn\Sapphire;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use Whojinn\Sapphire\Listener\SapphirePostParser;
use Whojinn\Sapphire\Listener\SapphirePreParser;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Parser\SapphireEscapeParser;
use Whojinn\Sapphire\Parser\SapphireInlineParser;
use Whojinn\Sapphire\Parser\SapphireSeparateParser;
use Whojinn\Sapphire\Renderer\SapphireInlineRenderer;

/**
 * 青空文庫式ルビを追加する拡張機能。
 *
 * 独自コンフィグ
 * sutegana: ルビ内の特定の小文字を大文字にするか否か(デフォルトはfalse)
 * rp_tag: ルビ非対応ブラウザにて代替表現を提供する<rp>タグをつけるか否か(デフォルトはfalse)
 */
class SapphireExtension implements ExtensionInterface
{
    private const CONFIG_NAME = 'sapphire';

    private const DEFAULT_CONFIG = [
        'sutegana' => false,
        'rp_tag' => false,
    ];

    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $config = $environment->getConfig(self::CONFIG_NAME, self::DEFAULT_CONFIG);

        $sutegana = $config['sutegana'];
        $rp_tag = $config['rp_tag'];

        $environment->addInlineParser(new SapphireSeparateParser(), 100)
                    ->addInlineParser(new SapphireEscapeParser(), 100)
                    ->addInlineParser(new SapphireInlineParser($sutegana))
                    // ->addEventListener(DocumentPreParsedEvent::class, [new SapphirePreParser(), 'preParse'])
                    ->addEventListener(DocumentParsedEvent::class, [new SapphirePostParser(), 'postParse'])
                    ->addInlineRenderer(RubyNode::class, new SapphireInlineRenderer($rp_tag));
    }
}
