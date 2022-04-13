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

namespace Whojinn\Sapphire\Parser;

use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;
use Whojinn\Sapphire\Node\RubyParentNode;

/**
 * ルビ記号のうち、区切り文字を制御させるクラス。
 */
class SapphireSeparateParser implements InlineParserInterface
{
    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::string('｜');
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        $cursor = $inlineContext->getCursor();
        $restore = $cursor->saveState();
        $parent_char = '';

        if ('《' === $cursor->peek()) {
            return false;
        }

        $cursor->advance();

        /**
         * 行末近くのエスケープされていないルビ記号「《」にマッチングするまでカーソルを進めると共に、
         * マッチングしたらアサーション以外の文字を文字列に加える.
         */
        $parent_char = $cursor->match('/^(.+?)(?=(?<!\\\)《)/u');

        /*
         * ルビ記号が見つからなかったらレストアしてfalseを返す
         * */
        if (null === $parent_char || $cursor->isAtEnd()) {
            $cursor->restoreState($restore);

            return false;
        }
        /*
         * ルビ記号より前に区切り文字が見つかったら、そちらに処理を譲る
         */
        if (preg_match('/(.+)(?<!\\\)((\\\)(\\\))*｜/u', $cursor->getPreviousText())) {
            $cursor->restoreState($restore);
            $inlineContext->getContainer()->appendChild(new Text('｜'));
            $cursor->advance();

            return true;
        }

        // 頭文字からエスケープ文字を除く(一つだけ)
        $parent_char = str_replace('\｜', '｜', str_replace('\《', '《', $parent_char));

        $inlineContext->getContainer()->appendChild(new RubyParentNode($parent_char));

        return true;
    }
}
