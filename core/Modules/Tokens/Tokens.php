<?php

namespace Mini\Cms\Modules\Tokens;

use Mini\Cms\Modules\Extensions\Extensions;
use Mini\Cms\Theme\MarkUp;

/**
 * @class Tokens This class make use of hook _tokens_info  and _token_replacement to build up the makeups
 */

class Tokens
{
    private array $tokens_lists;
    private string $content;

    private array $options;

    public function __construct()
    {
        $this->tokens_lists = [];
        Extensions::runHooks('_tokens_info',[&$this->tokens_lists]);
    }

    /**
     * @param string|MarkUp $content string data containing tokens.
     * @param array $options array of key that is group name of tokens
     * @return string
     */
    public function replace(string|MarkUp $content, array $options): string
    {
        if($content instanceof MarkUp) {
            $this->content = $content->getMarkup() ?? '';
        }
        else {
            $this->content = $content;
        }
        $this->options = $options;
        $data_keys = array_keys($options);
        $tokens_for_hooks = [];
        $data = [];
        foreach ($data_keys as $key) {
            if(isset($this->tokens_lists[$key]['tokens'])) {
                $tokens_for_hooks[$key] = $this->tokens_lists[$key]['tokens'];
                $data[$key] = $options[$key];
            }
        }
        $placement_data = [];
        foreach ($data as $token_type=>$bubble_data) {
            $tokens = $tokens_for_hooks[$token_type];
            Extensions::runHooks('_token_replacement',
                [
                    $token_type,
                    $bubble_data,
                    &$placement_data,
                    $tokens
                ]
            );
        }
        foreach ($placement_data as $key=>$value) {
            $this->content = str_replace("[$key]",$value, $this->content);
        }
        return $this->content;
    }

    public function getTokensLists(): array
    {
        return $this->tokens_lists;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getOptions(): array
    {
        return $this->options;
    }



}