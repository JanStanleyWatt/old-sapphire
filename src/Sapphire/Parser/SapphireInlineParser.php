<?php

namespace Whojinn\Sapphire\Parser;

use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Util\SapphireKugiri;

class SapphireInlineParser implements InlineParserInterface
{
    private string $parent_char;
    private string $ruby_char;

    public function getCharacters(): array
    {
        return ['《'];
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $parent_pattern = new SapphireKugiri();
        $cursor = $inlineContext->getCursor();
        $restore = $cursor->saveState();

        // 不正な構文を弾く
        if ($cursor->isAtEnd() or $cursor->getPosition() === 0) {
            return false;
        }

        //「《」の直前にバックスラッシュがある場合はパースしない(エスケープ)
        if ($cursor->peek(-1) === '\\') {
            return false;
        }

        // 頭文字とルビを抽出
        $this->parent_char = $cursor->getPreviousText();
        foreach ($parent_pattern->getKugiri() as $syurui => $pattern) {
            // 頭文字パターンのいずれかにマッチしたらパースをしてtrueを返す
            if (preg_match($pattern, $this->parent_char, $matches)) {
                $this->parent_char = $matches[1];
                $cursor->advance();

                // ルビを抽出
                // ルビが空だった場合はruby_charには空文字を入れる
                $this->ruby_char = $cursor->getCharacter() === '》' ? '' : $cursor->match('/^[^》]+/u');
                $inlineContext->getContainer()->appendChild(new RubyNode($this->parent_char, $this->ruby_char));
                $cursor->advance();

                return true;
            }
        }

        // 頭文字パターンにマッチしなかった場合はレストアしてfalseを返す
        $cursor->restoreState($restore);

        return false;
    }
}
