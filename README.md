# Sapphire
[Parsedownのルビ振り拡張機能](https://github.com/noisan/parsedown-rubytext)をリスペクトして作った、ルビ振り機能を追加するleague/commonmark用Extension

## 使い方
既に[PHP](https://www.php.net/)並びに[composer](https://getcomposer.org/)を使用できる環境にあることを前提とします。

### インストール
1. このリポジトリをクローンする
1. なにがしかのシェルで`composer require whojinn/sapphire`と入力する
    1. または、composer.jsonに以下の行を追加して`composer install`を実行
    ```
    {
        "require": {
            "whojinn/sapphire":"*"
        }
    }
    ```

### ルビの振り方のルール
1. ルビを振りたい単語の前に「｜」、単語の後ろに「《（ルビ文字）》」を入れる
    - 例:`シャッター破壊で｜Go Ahead《前進だ》！！`  
    -> `シャッター破壊で<ruby>Go Ahead<rt>前進だ</rt></ruby>！！`  
    -> シャッター破壊で<ruby>Go Ahead<rt>前進だ</rt></ruby>！！
1. ただし、文字種の違いでルビを振るべき単語を特定できる場合は「｜」を省略できる
    - 例: すなわち、第四極《だいよんきょく》とは力なり  
    -> `すなわち、<ruby>第四極<rt>だいよんきょく</rt></ruby>とは力なり`  
    -> すなわち、<ruby>第四極<rt>だいよんきょく</rt></ruby>とは力なり
1. ルビの分割数と単語の文字数が一致する場合はルビを半角スペースで分けることで単語ごとにルビを振ることができる
    - 例: 悪七兵衛景清《あく しち びょう え かげ きよ》  
    -> `<ruby>悪<rt>あく</rt>七<rt>しち</rt>兵<rt>びょう</rt>衛<rt>え</rt>景<rt>かげ</rt>清<rt>きよ</rt></ruby>`  
    -> <ruby>悪<rt>あく</rt>七<rt>しち</rt>兵<rt>びょう</rt>衛<rt>え</rt>景<rt>かげ</rt>清<rt>きよ</rt></ruby>
1. 上記に当てはまらない場合は、単語ごとにルビを振る
    - 例:`萌黄《もえぎ》白糸《しらいと》折鶴蘭《おりづるらん》`  
    -> `<ruby>萌黄<rt>もえぎ</rt></ruby><ruby>白糸<rt>しらいと</rt></ruby><ruby>折鶴蘭<rt>おりづるらん</rt></ruby>`  
    -> <ruby>萌黄<rt>もえぎ</rt></ruby><ruby>白糸<rt>しらいと</rt></ruby><ruby>折鶴蘭<rt>おりづるらん</rt></ruby>

### 設定
```php
// 以下、デフォルトでの設定
$config = [
    'sapphire' => [
        'use_sutegana' => false,    // trueにすると、ルビ文字のうち特定の小文字が大文字になる(ゅ→ゆ、ぁ→あ...etc)
        'use_rp_tag' => false,      // trueにすると、<rp>タグがルビにつく(<rp>（</rp><rt>ルビ</rt><rp>）</rp>)
        'use_danraku_atama' => false// trueにすると、段落の頭に全角スペースが付くようになる
    ]
];
```

## ライセンス
Apache License, Version 2.0  
- [英語原文](https://www.apache.org/licenses/LICENSE-2.0)
- [日本語参考訳](https://licenses.opensource.jp/Apache-2.0/Apache-2.0.html)