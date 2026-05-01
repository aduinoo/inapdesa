<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PagesController extends Controller
{
    public function homepage(): View
    {
        [$homestays, $homestayImages] = $this->activeHomestaysWithImages(4);

        return view('profile.landingPages.homepage', [
            'homestays' => $homestays,
            'homestayImages' => $homestayImages,
        ]);
    }

    public function maps(): View
    {
        [$homestays, $homestayImages] = $this->activeHomestaysWithImages();

        $mapHomestays = $homestays->map(function ($homestay) use ($homestayImages) {
            $primaryImage = $homestayImages[$homestay->homestay_id][0]->image_path
                ?? 'assets/images/homepage/home.jpg';

            return [
                'id' => $homestay->homestay_id,
                'name' => $homestay->homestay_name,
                'description' => $homestay->description,
                'price' => $homestay->base_price,
                'max_guest' => $homestay->max_guest,
                'city' => $homestay->city,
                'state' => $homestay->state,
                'latitude' => $homestay->latitude ? (float) $homestay->latitude : null,
                'longitude' => $homestay->longitude ? (float) $homestay->longitude : null,
                'address' => trim(implode(', ', array_filter([
                    $homestay->address,
                    $homestay->street,
                    $homestay->city,
                    $homestay->zipcode,
                    $homestay->state,
                    'Malaysia',
                ]))),
                'image' => asset($primaryImage),
            ];
        })->values();

        return view('profile.landingPages.maps', [
            'mapHomestays' => $mapHomestays,
        ]);
    }

    private function activeHomestaysWithImages(?int $limit = null): array
    {
        $query = DB::table('homestays')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        $homestays = $query->get();

        $homestayImages = DB::table('homestay_images')
            ->whereIn('homestay_id', $homestays->pluck('homestay_id'))
            ->orderBy('created_at')
            ->get()
            ->groupBy('homestay_id');

        return [$homestays, $homestayImages];
    }
}
