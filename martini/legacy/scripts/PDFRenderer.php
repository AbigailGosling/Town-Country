<?php
namespace InternalScripts;

require_once(__DIR__.'/../../vendor/autoload.php');
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
        if ($debug) Log::error("BrowserFactory",[$targetURL,$pathToFile,$fileName]);
        $browserFactory = new BrowserFactory("C:\Program Files\Google\Chrome\Application\chrome.exe");
        // starts headless chrome
        if ($debug) Log::error("Browser",[$targetURL,$pathToFile,$fileName]);
        $browser = $browserFactory->createBrowser(['noSandbox' => true,'debugLogger'     => Log::getLogger(),'connectionDelay' => 0.8,  'headless' => true,]);
        try {
            // creates a new page and navigate to an URL
            if ($debug) Log::error("LoginPage",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            $page = $browser->createPage();

            if ($debug) Log::error("LoginNav",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl()]);
            $page->navigate('https:'.$domainOld.'login')->waitForNavigation();

            //login
            if ($debug) Log::error("EvalLogin",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl()]);
            $evaluation = $page->evaluate(
                '(() => {
                        document.querySelector("#email").value = "php-pdf-generator@tang.solutions";
                        document.querySelector("#password").value = "{CY_}TD87q&)fUqp";
                        document.querySelector("#loginform").submit();
                    })()'
                )->waitForPageReload();

            if ($debug) Log::error("TargetPage",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl()]);
            $page->navigate('https:'.$domain.$targetURL)->waitForNavigation(Page::DOM_CONTENT_LOADED, 300000);

            if ($debug) Log::error("TargetPageEvalLoopStart",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl()]);
            $hasResult = !$awaitRenderComplete;
            $start = time();
            while (!$hasResult)
            {
                if ($debug) Log::error("EvalLoopTick",['https:'.$domain,$targetURL,$pathToFile,$fileName,$page->getCurrentUrl()]);
                try
                {
                    $evaluation = $page->evaluate(
                        '(() => {
                                return renderComplete();
                            })()'
                        )->getReturnValue(1000*60*5);
                }
                catch (\Exception $e) {
                    if ($debug) Log::error("EvalLoopException",['https:'.$domain,$targetURL,$pathToFile,$fileName,time() - $start,$page->getCurrentUrl(),$e]);
                }
                if ($evaluation)
                {
                    $hasResult = true;
                    break;
                }
                if (time() - $start > 300)
                {
                    break;
                }
                sleep(1);
            }
            // pdf
            if ($debug) Log::error("EvalLoopFinish",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            if (!$hasResult) return false;

            if ($debug) Log::error("PageToPDF",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            $out= $page->pdf(['printBackground' => false]);

            if ($debug) Log::error("WriteFile",['https:'.$domain,$targetURL,$pathToFile,$fileName,join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName))]);
            $out->saveToFile(join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)),500000);

            if ($debug) Log::error("Logout",['https:'.$domain,$targetURL,$pathToFile,$fileName]);
            $page->navigate('https:'.$domain.'logout.php')->waitForNavigation();
        } catch (\Exception $e) {
            Log::error($e);
        } finally {
            // bye
            $browser->close();
        }
        return true;
    }
    public static function generatePDFfromHTML($htmlString,$pathToFile,$fileName,$debug=false){

        require(__DIR__."/../config.php");

        //---PHP CONFIG---//
        ini_set('memory_limit', '1024M');
        set_time_limit(1800); //seconds
        if ($debug) Log::error("BrowserFactory",[$pathToFile,$fileName]);
        $browserFactory = new BrowserFactory('/usr/bin/google-chrome');
        // starts headless chrome
        if ($debug) Log::error("Browser",[$pathToFile,$fileName]);
        $browser = $browserFactory->createBrowser();
        try {
            // creates a new page and navigate to an URL
            if ($debug) Log::error("LoginPage",[$pathToFile,$fileName]);
            $page = $browser->createPage();

            if ($debug) Log::error("RenderPage",[$pathToFile,$fileName,$page->getCurrentUrl()]);
            $page->setHTML($htmlString);

            if ($debug) Log::error("PageToPDF",[$pathToFile,$fileName]);
            $out= $page->pdf(['printBackground' => false]);

            if ($debug) Log::error("WriteFile",[$pathToFile,$fileName]);
            $out->saveToFile(join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)),500000);
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

        $browserFactory = new BrowserFactory();
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
