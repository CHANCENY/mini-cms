<?php

namespace Mini\Cms\Theme;

class MarkUp
{
    public Theme $theme;
    private ?string $markup;

    /**
     * @throws \Exception
     */
    public function __construct(string $theme_name)
    {
        if($theme_name) {
            $override = Theme::override($theme_name);
            $this->theme = $override;
        }
        else {
            throw new \Exception("Theme name is required");
        }
    }

    /**
     * @throws \Exception
     */
    public function markup(string $theme_file_name, array $options = []): ?string
    {
        $this->markup = $this->theme->view($theme_file_name, $options);
        return $this->markup;
    }

    /**
     * Create the mark up html from theme file.
     * @param string $theme_name
     * @param string $theme_file_name
     * @param array $options
     * @return string|null
     * @throws \Exception
     */
    public static function create(string $theme_name, string $theme_file_name, array $options = []): ?string
    {
        return (new MarkUp($theme_name))->markup($theme_file_name,$options);
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function getMarkup(): ?string
    {
        return $this->markup;
    }


}