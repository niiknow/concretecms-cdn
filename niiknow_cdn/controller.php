<?php
namespace Concrete\Package\NiiknowCdn;
defined('C5_EXECUTE') or die("Access Denied.");

include 'lib/minifier.php';

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page as Page;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Support\Facade\Events;
use Permissions;

class Controller extends Package
{
    protected $pkgHandle          = 'niiknow_cdn';
    protected $appVersionRequired = '5.7.0.4';
    protected $pkgVersion         = '0.1.2';

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
        return $this->getConfig()->get('niiknow_cdn.enabled') == 1 ? true : false;
    }

    public function setEnabled($enabled = false)
    {
        $this->getConfig()->save('niiknow_cdn.enabled', $enabled ? 1 : 0);
    }

    public function getOffsiteUrl()
    {
        return $this->getConfig()->get('niiknow_cdn.offsite_url');
    }

    public function setOffsiteUrl($arg1 = 'http://your.cdn-url.com')
    {
        $this->getConfig()->save('niiknow_cdn.offsite_url', preg_replace('/[\s\/]*$/', '', $arg1));
    }

    public function getIncludePath()
    {
        return $this->getConfig()->get('niiknow_cdn.include_path');
    }

    public function setIncludePath($arg1 = 'application/files/,concrete/,fundamental/,download_file/,packages/')
    {
        $this->getConfig()->save('niiknow_cdn.include_path',
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

    public function minifyHtml()
    {
        return $this->getConfig()->get('niiknow_cdn.minifyhtml') == 1 ? true : false;
    }

    public function setMinifyHtml($enabled = false)
    {
        $this->getConfig()->save('niiknow_cdn.minifyhtml', $enabled ? 1 : 0);
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
        $this->setIncludePath();
        $this->setExcludeSubstrings();
        $this->setMinifyHtml();

        $single_page = SinglePage::add('/dashboard/system/basics/niiknowcdn', $pkg);
        $single_page->update(array('cName' => t('CDN Settings'), 'cDescription' => t('CDN Settings')));
    }

    public function on_start()
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

                $baseUrlReg = '/\s+(href|src)\=([\"\'])https?:\/\/(' . $site_domain . ')\//umi';
                $cdnUrl     = $that->getOffsiteUrl();

                // Get the page content from event instance
                $contents = $event->getArgument('contents');

                // perform CDN replace
                // 1. replace full URL with simply forward slash (/)
                $contents = preg_replace($baseUrlReg, " $1=$2/", $contents);

               // 2. replace forward slash with __CDN_URL__
                $includeFolders = $that->getIncludePath();
                if ($includeFolders) {
                    $includeFolders = str_replace('/', '\/', $includeFolders);

                    $cdnUrlReg = '/\s+(href|src)\=([\"\'])\/('.$includeFolders.')/umi';
                    $contents = preg_replace($cdnUrlReg, " $1=$2__CDNURL__/$3", $contents);

                    // 2-2. regex for inline url
                    $inlineReg = '(\s*url\s*\(\s*[\'\"]?\/)('.$includeFolders.')+([^)]+)/umi';
                    $contents = preg_replace($cdnUrlReg, "$1__CDNURL__$3", $contents);

                    // 3. replace excluded back to just relative URL
                    $excludeSubstrings = $that->getExcludeSubstrings();
                    if ($excludeSubstrings) {
                        $excludeSubstrings = str_replace('/', '\/', $excludeSubstrings);

                        $cdnUrlReg = '/(__CDNURL__)(.*)('.$excludeSubstrings.')/umi';
                        $contents = preg_replace($cdnUrlReg, "$2$3", $contents);
                    }
                }

                // finally update __CDNURL__ to the real URL
                $contents = str_replace('__CDNURL__', $cdnUrl, $contents);

                // minify html
                if ($this->minifyHtml()) {
                    $contents = minify_html($contents);
                }

                // output signature
                // $contents = $contents.'<!-- UseCDN: '.gmdate("Y-m-d\TH:i:s\Z").' -->';

                // Back the replaced content to the event instance
                $event->setArgument('contents', $contents);
            }
        });
    }
}
