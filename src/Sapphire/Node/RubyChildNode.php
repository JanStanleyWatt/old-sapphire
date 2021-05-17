<?php

namespace Whojinn\Sapphire\Node;

use League\CommonMark\Inline\Element\AbstractStringContainer;
use League\CommonMark\Inline\Element\Text;

class RubyChildNode extends AbstractStringContainer
{
    public function isContainer(): bool
    {
        return true;
    }

    public function __construct(string $content, array $data = [])
    {
        $this->appendChild(new Text($content, $data));
    }
}
