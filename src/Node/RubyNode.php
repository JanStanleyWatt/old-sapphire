<?php

declare(strict_types=1);
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

namespace Whojinn\Sapphire\Node;

use League\CommonMark\Node\Inline\AbstractStringContainer;

/**
 * <ruby>タグを担当するノード。
 */
class RubyNode extends AbstractStringContainer
{
    private string $parent_char = '';
    private string $ruby_char = '';

    public function __construct(string $ruby_char, array $data = [])
    {
        parent::__construct($ruby_char, $data);
        $this->ruby_char = $ruby_char;
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
