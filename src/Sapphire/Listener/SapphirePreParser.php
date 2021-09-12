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

namespace Whojinn\Sapphire\Listener;

use League\CommonMark\Event\DocumentPreParsedEvent;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

/**
 * パース前のマークダウンに対して処理を行うためのクラス。
 * 必要になったらなにか作る予定.
 */
class SapphirePreParser implements ConfigurationAwareInterface
{
    private $config;
    
    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function preParse(DocumentPreParsedEvent $event)
    {
        foreach ($event->getMarkdown()->getLines() as $markdown) {
            if ($this->config->get('sapphire/use_danraku_atama') && mb_ereg('^[^\p{blank}]', $markdown)) {
                $markdown = '　'.$markdown;
            }
        }
    }
}
