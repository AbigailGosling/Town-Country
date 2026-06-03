<?php

namespace App\Console\Commands;

use App\Helpers\GraphHopperHelper;
use App\Models\ClientAddress;
use Illuminate\Console\Command;

class geocode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:geocode {id}';

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
        $ca = ClientAddress::find($id);
        $location = GraphHopperHelper::geocodeAddress(GraphHopperHelper::formatAddressForGeocoding($ca));
        if ($location !== null) {
            $ca->lat = $location['lat'];
            $ca->lon = $location['lon'];
        }
        $ca->geocoding_tried = 1;
        $ca->save();
        return Command::SUCCESS;
    }
}
