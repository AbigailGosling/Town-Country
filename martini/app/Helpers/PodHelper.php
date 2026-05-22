<?php
namespace App\Helpers;

use App\Http\Controllers\FileController;
use App\Models\Brand;
use App\Models\Cut;
use App\Models\Intake;
use App\Models\Location;
use App\Models\Nationality;
use App\Models\OutgoingPallet;
use App\Models\OutgoingPalletPickWeight;
use App\Models\Pallet;
use App\Models\PickerSheet;
use App\Models\PickersheetDocument;
use App\Models\PickWeightOut;
use App\Models\Product;
use App\Models\Site;
use App\Models\Species;
use App\Models\Temperature;
use App\Models\Vehicle;
use App\Models\VehicleOutgoingPalletAllocation;
use App\Models\Weight;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PodHelper
{
    /**
     * Send PODs to an external system or service.
     * @param Collection<OutgoingPallet> $outgoingPallets
     * @param Vehicle $vehicle
     */
    public static function sendPods(Collection $outgoingPallets,Vehicle $vehicle): void
    {
        $processedPicks = [];
        $outgoingPallets = $outgoingPallets->reverse()->values();

        $locations = Location::all()->keyBy('id');
        $sites = Site::all()->keyBy('id');
        $nationalities = Nationality::all()->keyBy('id');
        $temperatures = Temperature::all()->keyBy('id');
        $species = Species::all()->keyBy('id');
        $brands = Brand::all()->keyBy('id');

        foreach ($outgoingPallets as $outgoingPallet) {
            // if ((bool) $outgoingPallet->pod_sent) {
            //     continue;
            // }
            $allCallsSucceeded = true;

            /** @var OutgoingPalletPickWeight $oppw */
            foreach ($outgoingPallet->pickWeightOuts as $oppw) {
                /** @var PickerSheet $pickSheet */
                $pickSheet = $oppw->pickWeightOut->pickerSheet;
                if (in_array($pickSheet->id, $processedPicks)) {
                    continue;
                }
                $processedPicks[] = $pickSheet->id;
                $clientAddress = $pickSheet->getAddress();
                $pickedAt = [];
                $thisData = (object)[
                    "TASK_INFO" => (object)[
                        "TASK_START_DATE" => $outgoingPallet->estimated_delivery_date->format('d/m/Y'),
                        "TASK_START_TIME" => "10:00",
                        //"TASK_MOBILE_USER" => implode('', explode(' ', $vehicle->reg)).'@tc.co.uk',
                        "TASK_MOBILE_USER_ID" => 13,
                        "TASK_MOBILE_USER_PROF_ID" => "",
                        "PROJECT_GUID" => "AB58CF2A-2D37-99B0-4A2F-D5E94144EBAD"
                    ],
                    "TASK_DATA" => (object)[
                        "TC_VEHICLE_ID"=> $vehicle->id,
                        "TC_DNOTE" => $pickSheet->id,
                        "TC_PO_NUMBER"=> $pickSheet->orderReferenceNumber,
                        "BUSINESS_NAME"=> $pickSheet->customer->businessname,
                        "TRADING_NAME"=> $pickSheet->customer->tradingas,
                        "ADDR_1"=> $clientAddress->address_1,
                        "ADDR_2"=> $clientAddress->address_2,
                        "ADDR_3"=> $clientAddress->address_3,
                        "ADDR_4"=> $clientAddress->address_4,
                        "POSTCODE"=> $clientAddress->postcode,
                        "TELEPHONE"=> $clientAddress->address_number,
                        "INVOICE_BUSINESS_NAME"=> $pickSheet->customer->businessname,
                        "INVOICE_TRADING_NAME"=> $pickSheet->customer->tradingas,
                        "INVOICE_ADDR_1"=> $pickSheet->customer->accounts_address_1,
                        "INVOICE_ADDR_2"=> $pickSheet->customer->accounts_address_2,
                        "INVOICE_ADDR_3"=> $pickSheet->customer->accounts_address_3,
                        "INVOICE_ADDR_4"=> $pickSheet->customer->accounts_address_4,
                        "INVOICE_POSTCODE"=> $pickSheet->customer->accounts_postcode,
                        "INVOICE_TELEPHONE"=> ($pickSheet->customer->contactnumber != null && $pickSheet->customer->contactnumber != "")?$pickSheet->customer->contactnumber:$pickSheet->customer->tel_number,
                        "CUSTOMER_ID"=> $pickSheet->customer->id,
                        "SERVED_BY"=> $sites[$pickSheet->customer->site_id]->name,
                        "PICKED_AT"=> "",
                        "RESTRICTIONS"=> $clientAddress->restrictions,
                        "ASSEMBLED"=> $pickSheet->date_completed?->format('d/m/Y'),
                        "DELIVERY_DATE"=> $pickSheet->estimated_delivery_date,
                        "SUB_TASKS"=> [],
                    ],
                ];
                foreach($pickSheet->pickWeightOuts as $pwo) {
                    $productIDArray = Weight::whereIn('id', $pwo->getWeights())->pluck('product_id')->unique()->toArray();
                    foreach($productIDArray as $productID){
                        $product = Product::find($productID);

                        $theseWeights = Weight::where('product_id', $productID)->whereIn('id', $pwo->getWeights())->get();
                        $count = $theseWeights->count();
                        $k = 0;
                        $weightInfo = [];
                        foreach($theseWeights as $weight){
                            $tw = $weight->getNetWeight();
                            $k = $k + $tw;
                            $weightInfo[] = $weight->id."|".$tw;
                        }
                        $smallestDate = ($product->range_extension!= null && $product->range_extension!= '')?"EXTENSION":$product->range_from;
                        $largestDate = ($product->range_extension!= null && $product->range_extension!= '')?$product->range_extension:$product->range_to;
                        $ext = ' kg';
                        if($product->unit == 'PPC'){
                            $unit = 'Per Case';
                        }else if($product->unit == 'P'){
                            $unit = 'Pallet';
                        }else if($product->unit == 'KG'){
                            $unit = 'Kilo';
                        }else{
                            $ext = $unit = 'Cases';
                        }
                        $cut = Cut::find($product->cut_id);
                        $pallet = Pallet::find($product->pallet_id);
                        $pickedAt[] = $sites[$locations[$pallet->storage_location]->site_id]->name;
                        $thisData->TASK_DATA->SUB_TASKS[] = (object)[
                            "PROJECT_GUID"=> "BB2E3D63-DEE9-EC74-9906-DB5FD2522D76",
                            "TASK_DATA"=> (object)[
                                "INTAKE"=> $pallet->intake_id,
                                "PALLET"=> $pallet->id,
                                "UNIT"=> $unit,
                                "WEIGHT"=> ($product->unit == 'PPC') ? $count." ".$ext : $k.$ext,
                                "QUANTITY"=> $count,
                                "PRODUCT_ID"=> $product->id,
                                "PRODUCT"=> implode(" ", [
                                    $nationalities[$product->nationality_id]->name,
                                    $temperatures[$product->cooling_id]->tempurature,
                                    $species[$cut->species_id]->name,
                                    $cut->name,
                                    $brands[$product->brand_id]->name,
                                    $smallestDate.' - '.$largestDate,
                                ]),
                                "PRODUCT_WEIGHT_INFO"=> implode(",", $weightInfo)
                            ],
                        ];
                    }
                }
                $thisData->TASK_DATA->PICKED_AT = implode(", ", array_filter(array_unique($pickedAt)));
                $result = static::callExternalApi(
                    url: env('POD_API_URL'),
                    method: 'POST',
                    headers: [],
                    payload: [
                        "Key"=> env('POD_API_KEY'),
                        "Method"=> "createTask",
                        "Data"=> (object)$thisData
                    ]
                );
                if (!($result['success'] ?? false)) {
                    $allCallsSucceeded = false;
                }
            }
            if ($allCallsSucceeded) {
                $outgoingPallet->pod_sent = true;
                $outgoingPallet->save();
            }
        }
    }
    /**
    * Process received POD data from an external system or service.
    * @param array<string, mixed> $payload
    * @return bool
    */
    public static function receivePod(array $payload): bool
    {
        Log::info('Received POD data', [$payload]);
        $rejected_weight_ids = [];
        $rejected_reason = [];

        $pickerSheetID = $payload["PARENT_TASK"]["UserData"]["TC_DNOTE"];
        $pickerSheet = PickerSheet::where('id', $pickerSheetID)->first();
        $pickerSheet->receiver_name = $payload["PARENT_TASK"]["UserData"]["SIGN_NAME"]. " ".$payload["PARENT_TASK"]["UserData"]["SIGN_SURNAME"];
        $pickerSheet->signature_file_id = FileController::PROCESS_BASE64_IMAGE_FILE($payload["PARENT_TASK"]["UserData"]["CUSTOMER_SIGNATURE"])->id;
        $pickerSheet->save();
        $psd = new PickersheetDocument();
        $psd->user_id = null;
        $psd->pickersheet_id = $pickerSheetID;
        $psd->message = 'POD Received. Signed By: '.$pickerSheet->receiver_name;
        $psd->dfile = null;
        $psd->type = 'DELIVERY_NOTE';
        $psd->pod = true;
        $psd->file_id = $pickerSheet->signature_file_id;
        $psd->save();
        //Process complete failure - all items rejected
        if ($payload["PARENT_TASK"]["UserData"]["STATUS"] == "DELIVERY_REJECTED") {
            foreach ($payload["SUB_TASKS"] as $line) {
                $thisWeights = explode(',', $line["UserData"]["PRODUCT_WEIGHT_INFO"]);
                foreach ($thisWeights as $weightInfo) {
                    $weightId = (int) explode('|', $weightInfo)[0];
                    $rejected_weight_ids[] = $weightId;
                    $rejected_reason[$weightId] = $payload["PARENT_TASK"]["UserData"]["ALL_FAIL_REASON"] . ' - ' . $payload["PARENT_TASK"]["UserData"]["ALL_FAIL_NOTES"];
                }
            }
        }
        //Partial failure - some items rejected, some accepted
        foreach ($payload["SUB_TASKS"] as $line) {
            if (isset($line["UserData"]["STATUS"]) && $line["UserData"]["STATUS"] == "REJECTED_ITEMS") {
                $thisRejctions = explode(',', $line["UserData"]["REJECTED_PRODUCTS"]);
                foreach ($thisRejctions as $rej) {
                    $rejected_weight_ids[] = (int) $rej;
                    $rejected_reason[$rej] = $line["UserData"]["ITEM_FAIL_REASON"] . ' - ' . $line["UserData"]["ITEM_FAIL_NOTES"];
                }
            }
        }
        $rejected_weights = Weight::whereIn('id', $rejected_weight_ids)->get();
        if (count($rejected_weights) === 0) {
            //No rejected weights, nothing to process
            return true;
        }

        //Store rejected for return intake creation, but also organise by nationality/brand/cut for easier processing when creating new pallets/products/weights for the return intake
        $organisedByNatBrandCut = [];
        foreach ($rejected_weights as $weight) {
            $natBrandCut = $weight->product->nationality_id . '-' . $weight->product->brand_id . '-' . $weight->product->cut_id;
            if (!isset($organisedByNatBrandCut[$natBrandCut])) {
                $organisedByNatBrandCut[$natBrandCut] = [];
            }
            $organisedByNatBrandCut[$natBrandCut][] = $weight;
        }
        $returnIntake = new Intake();
        $returnIntake->returned = 1;
        $returnIntake->delivery_note_number = $pickerSheetID;
        $returnIntake->supplier_id = $pickerSheet->customer_id;
        $returnIntake->security_id = 3; // TODO: Determine appropriate security_id

        //find the original vehicle by looking at the original pick weights and their associated outgoing pallets and vehicle allocations
        if (array_key_exists('TC_VEHICLE_ID', $payload["PARENT_TASK"]["UserData"])) {
            $vehicle = Vehicle::find($payload["PARENT_TASK"]["UserData"]["TC_VEHICLE_ID"]);
        } else {
            $pwos = PickWeightOut::where('pickersheet_id', $pickerSheetID)->get();
            foreach ($pwos as $pwo) {
                $pwoWeights = explode(',', $pwo->weight_ids);
                if (count(FuncHelper::custom_intersect($rejected_weight_ids, $pwoWeights)) > 0) {
                    $oppw = OutgoingPalletPickWeight::where('pickWeightOut_id', $pwo->id)->first();
                    $vopa = VehicleOutgoingPalletAllocation::where("outgoing_pallet_id", $oppw->outgoing_pallet_id)->first();
                    $vehicle = Vehicle::find($vopa->vehicle_id);
                    break;
                }
            }
        }
        $returnIntake->vehicle_reg = $vehicle->reg ?? 'UNKNOWN';
        $returnIntake->user_id = $vehicle->driver ?? 'UNKNOWN';
        $returnIntake->date_received = Carbon::now()->format('Y-m-d H:i:s');
        $returnIntake->notes = 'Auto-created return intake for rejected items. Rejection Reason(s):' . PHP_EOL . implode(PHP_EOL, array_unique($rejected_reason));
        $returnIntake->save();

        $site_id = null;
        foreach ($organisedByNatBrandCut as $natBrandCut => $weights) {
            $oldPallet = $weights[0]->product->pallet;
            if (!$site_id) {

                $site_id = Location::find($oldPallet->storage_location)->site_id;
            }
            $newPallet = $oldPallet->replicate();
            $newPallet->intake_id = $returnIntake->id;
            $newPallet->comments = $rejected_reason[$weights[0]->id] . PHP_EOL . $oldPallet->comments;
            $newPallet->save();

            $oldProduct = $weights[0]->product;

            $newProduct = $oldProduct->replicate();
            $newProduct->pallet_id = $newPallet->id;
            $oldWeightNote = $oldProduct->weightnote ?? '';
            $newProduct->weightnote = $rejected_reason[$weights[0]->id] . PHP_EOL . $oldWeightNote;
            $newProduct->original_product_id = $oldProduct->id;
            $newProduct->original_pallet_id = $oldProduct->pallet_id;
            $newProduct->original_intake_id = $oldPallet->intake_id;
            $newProduct->save();

            foreach ($weights as $oldWeight) {
                $newWeight = $oldWeight->replicate();
                $newWeight->product_id = $newProduct->id;
                $newWeight->status_id = 0;
                $newWeight->original_weight_id = $oldWeight->id;
                $newWeight->save();
            }
        }
        $returnIntake->site_id = $site_id ?? 1;
        $returnIntake->save();
        return true;
    }
    /**
     * Create or update a vehicle in the external system and store the returned ID.
     *
     * @param Vehicle $vehicle
     * @return Vehicle
     */
    private static function createUpdateVehicle(Vehicle $vehicle): Vehicle
    {
        $result = static::callExternalApi(
            url: env('POD_API_URL'),
            method: 'POST',
            headers: [],
            payload: [
                "Key"=> env('POD_API_KEY'),
                "Method"=>  "updateVehicle",
                "Data"=> (object)[
                    "USER_ID" => $vehicle->barracuda_id ?? "NEW",
                    "VEHICLE_ID" => $vehicle->id,
                    "REGISTRATION" => $vehicle->reg,
                ]
            ]
        );
        if (isset($result['success'])) {
            $vehicle->barracuda_id = $result['body']['USER_ID'] ?? null;
            $vehicle->save();
        }
        return $vehicle;
    }
	/**
	 * Send an HTTP request to an external API using Guzzle.
	 *
	 * @param string $url
	 * @param string $method
	 * @param array<int, string> $headers
	 * @param array<string, mixed>|null $payload
	 * @param int $timeout
	 * @return array<string, mixed>
	 */
	public static function callExternalApi(
		string $url,
		string $method = 'GET',
		array $headers = [],
		?array $payload = null,
		int $timeout = 30
	): array {
		if ($url === '') {
			return [
				'success' => false,
				'status' => null,
				'headers' => [],
				'body' => null,
				'raw_body' => null,
				'error' => 'API URL is empty.',
			];
		}
		$method = strtoupper($method);
		$normalizedHeaders = [];
		foreach ($headers as $header) {
			if (!str_contains($header, ':')) {
				continue;
			}
			[$key, $value] = array_map('trim', explode(':', $header, 2));
			$normalizedHeaders[$key] = $value;
		}
		if ($method !== 'GET' && $payload !== null && !array_key_exists('Content-Type', $normalizedHeaders)) {
			$normalizedHeaders['Content-Type'] = 'application/json';
		}
		$options = [
			'headers' => $normalizedHeaders,
			'timeout' => $timeout,
			'http_errors' => false,
		];
		if ($method === 'GET' && !empty($payload)) {
			$options['query'] = $payload;
		} elseif ($payload !== null) {
			$options['json'] = $payload;
		}
        $options['verify'] = false;
        $maxAttempts = 3;
        $baseDelayMs = 250;
        $retryStatusCodes = [429, 500, 502, 503, 504];
		$client = new Client();
        $attempt = 1;
        while ($attempt <= $maxAttempts) {
            try {
                $response = $client->request($method, $url, $options);
                $status = $response->getStatusCode();
                $rawBody = (string) $response->getBody();
                $parsedHeaders = [];
                foreach ($response->getHeaders() as $key => $values) {
                    $parsedHeaders[$key] = implode(', ', $values);
                }
                $decodedBody = json_decode($rawBody, true);
                $body = json_last_error() === JSON_ERROR_NONE ? $decodedBody : $rawBody;
                if (in_array($status, $retryStatusCodes, true) && $attempt < $maxAttempts) {
                    $delayMs = $baseDelayMs * (2 ** ($attempt - 1));
                    usleep($delayMs * 1000);
                    $attempt++;
                    continue;
                }
                return [
                    'success' => $status >= 200 && $status < 300,
                    'status' => $status,
                    'headers' => $parsedHeaders,
                    'body' => $body,
                    'raw_body' => $rawBody,
                    'error' => null,
                ];
            } catch (GuzzleException $e) {
                if ($attempt < $maxAttempts) {
                    $delayMs = $baseDelayMs * (2 ** ($attempt - 1));
                    usleep($delayMs * 1000);
                    $attempt++;
                    continue;
                }
                return [
                    'success' => false,
                    'status' => null,
                    'headers' => [],
                    'body' => null,
                    'raw_body' => null,
                    'error' => $e->getMessage(),
                ];
            }
        }
        return [
            'success' => false,
            'status' => null,
            'headers' => [],
            'body' => null,
            'raw_body' => null,
            'error' => 'Request failed after max retry attempts.',
        ];
	}
}
