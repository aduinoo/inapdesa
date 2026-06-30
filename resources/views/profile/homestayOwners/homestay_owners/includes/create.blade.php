@php
    $malaysiaStates = [
        'Johor',
        'Kedah',
        'Kelantan',
        'Melaka',
        'Negeri Sembilan',
        'Pahang',
        'Perak',
        'Perlis',
        'Pulau Pinang',
        'Sabah',
        'Sarawak',
        'Selangor',
        'Terengganu',
        'Kuala Lumpur',
        'Putrajaya',
        'Labuan',
    ];
@endphp

<div id="homestayModal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-4xl rounded-2xl relative max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between px-8 py-5 border-b">
            <h2 class="text-2xl font-semibold text-gray-800">Add Homestay</h2>
            <button id="closeModalBtn" class="text-gray-400 text-xl hover:text-gray-600">&times;</button>
        </div>

        <div class="px-8 py-6 overflow-y-auto max-h-[70vh]">
            <form method="POST"
                action="{{ route('owner.homestays.store') }}"
                enctype="multipart/form-data"
                id="createHomestayForm"
                autocomplete="off"
                data-location-picker="create">
                @csrf

                <input type="hidden" name="latitude" id="create_latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="create_longitude" value="{{ old('longitude') }}">

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Homestay Name</label>
                    <input type="text" name="homestay_name"
                        value="{{ old('homestay_name') }}"
                        placeholder="Ex: Cozy Family Homestay"
                        required
                        class="w-full rounded-xl border px-4 py-3">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full rounded-xl border px-4 py-3">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">House / Unit No</label>
                        <input type="text" name="address" id="create_address"
                            value="{{ old('address') }}"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="words"
                            spellcheck="false"
                            placeholder="Ex: No. 29 or Unit 3A"
                            class="w-full rounded-xl border px-4 py-3">
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-500 mb-2">Street</label>
                        <input type="text" name="street" id="create_street"
                            value="{{ old('street') }}"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="words"
                            spellcheck="false"
                            placeholder="Ex: Jalan Hijau 6/6"
                            required class="w-full rounded-xl border px-4 py-3">
                        <div id="create_address_suggestions"
                            class="hidden absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-2xl border border-[#d8e5d8] bg-white shadow-xl">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">City</label>
                        <input type="text" name="city" id="create_city"
                            value="{{ old('city') }}"
                            autocomplete="off"
                            required class="w-full rounded-xl border px-4 py-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-2">Zip Code</label>
                        <input type="text" name="zipcode" id="create_zipcode"
                            value="{{ old('zipcode') }}"
                            autocomplete="off"
                            required class="w-full rounded-xl border px-4 py-3">
                        <p class="mt-2 text-xs text-gray-500">
                            Enter a postcode and we will try to autofill the city, state, and map pin.
                        </p>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">State</label>
                    <select name="state" id="create_state" required class="w-full rounded-xl border px-4 py-3">
                        <option value="">Select state</option>
                        @foreach ($malaysiaStates as $state)
                            <option value="{{ $state }}" {{ old('state') == $state ? 'selected' : '' }}>
                                {{ $state }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-8 rounded-2xl border border-[#d8e5d8] bg-[#f5faf5] p-4 sm:p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Pin homestay location</h3>
                            <p class="text-sm text-gray-600">
                                Typing the street will move the pin automatically. You can also add a house or unit number and drag the pin to fine-tune the location.
                            </p>
                        </div>
                        <div class="rounded-full bg-white px-4 py-2 text-xs font-medium text-green-700 shadow-sm"
                            id="createLocationCoords">
                            Coordinates will appear here
                        </div>
                    </div>

                    <div id="createHomestayMap" class="mt-4 h-[320px] rounded-2xl border border-[#cfe0cf]"></div>

                    <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-gray-500" id="createLocationStatus">
                            Start typing the street or postcode to place the pin.
                        </p>
                        <button type="button"
                            class="rounded-full border border-green-200 bg-white px-4 py-2 text-xs font-medium text-green-700 hover:bg-green-50"
                            data-location-action="create-reverse">
                            Use current pin to fill address
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <input type="number" step="0.01" name="base_price"
                        value="{{ old('base_price') }}"
                        placeholder="Base Price (RM)"
                        required class="rounded-xl border px-4 py-3">

                    <input type="number" name="max_guest"
                        value="{{ old('max_guest') }}"
                        placeholder="Max Guests"
                        required class="rounded-xl border px-4 py-3">
                </div>

                <div class="mb-6">
                    <select name="status" class="w-full rounded-xl border px-4 py-3">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="mb-6">
                    <div class="flex justify-between mb-3">
                        <label class="text-sm font-medium text-gray-500">Amenities</label>
                        <button type="button" id="addAmenityBtn" class="text-green-600 text-sm">+ Add amenity</button>
                    </div>

                    <div id="customAmenities" class="flex flex-wrap gap-2 mt-3"></div>

                    <div id="addAmenityWrapper" class="hidden mt-3 flex gap-2">
                        <input id="amenityInput" class="rounded-xl border px-4 py-2 flex-1"
                            placeholder="e.g Wifi">
                        <button type="button" id="saveAmenityBtn"
                            class="bg-green-600 text-white px-4 rounded-xl">Add</button>
                    </div>
                </div>

                <div class="mb-6">
                    <input type="file" name="images[]" multiple
                        class="w-full rounded-xl border px-4 py-3">
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t">
                    <button type="button" id="cancelModalBtn"
                        class="border px-5 py-2 rounded-xl">Cancel</button>
                    <button type="submit"
                        class="bg-green-600 text-white px-6 py-2 rounded-xl">
                        Save Homestay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .custom-pill {
        padding: 6px 14px;
        border-radius: 9999px;
        border: 1.5px solid #22c55e;
        background: #f0fdf4;
        font-size: 14px;
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('homestayModal');
        const closeBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelModalBtn');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        }

        const addBtn = document.getElementById('addAmenityBtn');
        const wrapper = document.getElementById('addAmenityWrapper');
        const input = document.getElementById('amenityInput');
        const saveBtn = document.getElementById('saveAmenityBtn');
        const container = document.getElementById('customAmenities');

        if (addBtn) {
            addBtn.addEventListener('click', () => {
                wrapper.classList.toggle('hidden');
                input.focus();
            });
        }

        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                const value = input.value.trim();

                if (!value) {
                    return;
                }

                const tag = document.createElement('div');
                tag.className = 'custom-pill';
                tag.innerHTML = `
                    ${value}
                    <button type="button" class="remove">&times;</button>
                    <input type="hidden" name="amenities[]" value="${value}">
                `;

                tag.querySelector('.remove').addEventListener('click', () => {
                    tag.remove();
                });

                container.appendChild(tag);
                input.value = '';
            });
        }
    });
</script>

@if (session('success'))
    <script>
        Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('success') }}' });
    </script>
@endif

@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Form',
            html: `<ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>`
        });
        document.getElementById('homestayModal').classList.remove('hidden');
        window.addEventListener('load', () => window.refreshHomestayLocationPicker?.('create'));
    </script>
@endif
