<?php

namespace Whojinn\Sapphire\Node;

use League\CommonMark\Inline\Element\AbstractStringContainer;
use League\CommonMark\Inline\Element\Text;

class RubyNode extends AbstractStringContainer
{
    public function isContainer(): bool
    {
        return true;
    }

    public function __construct(array $parent, array $ruby, array $data = [])
    {
        assert(count($parent) === count($ruby));

        for ($i = 0; $i < count($ruby); ++$i) {
            $this->appendChild(new Text($parent[$i]));
            $this->appendChild(new RubyChildNode($ruby[$i]));
        }

        $this->data = $data;
    }
}
