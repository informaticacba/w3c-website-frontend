<?php

namespace App\Tests\Controller;

use Symfony\Component\Panther\PantherTestCase;

class DefaultControllerTest extends PantherTestCase
{
    protected $client;

    public function setUp(): void
    {
        $browser = array_key_exists('PANTHER_BROWSER', $_SERVER) ? $_SERVER['PANTHER_BROWSER'] : self::CHROME;
        $this->client  = static::createPantherClient(['browser' => $browser]);
    }

    /**
     * @dataProvider provider
     */
    public function testIndex(string $lang, string $title, string $langPrefix, string $path): void
    {
        $crawler = $this->client->request('GET', $langPrefix . $path);

        $this->assertSelectorAttributeContains('html', 'lang', $lang);
        $this->assertSelectorTextSame('h1', $title);
    }

    public function provider()
    {
        return [
            ['lang' => 'en', 'title' => 'Home', 'lang_prefix' => '', 'path' => '/'],
            ['lang' => 'en', 'title' => 'Landing Page', 'lang_prefix' => '', 'path' => '/landing-page/'],
            ['lang' => 'en', 'title' => 'Blog listing', 'lang_prefix' => '', 'path' => '/blog/'],
            ['lang' => 'ja', 'title' => 'Homepage', 'lang_prefix' => '/ja', 'path' => '/'],
            ['lang' => 'ja', 'title' => '日本語で Landing Page', 'lang_prefix' => '/ja', 'path' => '/landing-page/'],
            ['lang' => 'ja', 'title' => 'Blog listing', 'lang_prefix' => '/ja', 'path' => '/blog/']
        ];
    }
}
