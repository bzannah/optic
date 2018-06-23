<?php


namespace AppBundle\Service;


use Doctrine\Common\Cache\Cache;
use Knp\Bundle\MarkdownBundle\MarkdownParserInterface;

class MarkdownTransformer
{
    /**
     * @var MarkdownParserInterface
     */
    private $markdownParser;
    /**
     * @var Cache
     */
    private $cache;

    public function __construct(MarkdownParserInterface $markdownParser, Cache $cache)
    {
        $this->markdownParser = $markdownParser;
        $this->cache = $cache;
    }

    public function parse(string $str)
    {
        $cache = $this->cache;

        $cacheKey = md5($str);
        if($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
        }
        $str  = $this->markdownParser
            ->transformMarkdown($str);

        $cache->save($cacheKey, $str);
        return $str;
    }
}