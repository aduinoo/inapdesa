<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PagesController extends Controller
{
    public function homepage(): View
    {
        [$homestays, $homestayImages] = $this->activeHomestaysWithImages();

        $homestayAmenities = DB::table('homestay_amenities')
            ->whereIn('homestay_id', $homestays->pluck('homestay_id'))
            ->get()
            ->groupBy('homestay_id');

        $amenityNames = DB::table('amenities')
            ->whereIn('amenity_id', $homestayAmenities->flatten()->pluck('amenity_id')->unique())
            ->pluck('amenity_name', 'amenity_id');

        $states = $homestays->pluck('state')->filter()->unique()->sort()->values();

        return view('profile.landingPages.homepage', [
            'homestays' => $homestays,
            'homestayImages' => $homestayImages,
            'homestayAmenities' => $homestayAmenities,
            'amenityNames' => $amenityNames,
            'states' => $states,
        ]);
    }

    public function maps(): View
    {
        [$homestays, $homestayImages] = $this->activeHomestaysWithImages();

        $mapHomestays = $homestays->map(function ($homestay) use ($homestayImages) {
            $primaryImage = data_get($homestayImages, "{$homestay->homestay_id}.0.image_path")
                ?: 'assets/images/homepage/home.jpg';

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

    public function contact(): View
    {
        return view('profile.landingPages.contact');
    }

    public function helpCenter(): View
    {
        return view('profile.landingPages.help-center');
    }

    public function cancellationOptions(): View
    {
        return $this->renderInfoPage(
            'Cancellation Options',
            'Booking Policy',
            'Understand how cancellation requests should be handled between guests, hosts, and the platform team.',
            [
                [
                    'title' => 'Before payment confirmation',
                    'body' => 'If a booking has not been fully confirmed yet, guests should review the booking flow carefully before completing payment.',
                ],
                [
                    'title' => 'After confirmation',
                    'body' => 'For confirmed bookings, guests should contact the host and platform support as early as possible to discuss any change or cancellation request.',
                ],
                [
                    'title' => 'Host communication',
                    'body' => 'Use the booking chat so both guest and host have a clear written record when discussing schedule changes or cancellations.',
                ],
            ]
        );
    }

    public function safetyIssues(): View
    {
        return $this->renderInfoPage(
            'Safety Issues',
            'Safety',
            'RumaRehat encourages guests and hosts to report urgent safety concerns quickly and clearly.',
            [
                [
                    'title' => 'Immediate danger',
                    'body' => 'If there is an emergency or immediate danger, contact local emergency services first before taking any platform action.',
                ],
                [
                    'title' => 'Property-related concern',
                    'body' => 'Document the issue with photos or written notes where appropriate, then contact the host and the platform support team.',
                ],
                [
                    'title' => 'Stay prepared',
                    'body' => 'Guests should review house rules, arrival instructions, and property details before check-in to reduce confusion during the stay.',
                ],
            ]
        );
    }

    public function neighbourhoodConcerns(): View
    {
        return $this->renderInfoPage(
            'Report Neighbourhood Concerns',
            'Community Care',
            'Use this page as guidance when something around a stay affects neighbours, public comfort, or community wellbeing.',
            [
                [
                    'title' => 'What to report',
                    'body' => 'Examples include excessive noise, inappropriate guest behaviour, unsafe parking, or repeated disruption to nearby residents.',
                ],
                [
                    'title' => 'What to include',
                    'body' => 'Share the property name, date, time, and a short factual description of what happened so the team can review it properly.',
                ],
                [
                    'title' => 'Where to escalate',
                    'body' => 'For immediate safety risks, contact local authorities first. For platform follow-up, use the RumaRehat contact channel.',
                ],
            ]
        );
    }

    public function hostingGuide(): View
    {
        return $this->renderInfoPage(
            'Hosting Guide',
            'Hosting',
            'A simple guide for future homestay owners preparing listings, guest expectations, and basic hosting operations.',
            [
                [
                    'title' => 'Create a complete listing',
                    'body' => 'Add clear room details, amenities, pricing, guest limits, house rules, and accurate photos so guests know what to expect.',
                ],
                [
                    'title' => 'Prepare for guest arrival',
                    'body' => 'Keep check-in instructions, contact information, and property directions easy to understand before guests arrive.',
                ],
                [
                    'title' => 'Host with consistency',
                    'body' => 'Good communication, cleanliness, and accurate expectations usually lead to smoother stays and better reviews.',
                ],
            ]
        );
    }

    public function communityForum(): View
    {
        return $this->renderInfoPage(
            'Community Forum',
            'Community',
            'This area is reserved for future conversations between hosts, guests, and the RumaRehat team.',
            [
                [
                    'title' => 'What it will support',
                    'body' => 'Hosts will be able to exchange practical tips, ask setup questions, and share ideas for improving local stay experiences.',
                ],
                [
                    'title' => 'Guest perspective',
                    'body' => 'Community input can also help surface useful travel advice, clearer expectations, and better platform guidance.',
                ],
                [
                    'title' => 'Current status',
                    'body' => 'The forum is not live yet, but this page keeps the navigation connected and ready for that future feature.',
                ],
            ]
        );
    }

    public function privacy(): View
    {
        return $this->renderInfoPage(
            'Privacy',
            'Legal',
            'RumaRehat collects only the information needed to support accounts, bookings, hosting applications, and platform communication.',
            [
                [
                    'title' => 'Account data',
                    'body' => 'Basic profile details such as name, email, and role are used to identify users and support customer, host, or admin access.',
                ],
                [
                    'title' => 'Booking and hosting data',
                    'body' => 'Booking details and owner-application records are used to operate the service and review host eligibility.',
                ],
                [
                    'title' => 'Operational use',
                    'body' => 'Platform data is used to support communication, service updates, and general platform administration.',
                ],
            ]
        );
    }

    public function terms(): View
    {
        return $this->renderInfoPage(
            'Terms',
            'Legal',
            'Using RumaRehat means agreeing to provide accurate information, respect bookings, and use the platform responsibly.',
            [
                [
                    'title' => 'User responsibilities',
                    'body' => 'Guests and hosts should keep submitted details accurate and avoid misuse of bookings, messages, listings, or account roles.',
                ],
                [
                    'title' => 'Host approval flow',
                    'body' => 'Customer accounts must be reviewed and approved by admin before they can be upgraded to owner access.',
                ],
                [
                    'title' => 'Platform access',
                    'body' => 'RumaRehat may update features, workflows, or moderation steps as the platform grows and operational needs change.',
                ],
            ]
        );
    }

    public function listProperty(): View
    {
        $user = auth()->user();

        $ownerApplication = $user && (int) $user->role === 2
            ? DB::table('owner_applications')->where('user_id', $user->id)->first()
            : null;

        return view('profile.landingPages.list-property', [
            'user' => $user,
            'ownerApplication' => $ownerApplication,
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

    private function renderInfoPage(string $title, string $eyebrow, string $lead, array $sections): View
    {
        return view('profile.landingPages.info-page', compact('title', 'eyebrow', 'lead', 'sections'));
    }
}
