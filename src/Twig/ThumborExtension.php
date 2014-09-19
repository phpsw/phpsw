<?php

namespace PHPSW\Twig;

class ThumborExtension extends \Twig_Extension
{
    public function __construct(\Thumbor\Url\BuilderFactory $builder)
    {
        $this->builder = $builder;
    }

    public function getFunctions()
    {
        return [
            'avatar'    => new \Twig_Function_Method($this, 'avatar'),
            'hero'      => new \Twig_Function_Method($this, 'hero'),
            'photo'     => new \Twig_Function_Method($this, 'photo'),
            'speaker'   => new \Twig_Function_Method($this, 'speaker'),
            'thumbnail' => new \Twig_Function_Method($this, 'thumbnail'),
            'thumbor'   => new \Twig_Function_Method($this, 'thumbor')
        ];
    }

    public function avatar($url, $size = 'small')
    {
        switch ($size) {
            case 'small':  $width = $height = 36; break;
            case 'medium': $width = $height = 72; break;
            case 'large':  $width = $height = 400; break;
        }

        return $this->thumbor($url)->resize($width, $height);
    }

    public function hero($url)
    {
        return $this->thumbor($url)->resize(1200, 400);
    }

    public function photo($url)
    {
        return $this->thumbor($url)->resize(1000, 800);
    }

    public function speaker($url)
    {
        return $this->hero($url)
            ->addFilter('brightness', 30)
            ->addFilter('grayscale')
            ->addFilter('contrast', 20)
            ->addFilter('rgb', 6, 10, 15)
            ->smartCrop(true)
        ;
    }

    public function thumbnail($url)
    {
        return $this->thumbor($url)->resize(80, 60);
    }

    public function thumbor($url)
    {
        return $this->builder->url($url);
    }

    public function getName()
    {
        return "thumbor";
    }
}
