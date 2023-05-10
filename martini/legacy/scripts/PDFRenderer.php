<?php
namespace InternalScripts;

require_once(__DIR__.'/../vendor/autoload.php');
require_once('../vendor/laravel/framework/src/Illuminate/Support/Facades/Log.php');
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
use Illuminate\Support\Facades\Log;
class PDFRenderer{
    public static function generatePDFfromWeb($targetURL,$pathToFile,$fileName,$awaitRenderComplete = true,$debug=false){

        require("martini/legacy/config.php");
        
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
            if ($debug) Log::debug("LoginPage",[$targetURL,$pathToFile,$fileName]);
            $page = $browser->createPage();
            if ($debug) Log::debug("LoginNav",[$targetURL,$pathToFile,$fileName]);
            $page->navigate('https:'.$domain)->waitForNavigation();   
            //login
            if ($debug) Log::debug("EvalLogin",[$targetURL,$pathToFile,$fileName]);
            $evaluation = $page->evaluate(
                '(() => {
                        document.querySelector("#email").value = "php-pdf-generator@tang.solutions";
                        document.querySelector("#password").value = "{CY_}TD87q&)fUqp";
                        document.querySelector("#loginform").submit();
                    })()'
                )->waitForPageReload();
            if ($debug) Log::debug("TargetPage",[$targetURL,$pathToFile,$fileName]);
            $page->navigate('https:'.$domain.$targetURL)->waitForNavigation();
            if ($debug) Log::debug("TargetPageEvalLoopStart",[$targetURL,$pathToFile,$fileName]);
            $hasResult = !$awaitRenderComplete;
            $start = time();
            while (!$hasResult)
            {
                if ($debug) Log::debug("EvalLoopTick",[$targetURL,$pathToFile,$fileName]);
                try
                {
                    $evaluation = $page->evaluate(
                        '(() => {
                                return renderComplete();
                            })()'
                        )->getReturnValue(300);
                }
                catch (\Exception $e) {}
                if ($evaluation)
                {
                    $hasResult = true;
                    break;
                }
                if (time() - $start > 3600)
                {
                    break;
                }
                sleep(1);
            }
            // pdf
            if ($debug) Log::debug("EvalLoopFinish",[$targetURL,$pathToFile,$fileName]);
            if (!$hasResult) return false;
            if ($debug) Log::debug("PageToPDF",[$targetURL,$pathToFile,$fileName]);
            $out= $page->pdf(['printBackground' => false]);
            if ($debug) Log::debug("WriteFile",[$targetURL,$pathToFile,$fileName]);
            $out->saveToFile(join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)),500000);
            if ($debug) Log::debug("Logout",[$targetURL,$pathToFile,$fileName]);
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