<?php

namespace QuinnInteractive\Seo\Extensions;

use KubAT\PhpSimple\HtmlDomParser;
use SilverStripe\CMS\Controllers\ModelAsController;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use Vulcan\Seo\Analysis\Analysis;
use Vulcan\Seo\Forms\GoogleSearchPreview;
use Vulcan\Seo\Forms\HealthAnalysisField;

/**
 * Class PageHealthExtension
 * @package Vulcan\Seo\Extensions
 *
 * @property string FocusKeyword
 */
class PageHealthExtension extends Extension
{
    /**
     * @var string|null
     */
    protected $renderedHtml;

    private static $db = [
        'FocusKeyword' => 'Varchar(50)'
    ];

    /**
     * @param FieldList $fields
     */
    protected function updateCMSFields(FieldList $fields)
    {
        if ($this->owner instanceof \SilverStripe\ErrorPage\ErrorPage) {
            return;
        }

        $fields->addFieldsToTab('Root.Main', [
            ToggleCompositeField::create('SEOHealthAnalysis', 'SEO Health Analysis', [
                GoogleSearchPreview::create('GoogleSearchPreview', 'Search Preview', $this->getOwner(), $this->getRenderedHtmlDomParser()),
                TextField::create('FocusKeyword', 'Set focus keyword'),
                HealthAnalysisField::create('ContentAnalysis', 'Content Analysis', $this->getOwner()),
            ])
        ], 'Metadata');
    }

    /**
     * Gets the rendered html (current version, either draft or live)
     *
     * @return string|null
     */
    public function getRenderedHtml()
    {
        if (!$this->renderedHtml) {
            $this->renderedHtml = $this->owner->Me();
        }

        if ($this->renderedHtml === false) {
            $this->renderedHtml = '<p></p>';
        }

        return $this->renderedHtml;
    }

    /**
     * Gets the DOM parser for the rendered html
     *
     * @return \simple_html_dom\simple_html_dom
     */
    public function getRenderedHtmlDomParser()
    {
        return HtmlDomParser::str_get_html($this->getRenderedHtml());
    }

    /**
     * @return \Page|static
     */
    public function getOwner()
    {
        /** @var \Page $owner */
        $owner = parent::getOwner();
        return $owner;
    }

    /**
     * Override this if you have more than just `Content` (or don't have `Content` at all). Fields should
     * be in the order for which they appear for a frontend user
     *
     * @return array
     */
    public function seoContentFields()
    {
        return [
            'Content'
        ];
    }
}
