<?php

namespace Whojinn\Sapphire\Listener;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Inline\Element\Text;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Util\SapphireKugiri;

/**
 * パース後の抽象公分木に操作を行うクラス。
 * ここでは<ruby>タグ直前の文章から、ルビの頭文字部分を取り除くクラス。
 */
class SapphirePostParser
{
    public function postParse(DocumentParsedEvent $event)
    {
        $walker = $event->getDocument()->walker();
        $parent_pattern = new SapphireKugiri();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if ($node instanceof Text) {
                $tmp = $node->getContent();
                if ($node->next() instanceof RubyNode) {
                    foreach ($parent_pattern->getKugiri() as $pattern) {
                        $replaced = preg_replace($pattern, '', $tmp);
                        // マッチしたら頭文字を取り除いてforeachループから抜ける
                        if ($tmp !== $replaced) {
                            $node->setContent($replaced);
                            break;
                        }
                    }
                } // if ($node->next() instanceof RubyNode)終端

                // 「《」の直前にあるバックスラッシュを除去
                $node->setContent(mb_ereg_replace('\\\《', '《', $node->getContent()));
            }
        }
    }
}
