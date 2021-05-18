<?php

namespace Whojinn\Sapphire\Node;

use League\CommonMark\Inline\Element\AbstractStringContainer;

/**
 * <ruby>タグを担当するノード。
 */
class RubyNode extends AbstractStringContainer
{
    private string $parent_char = '';
    private string $ruby_char = '';

    public function __construct(string $ruby_char, array $data = [])
    {
        $this->ruby_char = $ruby_char;
        $this->data = $data;
    }

    public function setParentString(string $parent_char)
    {
        $this->parent_char = $parent_char;
    }

    public function getParentString(): string
    {
        return $this->parent_char;
    }

    public function getRubyString(): string
    {
        return $this->ruby_char;
    }
}
