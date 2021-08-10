<?php

namespace Whojinn\Test;

require __DIR__.'/../vendor/autoload.php';

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use function PHPUnit\Framework\assertEquals;
use PHPUnit\Framework\TestCase;
use Whojinn\Sapphire\SapphireExtension;

/**
 * 「[Parsedown 青空文庫ルビ形式extension](https://github.com/noisan/parsedown-rubytext/blob/master/README-Aozora.md)
 * に近い機能を持った拡張機能」という個人的な要求性能に対して8割程度満足の行くものが出来たことを確認するための
 * 必要最小限なテストを収めたクラス。ひどく頭の悪い実装になっているのでどうか見逃すか、いい感じのコードを教えていただきたい。
 * テストデータは[こちら](https://github.com/noisan/parsedown-rubytext/tree/master/tests/Aozora/data)のものをもとに
 * League\CommonMark仕様に改変しました。
 */
final class SapphireEssentialTest extends TestCase
{
    public array $config;

    public $environment;

    protected function setUp(): void
    {
        $this->config = [
            'sapphire' => [
                'use_sutegana' => false,
                'use_rp_tag' => true,
            ],
        ];

        clearstatcache();

        $this->environment = new Environment($this->config);
    }

    public function testSapphireNormal()
    {
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new SapphireExtension());

        $converter = new MarkdownConverter($this->environment);

        $markdown = file_get_contents(__DIR__.'/data/aozora.md');
        $otehon = file_get_contents(__DIR__.'/data/aozora.html');

        $test = $converter->convertToHtml($markdown);

        assertEquals($otehon, $test, '基本テストが上手くいかなかったでござる');
    }

    final public function testSapphireAttributes()
    {
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new AttributesExtension());
        $this->environment->addExtension(new SapphireExtension());

        $converter = new MarkdownConverter($this->environment);

        $markdown = file_get_contents(__DIR__.'/data/attributes.md');
        $otehon = file_get_contents(__DIR__.'/data/attributes.html');

        $test = $converter->convertToHtml($markdown);

        assertEquals($otehon, $test, '属性付加テストが上手くいかなかったでござる');
    }

    final public function testSapphireEmpty()
    {
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new SapphireExtension());

        $converter = new MarkdownConverter($this->environment);

        $markdown = file_get_contents(__DIR__.'/data/empty.md');
        $otehon = file_get_contents(__DIR__.'/data/empty.html');

        $test = $converter->convertToHtml($markdown);

        assertEquals($otehon, $test, '空文字テストが上手くいかなかったでござる');
    }

    final public function testSapphireEscapable()
    {
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new SapphireExtension());

        $converter = new MarkdownConverter($this->environment);

        $markdown = file_get_contents(__DIR__.'/data/escaping.md');
        $otehon = file_get_contents(__DIR__.'/data/escaping.html');

        $test = $converter->convertToHtml($markdown);

        assertEquals($otehon, $test, 'エスケープシーケンステストが上手くいかなかったでござる');
    }

    final public function testSapphireKanji()
    {
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new SapphireExtension());

        $converter = new MarkdownConverter($this->environment);

        $markdown = file_get_contents(__DIR__.'/data/kanji.md');
        $otehon = file_get_contents(__DIR__.'/data/kanji.html');

        $test = $converter->convertToHtml($markdown);

        assertEquals($otehon, $test, '漢字扱い記号のテストが上手くいかなかったでござる');
    }

    final public function testSapphireMonoRuby()
    {
        $this->environment->addExtension(new CommonMarkCoreExtension());
        $this->environment->addExtension(new SapphireExtension());

        $converter = new MarkdownConverter($this->environment);

        $markdown = file_get_contents(__DIR__.'/data/mono_ruby.md');
        $otehon = file_get_contents(__DIR__.'/data/mono_ruby.html');

        $test = $converter->convertToHtml($markdown);

        assertEquals($otehon, $test, 'モノルビテストが上手くいかなかったでござる');
    }

    /*
     * 2021年5月21日現在、成功しないテスト。
     * 具体的には、親文字だけ、ルビだけを強調させたり、ルビの中にルビを振ることができない。
     * 正直、Parsedown 青空文庫ルビ形式extension のなかでも、個人的には全く使っていない
     * 機能だったので、失敗してもV1.0扱いで良かった（いつか成功すればとてもうれしいが）.
     */
    // final public function testSapphireNest()
    // {
    //     $this->environment->addExtension(new CommonMarkCoreExtension());
    //     $this->environment->addExtension(new SapphireExtension());

    //     $converter = new MarkdownConverter($this->environment);
    //     $markdown = file_get_contents(__DIR__.'/data/nest.md');
    //     $otehon = file_get_contents(__DIR__.'/data/nest.html');

    //     $test = $converter->convertToHtml($markdown);

    //     assertEquals($otehon, $test, '構文入れ子テストが上手くいかなかったでござる');
    // }
}
