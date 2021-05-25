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

use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use Whojinn\Sapphire\Node\RubyNode;

class SapphireInlineParser implements InlineParserInterface
{
    private ?string $ruby_char;
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
        $cursor = $inlineContext->getCursor();
        $restore = $cursor->saveState();

        // 不正な構文を弾く
        if ($cursor->isAtEnd() or $cursor->getPosition() === 0 or $cursor->peek(-1) === '｜') {
            return false;
        }

        // ルビを抽出
        // ルビが空だった場合はruby_charには空文字を入れる
        $cursor->advance();
        $this->ruby_char = $cursor->getCharacter() === '》' ? '' : $cursor->match('/^(.+?)(?=》)/u');

        // マッチングしなかったり、ルビ文字があるのに「》」がなかったらレストアしてfalseを返す
        if ($this->ruby_char === null or $cursor->isAtEnd()) {
            $cursor->restoreState($restore);

            return false;
        }

        // 捨て仮名フラグが立っていた場合は該当する文字列を置換する
        $this->ruby_char = $this->sutegana($this->ruby_char, $this->is_sutegana);

        $inlineContext->getContainer()->appendChild(new RubyNode($this->ruby_char, ['delim' => true]));

        $cursor->advance();

        return true;
    }
}
