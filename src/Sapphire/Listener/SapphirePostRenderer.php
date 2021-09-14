<?php declare(strict_types=1);
namespace Whojinn\Sapphire\Listener;

use League\CommonMark\Event\DocumentRenderedEvent;
use League\CommonMark\Output\RenderedContent;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;

class SapphirePostRenderer implements ConfigurationAwareInterface
{
    private $config;
    private $pattern = '<p>(?:(<[(a)|(b)|(em)|(strong)|(code)|(del)|(dfn)|(mark)|(ruby)]>))';

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    public function postrender(DocumentRenderedEvent $event)
    {
        // configで有効化しているときのみ処理を行う
        if ($this->config->get('sapphire/use_danraku_atama')) {
            $html = $event->getOutput()->getContent();
            $document = $event->getOutput()->getDocument();
            $replaced ="";

            $replaced .= mb_ereg_replace($this->pattern, "<p>　", $html);

            $event->replaceOutput(new RenderedContent($document, $replaced));
        }
    }
}
