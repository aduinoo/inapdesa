@extends('layouts.app')

@section('content')
<div class="bg-[#f8f2df]">
    <section class="relative overflow-hidden">
        <div class="h-[320px] bg-cover bg-center sm:h-[380px]"
            style="background-image: linear-gradient(90deg, rgba(7, 30, 19, 0.68), rgba(7, 30, 19, 0.2)), url('{{ asset('assets/images/homepage/hero.jpg') }}');">
        </div>

        <div class="absolute inset-0">
            <div class="mx-auto flex h-full max-w-7xl items-center px-6">
                <div class="max-w-2xl text-white">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-[0.45em] text-green-100/80">RumaRehat Support</p>
                    <h1 class="text-4xl font-bold leading-tight sm:text-5xl">Get in touch with us</h1>
                    <p class="mt-4 max-w-xl text-sm text-green-50/90 sm:text-base">
                        We are here to help with bookings, hosting questions, and anything else you need for your next village stay.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="relative z-10 -mt-14 pb-16 sm:pb-20">
        <div class="mx-auto max-w-7xl px-6">
            <div class="grid gap-6 lg:grid-cols-[1.65fr_minmax(0,0.95fr)]">
                <div class="rounded-[28px] bg-white p-6 shadow-[0_20px_60px_rgba(60,84,43,0.14)] ring-1 ring-black/5 sm:p-8">
                    <div class="mb-8 flex flex-col gap-4 border-b border-[#e7e2d4] pb-6 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-green-700/70">Send a message</p>
                            <h2 class="mt-2 text-3xl font-semibold text-gray-900">Contact our team</h2>
                        </div>
                        <div class="rounded-2xl bg-[#f6fbf5] px-4 py-3 text-sm text-gray-600 ring-1 ring-green-100">
                            Usually replies within 24 hours
                        </div>
                    </div>

                    <form class="space-y-5">
                        <div class="grid gap-5 md:grid-cols-2">
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-gray-800">Name</span>
                                <input type="text" placeholder="Your name"
                                    class="w-full rounded-2xl border border-[#b7c6b6] bg-white px-4 py-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-semibold text-gray-800">Email</span>
                                <input type="email" placeholder="you@example.com"
                                    class="w-full rounded-2xl border border-[#b7c6b6] bg-white px-4 py-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            </label>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-gray-800">Subject</span>
                            <input type="text" placeholder="How can we help?"
                                class="w-full rounded-2xl border border-[#b7c6b6] bg-white px-4 py-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-semibold text-gray-800">Message</span>
                            <textarea rows="7" placeholder="Write your message here..."
                                class="w-full rounded-[24px] border border-[#b7c6b6] bg-white px-4 py-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-100"></textarea>
                        </label>

                        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm text-gray-500">This is a static UI preview for now. Form submission is not connected yet.</p>
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-full bg-green-600 px-8 py-3 text-sm font-semibold text-white shadow-lg shadow-green-800/20 transition hover:bg-green-700">
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="space-y-6">
                    <div class="rounded-[28px] bg-white p-6 shadow-[0_20px_60px_rgba(60,84,43,0.14)] ring-1 ring-black/5 sm:p-7">
                        <h3 class="text-2xl font-semibold text-gray-900">Contact information</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Reach out through any of these channels and our support team will guide you from there.
                        </p>

                        <div class="mt-6 space-y-5">
                            <div class="flex gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f4faef] text-green-700">
                                    <i class="fa-regular fa-envelope text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Email</p>
                                    <p class="text-sm text-gray-600">support@rumarehat.com</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f4faef] text-green-700">
                                    <i class="fa-solid fa-phone text-base"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Phone</p>
                                    <p class="text-sm text-gray-600">+60 3-1234 5678</p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f4faef] text-green-700">
                                    <i class="fa-solid fa-location-dot text-base"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Address</p>
                                    <p class="text-sm leading-6 text-gray-600">
                                        Level 10, Menara RumaRehat<br>
                                        Jalan Pinang, 50450 Kuala Lumpur
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 overflow-hidden rounded-[24px] border border-[#d9e0d4] bg-[#eef5e7]">
                            <div class="flex h-44 items-end justify-between bg-gradient-to-br from-[#dcebd3] via-[#f5f1d6] to-[#d8e2d2] p-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-green-700/70">Office map</p>
                                    <p class="mt-2 text-sm font-semibold text-gray-900">Kuala Lumpur HQ</p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-500 text-white shadow-lg shadow-red-500/30">
                                    <i class="fa-solid fa-location-dot text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center gap-3 text-lg text-gray-700">
                            <a href="#" class="flex h-11 w-11 items-center justify-center rounded-full border border-[#dfe7d9] transition hover:border-green-600 hover:text-green-700">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="flex h-11 w-11 items-center justify-center rounded-full border border-[#dfe7d9] transition hover:border-green-600 hover:text-green-700">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="flex h-11 w-11 items-center justify-center rounded-full border border-[#dfe7d9] transition hover:border-green-600 hover:text-green-700">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="#" class="flex h-11 w-11 items-center justify-center rounded-full border border-[#dfe7d9] transition hover:border-green-600 hover:text-green-700">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>

                    <div class="rounded-[28px] bg-gradient-to-br from-green-900 via-green-800 to-[#163d2a] p-6 text-white shadow-[0_20px_60px_rgba(14,43,29,0.22)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-green-100/70">Need quick help?</p>
                        <h3 class="mt-3 text-2xl font-semibold">We can help with bookings, payments, and hosting support.</h3>
                        <div class="mt-5 grid gap-3 text-sm text-green-50/90">
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Booking assistance</div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Property listing guidance</div>
                            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Community and safety concerns</div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection
