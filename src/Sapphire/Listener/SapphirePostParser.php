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

namespace Whojinn\Sapphire\Listener;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Node\Inline\Text;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Node\RubyParentNode;
use Whojinn\Sapphire\Util\SapphireKugiri;

/**
 * パース後の抽象構文木に操作を行うクラス。
 * つまり頭文字をTextノードから切り出すわけだが、ぶっちゃけ文字の種類の違いを元になんとなくここと推定するという
 * かなりいい加減な代物であるため、困ったときには「｜」をガンガン使っていただきたい。
 */
class SapphirePostParser
{
    public function postParse(DocumentParsedEvent $event)
    {
        $walker = $event->getDocument()->iterator();
        $parent_pattern = new SapphireKugiri();
        $parent_char = '';

        foreach ($walker as $node) {
            if ($node instanceof RubyParentNode) {
                $parent_char = $node->getLiteral();
                $node->detach();
            }

            if ($node instanceof Text and $node->next() instanceof RubyNode) {
                $tmp = $node->getLiteral();
                foreach ($parent_pattern->getKugiri() as $pattern) {
                    if (mb_ereg($pattern, $tmp, $matches)) {
                        $node->setLiteral(mb_ereg_replace($pattern, '', $tmp));
                        $parent_char = $matches[0];
                        break;
                    }
                }// foreach終端
            }// if Text終端

            if ($node instanceof RubyNode and $node->getParentString() === '') {
                $node->setParentString($parent_char);
                $parent_char = '';
            }
        }
    }
}
