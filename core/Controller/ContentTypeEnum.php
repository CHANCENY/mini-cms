<?php

namespace Mini\Cms\Controller;


enum ContentTypeEnum: string
{
    // Text
    case TEXT_PLAIN = 'text/plain';
    case TEXT_HTML = 'text/html';
    case TEXT_CSS = 'text/css';
    case TEXT_XML = 'text/xml';
    case TEXT_CSV = 'text/csv';
    case TEXT_JAVASCRIPT = 'text/javascript';
    case TEXT_MARKDOWN = 'text/markdown';

    // Application
    case APPLICATION_JSON = 'application/json';
    case APPLICATION_XML = 'application/xml';
    case APPLICATION_X_WWW_FORM_URLENCODED = 'application/x-www-form-urlencoded';
    case APPLICATION_PDF = 'application/pdf';
    case APPLICATION_MS_WORD = 'application/msword';
    case APPLICATION_MS_EXCEL = 'application/vnd.ms-excel';
    case APPLICATION_MS_POWERPOINT = 'application/vnd.ms-powerpoint';
    case APPLICATION_ZIP = 'application/zip';
    case APPLICATION_GZIP = 'application/gzip';
    case APPLICATION_TAR = 'application/x-tar';
    case APPLICATION_RAR = 'application/x-rar-compressed';
    case APPLICATION_JAVASCRIPT = 'application/javascript';
    case APPLICATION_ECMASCRIPT = 'application/ecmascript';

    // Image
    case IMAGE_JPEG = 'image/jpeg';
    case IMAGE_PNG = 'image/png';
    case IMAGE_GIF = 'image/gif';
    case IMAGE_BMP = 'image/bmp';
    case IMAGE_WEBP = 'image/webp';
    case IMAGE_TIFF = 'image/tiff';
    case IMAGE_SVG = 'image/svg+xml';

    case IMAGE_JPG = 'image/jpg';

    // Audio
    case AUDIO_MPEG = 'audio/mpeg';
    case AUDIO_OGG = 'audio/ogg';
    case AUDIO_WAV = 'audio/wav';
    case AUDIO_FLAC = 'audio/flac';
    case AUDIO_AAC = 'audio/aac';
    case AUDIO_MIDI = 'audio/midi';

    // Video
    case VIDEO_MP4 = 'video/mp4';
    case VIDEO_WEBM = 'video/webm';
    case VIDEO_OGG = 'video/ogg';
    case VIDEO_3GPP = 'video/3gpp';
    case VIDEO_3GPP2 = 'video/3gpp2';
    case VIDEO_AVI = 'video/x-msvideo';
    case VIDEO_QUICKTIME = 'video/quicktime';
    case VIDEO_MATROSKA = 'video/x-matroska';
    case VIDEO_FLV = 'video/x-flv';

    // Font
    case FONT_OTF = 'font/otf';
    case FONT_TTF = 'font/ttf';
    case FONT_WOFF = 'font/woff';
    case FONT_WOFF2 = 'font/woff2';
    case FONT_EOT = 'application/vnd.ms-fontobject';

    // Multipart
    case MULTIPART_FORM_DATA = 'multipart/form-data';

    // Model
    case MODEL_3MF = 'model/3mf';
    case MODEL_STL = 'model/stl';
    case MODEL_OBJ = 'model/obj';
    case MODEL_GLTF = 'model/gltf+json';
    case MODEL_STEP = 'model/step';
    case MODEL_VRML = 'model/vrml';
    case MODEL_X3D_XML = 'model/x3d+xml';
    case MODEL_X3D_BINARY = 'model/x3d+binary';
    case MODEL_X3D_VRML = 'model/x3d+vrml';
    case MODEL_X3D_FASTINFOSET = 'application/x3d+fastinfoset';
    case MODEL_X3D_VRML_BINARY = 'model/x3d-vrml+binary';
}
