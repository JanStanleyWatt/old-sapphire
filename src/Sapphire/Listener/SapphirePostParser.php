<?php

namespace Whojinn\Sapphire\Listener;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Inline\Element\Text;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Util\SapphireKugiri;

class SapphirePostParser
{
    public function postParse(DocumentParsedEvent $event)
    {
        $walker = $event->getDocument()->walker();
        $parent_pattern = new SapphireKugiri();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            // 頭文字が末尾にあるTextノードから頭文字部分を除去する
            if ($node instanceof Text) {
                // 「《」と「｜」の直前にあるバックスペースを除去
                $tmp = str_replace('\\《', '《', $node->getContent());

                if ($node->next() instanceof RubyNode) {
                    foreach ($parent_pattern->getKugiri() as $pattern) {
                        $replaced = preg_replace($pattern, '', $tmp);

                        // マッチしたら頭文字を取り除いてforeachループから抜ける
                        if ($tmp !== $replaced) {
                            $node->setContent($replaced);
                            break;
                        }
                    }
                } elseif ($tmp !== $node->getContent()) {
                    $node->setContent($tmp);
                }
            }
        }
    }
}
