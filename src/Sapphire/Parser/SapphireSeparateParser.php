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
        $parent_char = '';

        if ($cursor->peek() === '《') {
            return false;
        }

        $cursor->advance();

        /**
         * 行末近くのルビ記号「《》」にマッチングするまでカーソルを進めると共に、
         * マッチングしたらアサーション以外の文字を文字列に加える.
         */
        $parent_char = $cursor->match('/^(.+)(?=《(.+?)》.*?$)/u');

        /*
         * ルビ記号が見つからなかったらレストアしてfalseを返す
         * */
        if ($parent_char === null or $cursor->isAtEnd()) {
            $cursor->restoreState($restore);

            return false;
        }
        /*
         * ルビ記号より前に区切り文字が見つかったら、そちらに処理を譲る
         * また、ルビ記号「《」の手前にバックスラッシュを見つけたら
         * 全部平文として解釈させる
         */
        if (mb_ereg_match('(.+)(?<!\\\)｜|(\\\$)', $cursor->getPreviousText())) {
            $cursor->restoreState($restore);
            $inlineContext->getContainer()->appendChild(new Text('｜'));
            $cursor->advance();

            return true;
        }

        $parent_char = str_replace('\\｜', '｜', str_replace('\\《', '《', $parent_char));
        $inlineContext->getContainer()->appendChild(new RubyParentNode($parent_char));

        return true;
    }
}
