<?php

namespace QuinnInteractive\Seo\Extensions;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class SiteConfigSettingsExtension
 * @package QuinnInteractive\Seo\Extensions
 *
 * @property string GoogleAnalytics
 * @property string FacebookPixel
 * @property string TwitterPixel
 * @property string SnapPixel
 * @property string TwitterAccountName
 */
class SiteConfigSettingsExtension extends Extension
{
    use Configurable;

    private static $casting = [
        'GoogleAnalytics' => 'HTMLText'
    ];

    private static $db = [
        'GoogleAnalytics'    => 'Text',
        'FacebookPixel'      => 'Text',
        'TwitterPixel'       => 'Text',
        'SnapPixel'          => 'Text',
        'TwitterAccountName' => 'Varchar(80)',
        'TitleTagEnding' => 'Varchar(255)'
    ];

    private static $has_one = [
        'DefaultShareImage' => Image::class
    ];

    protected function updateCMSFields(FieldList $fields)
    {
        $fbPixelHelp = 'https://www.facebook.com/business/help/952192354843755';
        $twPixelHelp =
            'https://business.twitter.com/en/solutions/twitter-ads/website-clicks/set-up-conversion-tracking.html';
        $snPixelHelp = 'https://businesshelp.snapchat.com/en-US/article/snap-pixel';
        $gaHelp      = 'https://support.google.com/analytics/answer/1008080?hl=en';

        $fields->addFieldsToTab('Root.SEO', [
            TextField::create('TitleTagEnding'),
            UploadField::create('DefaultShareImage'),
            TextField::create('TwitterAccountName'),
            TextareaField::create('GoogleAnalytics', 'Google Analytics')->setRightTitle($this->getHelpLink($gaHelp)),
            ToggleCompositeField::create(null, 'Pixels', [
                TextareaField::create('FacebookPixel', 'Facebook Pixel')
                    ->setRightTitle($this->getHelpLink($fbPixelHelp)),
                TextareaField::create('TwitterPixel', 'Twitter Pixel')->setRightTitle($this->getHelpLink($twPixelHelp)),
                TextareaField::create('SnapPixel', 'Snap Pixel')->setRightTitle($this->getHelpLink($snPixelHelp))
            ])
        ]);
    }

    /**
     * @param $link
     *
     * @return string
     */
    private function getHelpLink($link)
    {
        return DBHTMLText::create()->setValue(
            sprintf(
                'Find out more: <a href="%s" target="_blank">%s</a>',
                $link,
                $link
            )
        );
    }
}
