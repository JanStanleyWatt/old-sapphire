<?php

declare(strict_types=1);

namespace Whojinn\Sapphire\Util;

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
class SapphireKugiri
{
    /**
     * 親文字の区切りパターンを収めた配列。
     * 区切りパターンはnoisan氏の以下のコードの内、626行目から695行目までを借用しました。
     *
     * @see https://github.com/noisan/parsedown-rubytext/blob/master/lib/Parsedown/AozoraRubyTextTrait.php
     */
    private $kugiri = [
        // 'unused-label'  => '/(regex)$/u',

        /* 漢字グループ:
         *   - 青空文庫では「仝々〆〇ヶ\x{303B}」も漢字として扱うと明記している(\x{303B}は二の字点)。
         *     @see http://www.aozora.gr.jp/KOSAKU/MANUAL_2.html#ruby
         *   - t2hs.rbでは外字記述用の※も漢字に含めていたが
         *     このExtensionだけでは青空文庫外字指定形式を扱えないので保留する。
         *   - このExtension独自の制限として、"ヶ" は漢字に続く場合だけ漢字とみなす。
         *     (例: "(OK)八ヶ岳", "(NG)5ヶ条")
         */
        'kanji' => '((?:[\p{Han}〆]+[ヶ]*)+)$',

        /* 全角英数字グループ:
         *   - t2hs.rbでは、全角記号、ギリシア文字、キリル文字も合わせて同一文字種としている。
         *   - このExtensionでもそれに従う。
         */
        'zenkaku_alphanum' => '([Ａ-Ｚａ-ｚ０-９\p{Greek}\p{Cyrillic}＆’，．－]+)$',

        /* 半角英数字グループ:
         *   - 全角英数字グループとは記号の種類が違う。
         *   - t2hs.rbでは半角英数記号グループに末尾専用記号(:hankaku_terminate)を定義している。
         *     :hankaku_terminate が出現した箇所は半角英数字の切れ目になる。
         *   - このExtensionではそれを少しだけ発展させて
         *     :hankaku_terminate の一部の記号は繰り返し可能にする。
         *     - [;]は元の仕様通り。その時点で終端(end;)
         *     - [.]は末尾で繰り返せる(oh...)
         *     - [!?]はそれぞれを組み合わせて繰り返し可(hey!!!?!????)
         *   - t2hs.rbでは "&" と '"' を半角英数字グループに含めているので
         *     このExtensionでも正規表現上は使っているが、実際はこの2つを
         *     半角英数字として認識できない仕様になっている。これらの文字は
         *     Parsedownがそれぞれ "&amp;"と"&quot;" に変換して確定してしまうため。
         *   - "&" や '"' を含めて親文字に指定したい場合は "｜" で範囲指定する。
         *     (例: "｜AT&T《ルビ》")
         *   - なお、以下のページから実際に変換を行ってみたところ、
         *     t2hs.rbでも "&" については半角英数字と認識されなかった。
         *     ("AT&T《ルビ》" では末尾の "T" にルビが振られる)
         *     @see http://kumihan.aozora.gr.jp/slabid-5.htm
         *
         *     2021/10/22引用者追記：League\CommonMarkではエスケープ変換がレンダリング時に行われるためか、エスケープ対象の"&"と'"'も正常に区切り文字として機能する。
         */
        'hankaku_alphanum' => '([A-Za-z0-9,#\-\&\']+(?:[\;\"]|\.+|[\!\?]+)?)$',

        /* 全角カナグループ:
         *   - t2hs.rbではカタカナの小書き"ヵ"と"ヶ"をカタカナに含めていない。
         *   - このExtensionではそれを少しだけ緩めて、
         *     カタカナの後にあれば"ヵ"と"ヶ"もカタカナとする。
         *   - 他にもカタカナの後ろに全角の濁点・半濁点・長音記号があれば
         *     それもカタカナの一部とみなす。
         *     これにより濁点付き "ワ゛" などを2文字で入力した稀なケースにもルビが振れる。
         *   - さらに、Unicodeを前提として濁点付きワ-ヲと合字コトを追加しておく。
         */
        'zenkaku_katakana' => '((?:[\x{30A0}-\x{30FF}]+[゛゜]*)+)$',

        /* 半角カナグループ:
         *   - 青空文庫では半角カナを使わないルールがある。
         *   - t2hs.rbにも半角カナ用の正規表現は定義されていない。
         *   - このExtensionでは独自に定義しておく。
         *   - 半角の濁点・半濁点は半角カナに続くものだけを半角カナの一部とみなし、
         *     他の用途で別の文字種の後に置かれていることがあっても無視する。
         */
        'hankaku_katakana' => '((?:[ｦ-ﾝ]+[ﾞﾟｰ]*)+)$/',

        /* ひらがなグループ:
         *   - ひらがなにもルビを振ることがある。(例: "てふてふ《ちょうちょう》"）
         *   - t2hs.rbでは、ひらがなに全角の濁点・半濁点・長音記号を含めていない。
         *   - このExtensionではそれを少しだけ緩めて、
         *     全角の濁点・半濁点はひらがなの後ろにあればひらがなの一部とみなす。
         *     (例: "ん゛ん゛ん゛ん゛《たすけて》！")
         *   - 長音記号に関しては元の仕様に従い、ひらがなに含めない。
         *   - ひらがなにルビを振りたい稀なケースでも、実際は前の単語の送り仮名と
         *     "｜" で区切ることになる状況が多いのではないだろうか。これを踏まえて
         *     パターンの優先順位は最後にしておく。
         */
        'hiragana' => '((?:\p{Hiragana}+[゛゜]*)+)$/',
    ];

    public function getKugiri(): array
    {
        return $this->kugiri;
    }
}
