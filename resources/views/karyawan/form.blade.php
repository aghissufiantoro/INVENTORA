@csrf

<div class="mb-4">
    <label for="nama" class="block text-sm font-medium text-gray-700">Nama Karyawan</label>
    <input type="text" name="nama" id="nama" value="{{ old('nama', $karyawan->nama ?? '') }}"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500"
        required>
</div>

<div class="mb-4">
    <label for="no_telp" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
    <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $karyawan->no_hp ?? '') }}"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
</div>

<div class="mb-4">
    <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
    <textarea name="alamat" id="alamat" rows="3"
        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('alamat', $karyawan->alamat ?? '') }}</textarea>
</div>