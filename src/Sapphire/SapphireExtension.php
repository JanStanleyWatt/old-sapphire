<?php

namespace Whojinn\Sapphire;

use League\CommonMark\ConfigurableEnvironmentInterface;
use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;
use Whojinn\Sapphire\Listener\SapphirePostParser;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Parser\SapphireInlineParser;
use Whojinn\Sapphire\Renderer\SapphireInlineRenderer;

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
        $configure = $environment->getConfig();
        $insert_rp = (array_key_exists('insert_rp', $configure) and $configure['insert_rp'] === true);

        $environment->addInlineParser(new SapphireInlineParser())
                    ->addEventListener(DocumentParsedEvent::class, [new SapphirePostParser(), 'postParse'])
                    ->addInlineRenderer(RubyNode::class, new SapphireInlineRenderer($insert_rp));
    }
}
