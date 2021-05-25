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

namespace Whojinn\Sapphire\Renderer;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use Whojinn\Sapphire\Node\RubyNode;

class SapphireInlineRenderer implements InlineRendererInterface
{
    /**
     * <rp>タグをつけるか否か.
     */
    private bool $is_set_rp = false;

    /**
     * ルビと親文字を分割できるのであれば分割する。
     * モノルビの条件：ルビの分割数と親文字の文字数が等しいこと.
     */
    private function devideRuby(string $ruby): array
    {
        return strpos($ruby, ' ') ? mb_split(' ', $ruby) : [$ruby];
    }

    private function mergeElement(array $parent, array $ruby, bool $flag = false): string
    {
        $string_array = '';
        assert(count($parent) === count($ruby));
        for ($i = 0; $i < count($ruby); ++$i) {
            $string_array .= $parent[$i];

            // ルビが空の場合は空の<rt>タグを入れる
            if ($ruby[$i] === '') {
                $string_array .= '<rt></rt>';
                continue;
            }
            $string_array .= $flag ? '<rp>（</rp><rt>'.$ruby[$i].'</rt><rp>）</rp>' : '<rt>'.$ruby[$i].'</rt>';
        }

        return $string_array;
    }

    public function __construct(bool $flag = false)
    {
        $this->is_set_rp = $flag;
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        $parent_array = [];
        $ruby_array = [];

        // RubyNode以外は処理しない
        if (!($inline instanceof RubyNode)) {
            throw new \InvalidArgumentException('Incompatible inline type: '.get_class($inline));
        }

        // ルビ配列の数と頭文字の数が同じならば頭文字を文字ごとに分解する
        if (count($this->devideRuby($inline->getRubyString())) === mb_strlen($inline->getParentString())) {
            $parent_array = mb_str_split($inline->getParentString());
            $ruby_array = mb_split(' ', $inline->getRubyString());
        } else {
            $parent_array = [$inline->getParentString()];
            $ruby_array = [$inline->getRubyString()];
        }

        $attrs = $inline->getData('attributes', []);

        // 出力
        return new HtmlElement('ruby', $attrs, $this->mergeElement($parent_array, $ruby_array, $this->is_set_rp));
    }
}
