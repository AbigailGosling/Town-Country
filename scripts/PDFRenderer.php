<?php
namespace InternalScripts;
require_once('../config.php');
require_once('../vendor/autoload.php');
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;
class PDFRenderer{
    public static function generatePDFfromWeb($targetURL,$pathToFile,$fileName){

        global $domain;
        
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
            $hasResult = false;
            $start = time();
            while (!$hasResult)
            {
                try
                {
                    $evaluation = $page->evaluate(
                        '(() => {
                                return renderComplete();
                            })()'
                        )->getReturnValue(10000);
                }
                catch (Exception $e) {}
                if ($evaluation)
                {
                    $hasResult = true;
                    break;
                }
                if (time() - $start > 50000)
                {
                    break;
                }
                sleep(1);
            }
            // pdf
            if (!$hasResult) return false;
            $out= $page->pdf(['printBackground' => false]);
            $out->saveToFile(join(DIRECTORY_SEPARATOR,array(__DIR__,'..',$pathToFile,$fileName)),500000);
            $page->navigate('https:'.$domain.'logout.php')->waitForNavigation();
        } catch (Exception $e) {
            die($e->getMessage());
        } finally {
            // bye
            $browser->close();
        }
        return true;
    }
}
?>