<?php

namespace Whojinn\Sapphire\Listener;

use League\CommonMark\Event\DocumentPreParsedEvent;

class SapphirePreParser
{
    public function preParse(DocumentPreParsedEvent $event)
    {
        $markdown = $event->getMarkdown();
    }
}
