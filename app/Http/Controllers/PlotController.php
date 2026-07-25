<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlotRequest;
use App\Http\Requests\UpdatePlotRequest;
use App\Models\Block;
use App\Models\Plot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $plots = Plot::with('block')
            ->when($request->filled('plot_number'), function ($query) use ($request) {
                $query->where('plot_number', 'like', '%'.$request->plot_number.'%');
            })
            ->when($request->filled('block_id'), function ($query) use ($request) {
                $query->where('block_id', $request->block_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $blocks = Block::orderBy('name')->get();

        return view('plots.index', compact('plots', 'blocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $plot = new Plot();
        $blocks = Block::orderBy('name')->get();

        return view('plots.create', compact('plot', 'blocks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlotRequest $request): RedirectResponse
    {
        Plot::create($this->preparePlotData($request));

        return redirect()
            ->route('plots.index')
            ->with('success', 'Plot added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plot $plot): View
    {
        $blocks = Block::orderBy('name')->get();

        return view('plots.edit', compact('plot', 'blocks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlotRequest $request, Plot $plot): RedirectResponse
    {
        $plot->update($this->preparePlotData($request));

        return redirect()
            ->route('plots.index')
            ->with('success', 'Plot updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plot $plot): RedirectResponse
    {
        $plot->delete();

        return redirect()
            ->route('plots.index')
            ->with('success', 'Plot deleted successfully.');
    }

    /**
     * Turn a validated request into the data array we save to the database:
     * fill in unchecked checkboxes as false, and calculate total_price
     * from the three amount fields instead of trusting user input for it.
     */
    private function preparePlotData(StorePlotRequest|UpdatePlotRequest $request): array
    {
        $data = $request->validated();

        $data['corner_plot'] = $request->boolean('corner_plot');
        $data['park_facing'] = $request->boolean('park_facing');
        $data['main_boulevard'] = $request->boolean('main_boulevard');
        $data['development_charges'] = $data['development_charges'] ?? 0;
        $data['extra_charges'] = $data['extra_charges'] ?? 0;

        $data['total_price'] = $data['plot_price'] + $data['development_charges'] + $data['extra_charges'];

        return $data;
    }
}
