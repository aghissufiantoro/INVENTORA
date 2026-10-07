import './bootstrap';

import 'select2';
import 'select2/dist/css/select2.css';
import $ from 'jquery';

window.$ = $;
window.jQuery = $;

document.addEventListener('DOMContentLoaded', function () {
    $('.select2').select2();

    const kategoriModal = document.getElementById('kategoriModal');
    const kategoriNama = document.getElementById('kategoriNama');
    const kategoriSelect = document.getElementById('kategori');

    window.openKategoriModal = function () {
        document.getElementById('kategoriModal').classList.remove('hidden');
        document.getElementById('kategoriMsg').classList.add('hidden');
    };

    window.closeKategoriModal = function () {
        document.getElementById('kategoriModal').classList.add('hidden');
        document.getElementById('kategoriNama').value = '';
    };

    window.submitKategori = function () {
        const nama = document.getElementById('kategoriNama').value;
        fetch('/kategori-ajax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ nama })
        })
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('kategori');
                const option = document.createElement('option');
                option.value = data.kategori.nama;
                option.text = data.kategori.nama;
                option.selected = true;
                select.appendChild(option);

                document.getElementById('kategoriMsg').classList.remove('hidden');
                setTimeout(() => {
                    closeKategoriModal();
                }, 1000);
            })
            .catch(err => {
                alert('Gagal tambah kategori');
            });
    };
});
