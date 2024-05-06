<?php

namespace Mini\Cms\Modules\MetaTag;

enum MetagEnum: string
{
    // Standard meta tags
    case Title = '<meta name="title" content="{{VALUE}}">';
    case Description = '<meta name="description" content="{{VALUE}}">';
    case Keywords = '<meta name="keywords" content="{{VALUE}}">';
    case Author = '<meta name="author" content="{{VALUE}}">';
    case Charset = '<meta charset="{{VALUE}}">';

    // Open Graph meta tags
    case OGTitle = '<meta property="og:title" content="{{VALUE}}">';
    case OGDescription = '<meta property="og:description" content="{{VALUE}}">';
    case OGType = '<meta property="og:type" content="{{VALUE}}">';
    case OGImage = '<meta property="og:image" content="{{VALUE}}">';
    case OGURL = '<meta property="og:url" content="{{VALUE}}">';
    case OGSiteName = '<meta property="og:site_name" content="{{VALUE}}">';

    // Twitter meta tags
    case TwitterTitle = '<meta name="twitter:title" content="{{VALUE}}">';
    case TwitterDescription = '<meta name="twitter:description" content="{{VALUE}}">';
    case TwitterImage = '<meta name="twitter:image" content="{{VALUE}}">';
    case TwitterCard = '<meta name="twitter:card" content="{{VALUE}}">';

    // Other common meta tags
    case Viewport = '<meta name="viewport" content="{{VALUE}}">';
    case Robots = '<meta name="robots" content="{{VALUE}}">';
    case Canonical = '<link rel="canonical" href="{{VALUE}}">';

    // Additional meta tags
    case ApplicationName = '<meta name="application-name" content="{{VALUE}}">';
    case Generator = '<meta name="generator" content="{{VALUE}}">';
    case Expires = '<meta http-equiv="expires" content="{{VALUE}}">';
    case Rating = '<meta name="rating" content="{{VALUE}}">';
    case Distribution = '<meta name="distribution" content="{{VALUE}}">';
    case RevisitAfter = '<meta name="revisit-after" content="{{VALUE}}">';
    case Abstract = '<meta name="abstract" content="{{VALUE}}">';
    case Language = '<meta name="language" content="{{VALUE}}">';
    case Copyright = '<meta name="copyright" content="{{VALUE}}">';
    case Authorship = '<meta name="authorship" content="{{VALUE}}">';

    // Icon meta tag
    case Icon = '<link rel="icon" type="image/{{TYPE}}" href="{{VALUE}}">';
}
