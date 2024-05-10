<?php

namespace Mini\Cms\Modules\Messenger;

use Mini\Cms\Modules\Storage\Tempstore;
use Mini\Cms\Theme\Theme;

abstract class MessengerBuilder
{
    protected $messages = [];

    private string $theme_message = 'default_message.php';

    private string $theme_error_message = 'default_error_message.php';

    private string $theme_success_message = 'default_success_message.php';

    private string $theme_warning_message = 'default_warning_message.php';

    public function __construct()
    {
        $this->messages = Tempstore::load('messenger') ?? [];
    }

    public function setThemeMessage(string $theme_message): void
    {
        $this->theme_message = $theme_message;
    }

    public function setThemeErrorMessage(string $theme_error_message): void
    {
        $this->theme_error_message = $theme_error_message;
    }

    public function setThemeSuccessMessage(string $theme_success_message): void
    {
        $this->theme_success_message = $theme_success_message;
    }

    public function setThemeWarningMessage(string $theme_warning_message): void
    {
        $this->theme_warning_message = $theme_warning_message;
    }

    public function addMessage($message): void
    {
        $this->messages[] = [
            'message' => $message,
            'type' => 'info',
            'theme_message' => $this->theme_message,
        ];
        Tempstore::save('messenger', $this->messages);
    }

    public function addErrorMessage($message): void
    {
        $this->messages[] = [
            'message' => $message,
            'type' => 'error',
            'theme_message' => $this->theme_error_message,
        ];
        Tempstore::save('messenger', $this->messages);
    }

    public function addSuccessMessage($message): void
    {
        $this->messages[] = [
            'message' => $message,
            'type' => 'success',
            'theme_message' => $this->theme_success_message,
        ];
        Tempstore::save('messenger', $this->messages);
    }

    public function addWarningMessage($message): void
    {
        $this->messages[] = [
            'message' => $message,
            'type' => 'warning',
            'theme_message' => $this->theme_warning_message,
        ];
        Tempstore::save('messenger', $this->messages);
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getMessages(): mixed
    {
        $theme = Tempstore::load('theme_loaded');
        $message_line = null;
       foreach ($this->messages as $key=>$message) {
           if($theme instanceof Theme) {
               $message_line .= $theme->view($message['theme_message'], $message);
           }
       }
       Tempstore::save('messenger',[]);
       return $message_line;
    }
}