<meta name="csrf-token" content="{{ csrf_token() }}">

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

<div id="editHomestayModal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white w-full max-w-4xl rounded-2xl max-h-[90vh] overflow-hidden relative">
        <div class="flex justify-between items-center px-8 py-5 border-b">
            <h2 class="text-2xl font-semibold">Edit Homestay</h2>
            <button id="closeEditModalBtn" class="text-xl text-gray-400">&times;</button>
        </div>

        <div class="px-8 py-6 overflow-y-auto max-h-[70vh]">
            <form method="POST"
                  action="{{ route('owner.homestays.update') }}"
                  enctype="multipart/form-data"
                  id="editHomestayForm"
                  autocomplete="off"
                  data-location-picker="edit">

                @csrf
                @method('PATCH')

                <input type="hidden" name="homestay_id" id="edit_homestay_id">
                <input type="hidden" name="latitude" id="edit_latitude">
                <input type="hidden" name="longitude" id="edit_longitude">

                <div class="space-y-6">
                    <div>
                        <label for="edit_homestay_name" class="block text-sm font-medium text-gray-500 mb-1">
                            Homestay Name
                        </label>
                        <input id="edit_homestay_name"
                               name="homestay_name"
                               autocomplete="off"
                               class="w-full rounded-xl border px-4 py-3"
                               placeholder="Ex: Cozy Family Homestay">
                    </div>

                    <div>
                        <label for="edit_description" class="block text-sm font-medium text-gray-500 mb-1">
                            Description
                        </label>
                        <textarea id="edit_description"
                                  name="description"
                                  class="w-full rounded-xl border px-4 py-3"
                                  placeholder="Description"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_address" class="block text-sm font-medium text-gray-500 mb-1">
                                House / Unit No
                            </label>
                            <input id="edit_address"
                                   name="address"
                                   autocomplete="off"
                                   autocorrect="off"
                                   autocapitalize="words"
                                   spellcheck="false"
                                   placeholder="Ex: No. 29 or Unit 3A"
                                   class="w-full rounded-xl border px-4 py-3">
                        </div>

                        <div class="relative">
                            <label for="edit_street" class="block text-sm font-medium text-gray-500 mb-1">
                                Street
                            </label>
                            <input id="edit_street"
                                   name="street"
                                   autocomplete="off"
                                   autocorrect="off"
                                   autocapitalize="words"
                                   spellcheck="false"
                                   placeholder="Ex: Jalan Hijau 6/6"
                                   required
                                   class="w-full rounded-xl border px-4 py-3">
                            <div id="edit_address_suggestions"
                                class="hidden absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-2xl border border-[#d8e5d8] bg-white shadow-xl">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_city" class="block text-sm font-medium text-gray-500 mb-1">City</label>
                            <input id="edit_city"
                                   name="city"
                                   autocomplete="off"
                                   class="rounded-xl border px-4 py-3 w-full"
                                   placeholder="City">
                        </div>
                        <div>
                            <label for="edit_zipcode" class="block text-sm font-medium text-gray-500 mb-1">Postcode</label>
                            <input id="edit_zipcode"
                                   name="zipcode"
                                   autocomplete="off"
                                   class="rounded-xl border px-4 py-3 w-full"
                                   placeholder="Postcode">
                            <p class="mt-2 text-xs text-gray-500">
                                Enter a postcode to re-center the map and autofill the area details.
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="edit_state" class="block text-sm font-medium text-gray-500 mb-1">
                            State
                        </label>
                        <select id="edit_state"
                                name="state"
                                class="w-full rounded-xl border px-4 py-3">
                            <option value="">Select state</option>
                            @foreach ($malaysiaStates as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-2xl border border-[#d8e5d8] bg-[#f5faf5] p-4 sm:p-5">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Adjust homestay pin</h3>
                                <p class="text-sm text-gray-600">
                                    Edit the street and the pin will follow. You can also set the house or unit number separately and drag the pin to fine-tune the map.
                                </p>
                            </div>
                            <div class="rounded-full bg-white px-4 py-2 text-xs font-medium text-green-700 shadow-sm"
                                id="editLocationCoords">
                                Coordinates will appear here
                            </div>
                        </div>

                        <div id="editHomestayMap" class="mt-4 h-[320px] rounded-2xl border border-[#cfe0cf]"></div>

                        <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-gray-500" id="editLocationStatus">
                                Update the address or drag the marker to fine-tune the location.
                            </p>
                            <button type="button"
                                class="rounded-full border border-green-200 bg-white px-4 py-2 text-xs font-medium text-green-700 hover:bg-green-50"
                                data-location-action="edit-reverse">
                                Use current pin to fill address
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">
                            Base Price & Max Guest
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <input id="edit_base_price"
                                   name="base_price"
                                   type="number"
                                   step="0.01"
                                   class="rounded-xl border px-4 py-3">
                            <input id="edit_max_guest"
                                   name="max_guest"
                                   type="number"
                                   class="rounded-xl border px-4 py-3">
                        </div>
                    </div>

                    <div>
                        <label for="edit_status" class="block text-sm font-medium text-gray-500 mb-1">
                            Status
                        </label>
                        <select id="edit_status"
                                name="status"
                                class="w-full rounded-xl border px-4 py-3">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mt-8">
                    <div class="flex justify-between items-center mb-2">
                        <label class="text-sm font-medium text-gray-500">
                            Amenities
                        </label>
                        <button type="button" id="editAddAmenityBtn" class="text-green-600 text-sm">
                            + Add amenity
                        </button>
                    </div>

                    <div id="editAmenitiesTags" class="flex flex-wrap gap-2"></div>

                    <div id="editAmenityInputWrapper" class="hidden mt-3 flex gap-2">
                        <input id="editAmenityInput"
                               type="text"
                               class="flex-1 rounded-xl border px-4 py-2"
                               placeholder="e.g Wifi">
                        <button type="button"
                                id="editSaveAmenityBtn"
                                class="px-4 py-2 rounded-xl bg-green-600 text-white">
                            Add
                        </button>
                    </div>
                </div>

                <div class="mt-8">
                    <label class="block text-sm font-medium text-gray-500 mb-2">
                        Existing Images
                    </label>
                    <div id="editExistingImages" class="flex gap-4 overflow-x-auto pb-2"></div>
                </div>

                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-500 mb-2">
                        Add New Images
                    </label>

                    <input id="imageInput"
                           type="file"
                           name="images[]"
                           multiple
                           accept="image/*"
                           class="w-full rounded-xl border px-4 py-3">

                    <p id="imageCounter" class="text-xs text-gray-400 mt-1">
                        No images selected
                    </p>

                    <div id="imagePreview" class="flex gap-4 mt-4 overflow-x-auto"></div>
                </div>

                <div class="flex justify-end gap-4 mt-8 border-t pt-4">
                    <button type="button" id="cancelEditModalBtn" class="px-5 py-2 rounded-xl border">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-blue-600 text-white">
                        Update Homestay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.amenity-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 9999px;
    border: 1.5px solid #22c55e;
    background: #f0fdf4;
    color: #15803d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('editHomestayModal');
    const tags = document.getElementById('editAmenitiesTags');
    const inputWrapper = document.getElementById('editAmenityInputWrapper');
    const input = document.getElementById('editAmenityInput');
    const saveBtn = document.getElementById('editSaveAmenityBtn');
    const addBtn = document.getElementById('editAddAmenityBtn');
    const imageInput = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');
    const counter = document.getElementById('imageCounter');
    const existingImages = document.getElementById('editExistingImages');

    let amenitiesSet = new Set();
    let filesArray = [];

    document.querySelectorAll('.openEditModal').forEach((btn) => {
        btn.onclick = () => {
            const data = JSON.parse(btn.dataset.homestay);
            const amenities = JSON.parse(btn.dataset.amenities || '[]');
            const images = JSON.parse(btn.dataset.images || '[]');

            edit_homestay_id.value = data.homestay_id;
            edit_homestay_name.value = data.homestay_name;
            edit_description.value = data.description ?? '';
            const legacyAddress = (data.address ?? '').trim();
            const legacyStreet = (data.street ?? '').trim();
            const splitLegacyAddress = legacyStreet === legacyAddress && legacyAddress.includes(',')
                ? legacyAddress.split(',').map((value) => value.trim()).filter(Boolean)
                : [];

            edit_address.value = splitLegacyAddress.length ? splitLegacyAddress.shift() : legacyAddress;
            edit_street.value = splitLegacyAddress.length
                ? splitLegacyAddress.join(', ')
                : (legacyStreet && legacyStreet !== legacyAddress ? legacyStreet : '');
            edit_city.value = data.city;
            edit_zipcode.value = data.zipcode;
            edit_state.value = data.state;
            edit_latitude.value = data.latitude ?? '';
            edit_longitude.value = data.longitude ?? '';
            edit_base_price.value = data.base_price;
            edit_max_guest.value = data.max_guest;
            edit_status.value = data.status;

            tags.innerHTML = '';
            amenitiesSet.clear();
            amenities.forEach((amenity) => addAmenityTag(amenity.amenity_name, amenity.amenity_id));

            existingImages.innerHTML = '';
            images.forEach((img) => {
                existingImages.innerHTML += `
                <div class="relative min-w-[160px] h-[200px] border rounded-xl overflow-hidden">
                    <img src="/${img.image_path}" class="w-full h-full object-cover">
                    <button type="button"
                            data-id="${img.image_id}"
                            class="delete-image-btn absolute top-2 right-2 bg-white rounded-full px-2">
                        X
                    </button>
                </div>`;
            });

            filesArray = [];
            imageInput.value = '';
            preview.innerHTML = '';
            counter.textContent = 'No images selected';

            modal.classList.remove('hidden');
            window.refreshHomestayLocationPicker?.('edit');
        };
    });

    closeEditModalBtn.onclick =
    cancelEditModalBtn.onclick = () => modal.classList.add('hidden');

    addBtn.onclick = () => inputWrapper.classList.toggle('hidden');

    saveBtn.onclick = () => {
        if (!input.value.trim()) {
            return;
        }

        addAmenityTag(input.value.trim());
        input.value = '';
    };

    function addAmenityTag(name, id = null) {
        if (amenitiesSet.has(name.toLowerCase())) {
            return;
        }

        amenitiesSet.add(name.toLowerCase());

        const tag = document.createElement('div');
        tag.className = 'amenity-tag';
        tag.innerHTML = `
            ${name}
            <button type="button">&times;</button>
            <input type="hidden" name="amenities[]" value="${id ?? name}">
        `;
        tag.querySelector('button').onclick = () => {
            amenitiesSet.delete(name.toLowerCase());
            tag.remove();
        };
        tags.appendChild(tag);
    }

    imageInput.onchange = (event) => {
        Array.from(event.target.files).forEach((file) => filesArray.push(file));
        const dataTransfer = new DataTransfer();
        filesArray.forEach((file) => dataTransfer.items.add(file));
        imageInput.files = dataTransfer.files;

        counter.textContent = `${filesArray.length} image(s) selected`;

        preview.innerHTML = '';
        filesArray.forEach((file) => {
            const reader = new FileReader();
            reader.onload = (loadEvent) => {
                preview.innerHTML += `
                <div class="relative min-w-[140px] h-[180px] border rounded-xl overflow-hidden">
                    <img src="${loadEvent.target.result}" class="w-full h-full object-cover">
                </div>`;
            };
            reader.readAsDataURL(file);
        });
    };

    document.addEventListener('click', (event) => {
        const btn = event.target.closest('.delete-image-btn');
        if (!btn) {
            return;
        }

        fetch(`/homestays/image/${btn.dataset.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        }).then(() => btn.closest('div').remove());
    });
});
</script>
