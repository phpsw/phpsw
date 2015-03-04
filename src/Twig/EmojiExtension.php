<?php

namespace PHPSW\Twig;

use Emojione\Emojione;

class EmojiExtension extends \Twig_Extension
{
    public function getFilters()
    {
        Emojione::$ascii = true;
        Emojione::$imageType = 'svg';

        return [
            'emoji' => new \Twig_Filter_Method($this, 'emoji')
        ];
    }

    public function emoji($text)
    {
        return Emojione::toImage($text);
    }

    public function getName()
    {
        return 'emoji';
    }
}
