<?php

namespace QuinnInteractive\Seo\Extensions;

use QuinnInteractive\Seo\Builders\FacebookMetaGenerator;
use QuinnInteractive\Seo\Seo;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\VersionedAdmin\Controllers\HistoryViewerController;

/**
 * Class PageSeoExtension
 * @package QuinnInteractive\Seo\Extensions
 *
 * @property string FacebookPageType
 * @property string FacebookPageTitle
 * @property string FacebookPageDescription
 * @property int    FacebookPageImageID
 * @property int    CreatorID
 *
 * @method Image FacebookPageImage()
 * @method Member|MemberExtension Creator()
 */
class PageSeoExtension extends Extension
{
    use Configurable;

    private static $cascade_deletes = [
        'FacebookPageImage',
        'TwitterPageImage'
    ];

    private static $db = [
        'FacebookPageType'        => 'Varchar(50)',
        'FacebookPageTitle'       => 'Varchar(255)',
        'FacebookPageDescription' => 'Text',
        'TwitterPageTitle'        => 'Varchar(255)',
        'TwitterPageDescription'  => 'Text',
        'StructuredData' => 'HTMLText',
        'MetaTitle' => 'Varchar(255)'
    ];

    /**
     * The "creator tag" is the meta tag for Twitter to specify the creators Twitter account. Enabled by default
     *
     * @config
     * @var bool
     */
    private static $enable_creator_tag = true;

    private static $has_one = [
        'FacebookPageImage' => Image::class,
        'TwitterPageImage'  => Image::class,
        'Creator'           => Member::class
    ];

    private static $owns = [
        'FacebookPageImage',
        'TwitterPageImage'
    ];

    /**
     * Extension point for SiteTree to merge all tags with the standard meta tags
     *
     * @param $tags
     */
    public function MetaTags(&$tags)
    {
        $siteConfig = SiteConfig::current_site_config();
        $tags = explode(PHP_EOL, $tags);
		for($i = 0; $i < count($tags); $i++){
			if(($pos = strpos($tags[$i], '</title>')) !== false){
                if($this->owner->MetaTitle){
                    $start = strpos($tags[$i], '<title>') + 7;
                    $tags[$i] = substr_replace($tags[$i], $this->owner->MetaTitle, $start, ($pos - $start));
                } else {
				    $tags[$i] = substr_replace($tags[$i], ' '.$siteConfig->TitleTagEnding, $pos, 0);
                }
				break;
			}
		}
        $tags = array_merge(
            $tags,
            Seo::getCanonicalUrlLink($this->getOwner()),
            Seo::getFacebookMetaTags($this->getOwner()),
            Seo::getTwitterMetaTags($this->getOwner()),
            Seo::getArticleTags($this->getOwner()),
            Seo::getGoogleAnalytics(),
            Seo::getPixels(),
            [$this->owner->StructuredData]
        );

        $tags = implode(PHP_EOL, $tags);
    }

    public function onBeforeWrite()
    {
        if (!$this->getOwner()->ID && !$this->getOwner()->Creator()->exists() && $member = Security::getCurrentUser()) {
            $this->getOwner()->CreatorID = $member->ID;
        }
    }

    /**
     * @param FieldList $fields
     */
    protected function updateCMSFields(FieldList $fields)
    {
        $suppressMessaging = false;
        if (Controller::curr() instanceof HistoryViewerController) { // avoid cluttering the history comparison UI
            $suppressMessaging = true;
        }

        $fields->addFieldsToTab('Root.Main', TextField::create('MetaTitle'), 'MetaDescription');
        $fields->addFieldsToTab('Root.Main', TextareaField::create('StructuredData'), 'ExtraMeta');

        $fields->insertAfter('Metadata',
            ToggleCompositeField::create('FacebookSeoComposite', 'Facebook SEO', [
                DropdownField::create('FacebookPageType', 'Type', FacebookMetaGenerator::getValidTypes()),
                TextField::create('FacebookPageTitle', 'Title')
                    ->setAttribute('placeholder', $this->getOwner()->Title)
                    ->setRightTitle($suppressMessaging ? '' : 'If blank, inherits default page title')
                    ->setTargetLength(45, 25, 70),
                UploadField::create('FacebookPageImage', 'Image')
                    ->setRightTitle($suppressMessaging
                        ? ''
                        : 'Facebook recommends images to be 1200 x 630 pixels. ' .
                        'If no image is provided, Facebook will choose the first image that appears on the page, ' .
                        'which usually has bad results')
                    ->setFolderName('seo'),
                TextareaField::create('FacebookPageDescription', 'Description')
                    ->setAttribute('placeholder', $this->getOwner()->MetaDescription ?:
                     $this->getOwner()->dbObject('Content')->LimitCharacters(297))
                    ->setRightTitle($suppressMessaging
                        ? ''
                        : 'If blank, inherits meta description if it exists ' .
                        'or gets the first 297 characters from content')
                    ->setTargetLength(200, 160, 320),
            ])
        );
        $fields->insertAfter('Metadata',
            ToggleCompositeField::create('TwitterSeoComposite', 'Twitter SEO', [
                TextField::create('TwitterPageTitle', 'Title')
                    ->setAttribute('placeholder', $this->getOwner()->Title)
                    ->setRightTitle($suppressMessaging ? '' : 'If blank, inherits default page title')
                    ->setTargetLength(45, 25, 70),
                UploadField::create('TwitterPageImage', 'Image')
                    ->setRightTitle($suppressMessaging ? '' : 'Must be at least 280x150 pixels')
                    ->setFolderName('seo'),
                TextareaField::create('TwitterPageDescription', 'Description')
                    ->setAttribute('placeholder', $this->getOwner()->MetaDescription ?:
                        $this->getOwner()->dbObject('Content')->LimitCharacters(297))
                    ->setRightTitle($suppressMessaging
                        ? ''
                        : 'If blank, inherits meta description if it exists ' .
                            'or gets the first 297 characters from content')
                    ->setTargetLength(200, 160, 320),
            ])
        );
    }

}
