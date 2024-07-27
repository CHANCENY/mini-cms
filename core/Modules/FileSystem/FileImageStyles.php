<?php

namespace Mini\Cms\Modules\FileSystem;

class FileImageStyles
{
    private array $styles;
    private $default;

    public function __construct()
    {
        //TODO Getting images styles here.
        $this->styles = [
            'small'=>[
                'width' => 200,
                'height' => 150,
                'default'=>true
            ],
            'medium' => [
                'width' => 300,
                'height' => 250,
            ],
            'large' => [
                'width' => 500,
                'height' => 450,
            ]
        ];

        $this->default = array_filter($this->styles,function($style){
           return !empty($style['default']);
        });

        if(empty($this->default)){
            $this->switchStyle('small');
        }
    }

    public function getStyles(): array
    {
        return $this->styles;
    }

    public function setStyles(string $style_name, array $styles): FileImageStyles
    {
        $this->styles[$style_name] = $styles;
        return $this;
    }

    public function getStyle(string $style_name): ?array
    {
        return $this->styles[$style_name] ?? null;
    }

    public function getDefaultStyle(): array
    {
        return reset($this->default);
    }

    public function style(): string
    {
        return array_keys($this->default)[0] ?? '';
    }

    public function switchStyle(string $style_name): void
    {
        if(isset($this->styles[$style_name])) {
           foreach ($this->styles as $key=>$style) {
               if($key === $style_name) {
                   $this->default = [$key => $style];
                   break;
               }
           }
        }
    }
}