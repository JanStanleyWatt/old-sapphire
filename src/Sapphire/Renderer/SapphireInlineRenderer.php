<?php

namespace Whojinn\Sapphire\Renderer;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use Whojinn\Sapphire\Node\RubyNode;

class SapphireInlineRenderer implements InlineRendererInterface
{
    private array $parent_chars;
    private array $ruby_chars;
    private bool $insert_rp;

    private function MargeRubyElement(string $char, bool $insert_rp = true): string
    {
        return $insert_rp ? '<rp>（</rp>'.'<rt>'.$char.'</rt>'.'<rp>）</rp>'
                        : '<rt>'.$char.'</rt>';
    }

    /**
     * コンストラクタ
     *
     * @param bool $insert_rp trueにするとルビに<rp>(<rp>...<rp>(<rp>がつくようになる。デフォルトはtrue
     */
    public function __construct(bool $insert_rp = true)
    {
        $this->insert_rp = $insert_rp;
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        // <ruby>タグの中身
        $element = '';

        // プロパティ初期化
        $this->ruby_chars = [];
        $this->parent_chars = [];

        // RubyNode以外が$inlineに収まっていたらエラーを吐く
        if (!($inline instanceof RubyNode)) {
            throw new \InvalidArgumentException('Incompatible inline type: '.get_class($inline));
        }

        $parent = $inline->getParentCher();
        $ruby = $inline->getRubyCher();
        $devided_ruby = mb_split(' ', $ruby);
        $ruby_count = count($devided_ruby);

        // モノルビにできる場合は親文字を一文字ずつ分割する
        // モノルビの条件：親文字の文字数とルビの分割数が一致
        if ($ruby_count > 1 and mb_strlen($parent) === $ruby_count) {
            foreach (mb_str_split($parent) as $char) {
                array_push($this->parent_chars, $char);
            }
            foreach ($devided_ruby as $char) {
                $ruby = $char === '' ? $this->margeRubyElement($char, false) : $this->margeRubyElement($char, $this->insert_rp);
                array_push($this->ruby_chars, $ruby);
            }
        } else {
            $this->parent_chars = [$parent];

            // ルビに何もない場合は空の<rt>タグを入れる
            $ruby = $ruby === '' ? $this->MargeRubyElement('', false) : $this->margeRubyElement($inline->getRubyCher(), $this->insert_rp);
            $this->ruby_chars = [$ruby];
        }

        for ($i = 0; $i < count($this->ruby_chars); ++$i) {
            $element .= $this->parent_chars[$i];
            $element .= $this->ruby_chars[$i];
        }

        return new HtmlElement('ruby', [], $element);
    }
}
