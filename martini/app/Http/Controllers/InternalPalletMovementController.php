<?php

namespace App\Http\Controllers;

use App\Models\InternalPalletMovement;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InternalPalletMovementController extends Controller
{
    public function index(): View
    {
        $user = User::find(Auth::id());
        $movements = InternalPalletMovement::where('processed', false)
            ->orderBy('created_at');
        if ($user->hasPermission('view-all-internal-pallet-movement')) {
            $movements = $movements->get();
        } else {
            $movements = $movements
                ->where(function ($query) use ($user) {
                    $query->where('from_location_id', $user->location_id)
                        ->orWhere('to_location_id', $user->location_id);
                })
                ->get();
        }
        return view('internal-pallet-movements.index', [
            'movements' => $movements,
        ]);
    }

    public function accept(InternalPalletMovement $internalPalletMovement): RedirectResponse
    {
        $authorizationFailure = $this->authorizeMovementProcessing($internalPalletMovement);
        if ($authorizationFailure !== null) {
            return $authorizationFailure;
        }

        $internalPalletMovement->update([
            'processed' => true,
            'accepted' => true,
            'movement_processed_by' => Auth::id(),
        ]);

        return redirect()
            ->route('internal-pallet-movements.index')
            ->with('success', 'Internal pallet movement accepted.');
    }

    public function reject(InternalPalletMovement $internalPalletMovement): RedirectResponse
    {
        $authorizationFailure = $this->authorizeMovementProcessing($internalPalletMovement);
        if ($authorizationFailure !== null) {
            return $authorizationFailure;
        }

        $internalPalletMovement->update([
            'processed' => true,
            'accepted' => false,
            'movement_processed_by' => Auth::id(),
        ]);
        $internalPalletMovement->pallet->update([
            'storage_location' => $internalPalletMovement->fromLocation->id,
        ]);
        return redirect()
            ->route('internal-pallet-movements.index')
            ->with('success', 'Internal pallet movement rejected.');
    }

    private function authorizeMovementProcessing(InternalPalletMovement $internalPalletMovement): ?RedirectResponse
    {
        if ($internalPalletMovement->processed) {
            return redirect()
                ->route('internal-pallet-movements.index')
                ->with('error', 'This internal pallet movement has already been processed.');
        }

        $user = User::find(Auth::id());

        if ($user->hasPermission('view-all-internal-pallet-movement')) {
            return null;
        }

        $matchesUserLocation = (int) $internalPalletMovement->from_location_id === (int) $user->location_id
            || (int) $internalPalletMovement->to_location_id === (int) $user->location_id;

        if ($matchesUserLocation) {
            return null;
        }

        return redirect()
            ->route('internal-pallet-movements.index')
            ->with('error', 'You do not have permission to process this internal pallet movement.');
    }
}
