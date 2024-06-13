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
        $s = time();
        $d = date('Y-m-d H:i:s');
        $targetFile = str_replace("?".request()->server('QUERY_STRING'),'',request()->server('REQUEST_URI'));
        $ext = explode(".",$targetFile);
        $ext = $ext[array_key_last($ext)];
        if ($ext == "php")
        {
            $pagePerm = PagePermission::where("file",basename($targetFile))->first();
            if ($pagePerm == null || User::find(Auth::id())->hasPermission(basename($targetFile)))
            {
                DB::disconnect("tandc_live");
                ob_start();
                require app_path('Http') . '/legacy.php';
                $output = ob_get_clean();

                if (time()-$s>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        $d.';'.date('Y-m-d H:i:s').';'.(time()-$s).';'.Auth::id().';'.$targetFile.json_encode(request()->all()).PHP_EOL
                    );
                }

                return Response::make($output,200);
            }
            else
            {   
                if (time()-$s>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        $d.';'.date('Y-m-d H:i:s').';'.(time()-$s).';'.Auth::id().';'.$targetFile.json_encode(request()->all()).PHP_EOL
                    );
                }
                abort(403);
            }
        }
        else
        {
            try
            {
                $file = File::get(__DIR__.'\..\..\..'. $targetFile);
                $response = Response::make($file,200);
                $response->header('Content-Type', $this->getMimeType($ext));
                if (time()-$s>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        date('Y-m-d H:i:s').';'.(time()-$s).';'.Auth::id().';'.$targetFile.json_encode(request()->all()).PHP_EOL
                    );
                }
                return $response;
            }
            catch (Exception $e)
            {
                if (time()-$s>4)
                {
                    File::append(
                        storage_path('/logs/slow-page.log'),
                        date('Y-m-d H:i:s').';'.(time()-$s).';'.Auth::id().';'.$targetFile.json_encode(request()->all()).PHP_EOL
                    );
                }
                Log::error($e,[$targetFile]);
                abort(404);
            }
        }
    }
    private function getMimeType(string $ext) {
        switch(strtolower($ext))
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
                $t = MimeType::get($ext);
                if ($t != null && $t != "") return $t;
                else throw new Exception("No Mime Type defined for extension: ".$ext);
        }
    }
}
?>