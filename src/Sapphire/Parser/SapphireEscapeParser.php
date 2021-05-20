<?php

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
