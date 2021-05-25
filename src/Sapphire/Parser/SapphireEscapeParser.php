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

namespace Whojinn\Sapphire\Parser;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;

class SapphireEscapeParser implements InlineParserInterface
{
    public function getCharacters(): array
    {
        return ['\\'];
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $next_char = $cursor->peek();

        // 後ろの文字がルビ記号やバックスラッシュでなかったらfalseを返す
        if ($next_char !== '｜' and $next_char !== '《' and
            $next_char !== '\\' and $next_char === null) {
            return false;
        }
        $cursor->advanceBy(2);
        $inlineContext->getContainer()->appendChild(new Text($next_char));

        return true;
    }
}
