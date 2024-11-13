<?php

namespace Mini\Cms\Modules\FileSystem;

enum FileSizeEnum: int
{

    case XX_SMALL_FILES = 3011000;
    case X_SMALL_FILES = 6011000;
    case SMALL_FILES = 12011000;
    case XX_MEDIUM_FILES = 20011000;
    case X_MEDIUM_FILES = 25011000;
    case MEDIUM_FILES = 30011000;
    case XX_BIG_FILES = 40011000;
    case X_BIG_FILES = 45011000;
    case BIG_FILES = 1000011000;

}
