<?php

namespace Whojinn\Sapphire\Renderer;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use Whojinn\Sapphire\Node\RubyChildNode;

class SapphireRubyRender implements InlineRendererInterface
{
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        // RubyNode以外が$inlineに収まっていたらエラーを吐く
        if (!($inline instanceof RubyChildNode)) {
            throw new \InvalidArgumentException('Incompatible inline type: '.get_class($inline));
        }

        return new HtmlElement('rt', [], $htmlRenderer->renderInlines($inline->children()));
    }
}
