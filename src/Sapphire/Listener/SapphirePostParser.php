<?php

namespace Whojinn\Sapphire\Listener;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Inline\Element\Text;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Node\RubyParentNode;
use Whojinn\Sapphire\Util\SapphireKugiri;

/**
 * パース後の抽象構文木に操作を行うクラス。
 */
class SapphirePostParser
{
    public function postParse(DocumentParsedEvent $event)
    {
        $walker = $event->getDocument()->walker();
        $parent_pattern = new SapphireKugiri();

        while ($event = $walker->next()) {
            $node = $event->getNode();

            if ($node instanceof RubyParentNode) {
                $parent_char = $node->getContent();
                $node->detach();
            }

            if (($node instanceof Text) and $node->next() instanceof RubyNode) {
                $tmp = $node->getContent();
                foreach ($parent_pattern->getKugiri() as $pattern) {
                    if (mb_ereg($pattern, $tmp, $matches)) {
                        $node->setContent(mb_ereg_replace($pattern, '', $tmp));
                        $parent_char = $matches[0];
                        break;
                    }
                }// foreach終端
            }// if Text終端

            if ($node instanceof RubyNode) {
                $node->setParentString($parent_char);
                $parent_char = '';
            }
        }
    }
}
