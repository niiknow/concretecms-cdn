<?php

namespace Concrete\Package\Niiknowcdn;

use Concrete\Core\Package\Package;
use Concrete\Core\Support\Facade\Events;
use \Concrete\Core\Page\Single as SinglePage;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends Package
{
    protected $pkgHandle = 'niiknowcdn';
    protected $appVersionRequired = '5.7';
    protected $pkgVersion = '0.0.1';

    public function getPackageDescription()
    {
        return t('Update all your assets to your CDN/Offsite Url');
    }

    public function getPackageName()
    {
        return t('Serve Content from CDN');
    }

    public function isEnabled() { return $this->getConfig()->get('niiknowcdn.enabled') == 1 ?true:false; }
    public function setEnabled($enabled=false) { $this->getConfig()->save('niiknowcdn.enabled',$enabled?1:0);}

    public function getOffsiteUrl() { return $this->getConfig()->get('niiknowcdn.offsite_url'); }
    public function setOffsiteUrl($arg1='http://your.cdn-url.com') { $this->getConfig()->save('niiknowcdn.offsite_url', $arg1);}
    
    public function getIncludeFolders() { return $this->getConfig()->get('niiknowcdn.include_substring'); }
    public function setIncludeFolders($arg1='applications/files, packages') { $this->getConfig()->save('niiknowcdn.include_substring', $arg1);}

    public function getExcludeSubstrings() { return $this->getConfig()->get('niiknowcdn.exclude_substring'); }
    public function setExcludeSubstrings($arg1='.php, .htm') { $this->getConfig()->save('niiknowcdn.exclude_substring', $arg1);}

    public function install() {
        $pkg = parent::install();
        
        //init
        $this->setEnabled();
        $this->setOffsiteUrl();
        $this->setIncludeFolders();
        $this->setExcludeSubstrings();

        $single_page = SinglePage::add('/dashboard/system/basics/niiknowcdn', $pkg);
        $single_page->update(array('cName' => t('CDN Settings'), 'cDescription' => t('CDN Settings')));
    }

    public function on_start()
    {
        Events::addListener('on_page_output', function() use ($renderer, $bar) {
$that = Package::getByHandle('niiknowcdn');
        $baseUrl = BASE_URL;
        $baseUrlParts = parse_url($baseUrl);
            if ($that->isEnabled()) {
                $site_domain = $baseUrlParts["host"];
                $baseUrlReg = "/\s+(href|src)\=([\"\'])(http|https)?:\/\/($site_domain)\//ugmi";
                $cdnUrl = $that->getOffsiteUrl();

                // Get the page content from event instance
                $contents = $event->getArgument('contents');
                
                // perform CDN replace
                // 1. replace full URL with /
                $contents = preg_replace($baseUrlReg, " $1=$2/", $contents);

                // 2. replace "/ with CDN URL
                $includeFolders = $that->getIncludeFolders();
                if ($includeFolders) {
                    $includeFolders = str_replace(', ', ',', $includeFolders);
                    $includeFolders = str_replace(',', '|', $includeFolders);
                    $cdnUrlReg = "/\s+(href|src)\=([\"\'])\/(".$includeFolders.")/ugmi";
                    $contents = preg_replace($cdnUrlReg, " $1=$2__CDNURL__$3", $contents);

                    // 3. replace excluded back to just relative URL
                    $excludeSubstrings = $that->getExcludeSubstrings();
                    if ($excludeSubstrings) {
                        $excludeSubstrings = str_replace(', ', ',', $excludeSubstrings);
                        $excludeSubstrings = str_replace(',', '|', $excludeSubstrings);
                        $cdnUrlReg = "\s+(href|src)\=([\"\'])(__CDNURL__)\/.*(".$excludeSubstrings.")/ugmi";
                        $contents = preg_replace($cdnUrlReg, " $1=$2/$4", $contents);
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