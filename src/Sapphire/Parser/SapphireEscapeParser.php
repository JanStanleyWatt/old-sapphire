<?php

declare(strict_types=1);
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

namespace Whojinn\Sapphire\Parser;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

class SapphireEscapeParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('\\');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $next_char = $cursor->peek();
        $is_escapable = ($next_char === ('｜' || '《' || '\\'));

        // 後ろの文字がルビ記号やバックスラッシュでなかったらfalseを返す
        if ($next_char === null || $is_escapable) {
            return false;
        }

        // 行頭または引用記号(>)か脚注記号([^\d])の先頭にバックスラッシュが来た場合はfalseを返す。
        // 仮にそれらの先頭に「《」が来た場合は仕様上ルビ記号として認識しないため
        if (($cursor->getPosition() === 0) || mb_ereg('^\[\^(\d+?)\]:|^>', $cursor->getPreviousText())) {
            return false;
        }

        $cursor->advanceBy(2);
        $inlineContext->getContainer()->appendChild(new Text($next_char));

        return true;
    }
}
