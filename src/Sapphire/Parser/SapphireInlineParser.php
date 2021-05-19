<?php

namespace Whojinn\Sapphire\Parser;

use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Util\SapphireKugiri;

class SapphireInlineParser implements InlineParserInterface
{
    private string $ruby_char;
    private bool $is_sutegana = false;

    /**
     * 捨て仮名を大文字に置換する関数。
     * 引数にある置換フラグは実際にはプロパティで持たせる。
     * 捨て仮名コードは以下よりお借りしました：.
     *
     * @see https://github.com/noisan/parsedown-rubytext/blob/master/lib/Parsedown/RubyTextTrait.php
     *
     * @param $ruby ルビ文字
     * @param $is_sutegana 捨て仮名処理を行うか否か
     *
     * @return string
     */
    private function sutegana(string $ruby, bool $is_sutegana)
    {
        $ruby_text_Sutegana = [
            // 小書き文字をfromに、並字をtoに置く。ペアの要素順は合わせること
            'from' => ['ぁ', 'ぃ', 'ぅ', 'ぇ', 'ぉ', 'っ', 'ゃ', 'ゅ', 'ょ', 'ゎ', 'ァ', 'ィ', 'ゥ', 'ェ', 'ォ', 'ヵ', 'ヶ', 'ッ', 'ャ', 'ュ', 'ョ', 'ヮ'],
            'to' => ['あ', 'い', 'う', 'え', 'お', 'つ', 'や', 'ゆ', 'よ', 'わ', 'ア', 'イ', 'ウ', 'エ', 'オ', 'カ', 'ケ', 'ツ', 'ヤ', 'ユ', 'ヨ', 'ワ'],
            // 小さいクなどは文字化けしてしまった
        ];

        if ($is_sutegana) {
            $ruby = str_replace($ruby_text_Sutegana['from'], $ruby_text_Sutegana['to'], $ruby);
        }

        return $ruby;
    }

    /**
     * コンストラクタ
     *
     * @param bool $is_sutegana trueにすると小文字を大文字に変換する
     */
    public function __construct(bool $is_sutegana)
    {
        $this->is_sutegana = $is_sutegana;
    }

    /**
     * パースの開始地点となる文字を定義.
     */
    public function getCharacters(): array
    {
        return ['《'];
    }

    /**
     * 実際の処理.
     */
    public function parse(InlineParserContext $inlineContext): bool
    {
        $parent_pattern = new SapphireKugiri();
        $cursor = $inlineContext->getCursor();
        $start_index = $cursor->getPosition() + 1;
        $restore = $cursor->saveState();

        // 不正な構文を弾く
        if ($cursor->isAtEnd() or $cursor->getPosition() === 0 or $cursor->peek(-1) === '｜') {
            return false;
        }

        // ルビを抽出
        // ルビが空だった場合はruby_charには空文字を入れる
        $cursor->advance();
        $this->ruby_char = $cursor->getCharacter() === '》' ? '' : $cursor->match('/^[^》]+/u');
        $this->ruby_char = $this->sutegana($this->ruby_char, $this->is_sutegana);

        // 「》」がなかったらレストアしてfalseを返す
        if ($cursor->isAtEnd()) {
            $cursor->restoreState($restore);

            return false;
        }

        $inlineContext->getContainer()->appendChild(new RubyNode($this->ruby_char, ['delim' => true]));

        $cursor->advance();

        return true;
    }
}
