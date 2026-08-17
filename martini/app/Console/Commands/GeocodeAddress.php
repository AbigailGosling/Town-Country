<?php

namespace App\Console\Commands;

use App\Helpers\GraphHopperHelper;
use App\Models\ClientAddress;
use Illuminate\Console\Command;

class GeocodeAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:geocode_address {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tries to geocode the address for the given id';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->argument('id');
        /** @var ClientAddress $ca */
        $ca = ClientAddress::find($id);
        if (($ca->address_1 ?? '') == '' && ($ca->address_2 ?? '') == ''  && ($ca->postcode ?? '') == '') return Command::SUCCESS;
        $location = GraphHopperHelper::geocodeAddress(GraphHopperHelper::formatAddressForGeocoding($ca));
        if ($location == null || $location["hits"]>1) $location = GraphHopperHelper::geocodeAddress($ca->postcode.", United Kingdom");
        if ($location !== null) {
            $ca->lat = $location['lat'];
            $ca->lon = $location['lon'];
        }
        $ca->geocoding_tried = 1;
        $ca->save();
        return Command::SUCCESS;
    }
}
