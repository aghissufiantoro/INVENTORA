<!-- kategori/modal.blade.php -->
<div class="modal" id="addCategoryModal" tabindex="-1" role="dialog" style="display:none;">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="padding: 20px;">
            <h5>Tambah Kategori</h5>
            <input type="text" id="newCategoryName" placeholder="Nama Kategori" />
            <div style="margin-top: 15px;">
                <button type="button" onclick="addCategory()">Simpan</button>
                <button type="button" onclick="hideAddCategoryModal()">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showAddCategoryModal() {
        document.getElementById('addCategoryModal').style.display = 'block';
    }

    function hideAddCategoryModal() {
        document.getElementById('addCategoryModal').style.display = 'none';
    }

    function addCategory() {
        let name = document.getElementById('newCategoryName').value.trim();
        if (!name) {
            alert('Nama kategori tidak boleh kosong');
            return;
        }
        fetch('/kategoris', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    name: name
                })
            }).then(response => response.json())
            .then(data => {
                let dropdown = document.getElementById('category-dropdown');
                let option = document.createElement('option');
                option.value = data.id;
                option.text = data.name;
                dropdown.add(option);
                dropdown.value = data.id;
                hideAddCategoryModal();
                document.getElementById('newCategoryName').value = '';
            })
            .catch(() => alert('Gagal menambah kategori'));
    }
</script>
