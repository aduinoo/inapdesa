@extends('layouts.app')

@section('content')
    <div class="bg-[#f5f0de]" x-data="{
        search: '',
        openCategory: null,
        openFaqs: [],
        toggleFaq(id) { this.openFaqs.includes(id) ? this.openFaqs = this.openFaqs.filter(i => i !== id) : this.openFaqs.push(id) },
        expandAll() { this.openFaqs = this.filteredFaqs.map(f => f.id) },
        collapseAll() { this.openFaqs = [] },
        faqs: [
            { id: 1, cat: 'general', q: 'What is this platform?', a: 'RumaRehat (branded as InapDesa) is a homestay booking site for Malaysia. Guests browse active listings, check photos and prices, then book through ToyyibPay. Hosts apply via the owner application flow and get approved by admin before they can list properties.' },
            { id: 2, cat: 'general', q: 'Do I need to sign up to browse?', a: 'No. You can see all active homestays, the map view, and attraction pages without logging in. You only need an account to book a stay, chat with a host, save wishlist items, or apply to become a host.' },
            { id: 3, cat: 'general', q: 'How do I filter homestays by state?', a: 'Scroll down on the homepage below the hero section. There is a row of filter buttons — "All states", "Johor", "Kedah", "Selangor", etc. Click any state to show only homestays in that location. Click "All states" to reset.' },
            { id: 4, cat: 'general', q: 'What does "Active stays" mean?', a: 'Only homestays with status "active" show up on the homepage and maps. If a host sets their listing to inactive or it is pending admin review, it will not appear. The count at the top of the homepage shows how many active listings exist right now.' },
            { id: 5, cat: 'booking', q: 'How do I book a homestay?', a: 'Click "View stay" on any listing card. Read the details, amenities, house rules, and guest limit. Scroll to the booking section, pick your check-in and check-out dates from the date picker, make sure the guest count is right, then click "Reserve". You will be taken to ToyyibPay to pay. Once payment goes through, the booking is confirmed.' },
            { id: 6, cat: 'booking', q: 'Can I see photos before I book?', a: 'Yes. Every listing has a gallery of images on the detail page. Some listings also have 360-degree room scans — look for a "360 Preview" or panorama section. Clicking on a scan lets you drag around the room.' },
            { id: 7, cat: 'booking', q: 'What happens after I pay?', a: 'You will get a notification in the bell icon on the top right of the navbar. The status in Dashboard > Bookings will show "Confirmed". The host also gets a notification. You can then message the host from Dashboard > Messages to coordinate check-in.' },
            { id: 8, cat: 'booking', q: 'How do I message the host?', a: 'Go to Dashboard > Messages. Every confirmed booking has its own chat thread. Click on a thread to send messages. The host sees it from their own Messages page. This is the only way to contact the host before your stay.' },
            { id: 9, cat: 'booking', q: 'Can I book for someone else?', a: 'The booking is tied to your account. If you are booking for family or friends, let the host know in the booking chat after payment so they have the right contact details for check-in.' },
            { id: 10, cat: 'booking', q: 'What if the host does not reply?', a: 'If the host has not replied within 24 hours, use the Contact Us page and include your booking ID. The admin team can follow up with the host directly.' },
            { id: 11, cat: 'payment', q: 'What payment method is used?', a: 'All bookings go through ToyyibPay. You can pay using FPX online banking from most Malaysian banks (Maybank, CIMB, Bank Islam, etc.), credit/debit cards, or e-wallet options depending on what ToyyibPay shows at checkout.' },
            { id: 12, cat: 'payment', q: 'Is ToyyibPay a real payment gateway?', a: 'Yes, ToyyibPay is a Malaysian payment gateway registered with Bank Negara Malaysia. In the sandbox/test environment, payments are simulated and no real money moves. On the live site, your payment is processed directly by ToyyibPay and RumaRehat does not store any card details.' },
            { id: 13, cat: 'payment', q: 'When exactly does the charge happen?', a: 'At the moment you click the final confirm button on the ToyyibPay page. The total shown on the booking form is the final amount — no extra fees are added after that.' },
            { id: 14, cat: 'payment', q: 'Can I get my money back if I cancel?', a: 'You submit a cancellation request from Dashboard > Bookings. The host reviews it and can approve or reject. If approved, the refund goes back through ToyyibPay. The amount and timing depend on when you cancel and what the host agrees to.' },
            { id: 15, cat: 'payment', q: 'Payment went through but the booking does not show up', a: 'Check the notification bell and Dashboard > Bookings. If the status is not "Confirmed" after a few minutes, use the Contact Us page with your ToyyibPay transaction reference number (bill code or bill ID). The admin can manually verify and fix it.' },
            { id: 16, cat: 'account', q: 'How do I register?', a: 'Click "Log In" in the navbar, then the "Register" link. Enter your name, email, and password. You will get a verification email — click the link in it to activate your account. Until you verify, you cannot access the dashboard or book anything.' },
            { id: 17, cat: 'account', q: 'The verification email never arrived', a: 'Check spam. If it is not there, log in anyway and the dashboard will show a banner with a "Resend verification email" button. Click that. Make sure you typed the email correctly during registration.' },
            { id: 18, cat: 'account', q: 'I forgot my password', a: 'On the login page, click "Forgot your password?". Enter your email and you will get a reset link. The link expires in 60 minutes. If you do not see the reset email, check spam and try again.' },
            { id: 19, cat: 'account', q: 'How do I change my name or email?', a: 'Click your name in the navbar, then "Profile". You can update your name and email there. If you change your email, you will need to verify the new one again before you can access the full dashboard.' },
            { id: 20, cat: 'account', q: 'Can I delete my account?', a: 'Yes, from the Profile page there is a delete account option. This removes your data permanently. If you have active bookings, cancel or complete them first. Deletion cannot be undone.' },
            { id: 21, cat: 'account', q: 'What do the user roles mean?', a: 'There are three roles: Customer (role 2) — can browse, book, and chat; Owner (role 3) — can list and manage homestays and respond to guests; Admin (role 1) — handles owner applications, reports, and platform moderation. Your role is set when you register (customer) and only changed when admin approves your owner application.' },
            { id: 22, cat: 'hosting', q: 'How do I become a host?', a: 'Click "List Your Property" in the navbar or "Become a Host" on the homepage. You need a verified customer account first. Submit the owner application form with your details. An admin reviews it manually from the admin dashboard. If approved, your role changes to Owner (role 3) and you can start listing properties.' },
            { id: 23, cat: 'hosting', q: 'What info do I need to list my homestay?', a: 'Name, description, full address with coordinates (latitude/longitude — you can search for them on the owner dashboard), base nightly price, max guest count, photos, amenities (WiFi, bathroom, pool, karaoke, etc.), and house rules. You can also upload 360 degree room scans later from the Room Scans page.' },
            { id: 24, cat: 'hosting', q: 'How do I edit my listing after publishing?', a: 'Go to Dashboard > My Homestay. You can update everything — price, name, description, amenities, photos, address, guest limit. Changes appear on the public listing immediately after you save. You can also delete the homestay from the same page (this is permanent).' },
            { id: 25, cat: 'hosting', q: 'How do I see booking requests as a host?', a: 'When a guest books your homestay, you get a notification in the bell icon. Go to Dashboard > Messages to chat with the guest. All confirmed bookings for your property are tracked in your dashboard. You cannot cancel a booking on your end — guests must request cancellations from their side.' },
            { id: 26, cat: 'hosting', q: 'Can I set different prices for weekends?', a: 'Not yet. The system uses a single base nightly price for each homestay. If you want to charge more for peak season, you can manually raise your base price before that period and lower it later. Mention any seasonal pricing in your listing description so guests know.' },
            { id: 27, cat: 'hosting', q: 'What happens if I do not maintain my listing?', a: 'Guests can submit reports (neighbourhood concerns, safety issues, etc.) that go to admin. If multiple reports come in, admin may contact you or suspend your listing. Keep photos current, respond to messages, and keep the property in the condition described in your listing.' },
            { id: 28, cat: 'safety', q: 'There is an emergency at the property — what do I do?', a: 'Call Malaysian emergency services at 999 first. After that, use the booking chat to tell the host or call them directly if they shared a phone number. For non-emergency safety concerns (broken lock, exposed wire, etc.), take photos and submit a report through the Neighbourhood Concerns page. Include the homestay name and booking ID.' },
            { id: 29, cat: 'safety', q: 'How do I submit a neighbourhood concern report?', a: 'Go to Dashboard > Reports (or the "Report neighbourhood concerns" link in the footer). Select the booking related to the issue, describe what happened with time and date, and submit. The owner gets a notification and can reply. Admin can also see the report and step in if needed. The owner can mark the report as "settled" once resolved.' },
            { id: 30, cat: 'safety', q: 'What data does RumaRehat collect from me?', a: 'When you register: name, email, password. When you book: booking dates, homestay ID, and payment reference. When you host: property address and details. When you chat: message content. That is it. No credit card numbers, no location tracking (except the attractions page if you grant location permission), no data sold to anyone.' },
            { id: 31, cat: 'safety', q: 'Do hosts need to meet any safety standards?', a: 'Hosts are expected to provide a safe environment, but there is no formal certification process on the platform yet. Guests should read house rules and check photos carefully. If something looks unsafe at check-in, contact the host immediately and report it if needed.' },
            { id: 32, cat: 'cancellation', q: 'How do I cancel a booking?', a: 'Go to Dashboard > Bookings, find the booking, and click the cancellation option. You will be asked to provide a reason. The cancellation request is sent to the host. The host can approve or reject it. You will get a notification with the outcome.' },
            { id: 33, cat: 'cancellation', q: 'Host approved my cancellation — when do I get refunded?', a: 'The refund goes back through ToyyibPay to your original payment method. The platform does not hold any money — ToyyibPay handles the return. Depending on your bank or e-wallet, it can take 3 to 14 business days.' },
            { id: 34, cat: 'cancellation', q: 'The host cancelled on me — what now?', a: 'Hosts should not cancel confirmed bookings unless there is a genuine emergency. If a host cancels, contact us through the Contact Us page with your booking ID. Admin can investigate and potentially remove the listing.' },
            { id: 35, cat: 'cancellation', q: 'Can I change my booking dates instead of cancelling?', a: 'There is no "modify booking" feature yet. You would need to cancel the existing booking and make a new one for the new dates. Talk to the host through the booking chat first so they know what is happening.' },
            { id: 36, cat: 'platform', q: 'How does the Maps page work?', a: 'The Maps page shows all active homestays as green home icon markers on a Leaflet map. The sidebar on the right lists all homestays as cards. Click a card or a marker — the map flies to that location at zoom level 16 and opens a popup with the listing name, address, price, guest count, and navigation buttons (Google Maps and Waze). Click the popup close button or the "Reset View" button in the header to zoom out to the full Malaysia view. You can also scroll to zoom in/out.' },
            { id: 37, cat: 'platform', q: 'What is the Attraction & Tours page?', a: 'It lists curated tourist spots across Malaysia organised by state. Each attraction has a description. If you allow location access, the page shows attractions near you. You can get directions to any attraction via Google Maps or Waze.' },
            { id: 38, cat: 'platform', q: 'How do I use the wishlist?', a: 'On any homestay detail page, click the wishlist button (heart icon) to save it. Your saved homestays appear under Dashboard > Wishlist. Click the heart again to remove. There is no limit on how many you can save.' },
            { id: 39, cat: 'platform', q: 'How do I contact the RumaRehat team?', a: 'Use the Contact Us page (link in navbar and footer). Fill in your name, email, subject, and message. Admin reviews submissions during business hours. For booking-related issues, include your booking ID in the message.' },
            { id: 40, cat: 'platform', q: 'Is there a phone app?', a: 'No. The site works in any browser on desktop, tablet, or phone. The layout adjusts to smaller screens. There are no plans for a dedicated mobile app right now.' },
        ],
        get filteredFaqs() { return this.faqs.filter(f => (!this.search || f.q.toLowerCase().includes(this.search.toLowerCase()) || f.a.toLowerCase().includes(this.search.toLowerCase()))); },
        get categories() { return [...new Set(this.filteredFaqs.map(f => f.cat))]; },
        faqsByCat(cat) { return this.filteredFaqs.filter(f => f.cat === cat); },
        categoryLabel(cat) { return { general: 'General', booking: 'Booking & Reservations', payment: 'Payments & Refunds', account: 'Account Management', hosting: 'Hosting Your Property', safety: 'Safety & Reporting', cancellation: 'Cancellations', platform: 'Platform & Navigation' }[cat] || cat; }
    }">
        <section class="relative isolate overflow-hidden">
            <div class="absolute inset-0 bg-cover bg-center"
                style="background-image: linear-gradient(90deg, rgba(7, 30, 19, 0.72), rgba(7, 30, 19, 0.42)), url('{{ asset('assets/images/homepage/hero.jpg') }}');">
            </div>
            <div class="relative mx-auto max-w-7xl px-6 py-20 lg:py-28">
                <p class="text-xs font-semibold uppercase tracking-[0.4em] text-green-100/80">Support</p>
                <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-white sm:text-5xl">
                    Help Center
                </h1>
                <p class="mt-6 max-w-3xl text-base leading-7 text-green-50/90">
                    Find answers to common questions about booking, payments, hosting, account management, and more.
                </p>

                <div class="mt-8 max-w-2xl">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" x-model="search" placeholder="Search for answers..."
                            class="w-full rounded-full border border-white/20 bg-white/95 py-4 pl-12 pr-12 text-base text-gray-900 shadow-lg backdrop-blur placeholder:text-gray-400 focus:border-green-600 focus:ring-2 focus:ring-green-200 focus:outline-none">
                        <button type="button" x-show="search.length > 0" @click="search = ''"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <template x-for="cat in categories" :key="cat">
                        <button type="button" @click="openCategory = (openCategory === cat ? null : cat)"
                            class="rounded-full border px-4 py-2.5 text-sm font-medium transition"
                            :class="openCategory === cat
                                ? 'border-green-600 bg-green-600 text-white shadow-md'
                                : 'border-white/30 bg-white/10 text-white hover:bg-white/20'">
                            <span x-text="categoryLabel(cat)"></span>
                        </button>
                    </template>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-6 pb-16 lg:pb-24 pt-10">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-600">
                    Showing <span class="font-semibold text-gray-900" x-text="filteredFaqs.length"></span> answer(s)
                </p>
                <div class="flex gap-2">
                    <button type="button" @click="expandAll()"
                        class="rounded-full border border-[#d9d0be] bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-green-600 hover:text-green-700">
                        Expand all
                    </button>
                    <button type="button" @click="collapseAll()"
                        class="rounded-full border border-[#d9d0be] bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-green-600 hover:text-green-700">
                        Collapse all
                    </button>
                </div>
            </div>

            <template x-for="cat in categories" :key="cat">
                <div x-show="!openCategory || openCategory === cat" x-transition class="mb-10">
                    <h2 class="mb-5 text-2xl font-semibold text-[#173423]" x-text="categoryLabel(cat)"></h2>
                    <div class="space-y-3">
                        <template x-for="faq in faqsByCat(cat)" :key="faq.id">
                            <div class="rounded-2xl bg-white shadow-[0_8px_30px_rgba(40,60,35,0.06)] ring-1 ring-black/5 overflow-hidden">
                                <button @click="toggleFaq(faq.id)"
                                    class="flex w-full items-center justify-between gap-3 px-6 py-4 text-left transition hover:bg-gray-50">
                                    <span class="text-base font-semibold text-gray-900" x-text="faq.q"></span>
                                    <i class="fa-solid text-sm text-gray-400 transition-transform duration-300 shrink-0"
                                        :class="openFaqs.includes(faq.id) ? 'fa-chevron-up rotate-0' : 'fa-chevron-down'"></i>
                                </button>
                                <div x-show="openFaqs.includes(faq.id)" x-transition.opacity.duration.200ms>
                                    <p class="px-6 pb-5 text-sm leading-7 text-gray-600" x-text="faq.a"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="filteredFaqs.length === 0" x-transition class="rounded-[30px] bg-white p-10 text-center shadow-[0_18px_50px_rgba(40,60,35,0.08)] ring-1 ring-black/5">
                <i class="fa-solid fa-magnifying-glass text-3xl text-gray-300 mb-3 block"></i>
                <p class="text-lg font-semibold text-gray-800">No results found</p>
                <p class="mt-2 text-sm text-gray-500">Try a different search term or browse all categories.</p>
                <button type="button" @click="search = ''"
                    class="mt-5 inline-flex items-center gap-2 rounded-full bg-green-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-800">
                    <i class="fa-solid fa-rotate-left"></i>
                    Clear search
                </button>
            </div>

            <div class="mt-8 rounded-[30px] bg-[#173423] p-8 text-white shadow-[0_24px_70px_rgba(21,43,29,0.18)]">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-emerald-200/75">Still need help?</p>
                        <h2 class="mt-2 text-2xl font-semibold">Contact our support team</h2>
                        <p class="mt-2 max-w-md text-sm leading-7 text-white/78">
                            If you could not find the answer you were looking for, reach out to us directly. Our team is here to help with booking issues, hosting questions, and platform guidance.
                        </p>
                    </div>
                    <a href="{{ route('contact') }}"
                        class="inline-flex shrink-0 items-center justify-center rounded-full bg-[#f3d08e] px-6 py-3 text-sm font-semibold text-[#173423] transition hover:bg-[#f0c566]">
                        <i class="fa-solid fa-paper-plane mr-2"></i>
                        Contact support
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
