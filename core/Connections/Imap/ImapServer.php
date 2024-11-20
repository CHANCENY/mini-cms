<?php

namespace Mini\Cms\Connections\Imap;

use Mini\Cms\Configurations\ConfigFactory;

class ImapServer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $mailinbox;
    private string $mailoutbox;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $config = new ConfigFactory();
        $imap_server = $config->get('Imap');
        if (empty($imap_server)) {
            throw new \Exception('Imap server not found');
        }
        $this->host = $imap_server['host'];
        $this->port = $imap_server['port'];
        $this->username = $imap_server['username'];
        $this->password = $imap_server['password'];
        $this->mailinbox = "{{$this->host}:{$this->port}/imap/ssl}INBOX";
        $this->mailoutbox = "{{$this->host}:{$this->port}/imap/ssl}OUTBOX";
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function readInbox(): array
    {
        $inbox = imap_open($this->mailinbox, $this->username, $this->password);
        if ($inbox === false) {
            throw new \Exception('Failed to open mailbox: ' . imap_last_error());
        }

        $emails = imap_search($inbox, 'ALL');
        $emails_found = [];
        if ($emails) {
            rsort($emails);
            foreach ($emails as $email_number) {
                $overview = imap_fetch_overview($inbox, $email_number, 0);
                $message = imap_fetchbody($inbox, $email_number, 2);
                $object = (array) reset($overview);
                $emails_found[] = [
                    'message' => $message,
                    ...$object
                ];
            }
        }
        $this->closeConnection($inbox);
        return $emails_found;
    }

    public function deleteEmails(array $email_numbers): bool
    {
        $inbox = imap_open($this->mailinbox, $this->username, $this->password);
        if ($inbox) {
            foreach ($email_numbers as $email_number) {
                imap_delete($inbox, $email_number);
            }
            imap_expunge($inbox);
            $this->closeConnection($inbox);
            return true;
        }
        return false;
    }

    public function fetchAttachments(int $email_number, string $save_dir): array
    {
        $inbox = imap_open($this->mailinbox, $this->username, $this->password);
        if ($inbox === false) {
            throw new \Exception('Failed to open mailbox: ' . imap_last_error());
        }

        $structure = imap_fetchstructure($inbox, $email_number);
        $attachments = [];

        if (isset($structure->parts) && count($structure->parts)) {
            for ($i = 0; $i < count($structure->parts); $i++) {
                $part = $structure->parts[$i];
                if ($part->ifdparameters) {
                    foreach ($part->dparameters as $param) {
                        if (strtolower($param->attribute) === 'filename') {
                            $filename = $param->value;
                            $content = imap_fetchbody($inbox, $email_number, $i + 1);
                            if ($part->encoding == 3) { // BASE64
                                $content = base64_decode($content);
                            } elseif ($part->encoding == 4) { // QUOTED-PRINTABLE
                                $content = quoted_printable_decode($content);
                            }
                            file_put_contents($save_dir . DIRECTORY_SEPARATOR . $filename, $content);
                            $attachments[] = $filename;
                        }
                    }
                }
            }
        }

        $this->closeConnection($inbox);
        return $attachments;
    }

    public function moveEmails(array $email_numbers, string $folder): bool
    {
        $inbox = imap_open($this->mailinbox, $this->username, $this->password);
        if ($inbox) {
            foreach ($email_numbers as $email_number) {
                if (!imap_mail_move($inbox, $email_number, $folder)) {
                    throw new \Exception('Failed to move email: ' . imap_last_error());
                }
            }
            imap_expunge($inbox);
            $this->closeConnection($inbox);
            return true;
        }
        return false;
    }

    public function markAsRead(int $email_number): bool
    {
        $inbox = imap_open($this->mailinbox, $this->username, $this->password);
        if ($inbox) {
            imap_setflag_full($inbox, (string) $email_number, '\\Seen');
            $this->closeConnection($inbox);
            return true;
        }
        return false;
    }

    public function markAsUnread(int $email_number): bool
    {
        $inbox = imap_open($this->mailinbox, $this->username, $this->password);
        if ($inbox) {
            imap_clearflag_full($inbox, (string) $email_number, '\\Seen');
            $this->closeConnection($inbox);
            return true;
        }
        return false;
    }

    private function closeConnection($imap_connection): void
    {
        if ($imap_connection) {
            imap_close($imap_connection);
        }
    }

    public static function saveSettings(array $settings): bool
    {
        $config = new ConfigFactory();
        $imap_server = $config->get('Imap');

        if (!empty($settings['host']) && !empty($settings['port']) && !empty($settings['username']) && !empty($settings['password'])) {
            $config->set('Imap', $settings);
            return $config->save();
        } else {
            $imap_server['host'] = $settings['host'] ?? $imap_server['host'];
            $imap_server['port'] = $settings['port'] ?? $imap_server['port'];
            $imap_server['username'] = $settings['username'] ?? $imap_server['username'];
            $imap_server['password'] = $settings['password'] ?? $imap_server['password'];

            $config->set('Imap', $imap_server);
            return $config->save();
        }
    }
}
