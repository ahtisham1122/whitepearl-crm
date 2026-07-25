<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlockRequest;
use App\Http\Requests\UpdateBlockRequest;
use App\Models\Block;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $blocks = Block::withCount([
            'plots',
            'plots as available_plots_count' => fn ($query) => $query->where('status', 'Available'),
            'plots as sold_plots_count' => fn ($query) => $query->where('status', 'Sold'),
            'plots as reserved_plots_count' => fn ($query) => $query->where('status', 'Reserved'),
        ])
            ->latest()
            ->paginate(10);

        return view('blocks.index', compact('blocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $block = new Block();

        return view('blocks.create', compact('block'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlockRequest $request): RedirectResponse
    {
        Block::create($request->validated());

        return redirect()
            ->route('blocks.index')
            ->with('success', 'Block added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Block $block): View
    {
        return view('blocks.edit', compact('block'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlockRequest $request, Block $block): RedirectResponse
    {
        $block->update($request->validated());

        return redirect()
            ->route('blocks.index')
            ->with('success', 'Block updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Block $block): RedirectResponse
    {
        if ($block->plots()->exists()) {
            return redirect()
                ->route('blocks.index')
                ->with('error', 'This block still has plots in it. Remove or reassign its plots before deleting it.');
        }

        $block->delete();

        return redirect()
            ->route('blocks.index')
            ->with('success', 'Block deleted successfully.');
    }
}
