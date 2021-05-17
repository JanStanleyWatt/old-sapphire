<?php

namespace Whojinn\Sapphire\Parser;

use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Inline\Parser\InlineParserInterface;
use League\CommonMark\InlineParserContext;
use Whojinn\Sapphire\Node\RubyNode;
use Whojinn\Sapphire\Util\SapphireKugiri;

class SapphireInlineParser implements InlineParserInterface
{
    private string $parent_char;
    private string $ruby_char;
    private bool $is_sutegana = false;

    /**
     * このパーサーにおいて最後にカーソルが存在していた地点。
     * カーソルにおける「》」のインデックスみたいなもの.
     */
    private int $prev_cursor_index = 0;

    /**
     * ルビと親文字を分割できるのであれば分割する。
     * モノルビの条件：ルビの分割数と親文字の文字数が等しいこと.
     */
    private function devideRuby(string $parent, string $ruby): array
    {
        $ruby_array = strpos($ruby, ' ') ? mb_split(' ', $ruby) : [$ruby];

        $ruby_count = count($ruby_array);

        $returns = ['parent' => [], 'ruby' => []];

        if ($ruby_count > 1 and mb_strlen($parent) === $ruby_count) {
            foreach (mb_str_split($parent) as $char) {
                array_push($returns['parent'], $char);
            }
            foreach ($ruby_array as $char_2) {
                // $char = $char === '' ? $this->margeRubyElement($char, false) : $this->margeRubyElement($char, $this->insert_rp);
                array_push($returns['ruby'], $char_2);
            }
        } else {
            $returns['parent'] = [$parent];
            $returns['ruby'] = [$ruby];
        }

        return $returns;
    }

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
        $restore = $cursor->saveState();

        // 不正な構文を弾く
        if ($cursor->isAtEnd() or $cursor->getPosition() === 0) {
            return false;
        }

        // 「《」の前にバックスラッシュがあったら
        // 「《」をテキストに登録してからカーソルを1つ進めてtrueを返す
        if ($cursor->peek(-1) === '\\') {
            $tmp = $cursor->match('/^(.+?)》+/u');

            // 文の終わりにまで来てしまったらfalseを返す
            if ($cursor->isAtEnd()) {
                $cursor->restoreState($restore);

                return false;
            }
            $inlineContext->getContainer()->appendChild(new Text($tmp));
            $this->prev_cursor_index = $cursor->getPosition();

            return true;
        }

        // 頭文字とルビを抽出
        $prev_cursor_index = $this->prev_cursor_index > $cursor->getCharacter() ? 0 : $this->prev_cursor_index;

        $this->parent_char = $cursor->getSubstring($prev_cursor_index, $cursor->getPosition());
        foreach ($parent_pattern->getKugiri() as $syurui => $pattern) {
            // 最後のパース地点から「《」までを頭文字候補として、それが頭文字パターンのいずれかにマッチしたらパースをしてtrueを返す
            if (preg_match($pattern, $this->parent_char, $matches)) {
                $this->parent_char = $matches[1];

                $cursor->advance();

                // ルビを抽出
                // ルビが空だった場合はruby_charには空文字を入れる
                $this->ruby_char = $cursor->getCharacter() === '》' ? '' : $cursor->match('/^[^》]+/u');
                $this->ruby_char = $this->sutegana($this->ruby_char, $this->is_sutegana);

                // カーソルが終端に来なかったときに限ってtrueを返す
                if (!$cursor->isAtEnd()) {
                    $tmp = $this->devideRuby($this->parent_char, $this->ruby_char);
                    $ruby_node = new RubyNode($tmp['parent'], $tmp['ruby']);
                    $inlineContext->getContainer()->appendChild($ruby_node);

                    $cursor->advance();
                    $this->prev_cursor_index = $cursor->getPosition();

                    return true;
                } else {
                    break;
                }
            }
        }

        // 頭文字パターンにマッチしなかった場合はレストアしてfalseを返す
        $cursor->restoreState($restore);

        return false;
    }
}
