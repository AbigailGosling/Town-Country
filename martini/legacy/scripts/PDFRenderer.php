<?php
namespace InternalScripts;

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../../vendor/laravel/framework/src/Illuminate/Support/Facades/Log.php');
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Illuminate\Support\Facades\Log;
class PDFRenderer{
    public static function generatePDFfromWeb($targetURL,$pathToFile,$fileName,$awaitRenderComplete = true,$debug=false){

        require(__DIR__."/../config.php");
        
        //---PHP CONFIG---//
        ini_set('memory_limit', '1024M');
        set_time_limit(1800); //seconds
        if ($debug) Log::debug("BrowserFactory",[$targetURL,$pathToFile,$fileName]);
        $browserFactory = new BrowserFactory('/usr/bin/google-chrome');
        // starts headless chrome
        if ($debug) Log::debug("Browser",[$targetURL,$pathToFile,$fileName]);
        $browser = $browserFactory->createBrowser();
        try {
            // creates a new page and navigate to an URL
            if ($debug) Log::debug("LoginPage",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            $page = $browser->createPage();
            if ($debug) Log::debug("LoginNav",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl(),$page->getHTML()]);
            $page->navigate('https:'.$domainOld.'login')->waitForNavigation();   
            //login
            if ($debug) Log::debug("EvalLogin",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl(),$page->getHTML()]);
            $evaluation = $page->evaluate(
                '(() => {
                        document.querySelector("#email").value = "php-pdf-generator@tang.solutions";
                        document.querySelector("#password").value = "{CY_}TD87q&)fUqp";
                        document.querySelector("#loginform").submit();
                    })()'
                )->waitForPageReload();
            if ($debug) Log::debug("TargetPage",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl(),$page->getHTML()]);
            $page->navigate('https:'.$domain.$targetURL)->waitForNavigation();
            if ($debug) Log::debug("TargetPageEvalLoopStart",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl(),$page->getHTML()]);
            $hasResult = !$awaitRenderComplete;
            $start = time();
            while (!$hasResult)
            {
                if ($debug) Log::debug("EvalLoopTick",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl(),$page->getHTML()]);
                try
                {
                    $evaluation = $page->evaluate(
                        '(() => {
                                return renderComplete();
                            })()'
                        )->getReturnValue(300);
                }
                catch (\Exception $e) {
                    if ($debug) Log::debug("EvalLoopException",['https:'.$domain,$targetURL,$pathToFile,$fileName,time() - $start,$page->getCurrentUrl(),$page->getHTML(),$e]);
                }
                if ($evaluation)
                {
                    $hasResult = true;
                    break;
                }
                if (time() - $start > 60)
                {
                    break;
                }
                sleep(1);
            }
            // pdf
            if ($debug) Log::debug("EvalLoopFinish",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            if (!$hasResult) return false;
            if ($debug) Log::debug("PageToPDF",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            $out= $page->pdf(['printBackground' => false]);
            if ($debug) Log::debug("WriteFile",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            $out->saveToFile(join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)),500000);
            if ($debug) Log::debug("Logout",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            $page->navigate('https:'.$domain.'logout.php')->waitForNavigation();
        } catch (\Exception $e) {
            Log::error($e);
        } finally {
            // bye
            $browser->close();
        }
        return true;
    }
    public static function getHTML($targetURL){
        require_once(__DIR__.'/../config.php');
        
        //---PHP CONFIG---//
        ini_set('memory_limit', '1024M');
        set_time_limit(1800); //seconds
        
        $browserFactory = new BrowserFactory('/usr/bin/google-chrome');
        // starts headless chrome
        $browser = $browserFactory->createBrowser();
        try {
            // creates a new page and navigate to an URL
            $page = $browser->createPage();
            $page->navigate('https:'.$domain)->waitForNavigation();   
            //login
            $evaluation = $page->evaluate(
                '(() => {
                        document.querySelector("#email").value = "php-pdf-generator@tang.solutions";
                        document.querySelector("#password").value = "{CY_}TD87q&)fUqp";
                        document.querySelector("#loginform").submit();
                    })()'
                )->waitForPageReload();
            $page->navigate('https:'.$domain.$targetURL)->waitForNavigation();
            $output = $page->getHtml();
        } catch (\Exception $e) {
            die($e->getMessage());
        } finally {
            // bye
            $browser->close();
        }
        return $output;
    }
}
?>