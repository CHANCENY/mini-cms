<?php

namespace Mini\Cms\Modules\Streams;

interface StreamWrapper
{
    /**
     * This method is called immediately after the wrapper is initialized (f.e. by fopen() and file_get_contents()).
     *
     * @param string $path
     *   Specifies the URL that was passed to the original function.
     * @param string $mode
     *   The mode used to open the file, as detailed for fopen().
     * @param int $options
     *   Holds additional flags set by the streams API. It can hold one or more of the following
     *   values OR'd together.
     * @param string $opened_path
     *   If the path is opened successfully, and STREAM_USE_PATH is set in options, opened_path
     *   should be set to the full path of the file/resource that was actually opened.
     *
     * @return bool
     *   Returns TRUE on success or FALSE on failure.
     */
    public function stream_open(string $path, string $mode, int $options, string $opened_path = NULL): bool;

    /**
     * This method is called in response to fclose().
     *
     * No value is returned.
     */
    public function stream_close(): void;

    /**
     * This method is called in response to fread() and fgets().
     *
     * @param int $count
     *   How many bytes of data from the current position should be returned.
     *
     * @return string
     *   If there are less than count bytes available, return as many as are available. If no
     *   more data is available, return either FALSE or an empty string.
     */
    public function stream_read(int $count): string;

    /**
     * This method is called in response to feof().
     *
     * @return bool
     *   Should return TRUE if the read/write position is at the end of the stream and if no
     *   more data is available to be read, or FALSE otherwise.
     */
    public function stream_eof(): bool;

    /**
     * Seeks to specific location in a stream.
     *
     * @param int $offset
     *   The stream offset to seek to.
     * @param int $whence
     *   Possible values:
     *     - SEEK_SET - Set position equal to offset bytes.
     *     - SEEK_CUR - Set position to current location plus offset.
     *     - SEEK_END - Set position to end-of-file plus offset.
     *
     * @return bool
     *   Return TRUE if the position was updated, FALSE otherwise.
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool;

    /**
     * Retrieve information about a file resource.
     *
     * @return array
     *   See stat().
     */
    public function stream_stat(): array;

    public function url_stat($uri, $flags);

    public function mkdir($uri, $mode, $options);

    public function stream_metadata($path, $option, $value);

    public function dir_opendir($path, $options);

    public function dir_readdir();

    public function dir_rewinddir();

    public function dir_closedir();

    public function rmdir($path, $options);

}