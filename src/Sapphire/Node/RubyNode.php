<?php

namespace Whojinn\Sapphire\Node;

use League\CommonMark\Inline\Element\AbstractInline;

class RubyNode extends AbstractInline
{
    private string $parent_char;
    private string $ruby_char;

    public function __construct(string $parent, string $ruby)
    {
        $this->parent_char = $parent;
        $this->ruby_char = $ruby;
    }

    public function getParentCher(): string
    {
        return $this->parent_char;
    }

    public function getRubyCher(): string
    {
        return $this->ruby_char;
    }
}
