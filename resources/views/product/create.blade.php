@extends('layouts.app')

@section('content')
    <h2 class="text-2xl font-bold mb-4">Tambah Produk</h2>

    <form action="{{ route('product.store') }}" method="POST" class="space-y-4">
        @csrf
        @include('product.form')
        <!-- ... Form product ... -->

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Simpan</button>
        <a href="{{ route('product.index') }}" class="text-gray-600 ml-2">Batal</a>
    </form>
@endsection

@section('scripts')
<script>
    function closeKategoriModal() {
        document.getElementById('kategoriModal').classList.add('hidden');
        document.getElementById('kategoriNama').value = '';
    }

    function submitKategori() {
        let namaKategori = document.getElementById('kategoriNama').value;

        if (!namaKategori.trim()) {
            alert("Nama kategori tidak boleh kosong.");
            return;
        }

        fetch("{{ route('kategori.storeAjax') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ nama: namaKategori })
        })
        .then(response => response.json())
        .then(data => {
            if (data.kategori) {
                // Tambah opsi baru ke dropdown kategori
                let kategoriSelect = document.getElementById('category-dropdown');
                let option = document.createElement('option');
                option.value = data.kategori.id;
                option.text = data.kategori.nama;
                option.selected = true;
                kategoriSelect.appendChild(option);

                closeKategoriModal();
            } else {
                alert(data.message || "Gagal menambahkan kategori.");
            }
        })
        .catch(error => {
            console.error(error);
            alert("Terjadi kesalahan saat menyimpan kategori.");
        });
    }
</script>
@endsection
