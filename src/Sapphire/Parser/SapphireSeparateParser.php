<?php

namespace Whojinn\Sapphire\Parser;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use Whojinn\Sapphire\Node\RubyParentNode;

/**
 * ルビ記号のうち、区切り文字を制御させるクラス。
 */
class SapphireSeparateParser implements InlineParserInterface
{
    public function getCharacters(): array
    {
        return ['｜'];
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $restore = $cursor->saveState();

        if ($cursor->peek() === '《') {
            return false;
        }

        $cursor->advance();
        $parent_char = $cursor->match('/^[^《]+/u');
        // 「《」が見つからなかったらレストアしてfalseを返す
        // あるいはより後ろに「｜」を見つけたら処理をそちらに譲る
        // それから「《」のエスケープも忘れずに
        if ($cursor->isAtEnd()) {
            $cursor->restoreState($restore);

            return false;
        } elseif (mb_ereg('(^([^\\\]+?)｜)|(\\\$)', $cursor->getPreviousText())) {
            $cursor->restoreState($restore);
            $inlineContext->getContainer()->appendChild(new Text('｜'));
            $cursor->advance();

            return true;
        }
        // elseif (mb_ereg_match('(^(.+?)｜)|(\\\$)', $cursor->getPreviousText())) {
        //     $cursor->restoreState($restore);
        //     $inlineContext->getContainer()->appendChild(new Text('｜'));
        //     $cursor->advance();

        //     return true;
        // }

        $parent_char = str_replace('\\', '', $parent_char);
        $inlineContext->getContainer()->appendChild(new RubyParentNode($parent_char));

        return true;
    }
}
