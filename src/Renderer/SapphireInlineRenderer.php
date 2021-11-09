<?php declare(strict_types=1);

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

use function assert;
use function count;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Whojinn\Sapphire\Node\RubyNode;

class SapphireInlineRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    private $config;

    /**
     * ルビと親文字を分割できるのであれば分割する。
     * モノルビの条件：ルビの分割数と親文字の文字数が等しいこと.
     */
    private function devideRuby(string $ruby): array
    {
        return strpos($ruby, ' ') ? mb_split(' ', $ruby) : [$ruby];
    }

    private function mergeElement(array $parent, array $ruby): string
    {
        $string_array = '';
        $flag = $this->config->get('sapphire/use_rp_tag');

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

    /**
     * ConfigurationAwareInterfaceの実装。
     */
    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        $parent_array = [];
        $ruby_array = [];

        // RubyNode以外は処理しない
        if ($node instanceof RubyNode) {
            // ルビ配列の数と頭文字の数が同じならば頭文字を文字ごとに分解する
            if (count($this->devideRuby($node->getRubyString())) === mb_strlen($node->getParentString())) {
                $parent_array = mb_str_split($node->getParentString());
                $ruby_array = mb_split(' ', $node->getRubyString());
            } else {
                $parent_array = [$node->getParentString()];
                $ruby_array = [$node->getRubyString()];
            }
        }

        // 出力
        $attrs = $node->data->get('attributes');

        return new HtmlElement('ruby', $attrs, $this->mergeElement($parent_array, $ruby_array));
    }
}
