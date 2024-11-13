<?php

namespace Mini\Cms\Theme;

interface FooterInterface
{
    /**
     * Build footer content.
     * @return string
     */
    public function render(): array;

    /**
     * Give footer file where to build footer from.
     * @param string $file eg footer.php
     * @return void
     */
    public function themeFile(string $file): void;

}