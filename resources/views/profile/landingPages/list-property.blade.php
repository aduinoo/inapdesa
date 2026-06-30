@extends('layouts.app')

@section('content')
    @php
        $hostCtaRoute = auth()->check()
            ? match ((int) auth()->user()->role) {
                1 => route('admin.dashboard'),
                2 => '#owner-application',
                3 => route('owner.myHomestay'),
                default => route('dashboard'),
            }
            : route('login');
        $hostCtaLabel = auth()->check()
            ? match ((int) auth()->user()->role) {
                1 => 'Review Applications',
                2 => 'Apply To Become A Host',
                3 => 'Go to Hosting Dashboard',
                default => 'Continue to Your Account',
            }
            : 'Get Started';
    @endphp

    <div class="overflow-hidden bg-[#f5f0de]">
        <section class="relative isolate">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(76,175,80,0.20),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(250,204,21,0.18),_transparent_28%)]"></div>
            <div class="relative mx-auto grid max-w-7xl gap-10 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:py-24">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.45em] text-green-700/70">Host With RumaRehat</p>
                    <h1 class="mt-5 max-w-2xl text-4xl font-semibold leading-tight text-[#173423] sm:text-5xl lg:text-6xl">
                        Turn your homestay into a destination guests remember.
                    </h1>
                    <p class="mt-6 max-w-xl text-base leading-7 text-gray-600">
                        Share your space, highlight village experiences, and welcome travellers looking for stays with local character. This page is a UI landing page for future hosting onboarding.
                    </p>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ $hostCtaRoute }}"
                            class="inline-flex items-center justify-center rounded-full bg-green-700 px-7 py-3.5 text-sm font-semibold text-white shadow-lg shadow-green-900/20 transition hover:bg-green-800">
                            {{ $hostCtaLabel }}
                        </a>
                        <a href="{{ route('contact') }}"
                            class="inline-flex items-center justify-center rounded-full border border-[#b8c7b1] bg-white/80 px-7 py-3.5 text-sm font-semibold text-gray-700 transition hover:border-green-600 hover:text-green-700">
                            Speak to our team
                        </a>
                    </div>

                    <div class="mt-10 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-[24px] bg-white/80 p-5 shadow-[0_16px_40px_rgba(40,60,35,0.08)] ring-1 ring-black/5 backdrop-blur">
                            <p class="text-3xl font-semibold text-[#173423]">3 steps</p>
                            <p class="mt-2 text-sm text-gray-600">Simple path from listing setup to your first booking.</p>
                        </div>
                        <div class="rounded-[24px] bg-white/80 p-5 shadow-[0_16px_40px_rgba(40,60,35,0.08)] ring-1 ring-black/5 backdrop-blur">
                            <p class="text-3xl font-semibold text-[#173423]">Local-first</p>
                            <p class="mt-2 text-sm text-gray-600">Showcase culture, food, and authentic village hospitality.</p>
                        </div>
                        <div class="rounded-[24px] bg-white/80 p-5 shadow-[0_16px_40px_rgba(40,60,35,0.08)] ring-1 ring-black/5 backdrop-blur">
                            <p class="text-3xl font-semibold text-[#173423]">Flexible</p>
                            <p class="mt-2 text-sm text-gray-600">Manage availability, pricing, and guest expectations your way.</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -left-6 top-10 h-32 w-32 rounded-full bg-green-200/60 blur-3xl"></div>
                    <div class="absolute -right-6 bottom-4 h-40 w-40 rounded-full bg-amber-200/70 blur-3xl"></div>
                    <div class="relative overflow-hidden rounded-[34px] bg-[#173423] p-4 shadow-[0_24px_80px_rgba(17,42,30,0.26)]">
                        <img src="{{ asset('assets/images/homepage/hero.jpg') }}" alt="Host with RumaRehat"
                            class="h-[420px] w-full rounded-[28px] object-cover opacity-85">
                        <div class="absolute inset-x-10 bottom-10 rounded-[28px] border border-white/10 bg-white/12 p-6 text-white backdrop-blur-md">
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-100/80">Hosting Preview</p>
                            <h2 class="mt-3 text-2xl font-semibold">Invite guests into a stay that feels personal.</h2>
                            <p class="mt-3 text-sm leading-6 text-green-50/90">
                                Build trust with clear amenities, beautiful photos, and nearby experiences that make your property stand out.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 pb-16 lg:pb-24">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-green-100 text-green-700">
                        <i class="fa-solid fa-house-circle-check text-lg"></i>
                    </div>
                    <h3 class="mt-5 text-2xl font-semibold text-[#173423]">Create your listing</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Add property details, photos, house rules, and nearby attractions to tell a fuller story about your stay.
                    </p>
                </div>

                <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                        <i class="fa-solid fa-calendar-days text-lg"></i>
                    </div>
                    <h3 class="mt-5 text-2xl font-semibold text-[#173423]">Set your hosting flow</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Control dates, pricing, guest counts, and expectations so you can host confidently without losing flexibility.
                    </p>
                </div>

                <div class="rounded-[30px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                        <i class="fa-solid fa-comments text-lg"></i>
                    </div>
                    <h3 class="mt-5 text-2xl font-semibold text-[#173423]">Welcome your guests</h3>
                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Stay responsive, provide local tips, and create a memorable experience that brings repeat visitors and stronger reviews.
                    </p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 pb-16 lg:pb-24">
            <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="rounded-[32px] bg-gradient-to-br from-[#173423] via-[#1d4f33] to-[#255a3d] p-8 text-white shadow-[0_24px_70px_rgba(18,42,31,0.22)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-100/70">Why owners join</p>
                    <h2 class="mt-4 text-3xl font-semibold">Hosting tools designed for village stays and local experiences.</h2>
                    <div class="mt-8 space-y-4 text-sm text-green-50/90">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">Present your homestay with a stronger visual first impression.</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">Highlight cultural activities, food experiences, and community value.</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">Keep everything in one place when you are ready to manage bookings.</div>
                    </div>
                </div>

                <div class="rounded-[32px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-700/70">Host journey</p>
                    <h2 class="mt-4 text-3xl font-semibold text-[#173423]">What this page can lead into next</h2>
                    <div class="mt-8 space-y-5">
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#eef5e7] font-semibold text-green-700">1</div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Register or sign in</h3>
                                <p class="mt-1 text-sm leading-6 text-gray-600">New hosts can create an account, while existing users can continue from their dashboard.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#eef5e7] font-semibold text-green-700">2</div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Complete property details</h3>
                                <p class="mt-1 text-sm leading-6 text-gray-600">Add rooms, amenities, address, photos, and the story behind the stay.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#eef5e7] font-semibold text-green-700">3</div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Publish when ready</h3>
                                <p class="mt-1 text-sm leading-6 text-gray-600">Once your listing is approved, guests can discover and book it through the platform.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 rounded-[28px] border border-[#d9e4d2] bg-[#f7fbf3] p-6">
                        <h3 class="text-xl font-semibold text-[#173423]">Ready to continue?</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            The hosting flow is not fully functional yet, but the navigation is now connected and ready for the next implementation step.
                        </p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $hostCtaRoute }}"
                                class="inline-flex items-center justify-center rounded-full bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800">
                                {{ $hostCtaLabel }}
                            </a>
                            <a href="{{ route('home-page') }}"
                                class="inline-flex items-center justify-center rounded-full border border-[#b8c7b1] px-6 py-3 text-sm font-semibold text-gray-700 transition hover:border-green-600 hover:text-green-700">
                                Back to homes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="owner-application" class="mx-auto max-w-7xl px-6 pb-16 lg:pb-24">
            <div class="grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
                <div class="rounded-[32px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                    <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-700/70">Become A Host</p>
                    <h2 class="mt-4 text-3xl font-semibold text-[#173423]">Apply to upgrade from customer to homestay owner.</h2>
                    <p class="mt-4 text-sm leading-7 text-gray-600">
                        Submit your personal details and IC document for admin review. Once approved, your account role will be upgraded to owner so you can add listings.
                    </p>

                    @if (session('success'))
                        <div class="mt-6 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mt-8 space-y-4 text-sm text-gray-600">
                        <div class="rounded-2xl border border-[#e9e2d5] bg-[#fcfaf4] px-5 py-4">Provide accurate personal information.</div>
                        <div class="rounded-2xl border border-[#e9e2d5] bg-[#fcfaf4] px-5 py-4">Upload your IC document so the admin can verify your identity.</div>
                        <div class="rounded-2xl border border-[#e9e2d5] bg-[#fcfaf4] px-5 py-4">Admin reviews your application and approves your host access.</div>
                    </div>
                </div>

                <div class="rounded-[32px] bg-white p-8 shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                    @guest
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-700/70">Start Here</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[#173423]">Sign in first to apply.</h2>
                        <p class="mt-4 text-sm leading-7 text-gray-600">
                            You need a customer account before you can request an owner upgrade.
                        </p>
                        <a href="{{ route('login') }}"
                            class="mt-8 inline-flex items-center justify-center rounded-full bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800">
                            Log in or sign up
                        </a>
                    @elseif ((int) $user->role === 1)
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-blue-700/70">Admin Access</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[#173423]">Owner access is approved by admin review.</h2>
                        <p class="mt-4 text-sm leading-7 text-gray-600">
                            User 2 customers apply here to become user 3 homestay owners. As user 1 admin, you can review, approve, or reject those applications from the admin dashboard.
                        </p>
                        <a href="{{ route('admin.dashboard') }}"
                            class="mt-8 inline-flex items-center justify-center rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                            Open admin dashboard
                        </a>
                    @elseif ((int) $user->role === 3)
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-700/70">Owner Access</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[#173423]">Your owner account is already active.</h2>
                        <p class="mt-4 text-sm leading-7 text-gray-600">
                            You can go straight to your owner dashboard and start managing your homestays.
                        </p>
                        <a href="{{ route('owner.myHomestay') }}"
                            class="mt-8 inline-flex items-center justify-center rounded-full bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800">
                            Open owner dashboard
                        </a>
                    @elseif (! $user->hasVerifiedEmail())
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-700/70">Verification Needed</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[#173423]">Verify your email before applying.</h2>
                        <p class="mt-4 text-sm leading-7 text-gray-600">
                            Email verification is required before we can accept an owner application.
                        </p>
                        <a href="{{ route('verification.notice') }}"
                            class="mt-8 inline-flex items-center justify-center rounded-full bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                            Verify email
                        </a>
                    @elseif ($ownerApplication && $ownerApplication->status === 'pending')
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-700/70">Application Pending</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[#173423]">Your application is under review.</h2>
                        <p class="mt-4 text-sm leading-7 text-gray-600">
                            Our admin team is reviewing your IC and personal information. We will update your role once approved.
                        </p>
                        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Submitted on {{ \Carbon\Carbon::parse($ownerApplication->updated_at)->format('d M Y, h:i A') }}
                        </div>
                    @else
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-700/70">Owner Application Form</p>
                        <h2 class="mt-4 text-3xl font-semibold text-[#173423]">{{ $ownerApplication && $ownerApplication->status === 'rejected' ? 'Resubmit your application' : 'Apply now' }}</h2>
                        <p class="mt-4 text-sm leading-7 text-gray-600">
                            Fill in your personal information and upload your IC document for admin approval.
                        </p>

                        @if ($ownerApplication && $ownerApplication->status === 'rejected' && $ownerApplication->admin_notes)
                            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                Previous admin note: {{ $ownerApplication->admin_notes }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('owner-applications.store') }}" enctype="multipart/form-data" class="mt-8 space-y-5">
                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input name="full_name" type="text" value="{{ old('full_name', $ownerApplication->full_name ?? $user->name) }}" required
                                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input name="phone_number" type="text" value="{{ old('phone_number', $ownerApplication->phone_number ?? '') }}" required
                                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    @error('phone_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">IC Number</label>
                                    <input name="ic_number" type="text" value="{{ old('ic_number', $ownerApplication->ic_number ?? '') }}" required
                                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    @error('ic_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                                    <input name="date_of_birth" type="date" value="{{ old('date_of_birth', $ownerApplication->date_of_birth ?? '') }}"
                                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    @error('date_of_birth') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Address Line</label>
                                <input name="address_line" type="text" value="{{ old('address_line', $ownerApplication->address_line ?? '') }}" required
                                    class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                @error('address_line') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid gap-5 sm:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">City</label>
                                    <input name="city" type="text" value="{{ old('city', $ownerApplication->city ?? '') }}" required
                                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">State</label>
                                    <input name="state" type="text" value="{{ old('state', $ownerApplication->state ?? '') }}" required
                                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    @error('state') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Postcode</label>
                                    <input name="postcode" type="text" value="{{ old('postcode', $ownerApplication->postcode ?? '') }}" required
                                        class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                    @error('postcode') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Occupation</label>
                                <input name="occupation" type="text" value="{{ old('occupation', $ownerApplication->occupation ?? '') }}"
                                    class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">
                                @error('occupation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Hosting Experience / Notes</label>
                                <textarea name="hosting_experience" rows="4"
                                    class="mt-1 w-full rounded-xl border-gray-300 focus:border-green-600 focus:ring-green-600">{{ old('hosting_experience', $ownerApplication->hosting_experience ?? '') }}</textarea>
                                @error('hosting_experience') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Upload IC</label>
                                    <input name="ic_document" type="file" {{ $ownerApplication ? '' : 'required' }}
                                        class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                                    @error('ic_document') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Supporting Document (Optional)</label>
                                    <input name="supporting_document" type="file"
                                        class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                                    @error('supporting_document') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full rounded-full bg-green-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-green-800">
                                {{ $ownerApplication && $ownerApplication->status === 'rejected' ? 'Resubmit application' : 'Submit application' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@if (session('success'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Application submitted',
                    text: 'Your form has been submitted successfully and is now waiting for admin approval.',
                    confirmButtonColor: '#15803d',
                });
            });
        </script>
    @endpush
@endif
