<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sponsor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SponsorController extends Controller
{
    /**
     * Display the sponsor management page.
     */
    public function index(): Response
    {
        $sponsors = Sponsor::orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('admin/KelolaSponsor', [
            'sponsors' => $sponsors,
        ]);
    }

    /**
     * Store a newly created sponsor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_sponsor' => 'required|string|max:100',
            'logo_sponsor' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'link_sponsor' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'nama_sponsor.required' => 'Nama sponsor wajib diisi.',
            'logo_sponsor.required' => 'Logo sponsor wajib diunggah.',
            'logo_sponsor.image' => 'File yang diunggah harus berupa gambar.',
            'logo_sponsor.mimes' => 'Format gambar harus jpeg, png, jpg, gif, svg, atau webp.',
            'logo_sponsor.max' => 'Ukuran logo maksimal adalah 2MB.',
            'link_sponsor.url' => 'Format tautan sponsor harus berupa URL yang valid.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan minimal bernilai 0.',
        ]);

        if ($request->hasFile('logo_sponsor')) {
            $path = $request->file('logo_sponsor')->store('sponsors', 'public');
            $validated['logo_sponsor'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['order'] = $request->input('order', 0) ?? 0;

        Sponsor::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sponsor berhasil ditambahkan!',
        ]);

        return redirect()->back();
    }

    /**
     * Update the specified sponsor.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $sponsor = Sponsor::findOrFail($id);

        $validated = $request->validate([
            'nama_sponsor' => 'required|string|max:100',
            'logo_sponsor' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'link_sponsor' => 'nullable|url|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'nama_sponsor.required' => 'Nama sponsor wajib diisi.',
            'logo_sponsor.image' => 'File yang diunggah harus berupa gambar.',
            'logo_sponsor.mimes' => 'Format gambar harus jpeg, png, jpg, gif, svg, atau webp.',
            'logo_sponsor.max' => 'Ukuran logo maksimal adalah 2MB.',
            'link_sponsor.url' => 'Format tautan sponsor harus berupa URL yang valid.',
            'order.integer' => 'Urutan harus berupa angka.',
            'order.min' => 'Urutan minimal bernilai 0.',
        ]);

        if ($request->hasFile('logo_sponsor')) {
            // Delete old logo
            if ($sponsor->logo_sponsor) {
                Storage::disk('public')->delete($sponsor->logo_sponsor);
            }

            $path = $request->file('logo_sponsor')->store('sponsors', 'public');
            $validated['logo_sponsor'] = $path;
        }

        $validated['is_active'] = $request->boolean('is_active');
        $validated['order'] = $request->input('order', 0) ?? 0;

        $sponsor->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sponsor berhasil diperbarui!',
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified sponsor.
     */
    public function destroy(int $id): RedirectResponse
    {
        $sponsor = Sponsor::findOrFail($id);

        // Delete logo file
        if ($sponsor->logo_sponsor) {
            Storage::disk('public')->delete($sponsor->logo_sponsor);
        }

        $sponsor->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Sponsor berhasil dihapus!',
        ]);

        return redirect()->back();
    }
}
