<?php

namespace QuinnInteractive\Seo\Extensions;

use SilverStripe\Core\Extension;

use SilverStripe\Forms\FieldList;

/**
 * Class MemberExtension
 * @package QuinnInteractive\Seo\Extensions
 *
 * @property string TwitterAccountName
 */
class MemberExtension extends Extension
{
    private static $db = [
        'TwitterAccountName' => 'Varchar(80)'
    ];
}
