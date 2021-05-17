<?php

namespace Whojinn\Sapphire;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use Whojinn\Sapphire\Listener\SapphirePostParser;
use Whojinn\Sapphire\Listener\SapphirePreParser;
use Whojinn\Sapphire\Node\RubyChildNode;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Parser\SapphireInlineParser;
use Whojinn\Sapphire\Renderer\SapphireInlineRenderer;
use Whojinn\Sapphire\Renderer\SapphireRubyRender;

/**
 * 青空文庫式ルビを追加する拡張機能。
 *
 * 独自コンフィグ
 * insert_rp: rpタグを追加するか否か(デフォルトはfalse)
 */
class SapphireExtension implements ExtensionInterface
{
    public function register(ConfigurableEnvironmentInterface $environment)
    {
        $environment->addInlineParser(new SapphireInlineParser($environment->getConfig('sutegana', false)), 9000)
                    // ->addEventListener(DocumentPreParsedEvent::class, [new SapphirePreParser(), 'preParse'])
                    ->addEventListener(DocumentParsedEvent::class, [new SapphirePostParser(), 'postParse'])
                    ->addInlineRenderer(RubyNode::class, new SapphireInlineRenderer())

                    ->addInlineRenderer(RubyChildNode::class, new SapphireRubyRender());
    }
}
