<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RentalSession;
use App\Models\Unit;
use App\Services\RentalService;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RentalController extends Controller
{
    public function index(RentalService $rentalService): View
    {
        $rentalService->completeExpired();

        return view('admin.rentals.index', ['units' => $this->monitoredUnits()]);
    }

    public function data(RentalService $rentalService): Response
    {
        $rentalService->completeExpired();

        return response($this->renderCards($this->monitoredUnits()));
    }

    public function complete(RentalSession $rental, RentalService $rentalService): RedirectResponse
    {
        $rentalService->complete($rental);

        return back()->with('status', "Rental unit {$rental->unit->name} diselesaikan.");
    }

    public function cancel(RentalSession $rental, RentalService $rentalService): RedirectResponse
    {
        $rentalService->cancel($rental);

        return back()->with('status', "Rental unit {$rental->unit->name} dibatalkan.");
    }

    /**
     * Unit aktif ditambah unit nonaktif yang masih memiliki rental berjalan.
     *
     * @return Collection<int, Unit>
     */
    private function monitoredUnits(): Collection
    {
        return Unit::query()
            ->with(['category', 'activeRentalSession.user'])
            ->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhereHas('rentalSessions', fn ($q2) => $q2->where('status', RentalSession::STATUS_ACTIVE));
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  Collection<int, Unit>  $units
     */
    private function renderCards(Collection $units): string
    {
        /** @var ViewContract $view */
        $view = view('admin.rentals._cards', compact('units'));

        return $view->render();
    }
}
