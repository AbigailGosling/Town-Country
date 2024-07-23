<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\PagePermission;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Testing\MimeType;
class LegacyController extends Controller
{
    public function entry_point()
    {
        $legacyControllerStartTime = time();
        $legacyControllerStartDate = date('Y-m-d H:i:s');
        $legacyControllerTargetFile = str_replace("?".request()->server('QUERY_STRING'),'',request()->server('REQUEST_URI'));
        $legacyControllerFileExt = explode(".",$legacyControllerTargetFile);
        $legacyControllerFileExt = $legacyControllerFileExt[array_key_last($legacyControllerFileExt)];
        if ($legacyControllerFileExt == "php")
        {
            $legacyControllerPagePerm = PagePermission::where("file",basename($legacyControllerTargetFile))->first();
            if ($legacyControllerPagePerm == null || User::find(Auth::id())->hasPermission(basename($legacyControllerTargetFile)))
            {
                DB::disconnect("tandc_live");
                ob_start();
                require app_path('Http') . '/legacy.php';
                $legacyControllerOutput = ob_get_clean();

                if (time()-$legacyControllerStartTime>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        $legacyControllerStartDate.';'.date('Y-m-d H:i:s').';'.(time()-$legacyControllerStartTime).';'.Auth::id().';'.$legacyControllerTargetFile.json_encode(request()->all()).PHP_EOL
                    );
                }

                return Response::make($legacyControllerOutput,200);
            }
            else
            {   
                if (time()-$legacyControllerStartTime>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        $legacyControllerStartDate.';'.date('Y-m-d H:i:s').';'.(time()-$legacyControllerStartTime).';'.Auth::id().';'.$legacyControllerTargetFile.json_encode(request()->all()).PHP_EOL
                    );
                }
                abort(403);
            }
        }
        else
        {
            try
            {
                $legacyControllerFile = File::get(__DIR__.'\..\..\..'. $legacyControllerTargetFile);
                $legacyControllerResponse = Response::make($legacyControllerFile,200);
                $legacyControllerResponse->header('Content-Type', $this->getMimeType($legacyControllerFileExt));
                if (time()-$legacyControllerStartTime>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        date('Y-m-d H:i:s').';'.(time()-$legacyControllerStartTime).';'.Auth::id().';'.$legacyControllerTargetFile.json_encode(request()->all()).PHP_EOL
                    );
                }
                return $legacyControllerResponse;
            }
            catch (Exception $legacyControllerE)
            {
                if (time()-$legacyControllerStartTime>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        date('Y-m-d H:i:s').';'.(time()-$legacyControllerStartTime).';'.Auth::id().';'.$legacyControllerTargetFile.json_encode(request()->all()).PHP_EOL
                    );
                }
                Log::error($legacyControllerE,[$legacyControllerTargetFile]);
                abort(404);
            }
        }
    }
    private function getMimeType(string $legacyControllerFileExt) {
        switch(strtolower($legacyControllerFileExt))
        {
            case "css": 
                return "text/css";
            case "map":
                return "application/json";
            case "gif":
                return "image/gif";
            case "jpg":
            case "jpeg":
                return "image/jpeg";
            case "js":
                return "text/javascript";
            case "pdf":
                return "application/pdf";
            case "png":
                return "image/png";
            case "ttf":
                return "application/octet-stream";
            case "woff":
                return "font/woff";
            case "woff2":
                return "font/woff2";
            default: 
                $legacyControllerT = MimeType::get($legacyControllerFileExt);
                if ($legacyControllerT != null && $legacyControllerT != "") return $legacyControllerT;
                else throw new Exception("No Mime Type defined for extension: ".$legacyControllerFileExt);
        }
    }
}
?>