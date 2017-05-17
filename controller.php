<?php
namespace Concrete\Package\NiiknowCdn;

defined('C5_EXECUTE') or die("Access Denied.");

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page as Page;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Support\Facade\Events;
use Permissions;

class Controller extends Package
{
    protected $pkgHandle          = 'niiknow_cdn';
    protected $appVersionRequired = '5.7';
    protected $pkgVersion         = '0.1.0';

    public function getPackageDescription()
    {
        return t('Serve static content with CDN/Offsite Url');
    }

    public function getPackageName()
    {
        return t('Use CDN');
    }

    public function isEnabled()
    {
        return $this->getConfig()->get('niiknow_cdn . enabled') == 1 ? true : false;
    }

    public function setEnabled($enabled = false)
    {
        $this->getConfig()->save('niiknow_cdn . enabled', $enabled ? 1 : 0);
    }

    public function getOffsiteUrl()
    {
        return $this->getConfig()->get('niiknow_cdn . offsite_url');
    }

    public function setOffsiteUrl($arg1 = 'http: //your.cdn-url.com')
    {
        $this->getConfig()->save('niiknow_cdn.offsite_url', preg_replace('/[\s\/]*$/', '', $arg1));
    }

    public function getIncludeFolders()
    {
        return $this->getConfig()->get('niiknow_cdn.include_substring');
    }

    public function setIncludeFolders($arg1 = 'application/files/,concrete/,fundamental/,download_file/,packages/')
    {
        $this->getConfig()->save('niiknow_cdn.include_substring',
            str_replace(',', '|', preg_replace('/\s*/', '', $arg1)));
    }

    public function getExcludeSubstrings()
    {
        return $this->getConfig()->get('niiknow_cdn.exclude_substring');
    }

    public function setExcludeSubstrings($arg1 = '.php,.htm')
    {
        $this->getConfig()->save('niiknow_cdn.exclude_substring',
            str_replace(',', '|', preg_replace('/\s*/', '', $arg1)));
    }

    public function getSiteUrl()
    {
        return (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    }

    public function install()
    {
        $pkg = parent::install();

        // init default values
        $this->setEnabled();
        $this->setOffsiteUrl();
        $this->setIncludeFolders();
        $this->setExcludeSubstrings();

        $single_page = SinglePage::add('/dashboard/system/basics/niiknow_cdn', $pkg);
        $single_page->update(array('cName' => t('CDN Settings'), 'cDescription' => t('CDN Settings')));
    }

    public function onStart()
    {
        Events::addListener('on_page_output', function ($event) {
            $cp = new Permissions(Page::getCurrentPage());
            if ($cp->canViewToolbar()) {
                return;
            }
            $that = Package::getByHandle('niiknow_cdn');

            if ($that->isEnabled()) {
                $baseUrl      = $that->getSiteUrl();
                $baseUrlParts = parse_url($baseUrl);
                $site_domain  = $baseUrlParts["host"];

                $baseUrlReg = '/\s+(href|src)\=([\"\'])(http|https)?:\/\/(' . $site_domain . ')\//umi';
                $cdnUrl     = $that->getOffsiteUrl();

                // Get the page content from event instance
                $contents = $event->getArgument('contents');

                // perform CDN replace
                // 1. replace full URL with /
                $contents = preg_replace($baseUrlReg, " $1=$2/", $contents);

                // 2. replace "/ with CDN URL
                $includeFolders = $that->getIncludeFolders();
                if ($includeFolders) {
                    $includeFolders = str_replace('/', '\/', $includeFolders);

                    $cdnUrlReg = '/\s+(href|src)\=([\"\'])\/(' . $includeFolders . ')/umi';
                    $contents  = preg_replace($cdnUrlReg, " $1=$2__CDNURL__/$3", $contents);

                    // 3. replace excluded back to just relative URL
                    $excludeSubstrings = $that->getExcludeSubstrings();
                    if ($excludeSubstrings) {
                        $excludeSubstrings = str_replace('/', '\/', $excludeSubstrings);

                        $cdnUrlReg = '/\s+(href|src)\=([\"\'])(__CDNURL__)\/.*(' . $excludeSubstrings . ')/umi';
                        $contents  = preg_replace($cdnUrlReg, " $1=$2/$4", $contents);
                    }
                }

                // finally update __CDNURL__
                $contents = str_replace('__CDNURL__', $cdnUrl, $contents);

                // Back the replaced content to the event instance
                $event->setArgument('contents', $contents);
            }
        });
    }
}
