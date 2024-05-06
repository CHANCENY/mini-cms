<?php

namespace Mini\Cms\Modules\FileSystem;

class FileImageStyles
{
    private array $styles;

    public function __construct()
    {
        $this->styles = [
            'small'=>[
                'width' => 200,
                'height' => 150,
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
        return $this->styles['small'];
    }
}