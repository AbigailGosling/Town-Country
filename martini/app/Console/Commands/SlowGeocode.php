<?php

namespace App\Console\Commands;

use App\Helpers\GraphHopperHelper;
use App\Models\ClientAddress;
use App\Models\ClientType;
use App\Models\Customer;
use Illuminate\Console\Command;
use Wrench\Client;

class SlowGeocode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:slow_geocode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $toGeocode = [];
        foreach (Customer::where("disabled",0)->get() as $customer)
        {
            $cas = ClientAddress::where([["client_id",$customer->id],["client_type",ClientType::CUSTOMER->value],["collection",false],["geocoding_tried",false]])->get();
            foreach ($cas as $ca)
            {
                if ($ca->postcode != null || $ca->postcode != "" || $ca->postcode != "''" || $ca->postcode != "\'\'" || $ca->postcode != "\\'\\'")
                {
                    $toGeocode[] = $ca;
                }
                else
                {
                    $ca->geocoding_tried = 1;
                    $ca->save();
                }
            }
        }
        foreach ($toGeocode as $ca)
        {
            $location = GraphHopperHelper::geocodeAddress(GraphHopperHelper::formatAddressForGeocoding($ca));
            if ($location == null)
            {
                sleep(1);
                $location = GraphHopperHelper::geocodeAddress($ca->postcode.", United Kingdom");
            }
            if ($location !== null) {
                $ca->lat = $location['lat'];
                $ca->lon = $location['lon'];
            }
            $ca->geocoding_tried = 1;
            $ca->save();
            sleep(1);
        }
        return Command::SUCCESS;
    }
}
